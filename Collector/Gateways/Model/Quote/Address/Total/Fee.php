<?php

namespace Collector\Gateways\Model\Quote\Address\Total;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var string
     */
    protected $_code = 'fee';
    /**
     * @var \Collector\Gateways\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * Fee constructor.
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Collector\Gateways\Helper\Data $helperData
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Collector\Gateways\Helper\Data $helperData,
        \Collector\Base\Model\Session $_collectorSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    )
    {
        $this->collectorSession = $_collectorSession;
        $this->_config = $config;
        $this->_helperData = $helperData;
        $this->_urlInterface = $urlInterface;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $checkout = strpos($this->_urlInterface->getCurrentUrl(), 'collectorcheckout') !== false
            && strpos($this->_urlInterface->getCurrentUrl(), 'success') == false;
        $result = [
            'code' => $this->getCode(),
            'title' => __('Invoice Fee'),
            'value' => 0
        ];

        if ($this->_helperData->canApply($quote) && !$checkout && !is_null($quote->getShippingAddress()->getCity())) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $result['value'] = $this->collectorSession->getVariable('btype') == 'b2b' ?
                floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b', $storeScope)) :
                floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c', $storeScope));
        }
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Invoice Fee');
    }
}
