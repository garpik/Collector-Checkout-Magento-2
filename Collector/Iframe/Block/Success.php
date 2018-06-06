<?php

namespace Collector\Iframe\Block;
 
class Success extends \Magento\Checkout\Block\Onepage {
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
     * Success constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param array $layoutProcessors
     */
    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context, 
		array $data = [],
		\Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
		\Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
		\Collector\Iframe\Helper\Data $_helper,
        array $layoutProcessors = []
	){
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
		$this->helper = $_helper;
		$this->shippingRate = $_shippingRate;
        $this->storeManager = $context->getStoreManager();
	}

	public function getStoreManagerObject() {
        return $this->storeManager;
    }
}