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

    protected $storeManager;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingData;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var bool
     */
    private $initialized = false;

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
        array $layoutProcessors = []
    )
    {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->storeManager = $context->getStoreManager();
        $this->pricingData = $pricingData;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $_helper;
        $this->checkoutSession = $_checkoutSession;
        $this->init();
    }

    public function getCheckoutSessionObject()
    {
        return $this->checkoutSession;
    }

    public function getStoreManagerObject()
    {
        return $this->storeManager;
    }

    public function getPricingObject()
    {
        return $this->pricingData;
    }

    public function getConfigObject()
    {
        return $this->scopeConfig;
    }

    public function init()
    {
        if ($this->initialized) {
            return;
        }
        if ($this->checkoutSession->getQuote()->getShippingAddress()->getPostcode() !== null) {
        } else {
            $this->checkoutSession->getQuote()->getBillingAddress()->addData(array(
                'firstname' => 'Kalle',
                'lastname' => 'Anka',
                'street' => 'Ankgatan',
                'city' => 'Ankeborg',
                'country_id' => 'SE',
                'postcode' => '12345',
                'telephone' => '0123456789'
            ));
            $this->checkoutSession->getQuote()->getShippingAddress()->addData(array(
                'firstname' => 'Kalle',
                'lastname' => 'Anka',
                'street' => 'Ankgatan',
                'city' => 'Ankeborg',
                'country_id' => 'SE',
                'postcode' => '12345'
            ));
            $this->checkoutSession->getQuote()->collectTotals();
        }
        $this->checkoutSession->getQuote()->save();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
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
            $_SESSION['collector_applied_discount_code'] = $code;
            return true;
        }
        return false;
    }

    public function getTotals()
    {
        return $this->checkoutSession->getQuote()->getTotals();
    }
}