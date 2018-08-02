<?php

namespace Collector\Gateways\Model\Payment;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Pay In Store payment method model
 */
class Invoice extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'collector_invoice';
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isOffline = false;
    protected $_canAuthorize = true;
    protected $_canCancel = true;
    /**
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $clientFactory;
    /**
     * @var \Collector\Gateways\Helper\Data
     */
    protected $helper;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $collectorLogger;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $collectorApi;

    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * Invoice constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Directory\Helper\Data|null $directory
     * @param \Collector\Gateways\Helper\Data $_helper
     * @param \Collector\Base\Logger\Collector $collectorLogger
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Model\ApiRequest $collectorApi
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory
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
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Directory\Helper\Data $directory = null,
        \Collector\Gateways\Helper\Data $_helper,
        \Collector\Base\Logger\Collector $collectorLogger,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Model\ApiRequest $collectorApi,
        \Collector\Base\Model\Config $collectorConfig,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->collectorConfig = $collectorConfig;
        $this->collectorApi = $collectorApi;
        $this->collectorLogger = $collectorLogger;
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
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function getTitle()
    {
        return "Collector Invoice";
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $info = $this->getInfoInstance();
        $paymentInfo = $info->getAdditionalInformation();
        $order = $payment->getOrder();
        $quote = $order->getQuote();
        $isIframe = false;
        if (!empty($this->collectorSession->getIsIframe(''))) {
            $isIframe = true;
            $payment->setShouldCloseParentTransaction(false);
            $payment->setIsTransactionClosed(false);
            $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
            $payment->addTransactionCommentsToOrder($transaction, "testing");
        }


        if (!$isIframe) {
            $soap = $this->collectorApi->getInvoiceSOAP(['ClientIpAddress' => $payment->getOrder()->getRemoteIp()]);
            if ($order->getBillingAddress()->getCompany()) {
                $storeID = $this->collectorConfig->getB2BStoreID();
            } else {
                $storeID = $this->collectorConfig->getB2CStoreID();
            }

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


            try {
                $resp = $soap->AddInvoice($req);
                if ($resp->InvoiceStatus < 5) {
                    $order->setData('collector_invoice_id', $resp->InvoiceNo);
                    $order->setData('collector_ssn', $paymentInfo['ssn']);
                    $order->setData('fee_amount', $quote->getData('fee_amount'));
                    $order->setData('base_fee_amount', $quote->getData('base_fee_amount'));
                    $payment->setIsTransactionClosed(false);
                }
            } catch (\Exception $e) {
                $this->collectorLogger->error($e->getMessage());
                $this->collectorLogger->error($e->getTraceAsString());
                throw new CouldNotSaveException(
                    __($e->getMessage()),
                    $e
                );
            }
        }
        $this->collectorSession->setIsIframe(false);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $soap = $this->collectorApi->getInvoiceSOAP(['ClientIpAddress' => $payment->getOrder()->getRemoteIp()]);

        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->collectorConfig->getB2BStoreID();
        } else {
            $storeID = $this->collectorConfig->getB2CStoreID();
        }
        if ($order->getGrandTotal() - $order->getTotalInvoiced() == $amount) {
            $req = array(
                'CorrelationId' => $payment->getOrder()->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeID,
            );
            try {
                $soap->ActivateInvoice($req);
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
                $this->collectorLogger->error($e->getMessage());
                $this->collectorLogger->error($e->getTraceAsString());
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
                            if ($product->getPriceType()
                                == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
                            ) {
                                $bundlesWithFixedPrice[] = $item->getItemId();
                            } elseif ($product->getPriceType()
                                == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
                            ) {
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
                        $resp = $soap->PartActivateInvoice($req);
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
                        $this->collectorLogger->error($e->getMessage());
                        $this->collectorLogger->error($e->getTraceAsString());
                        throw new CouldNotSaveException(
                            __($e->getMessage()),
                            $e
                        );
                    }
                }
            }
        }
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->collectorConfig->getB2BStoreID();
        } else {
            $storeID = $this->collectorConfig->getB2CStoreID();
        }
        $soap = $this->collectorApi->getInvoiceSOAP(['ClientIpAddress' => $payment->getOrder()->getRemoteIp()]);

        $req = array(
            'CorrelationId' => $order->getIncrementId(),
            'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
            'InvoiceNo' => $order->getData('collector_invoice_id'),
            'StoreId' => $storeID,
        );
        try {
            $soap->CancelInvoice($req);
        } catch (\Exception $e) {
            $this->collectorLogger->error($e->getMessage());
            $this->collectorLogger->error($e->getTraceAsString());
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->collectorConfig->getB2BStoreID();
        } else {
            $storeID = $this->collectorConfig->getB2CStoreID();
        }
        $soap = $this->collectorApi->getInvoiceSOAP(['ClientIpAddress' => $payment->getOrder()->getRemoteIp()]);

        $req = array(
            'CorrelationId' => $order->getIncrementId(),
            'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
            'InvoiceNo' => $order->getData('collector_invoice_id'),
            'StoreId' => $storeID,
        );
        try {
            $soap->CancelInvoice($req);
        } catch (\Exception $e) {
            $this->collectorLogger->error($e->getMessage());
            $this->collectorLogger->error($e->getTraceAsString());
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->collectorConfig->getB2BStoreID();
        } else {
            $storeID = $this->collectorConfig->getB2CStoreID();
        }

        $soap = $this->collectorApi->getInvoiceSOAP(['ClientIpAddress' => $payment->getOrder()->getRemoteIp()]);
        if ($order->getGrandTotal() == $amount) {
            $req = array(
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCodeNotNull($order->getStore()),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeID,
                'CreditDate' => date("Y-m-d")
            );
            try {
                $soap->CreditInvoice($req);
            } catch (\Exception $e) {
                $this->collectorLogger->error($e->getMessage());
                $this->collectorLogger->error($e->getTraceAsString());
                throw new CouldNotSaveException(
                    __($e->getMessage()),
                    $e
                );
            }
        }
    }
}
