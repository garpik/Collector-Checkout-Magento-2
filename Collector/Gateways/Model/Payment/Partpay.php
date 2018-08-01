<?php

namespace Collector\Gateways\Model\Payment;

/**
 * Pay In Store payment method model
 */


class Partpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'collector_partpay';
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isOffline = false;
    protected $_canAuthorize = false;


    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
    }
}
