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
    protected $helperData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * Fee constructor.
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Collector\Gateways\Helper\Data $helperData
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Collector\Gateways\Helper\Data $helperData,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Model\Config $collectorConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->collectorConfig = $collectorConfig;
        $this->collectorSession = $_collectorSession;
        $this->config = $config;
        $this->helperData = $helperData;
        $this->urlInterface = $urlInterface;
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
        $checkout = strpos($this->urlInterface->getCurrentUrl(), 'collectorcheckout') !== false
            && strpos($this->urlInterface->getCurrentUrl(), 'success') == false;
        $result = [
            'code' => $this->getCode(),
            'title' => __('Invoice Fee'),
            'value' => 0
        ];
        if (!$checkout && !is_null($quote->getShippingAddress()->getCity())) {
            $result['value'] = $this->collectorSession->getBtype('') == \Collector\Base\Model\Session::B2B ?
                $this->collectorConfig->getInvoiceB2BFee() :
                $this->collectorConfig->getInvoiceB2CFee();
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
