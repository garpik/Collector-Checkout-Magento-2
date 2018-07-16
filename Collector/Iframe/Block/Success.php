<?php

namespace Collector\Iframe\Block;

class Success extends \Magento\Checkout\Block\Onepage
{
    protected $storeManager;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * Success constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Quote\Model\Quote\Address\Rate $shippingRate
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param array $data
     * @param array $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Collector\Iframe\Helper\Data $_helper,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\Session $_collectorSession,
        array $data = [],
        array $layoutProcessors = []
    ) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->collectorSession = $_collectorSession;
        $this->logger = $logger;
        $this->helper = $_helper;
        $this->shippingRate = $shippingRate;
        $this->storeManager = $context->getStoreManager();
    }

    public function &getCollectorSession()
    {
        return $this->collectorSession;
    }

    public function getStoreBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
