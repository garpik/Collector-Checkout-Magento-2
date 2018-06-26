<?php

namespace Collector\Iframe\Block;

class Checkout extends \Magento\Checkout\Block\Onepage
{
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    protected $languageArray = [
        "NO" => "nb-NO",
        "SE" => "sv",
        "FI" => "fi-FI",
        "DK" => "en-DK",
        "DE" => "en-DE"
    ];

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;


    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

    /**
     * Checkout constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Checkout\Model\Cart $_cart
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param array $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Checkout\Model\Cart $_cart,
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\ApiRequest $apiRequest,
        array $layoutProcessors = []
    )
    {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->helper = $_helper;
        $this->cart = $_cart;
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getCheckoutUrl()
    {
        if ($this->collectorConfig->getTestMode()) {
            $this->collectorSession->setCollectorUrl("https://checkout-uat.collector.se/collector-checkout-loader.js");
        } else {
            $this->collectorSession->setCollectorUrl("https://checkout.collector.se/collector-checkout-loader.js");
        }
        return $this->collectorSession->getCollectorUrl('');
    }

    public function getLanguage()
    {
        $lang = $this->collectorConfig->getCountryCode();
        if (!empty($this->languageArray[$lang])) {
            $this->collectorSession->setCollectorLanguage($this->languageArray[$lang]);
            return $this->languageArray[$lang];
        }
        return null;
    }

    public function getDataVariant()
    {
        $dataVariant = ' async';
        if ($this->collectorSession->getBtype('') == \Collector\Base\Model\Session::B2B
            || empty($this->collectorSession->getBtype(''))
            && $this->collectorConfig->getCustomerType() == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER) {
            $dataVariant = ' data-variant="b2b" async';
        }
        $this->collectorSession->setCollectorDataVariant($dataVariant);
        return $dataVariant;
    }

    public function getPublicToken()
    {
        if (!empty($this->collectorSession->getCollectorPublicToken())) {
            $this->helper->updateCart();
            $this->helper->updateFees();
            return $this->collectorSession->getCollectorPublicToken();
        }
        if (empty($this->cart->getQuote()->getReservedOrderId())) {
            $this->cart->getQuote()->reserveOrderId()->save();
        }
        $result = $this->apiRequest->getTokenRequest([
            'storeId' => $this->collectorConfig->getB2BrB2CStore(),
            'countryCode' => $this->collectorConfig->getCountryCode(),
            'reference' => $this->cart->getQuote()->getReservedOrderId(),
            'redirectPageUri' => $this->helper->getSuccessPageUrl(),
            'merchantTermsUri' => $this->collectorConfig->getTermsUrl(),
            'notificationUri' => $this->helper->getNotificationUrl(),
            "cart" => ['items' => $this->helper->getProducts()],
            "fees" => $this->helper->getFees()
        ]);
        $this->collectorSession->setCollectorPublicToken($result["data"]["publicToken"]);
        $this->collectorSession->setCollectorPrivateId($result['data']['privateId']);
        $this->cart->getQuote()->setData('collector_private_id', $result['data']['privateId']);
        $this->cart->getQuote()->setData('collector_btype', $this->collectorSession->getBtype());
        $this->cart->getQuote()->save();
        return $publicToken = $result["data"]["publicToken"];
    }
}