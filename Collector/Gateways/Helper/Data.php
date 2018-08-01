<?php

namespace Collector\Gateways\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

    /**
     * Data constructor.
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->apiRequest = $apiRequest;
        parent::__construct($context);
    }

    public function getInvoiceRows($order)
    {
        $rows = [];
        $bundlesWithFixedPrice = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'configurable' ||
                in_array($item->getParentItemId(), $bundlesWithFixedPrice)) {
                continue;
            } elseif ($item->getProductType() == 'bundle') {
                $product = $item->getProduct();
                if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                    $bundlesWithFixedPrice[] = $item->getItemId();
                } elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    continue;
                }
            }
            $itemArr = [
                'ArticleId' => $item->getSku(),
                'Description' => $item->getName(),
                'Quantity' => $item->getQtyOrdered(),
                'UnitPrice' => $this->apiRequest->convert($item->getPriceInclTax(), 'SEK'),
                'VAT' => $item->getTaxPercent()
            ];
            array_push($rows, $itemArr);
        }
        $shipping = [
            'ArticleId' => 'shipping',
            'Description' => substr($order->getShippingDescription(), 0, 50),
            'Quantity' => 1,
            'UnitPrice' => sprintf("%01.2f", $this->apiRequest->convert($order->getBaseShippingInclTax(), 'SEK')),
            'VAT' => sprintf("%01.2f", $order->getBaseShippingTaxAmount() / $order->getBaseShippingAmount() * 100),
        ];
        if ($order->getDiscountAmount() < 0) {
            $code = [
                'ArticleId' => 'discount',
                'Description' => empty($order->getCouponCode()) ? 'no_code' : $order->getCouponCode(),
                'Quantity' => 1,
                'UnitPrice' => sprintf("%01.2f", $this->apiRequest->convert($order->getDiscountAmount(), 'SEK')),
                'VAT' => sprintf(
                    "%01.2f",
                    $order->getDiscountTaxCompensationAmount() / $order->getDiscountAmount() * 100
                ),
            ];
            array_push($rows, $code);
        }
        array_push($rows, $shipping);
        return $rows;
    }

    public function getInvoiceAddress($order)
    {
        return [
            'Address1' => $order->getBillingAddress()->getStreetLine(1),
            'Address2' => $order->getBillingAddress()->getStreetLine(2),
            'COAddress' => $order->getBillingAddress()->getStreetLine(1),
            'City' => $order->getBillingAddress()->getCity(),
            'CountryCode' => $order->getBillingAddress()->getCountryId(),
            'PostalCode' => $order->getBillingAddress()->getPostcode(),
            'CellPhoneNumber' => $order->getBillingAddress()->getTelephone(),
            'CompanyName' => $order->getBillingAddress()->getCompany(),
            'Email' => $order->getBillingAddress()->getEmail(),
            'Firstname' => $order->getBillingAddress()->getFirstname(),
            'Lastname' => $order->getBillingAddress()->getLastname(),
            'PhoneNumber' => $order->getBillingAddress()->getTelephone()
        ];
    }

    public function getDeliveryAddress($order)
    {
        return [
            'Address1' => $order->getShippingAddress()->getStreetLine(1),
            'Address2' => $order->getShippingAddress()->getStreetLine(2),
            'COAddress' => $order->getShippingAddress()->getStreetLine(1),
            'City' => $order->getShippingAddress()->getCity(),
            'CountryCode' => $order->getShippingAddress()->getCountryId(),
            'PostalCode' => $order->getShippingAddress()->getPostcode(),
            'CellPhoneNumber' => $order->getShippingAddress()->getTelephone(),
            'CompanyName' => $order->getShippingAddress()->getCompany(),
            'Email' => $order->getShippingAddress()->getEmail(),
            'Firstname' => $order->getShippingAddress()->getFirstname(),
            'Lastname' => $order->getShippingAddress()->getLastname(),
            'PhoneNumber' => $order->getShippingAddress()->getTelephone()
        ];
    }
}
