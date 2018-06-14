<?php

namespace Collector\Gateways\Model\Payment;

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
    protected $logger;

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
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->logger = $collectorLogger;
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
            $data,
            $directory
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
        if (!empty($this->collectorSession->getVariable('is_iframe'))) {
            $isIframe = true;
            $payment->setIsTransactionClosed(false);
        }
        if (!$isIframe) {
            $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
                'exceptions' => 1, 'trace' => true
            ]);
            if ($order->getBillingAddress()->getCompany()) {
                $storeID = $this->helper->getB2BStoreID();
            } else {
                $storeID = $this->helper->getB2CStoreID();
            }
            $header = array(
                'Username' => $this->helper->getUsername(),
                'Password' => $this->helper->getPassword(),
                'ClientIpAddress' => $this->helper->getRemoteIp($payment)
            );
            $req = array(
                'ActivationOption' => "0",
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->helper->getCountryCode(),
                'Currency' => 'SEK',
                'DeliveryAddress' => $this->helper->getDeliveryAddress($order),
                'InvoiceAddress' => $this->helper->getInvoiceAddress($order),
                'InvoiceDeliveryMethod' => $this->helper->getInvoiceDeliveryMethod(),
                'InvoiceRows' => $this->helper->getInvoiceRows($order),
                'InvoiceType' => $this->helper->getInvoiceType(),
                'OrderDate' => date("Y-m-d"),
                'OrderNo' => $order->getIncrementId(),
                'PurchaseType' => '0',
                'RegNo' => $paymentInfo['ssn'],
                'StoreId' => $storeID
            );
            $header['Username'] = $this->helper->getUsername();
            $header['Password'] = $this->helper->getPassword();
            $header['ClientIpAddress'] = $this->helper->getRemoteIp($payment);
            $headerList = array();
            foreach ($header as $k => $v) {
                $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
            }
            $client->__setSoapHeaders($headerList);

            $this->logger->info("auth " . $payment->getOrder()->getIncrementId() . ": " . var_export($req, true));
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
        $this->collectorSession->setVariable('is_iframe', false);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
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

        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->helper->getB2BStoreID();
        } else {
            $storeID = $this->helper->getB2CStoreID();
        }

        if ($order->getGrandTotal() - $order->getTotalInvoiced() == $amount) {
            $req = array(
                'CorrelationId' => $payment->getOrder()->getIncrementId(),
                'CountryCode' => $this->helper->getCountryCode(),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeID,
            );
            try {
                $client->ActivateInvoice($req);
                $payment->setTransactionId($order->getData('collector_invoice_id'));
                $payment->setParentTransactionId($payment->getTransactionId());
                $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true, "");
                $transaction->setIsClosed(true);
                $order->setData('fee_amount_invoiced', $order->getData('fee_amount'));
                $order->setData('base_fee_amount_invoiced', $order->getData('base_fee_amount'));
            } catch (\Exception $e) {
                $this->logger->info("capture " . $payment->getOrder()->getIncrementId() . ": " . var_export($req, true));
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());

            }
        } else {
            foreach ($payment->getOrder()->getInvoiceCollection() as $invoice) {
                if ($invoice->getState() == null) {
                    $req = array(
                        'CorrelationId' => $payment->getOrder()->getIncrementId(),
                        'CountryCode' => $this->helper->getCountryCode(),
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
                            } elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
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
                    $this->logger->info("part-capture " . $payment->getOrder()->getIncrementId() . ": " . var_export($req, true));
                    try {
                        $resp = $client->PartActivateInvoice($req);
                        $payment->setTransactionId($order->getData('collector_invoice_id'));
                        $payment->setParentTransactionId($payment->getTransactionId());
                        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true, "");
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
        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->helper->getB2BStoreID();
        } else {
            $storeID = $this->helper->getB2CStoreID();
        }
        $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
        $header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
        $req = array(
            'CorrelationId' => $order->getIncrementId(),
            'CountryCode' => $this->helper->getCountryCode(),
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
        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->helper->getB2BStoreID();
        } else {
            $storeID = $this->helper->getB2CStoreID();
        }
        $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
        $header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
        $req = array(
            'CorrelationId' => $order->getIncrementId(),
            'CountryCode' => $this->helper->getCountryCode(),
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
        if ($order->getBillingAddress()->getCompany()) {
            $storeID = $this->helper->getB2BStoreID();
        } else {
            $storeID = $this->helper->getB2CStoreID();
        }
        $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
        $header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
        if ($order->getGrandTotal() == $amount) {
            $req = array(
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->helper->getCountryCode(),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeID,
                'CreditDate' => date("Y-m-d")
            );
            $this->logger->info("refund " . $payment->getOrder()->getIncrementId() . ": " . var_export($req, true));
            try {
                $client->CreditInvoice($req);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
        }
    }
}