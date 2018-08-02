<?php
/**
 * A Magento 2 module named Collector/Iframe
 * Copyright (C) 2017 Collector
 *
 * This file is part of Collector/Iframe.
 *
 * Collector/Iframe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collector\Iframe\Model\Payment;

class BasePayment extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "collector_base";
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isOffline = false;
    protected $_canAuthorize = true;
    protected $_canCancel = true;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;
    /**
     * @var \Collector\Gateways\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $clientFactory;


    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    protected $logger;

    /**
     * BasePayment constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $paymentLogger
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory
     * @param \Collector\Gateways\Helper\Data $_helper
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Collector\Gateways\Helper\Data $_helper,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Base\Model\Config $collectorConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->collectorConfig = $collectorConfig;
        $this->apiRequest = $apiRequest;
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->helper = $_helper;
        $this->clientFactory = $clientFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $paymentLogger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    private function getB2BrB2CStoreId(&$order)
    {
        if ($order->getBillingAddress()->getCompany()) {
            return $this->collectorConfig->getB2BStoreID();
        }
        return $this->collectorConfig->getB2CStoreID();
    }

    public function canRefund()
    {
        return true;
    }

    public function canCapture()
    {
        return true;
    }

    public function canVoid()
    {
        return true;
    }

    public function isOffline()
    {
        return false;
    }

    public function canCancel()
    {
        return true;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //create soapclient, get details
        //get details
        //send addinvoice request
        //if error throw error
        //spara, corelation id och invoice id

        $info = $this->getInfoInstance();
        $paymentInfo = $info->getAdditionalInformation();
        $order = $payment->getOrder();
        $quote = $order->getQuote();
        $isIframe = false;
        if (!empty($this->collectorSession->getIsIframe())) {
            $isIframe = true;
            $payment->setIsTransactionClosed(false);
        }
        if (!$isIframe) {
            $storeID = $this->getB2BrB2CStoreId($order);
            $req = array(
                'ActivationOption' => "0",
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
                'Currency' => 'SEK',
                'DeliveryAddress' => $this->helper->getDeliveryAddress($order),
                'InvoiceAddress' => $this->helper->getInvoiceAddress($order),
                'InvoiceDeliveryMethod' => $this->collectorConfig->getInvoiceDeliveryMethod(),
                'InvoiceRows' => $this->helper->getInvoiceRows($order),
                'InvoiceType' => $this->collectorConfig->getInvoiceType(),
                'OrderDate' => date("Y-m-d"),
                'OrderNo' => $order->getIncrementId(),
                'PurchaseType' => '0',
                'RegNo' => $paymentInfo['ssn'],
                'StoreId' => $storeID
            );

            $client = $this->apiRequest->getInvoiceSOAP(['ClientIpAddress' => $payment->getOrder()->getRemoteIp()]);
            try {
                $resp = $client->AddInvoice($req);
                if ($resp->InvoiceStatus < 5) {
                    $order->setData('collector_invoice_id', $resp->InvoiceNo);
                    $order->setData('collector_ssn', $paymentInfo['ssn']);
                    $order->setData('fee_amount', $quote->getData('fee_amount'));
                    $order->setData('base_fee_amount', $quote->getData('base_fee_amount'));
                    $payment->setIsTransactionClosed(false);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
        }
        $this->collectorSession->setIsIframe(false);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $client = $this->apiRequest->getInvoiceSOAP();
        $storeID = $this->getB2BrB2CStoreId($order);

        if ($order->getGrandTotal() - $order->getTotalInvoiced() == $amount) {
            $req = array(
                'CorrelationId' => $payment->getOrder()->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeID,
            );
            try {
                $client->ActivateInvoice($req);
                $payment->setTransactionId($order->getData('collector_invoice_id'));
                $payment->setParentTransactionId($payment->getTransactionId());
                $transaction = $payment->addTransaction(
                    \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH,
                    null,
                    true,
                    ""
                );
                $transaction->setIsClosed(true);
                $order->setData('fee_amount_invoiced', $order->getData('fee_amount'));
                $order->setData('base_fee_amount_invoiced', $order->getData('base_fee_amount'));
            } catch (\Exception $e) {
                $this->logger->error(var_export($req, true));
                $this->logger->error(
                    "capture " . $payment->getOrder()->getIncrementId() . ": " .
                    var_export($req, true)
                );
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
        } else {
            foreach ($payment->getOrder()->getInvoiceCollection() as $invoice) {
                if ($invoice->getState() == null) {
                    $req = array(
                        'CorrelationId' => $payment->getOrder()->getIncrementId(),
                        'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
                        'InvoiceNo' => $order->getData('collector_invoice_id'),
                        'StoreId' => $storeID,
                        'ArticleList' => array()
                    );
                    $bundlesWithFixedPrice = array();
                    foreach ($invoice->getItemsCollection() as $item) {
                        if ($item->getOrderItem()->getProductType() == 'configurable') {
                            continue;
                        } elseif (in_array($item->getParentItemId(), $bundlesWithFixedPrice)) {
                            continue;
                        } elseif ($item->getOrderItem()->getProductType() == 'bundle') {
                            $product = $item->getOrderItem()->getProduct();
                            if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                                $bundlesWithFixedPrice[] = $item->getItemId();
                            } elseif ($product->getPriceType()
                                == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                                continue;
                            }
                        }
                        if ($item->getQty() < 1) {
                            continue;
                        }
                        array_push($req['ArticleList'], array(
                            'ArticleId' => $item->getSku(),
                            'Description' => $item->getName(),
                            'Quantity' => $item->getQty()
                        ));
                    }
                    if ($order->getData('shipping_invoiced') == 0) {
                        array_push($req['ArticleList'], array(
                            'ArticleId' => "shipping",
                            'Description' => $order->getShippingMethod(),
                            'Quantity' => 1
                        ));
                    }
                    if ($order->getData('fee_amount_invoiced') == 0) {
                        array_push($req['ArticleList'], array(
                            'ArticleId' => "invoice_fee",
                            'Description' => 'Invoice Fee',
                            'Quantity' => 1
                        ));
                    }
                    try {
                        $resp = $client->PartActivateInvoice($req);
                        $payment->setTransactionId($order->getData('collector_invoice_id'));
                        $payment->setParentTransactionId($payment->getTransactionId());
                        $transaction = $payment->addTransaction(
                            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH,
                            null,
                            true,
                            ""
                        );
                        $transaction->setIsClosed(true);
                        $order->setData('fee_amount_invoiced', $order->getData('fee_amount'));
                        $order->setData('base_fee_amount_invoiced', $order->getData('base_fee_amount'));
                        $order->setData('collector_invoice_id', $resp->NewInvoiceNo);
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                        $this->logger->error($e->getTraceAsString());
                    }
                }
            }
        }
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $storeID = $this->getB2BrB2CStoreId($order);
        $client = $this->apiRequest->getInvoiceSOAP();

        $req = array(
            'CorrelationId' => $order->getIncrementId(),
            'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
            'InvoiceNo' => $order->getData('collector_invoice_id'),
            'StoreId' => $storeID,
        );
        try {
            $client->CancelInvoice($req);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $storeID = $this->getB2BrB2CStoreId($order);
        $client = $this->apiRequest->getInvoiceSOAP();

        $req = array(
            'CorrelationId' => $order->getIncrementId(),
            'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
            'InvoiceNo' => $order->getData('collector_invoice_id'),
            'StoreId' => $storeID,
        );
        try {
            $client->CancelInvoice($req);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $storeID = $this->getB2BrB2CStoreId($order);
        $client = $this->apiRequest->getInvoiceSOAP();

        if ($order->getGrandTotal() == $amount) {
            $req = array(
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeID,
                'CreditDate' => date("Y-m-d")
            );
            try {
                $client->CreditInvoice($req);
            } catch (\Exception $e) {
                $this->logger->error(var_export($e->getMessage(), true));
                $this->logger->error(var_export($e->getTraceAsString(), true));
            }
        }
    }
}
