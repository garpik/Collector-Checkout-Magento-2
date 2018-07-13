<?php

namespace Collector\Gateways\Block\Adminhtml;

/**
 * Class PaymentInfo
 * @package Collector\Gateways\Block\Adminhtml
 */
class PaymentInfo extends \Magento\Sales\Block\Adminhtml\Order\View\Tab\Info
{
    /**
     * @return integer
     */
    public function getInvoiceId()
    {
        return $this->getOrder()->getData('collector_invoice_id');
    }
}
