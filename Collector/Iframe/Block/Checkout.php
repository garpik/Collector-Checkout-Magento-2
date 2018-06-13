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
     * Checkout constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Checkout\Model\Cart $_cart
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Logger\Collector $logger
     * @param array $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Checkout\Model\Cart $_cart,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        array $layoutProcessors = []
    )
    {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
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
        if ($this->helper->getTestMode()) {
            $this->collectorSession->setVariable('collector_url', "https://checkout-uat.collector.se/collector-checkout-loader.js");
        } else {
            $this->collectorSession->setVariable('collector_url', "https://checkout.collector.se/collector-checkout-loader.js");
        }
        return $this->collectorSession->getVariable('collector_url');
    }

    public function getLanguage()
    {
        $lang = $this->helper->getCountryCode();
        if (!empty($this->languageArray[$lang])) {
            $this->collectorSession->setVariable('collector_language', $this->languageArray[$lang]);
            return $this->languageArray[$lang];
        }
        return null;
    }

    public function getDataVariant()
    {
        $dataVariant = ' async';
        if ($this->collectorSession->getVariable('btype') == 'b2b'
            || empty($this->collectorSession->getVariable('btype'))
            && $this->helper->getCustomerType() == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER) {
            $dataVariant = ' data-variant="b2b" async';
        }
        $this->collectorSession->setVariable('collector_data_variant', $dataVariant);
        return $dataVariant;
    }

    public function getPublicToken()
    {
        if (!empty($this->collectorSession->getVariable('collector_public_token'))) {
            $this->helper->updateCart();
            $this->helper->updateFees();
            return $this->collectorSession->getVariable('collector_public_token');
        }
        if (empty($this->cart->getQuote()->getReservedOrderId())) {
            $this->cart->getQuote()->reserveOrderId()->save();
        }
        $req = array();

        if ($this->collectorSession->getVariable('btype') == 'b2b'
            || empty($this->collectorSession->getVariable('btype'))
            && $this->helper->getCustomerType() == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER) {

            $this->collectorSession->setVariable('btype', 'b2b');
            $req['storeId'] = $this->helper->getB2BStoreID();
        } else {
            $this->collectorSession->setVariable('btype', 'b2c');
            $req['storeId'] = $this->helper->getB2CStoreID();
        }

        $req['countryCode'] = $this->helper->getCountryCode();
        $req['reference'] = $this->cart->getQuote()->getReservedOrderId();
        $req['redirectPageUri'] = $this->helper->getSuccessPageUrl();
        $req['merchantTermsUri'] = $this->helper->getTermsUrl();
        $req['notificationUri'] = $this->helper->getNotificationUrl();
        $req["cart"] = $this->helper->getProducts();
        $req["fees"] = $this->helper->getFees();


        $result = $this->helper->getTokenRequest($req);



        $this->collectorSession->setVariable('collector_public_token', $result["data"]["publicToken"]);
        $this->collectorSession->setVariable('collector_private_id', $result['data']['privateId']);
        $this->cart->getQuote()->setData('collector_private_id', $result['data']['privateId']);
        $this->cart->getQuote()->setData('collector_btype', $this->collectorSession->getVariable('btype'));
        $this->cart->getQuote()->save();

        return $publicToken = $result["data"]["publicToken"];
    }
}