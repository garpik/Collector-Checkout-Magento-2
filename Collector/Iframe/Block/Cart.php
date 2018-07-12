<?php

namespace Collector\Iframe\Block;

class Cart extends \Magento\Checkout\Block\Onepage
{
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    protected $storeManager = null;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingData;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;


    /**
     * @var \Collector\Base\Helper\Prices
     */
    protected $collectorPriceHelper;

    /**
     * Cart constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Pricing\Helper\Data $pricingData
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Collector\Base\Helper\Prices $collectorPriceHelper
     * @param array $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\Helper\Data $pricingData,
        \Collector\Iframe\Helper\Data $_helper,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\Config $collectorConfig,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Collector\Base\Helper\Prices $collectorPriceHelper,
        array $layoutProcessors = []
    )
    {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->collectorPriceHelper = $collectorPriceHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->collectorConfig = $collectorConfig;
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->storeManager = $context->getStoreManager();
        $this->pricingData = $pricingData;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $_helper;
        $this->checkoutSession = $_checkoutSession;
        $this->init();
    }

    public function getQuoteCouponCode()
    {
        $code = $this->checkoutSession->getQuote()->getCouponCode();
        if (!empty($code)) {
            $this->collectorSession->setCollectorAppliedDiscountCode($code);
        }
        return $code;
    }

    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    public function getCollectorConfig()
    {
        return $this->collectorConfig;
    }

    public function getPricingObject()
    {
        return $this->pricingData;
    }

    public function getConfigObject()
    {
        return $this->scopeConfig;
    }

    public function isShippingAddressEnabled()
    {
        return $this->collectorConfig->isShippingAddressEnabled();
    }

    public function init()
    {
        $defaultData = [
            'firstname' => 'Kalle',
            'lastname' => 'Anka',
            'street' => 'Ankgatan',
            'city' => 'Ankeborg',
            'country_id' => 'SE',
            'postcode' => '12345',
            'telephone' => '0123456789'
        ];

        if ($this->isShippingAddressEnabled()) {
            $this->checkoutSession->getQuote()->getBillingAddress()->addData($defaultData);
        } else {
            $this->checkoutSession->getQuote()->getBillingAddress()->addData($defaultData);
            $this->checkoutSession->getQuote()->getShippingAddress()->addData($defaultData);
            $this->checkoutSession->getQuote()->getShippingAddress()->save();
        }

        $this->checkoutSession->getQuote()->getBillingAddress()->save();


        $this->helper->getShippingMethods();
        $this->checkoutSession->getQuote()->collectTotals();
        $this->checkoutSession->getQuote()->save();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getCheckoutHelper()
    {
        return $this->checkoutHelper;
    }

    public function getProducts()
    {
        return $this->helper->getBlockProducts();
    }

    public function getShippingPrice()
    {
        return $this->helper->getShippingPrice();
    }

    public function hasDiscount()
    {
        return $this->helper->hasDiscount();
    }

    public function getShippingPriceExclFormatting()
    {
        return $this->helper->getShippingPrice(false);
    }

    public function updateShipping()
    {
        if (empty($this->helper->getShippingMethod())) {
            $this->helper->setShippingMethod('');
        }
    }

    public function getShippingMethods()
    {
        return $this->helper->getShippingMethods();
    }

    public function getDiscount()
    {
        return $this->helper->getDiscount();
    }

    public function getTax()
    {
        return $this->helper->getTax();
    }

    public function getGrandTotal()
    {
        return $this->helper->getGrandTotal();
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('collectorcheckout/cajax/cajax');
    }

    public function hasCoupon()
    {
        $code = $this->checkoutSession->getQuote()->getCouponCode();
        if ($code) {
            $this->collectorSession->setCollectorAppliedDiscountCode($code);
            return true;
        }
        return false;
    }

    public function getTotals()
    {
        return $this->collectorPriceHelper->getQuoteTotalsArray($this->checkoutSession->getQuote(), false);
    }
}