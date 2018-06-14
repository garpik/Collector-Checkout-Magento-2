<?php

namespace Collector\Gateways\Cron;

class Partrefund
{

    protected $clientFactory;
    protected $helper;
    protected $creditmemoRepositoryInterface;
    protected $searchCriteriaBuilder;
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * Partrefund constructor.
     * @param \Collector\Gateways\Helper\Data $_helper
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $_clientFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $_creditmemoRepositoryInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder
     * @param \Collector\Base\Logger\Collector $logger
     */
    public function __construct(
        \Collector\Gateways\Helper\Data $_helper,
        \Magento\Framework\Webapi\Soap\ClientFactory $_clientFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $_creditmemoRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder,
        \Collector\Base\Logger\Collector $logger
    )
    {
        $this->logger = $logger;
        $this->helper = $_helper;
        $this->clientFactory = $_clientFactory;
        $this->searchCriteriaBuilder = $_searchCriteriaBuilder;
        $this->creditmemoRepositoryInterface = $_creditmemoRepositoryInterface;
    }

    private function getCreditmemos()
    {
        $results = array();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('collector_refunded', '0', 'eq')->create();
        $creditmemos = $this->creditmemoRepositoryInterface->getList($searchCriteria)->getItems();
        foreach ($creditmemos as $creditmemo) {
            if (strpos($creditmemo->getOrder()->getPayment()->getMethod(), "collector") !== false) {
                if (count($creditmemo->getOrder()->getCreditmemosCollection()) == 1 && $creditmemo->getGrandTotal() == $creditmemo->getOrder()->getGrandTotal()) {
                    $creditmemo->setData('collector_refunded', '1');
                    $creditmemo->save();
                } else {
                    array_push($results, $creditmemo);
                }
            } else {
                $creditmemo->setData('collector_refunded', '1');
                $creditmemo->save();
            }
        }
        return $results;
    }

    public function execute()
    {
        $memos = $this->getCreditmemos();
        if (count($memos) == 0) {
            return;
        }
        $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), [
            'soap_version' => SOAP_1_1,
            'exceptions' => 1,
            'trace' => true
        ]);
        $headerList = [
            new \SoapHeader($this->helper->getHeaderUrl(), 'Username', $this->helper->getUsername()),
            new \SoapHeader($this->helper->getHeaderUrl(), 'Password', $this->helper->getPassword())
        ];

        $client->__setSoapHeaders($headerList);
        foreach ($memos as $memo) {
            $order = $memo->getOrder();
            if ($order->getBillingAddress()->getCompany()) {
                $storeID = $this->helper->getB2BStoreID();
            } else {
                $storeID = $this->helper->getB2CStoreID();
            }
            $req = [
                'CorrelationId' => $memo->getOrder()->getIncrementId(),
                'CountryCode' => $this->helper->getCountryCode(),
                'InvoiceNo' => explode('-', $memo->getTransactionId())[0],
                'StoreId' => $storeID,
                'CreditDate' => date("Y-m-d"),
                'ArticleList' => []
            ];
            $bundlesWithFixedPrice = array();
            foreach ($memo->getItemsCollection() as $item) {
                if ($item->getProductType() == 'configurable') {
                    continue;
                } elseif (in_array($item->getParentItemId(), $bundlesWithFixedPrice)) {
                    continue;
                } elseif ($item->getProductType() == 'bundle') {
                    $product = $item->getProduct();
                    if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                        $bundlesWithFixedPrice[] = $item->getItemId();
                    } elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        continue;
                    }
                }
                array_push($req['ArticleList'], [
                    'ArticleId' => $item->getSku(),
                    'Description' => $item->getName(),
                    'Quantity' => $item->getQty()
                ]);
            }
            if ($memo->getShippingAmount() > 0) {
                array_push($req['ArticleList'], [
                    'ArticleId' => 'shipping',
                    'Description' => $order->getShippingMethod(),
                    'Quantity' => 1
                ]);
            }
            if ($order->getData('fee_amount_invoiced') > 0 && $order->getData('fee_amount_invoiced') > $order->getData('fee_amount_refunded')) {
                array_push($req['ArticleList'], [
                    'ArticleId' => 'invoice_fee',
                    'Description' => 'Invoice Fee',
                    'Quantity' => 1
                ]);
            }
            $this->logger->info("part-credit " . $memo->getOrder()->getIncrementId() . ": " . var_export($req, true));
            try {
                $client->PartCreditInvoice($req);
                $memo->setData('fee_amount', $order->getData('fee_amount_invoiced'));
                $memo->setData('base_fee_amount', $order->getData('fee_amount_invoiced'));
                $order->setData('fee_amount_refunded', $order->getData('fee_amount_invoiced'));
                $memo->setData('collector_refunded', '1');
                $memo->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());

                $memo->setData('collector_refunded', '1');
                $memo->save();
            }
        }
    }
}
