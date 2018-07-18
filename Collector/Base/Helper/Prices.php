<?php

namespace Collector\Base\Helper;

class Prices extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
        return parent::__construct($context);
    }

    public function getQuoteTotalsArray($quote, $format = false)
    {
        $quote->collectTotals();
        $returnTotals = [];
        $cartTotals = $quote->getTotals();
        foreach ($cartTotals as $total) {
            $price = $format ? $this->checkoutHelper->formatPrice($total->getValue()) : $total->getValue();
            $quoteTotals = $quote->getShippingAddress()->getData();
            if ($this->scopeConfig->getValue('tax/cart_display/subtotal') == 1) {
                if (!empty($quoteTotals[$total->getCode()])) {
                    $price = $format ? $this->checkoutHelper->formatPrice($quoteTotals[$total->getCode()]) : $quoteTotals[$total->getCode()];
                }
            } else {
                if (!empty($quoteTotals[$total->getCode() . '_incl_tax'])) {
                    $price = $format ? $this->checkoutHelper->formatPrice($quoteTotals[$total->getCode() . '_incl_tax']) : $quoteTotals[$total->getCode() . '_incl_tax'];
                }
            }
            $returnTotals[$total->getCode()] = [
                'title' => $total->getTitle(),
                'value' => $price
            ];
        }
        return $returnTotals;
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
