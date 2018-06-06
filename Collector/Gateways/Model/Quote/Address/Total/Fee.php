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

    protected $_urlInterface;

    protected $_config;

    /**
     * Fee constructor.
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Collector\Gateways\Helper\Data $helperData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Collector\Gateways\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    )
    {
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
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }
        $checkout = false;
        if (strpos($this->_urlInterface->getCurrentUrl(), 'collectorcheckout') !== false) {
            if (strpos($this->_urlInterface->getCurrentUrl(), 'success') !== false) {
                $checkout = false;
            } else {
                $checkout = true;
            }
        }
        /*	if (isset($_SESSION['col_paymentmethod'])){
                if ($_SESSION['col_paymentmethod'] == 'collector_invoice'){

                }
                else {
                    $checkout = true;
                }
            }
            else {
                $checkout = true;
            }*/
        $fee = 0;
        if ($this->_helperData->canApply($quote) && !$checkout) {
            if (is_null($quote->getShippingAddress()->getCity())) {
                $fee = 0;
            } else {
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                if (isset($_SESSION['btype'])) {
                    if ($_SESSION['btype'] == 'b2b') {
                        $fee = floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b', $storeScope));
                    } else {
                        $fee = floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c', $storeScope));
                    }
                } else {
                    $fee = floatval($this->config->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c', $storeScope));
                }
            }
        }
        /*	$total->setFeeAmount($fee);
            $total->setBaseFeeAmount($fee);*/
        /*	$total->setTotalAmount('fee_amount', $fee);
            $total->setBaseTotalAmount('base_fee_amount', $fee);*/
        /*	$total->setGrandTotal($total->getGrandTotal() + $total->getFeeAmount());
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $total->getBaseFeeAmount());*/
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
                if (isset($_SESSION['btype'])) {
                    if ($_SESSION['btype'] == 'b2b') {
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
