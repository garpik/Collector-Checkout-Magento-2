<?php

namespace Collector\Gateways\Model\Payment;

/**
 * Pay In Store payment method model
 */


class Partpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'collector_partpay';
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isOffline = false;
    protected $_canAuthorize = false;
    protected $clientFactory;
    protected $helper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Collector\Gateways\Helper\Data $_helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
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

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
      
        $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);

        $header = array(
            'Username' => $this->helper->getUsername(),
            'Password' => $this->helper->getPassword(),
            'ClientIpAddress' => $this->helper->getRemoteIp($payment)
        );


    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {

    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

    }
}