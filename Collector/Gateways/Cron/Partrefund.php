<?php

namespace Collector\Gateways\Cron;

class Partrefund
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepositoryInterface;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;
    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * Partrefund constructor.
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $_creditmemoRepositoryInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Base\Model\Config $collectorConfig
     */
    public function __construct(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $_creditmemoRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Base\Model\Config $collectorConfig
    ) {
        $this->collectorConfig = $collectorConfig;
        $this->apiRequest = $apiRequest;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $_searchCriteriaBuilder;
        $this->creditmemoRepositoryInterface = $_creditmemoRepositoryInterface;
    }

    private function getCreditmemos()
    {
        $results = [];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('collector_refunded', '0', 'eq')->create();
        $creditmemos = $this->creditmemoRepositoryInterface->getList($searchCriteria)->getItems();
        foreach ($creditmemos as $creditmemo) {
            if (strpos($creditmemo->getOrder()->getPayment()->getMethod(), "collector") !== false) {
                if (count($creditmemo->getOrder()->getCreditmemosCollection()) == 1
                    && $creditmemo->getGrandTotal() == $creditmemo->getOrder()->getGrandTotal()) {
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
        $client = $this->apiRequest->getInvoiceSOAP();
        foreach ($memos as $memo) {
            $order = $memo->getOrder();
            $storeID = !empty($order->getBillingAddress()->getCompany()) ?
                $this->collectorConfig->getB2BStoreID() :
                $this->collectorConfig->getB2CStoreID();
            $req = [
                'CorrelationId' => $memo->getOrder()->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCode(),
                'InvoiceNo' => explode('-', $memo->getTransactionId())[0],
                'StoreId' => $storeID,
                'CreditDate' => date("Y-m-d"),
                'ArticleList' => []
            ];
            $bundlesWithFixedPrice = [];
            foreach ($memo->getItemsCollection() as $item) {
                if ($item->getProductType() == 'configurable' ||
                    in_array(
                        $item->getParentItemId(),
                        $bundlesWithFixedPrice
                    )) {
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
            if ($order->getData('fee_amount_invoiced') > 0
                && $order->getData('fee_amount_invoiced') > $order->getData('fee_amount_refunded')) {
                array_push($req['ArticleList'], [
                    'ArticleId' => 'invoice_fee',
                    'Description' => 'Invoice Fee',
                    'Quantity' => 1
                ]);
            }
            try {
                $client->PartCreditInvoice($req);
                $memo->setData('fee_amount', $order->getData('fee_amount_invoiced'));
                $memo->setData('base_fee_amount', $order->getData('fee_amount_invoiced'));

                $order->setData('fee_amount_refunded', $order->getData('fee_amount_invoiced'));
                $order->setData('fee_amount_invoiced', 0);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
            $memo->setData('collector_refunded', '1');
            $memo->save();
        }
    }
}
