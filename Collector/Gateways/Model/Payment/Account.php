<?php

namespace Collector\Gateways\Model\Payment;

/**
 * Pay In Store payment method model
 */


class Account extends \Collector\Gateways\Model\Payment\Invoice
{
    protected $_code = 'collector_account';
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isOffline = false;
    protected $_canAuthorize = false;

	
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
		parent::authorize($payment, $amount);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
		parent::capture($payment, $amount);
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
		parent::void($payment, $amount);
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
		parent::refund($payment, $amount);
    }
}