<?php

namespace Collector\Base\Helper;

class Prices extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Helper\Data $checkoutHelper
    )
    {
        $this->checkoutHelper = $checkoutHelper;
        return parent::__construct($context);
    }

    public function getQuoteTotalsArray($quote, $format = false)
    {
        $quote->collectTotals();
        $returnTotals = [];
        $cartTotals = $quote->getTotals();

        foreach ($cartTotals as $total) {
            $returnTotals[$total->getCode()] = ['title' => $total->getTitle(), 'value' => $format ? $this->checkoutHelper->formatPrice($total->getValue()) : $total->getValue()];
        }
        return $returnTotals;
    }

    public function getOrderTotalsArray($order, $format = false)
    {

    }

    public function getQuoteTaxValue($quote, $format = false)
    {
        $totals = $this->getQuoteTotalsArray($quote, false, true);
        return !empty($totals['tax']) && !empty($totals['tax']['value']) ? $totals['tax']['value'] :
            ($format ? $this->checkoutHelper->formatPrice(0) : 0);

        //return $this->checkoutHelper->formatPrice($cartTotals['tax']->getData()['value']);
    }

    public function getQuoteDiscount($quote, $format = false)
    {
        $tax = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        return $format ? $this->checkoutHelper->formatPrice($tax) : $tax;
    }

    public function getQuoteShippingPrice($quote, $format = false)
    {
        $totals = $this->getQuoteTotalsArray($quote, $format);
        $shippingPrice = (isset($totals['shipping']) ? $totals['shipping']['value'] : 0);
        return $format ? $this->checkoutHelper->formatPrice($shippingPrice) : $shippingPrice;
    }

    public function getQuoteGrandTotal($quote, $format = false)
    {
        $quote->collectTotals();
        //$this->getQuoteTotalsArray($quote, false, $format);
        return $format ? $this->checkoutHelper->formatPrice($quote->getGrandTotal()) : $quote->getGrandTotal();
    }

    public function hasQuoteDiscount($quote)
    {
        return $quote->getSubtotal() != $quote->getSubtotalWithDiscount();
    }

}