<?php

namespace Collector\Gateways\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * Data constructor.
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }

    public function getEnable()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function canApply($quote = null)
    {
        return true;
    }

    public function getInfoWSDL()
    {
        return $this->getTestMode() ?
            "https://ecommercetest.collector.se/v3.0/InformationService.svc?singleWsdl" :
            "https://ecommerce.collector.se/v3.0/InformationService.svc?singleWsdl";
    }

    public function getInvoiceWSDL()
    {
        return $this->getTestMode() ?
            "https://ecommercetest.collector.se/v3.0/InvoiceServiceV33.svc?singleWsdl" :
            "https://ecommerce.collector.se/v3.0/InvoiceServiceV33.svc?singleWsdl";
    }

    public function getHeaderUrl()
    {
        return 'http://schemas.ecommerce.collector.se/v30/InvoiceService';
    }

    public function getUsername()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCustomerType()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/customer_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPassword()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/sharedkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTestMode()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/testmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getB2CStoreID()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/b2c_storeid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getB2BStoreID()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/b2b_storeid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRemoteIp($payment)
    {
        return $payment->getOrder()->getRemoteIp();
    }

    public function getCountryCode()
    {
        return $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getInvoiceType()
    {
        return "0";
    }

    public function getInvoiceDeliveryMethod()
    {
        return "2";
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
                'UnitPrice' => $item->getPriceInclTax(),
                'VAT' => $item->getTaxPercent()
            ];
            array_push($rows, $itemArr);
        }
        $shipping = [
            'ArticleId' => 'shipping',
            'Description' => substr($order->getShippingDescription(), 0, 50),
            'Quantity' => 1,
            'UnitPrice' => sprintf("%01.2f", $order->getBaseShippingInclTax()),
            'VAT' => sprintf("%01.2f", $order->getBaseShippingTaxAmount() / $order->getBaseShippingAmount() * 100),
        ];
        if ($order->getDiscountAmount() < 0) {
            $code = [
                'ArticleId' => 'discount',
                'Description' => empty($order->getCouponCode()) ? 'no_code' : $order->getCouponCode(),
                'Quantity' => 1,
                'UnitPrice' => sprintf("%01.2f", $order->getDiscountAmount()),
                'VAT' => sprintf("%01.2f", $order->getDiscountTaxCompensationAmount() / $order->getDiscountAmount() * 100),
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