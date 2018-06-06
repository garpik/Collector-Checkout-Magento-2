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
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        return $this;
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
        $checkout = false;
        if (strpos($this->_urlInterface->getCurrentUrl(), 'collectorcheckout') !== false) {
            if (strpos($this->_urlInterface->getCurrentUrl(), 'success') !== false) {
                $checkout = false;
            } else {
                $checkout = true;
            }
        }
        if ($this->_helperData->canApply($quote) && !$checkout) {
            if (is_null($quote->getShippingAddress()->getCity())) {
                $result = [
                    'code' => $this->getCode(),
                    'title' => __('Invoice Fee'),
                    'value' => 0
                ];
            } else {
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                if (!empty($this->collectorSession->getVariable('btype'))) {
                    if ($this->collectorSession->getVariable('btype') == 'b2b') {
                        $result = [
                            'code' => $this->getCode(),
                            'title' => __('Invoice Fee'),
                            'value' => floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b', $storeScope))
                        ];
                    } else {
                        $result = [
                            'code' => $this->getCode(),
                            'title' => __('Invoice Fee'),
                            'value' => floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c', $storeScope))
                        ];
                    }
                } else {
                    $result = [
                        'code' => $this->getCode(),
                        'title' => __('Invoice Fee'),
                        'value' => floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c', $storeScope))
                    ];
                }
            }
        } else {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Invoice Fee'),
                'value' => 0
            ];
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
