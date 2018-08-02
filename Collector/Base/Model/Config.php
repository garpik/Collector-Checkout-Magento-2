<?php

namespace Collector\Base\Model;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * @var Session
     */
    protected $collectorSession;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Session $collectorSession
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Collector\Base\Model\Session $collectorSession
    ) {
        $this->collectorSession = $collectorSession;
        $this->scopeConfig = $scopeConfig;
    }

    public function getEnable()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAcceptStatus()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/acceptstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getHoldStatus()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/holdstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getDeniedStatus()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/deniedstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTestMode()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/testmode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getShippingTaxClass()
    {
        return $this->scopeConfig->getValue(
            'tax/classes/shipping_tax_class',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getB2CInvoiceFeeTaxClass()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/invoice/invoice_fee_b2c_tax_class',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getUsername()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/username',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomerType()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/customer_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    public function getUpdateCustomer()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/updatecustomer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getShowDiscount()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/styling/showdiscount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPassword()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/sharedkey',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getB2CStoreID()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/b2c_storeid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getB2BStoreID()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/b2b_storeid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCountryCode()
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCountryCodeNotNull($store = null)
    {
        $countryCode = $this->getCountryCode();
        if ($countryCode == null) {
            $countryCode = $this->getStoreCountryCode($store);
            if ($countryCode == null) {
                $countryCode = $this->getDefaultCountryCode();
            }
        }
        return $countryCode;
    }

    public function getStoreCountryCode($store)
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    public function getDefaultCountryCode()
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            'default'
        );
    }

    public function getTermsUrl()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/terms_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getInvoiceB2BFee(): float
    {
        return floatval(
            $this->scopeConfig->getValue(
                'collector_collectorcheckout/invoice/invoice_fee_b2b',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    public function getInvoiceB2CFee(): float
    {
        return floatval(
            $this->scopeConfig->getValue(
                'collector_collectorcheckout/invoice/invoice_fee_b2c',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    public function getWSDL(): string
    {
        return $this->getTestMode() ?
            "https://checkout-api-uat.collector.se" :
            "https://checkout-api.collector.se";
    }

    public function getInvoiceWSDL(): string
    {
        return $this->getTestMode() ?
            "https://ecommercetest.collector.se/v3.0/InvoiceServiceV33.svc?singleWsdl" :
            "https://ecommerce.collector.se/v3.0/InvoiceServiceV33.svc?singleWsdl";
    }

    public function getHeaderUrl(): string
    {
        return 'http://schemas.ecommerce.collector.se/v30/InvoiceService';
    }

    public function isShippingAddressEnabled(): bool
    {
        $isEnabled = $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/shippingaddress',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($isEnabled)) {
            return false;
        }
        return boolval($isEnabled);
    }

    public function getInvoiceType()
    {
        return "0";
    }

    public function getInvoiceDeliveryMethod()
    {
        return "2";
    }

    public function getB2BrB2CStore($btype = null): int
    {
        if (empty($btype)) {
            $btype = $this->collectorSession->getBtype('');
        }
        if ($btype == \Collector\Base\Model\Session::B2B ||
            empty($btype) && $this->getCustomerType() ==
            \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER
        ) {
            $this->collectorSession->setBtype(\Collector\Base\Model\Session::B2B);
            return intval($this->getB2BStoreID());
        }
        $this->collectorSession->setBtype(\Collector\Base\Model\Session::B2C);
        return intval($this->getB2CStoreID());
    }


    public function getHash($path, $json = '')
    {
        return base64_encode($this->getUsername() . ":" . hash("sha256", $json . $path . $this->getPassword()));
    }

    public function createAccount()
    {
        return $this->scopeConfig->getValue(
            'collector_collectorcheckout/general/create_account',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
