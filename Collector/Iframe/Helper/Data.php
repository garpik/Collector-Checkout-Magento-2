<?php

namespace Collector\Iframe\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;
    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $coupon;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfigHelper;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * Data constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Framework\Pricing\Helper\Data $_pricingHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\SalesRule\Model\Coupon $_coupon
     * @param \Magento\Catalog\Helper\Product\Configuration $_productConfigHelper
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Magento\Framework\Message\ManagerInterface $_messageManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Framework\Pricing\Helper\Data $_pricingHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\SalesRule\Model\Coupon $_coupon,
        \Magento\Catalog\Helper\Product\Configuration $_productConfigHelper,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        \Magento\Framework\Message\ManagerInterface $_messageManager
    )
    {
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $_pricingHelper;
        $this->cart = $cart;
        $this->taxCalculation = $taxCalculation;
        $this->shippingRate = $_shippingRate;
        $this->checkoutSession = $_checkoutSession;
        $this->productConfigHelper = $_productConfigHelper;
        $this->messageManager = $_messageManager;
        $this->storeManager = $_storeManager;
        $this->coupon = $_coupon;
        return parent::__construct($context);
    }

    public function getEnable()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAcceptStatus()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/acceptstatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getHoldStatus()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/holdstatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDeniedStatus()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/deniedstatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTestMode()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/testmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getShowOptions()
    {
        return true;
    }

    public function getShippingTaxClass()
    {
        return $this->scopeConfig->getValue('tax/classes/shipping_tax_class', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getB2BInvoiceFeeTaxClass()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b_tax_class', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getB2CInvoiceFeeTaxClass()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c_tax_class', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

    public function getB2CStoreID()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/b2c_storeid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getB2BStoreID()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/b2b_storeid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCountryCode()
    {
        return $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSuccessPageUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . "collectorcheckout/success";
    }

    public function getTermsUrl()
    {
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/terms_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getNotificationUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . "collectorcheckout/notification";
    }

    public function getDiscount()
    {
        return $this->pricingHelper->currency(($this->cart->getQuote()->getSubtotal() - $this->cart->getQuote()->getSubtotalWithDiscount()), true, false);
    }

    public function hasDiscount()
    {
        return ($this->cart->getQuote()->getSubtotal() != $this->cart->getQuote()->getSubtotalWithDiscount());
    }

    public function getTax()
    {
        $this->cart->getQuote()->collectTotals();
        $cartTotals = $this->cart->getQuote()->getTotals();
        return $this->pricingHelper->currency($cartTotals['tax']->getData()['value'], true, false);
    }

    public function getGrandTotal()
    {

        if (empty($this->collectorSession->getVariable('curr_shipping_tax'))) {
            $this->getShippingPrice();
        }
        $this->cart->getQuote()->collectTotals();
        return $this->pricingHelper->currency($this->cart->getQuote()->getGrandTotal(), true, false);
    }

    public function getShippingMethods()
    {
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $shippingTaxClass = $this->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $shippingMethods = array();
        $first = true;
        $methods = $shippingAddress->getGroupedAllShippingRates();
        $selectedIsActive = false;
        if (!empty($this->collectorSession->getVariable('curr_shipping_code'))) {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    if ($rate->getCode() == $this->collectorSession->getVariable('curr_shipping_code')) {
                        $selectedIsActive = true;
                    }
                }
            }
        }
        if (!$selectedIsActive) {
            $this->collectorSession->setVariable('curr_shipping_code', '');
        }
        if (!empty($this->collectorSession->getVariable('curr_shipping_code'))) {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    $shipMethod = [
                        'first' => $first,
                        'code' => $rate->getCode(),
                        'content' => ''
                    ];
                    if ($rate->getCode() == $this->collectorSession->getVariable('curr_shipping_code')) {
                        $first = false;
                        if ($shippingTax == 0) {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency($rate->getPrice(), true, false);
                        } else {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency(($rate->getPrice() * (1 + ($shippingTax / 100))), true, false);
                        }
                        $this->setShippingMethod($rate->getCode());
                    } else {
                        if ($shippingTax == 0) {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency($rate->getPrice(), true, false);
                        } else {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency(($rate->getPrice() * (1 + ($shippingTax / 100))), true, false);
                        }
                    }
                    array_push($shippingMethods, $shipMethod);
                }
            }
        } else {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    $shipMethod = [
                        'first' => $first,
                        'code' => $rate->getCode(),
                        'content' => ''
                    ];
                    if ($first) {
                        $first = false;
                        if ($shippingTax == 0) {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency($rate->getPrice(), true, false);
                        } else {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency(($rate->getPrice() * (1 + ($shippingTax / 100))), true, false);
                        }
                        $this->setShippingMethod($rate->getCode());
                    } else {
                        if ($shippingTax == 0) {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency($rate->getPrice(), true, false);
                        } else {
                            $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency(($rate->getPrice() * (1 + ($shippingTax / 100))), true, false);
                        }
                    }
                    array_push($shippingMethods, $shipMethod);
                }
            }
        }
        return $shippingMethods;
    }

    public function setDiscountCode($code)
    {
        $ruleId = $this->coupon->loadByCode($code)->getRuleId();
        if (!empty($ruleId)) {
            $this->checkoutSession->getQuote()->setCouponCode($code)->collectTotals()->save();
            $this->collectorSession->setVariable('collector_applied_discount_code', $code);
            $this->cart->getQuote()->setData('collector_applied_discount_code', $code);
            $this->cart->getQuote()->save();
            $this->messageManager->addSuccess(__('You used coupon code "%1".', $code));
        } else {
            $this->messageManager->addError(__('The coupon code "%1" is not valid.', $code));
        }
    }

    public function unsetDiscountCode()
    {
        $this->collectorSession->setVariable('collector_applied_discount_code', '');
        $this->cart->getQuote()->setData('collector_applied_discount_code', NULL);
        $this->cart->getQuote()->save();
        $this->messageManager->addSuccess(__('You canceled the coupon code.'));
        $this->checkoutSession->getQuote()->setCouponCode()->collectTotals()->save();
    }

    public function setShippingMethod($methodInput)
    {
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();

        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $methods = $shippingAddress->getGroupedAllShippingRates();
        $shippingTaxClass = $this->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $first = true;
        foreach ($methods as $method) {
            foreach ($method as $rate) {
                if ($rate->getCode() == $methodInput) {
                    $this->collectorSession->setVariable('curr_shipping_description', $rate->getMethodTitle());
                    $this->collectorSession->setVariable('curr_shipping_tax_rate', $shippingTax);
                    $this->collectorSession->setVariable('curr_shipping_price', $rate->getPrice());
                    $this->collectorSession->setVariable('curr_shipping_tax', 0);

                    $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
                    $this->shippingRate->setCode($rate->getCode())->getPrice();
                    try {
                        $this->cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
                    } catch (\Exception $e) {
                    }
                    $this->cart->getQuote()->getShippingAddress()->save();
                    $this->cart->getQuote()->collectTotals();
                    $this->cart->getQuote()->save();
                    $this->cart->getQuote()->collectTotals();
                    $this->cart->getQuote()->getTotals();
                    $first = false;
                    $this->collectorSession->setVariable('curr_shipping_code', $rate->getCode());
                    $this->cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
                    $this->cart->getQuote()->save();
                    break;
                }
            }
            if (!$first) {
                break;
            }
        }
        return $this->pricingHelper->currency($this->collectorSession->getVariable('curr_shipping_price'), true, false);
    }

    public function getShippingPrice($inclFormatting = true)
    {
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $methods = $shippingAddress->getGroupedAllShippingRates();
        $shippingTaxClass = $this->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $first = true;

        if (!empty($this->collectorSession->getVariable('curr_shipping_code'))) {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    if ($rate->getCode() == $this->collectorSession->getVariable('curr_shipping_code')) {
                        $this->collectorSession->setVariable('curr_shipping_description', $rate->getMethodTitle());
                        $this->collectorSession->setVariable('curr_shipping_tax_rate', $shippingTax);
                        $this->collectorSession->setVariable('curr_shipping_price', $rate->getPrice());
                        $this->collectorSession->setVariable('curr_shipping_tax', 0);

                        $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
                        $this->shippingRate->setCode($rate->getCode())->getPrice();
                        try {
                            $this->cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
                        } catch (\Exception $e) {
                        }
                        $this->cart->getQuote()->getShippingAddress()->save();
                        $this->cart->getQuote()->collectTotals();
                        $this->cart->getQuote()->save();
                        $this->cart->getQuote()->getTotals();
                        $this->setShippingMethod($rate->getCode());
                        $this->collectorSession->setVariable('curr_shipping_code', $rate->getCode());
                        $this->cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
                        $this->cart->getQuote()->save();
                        break;
                    }
                }
            }
        } else {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    if ($first) {
                        $this->collectorSession->setVariable('curr_shipping_description', $rate->getMethodTitle());
                        $this->collectorSession->setVariable('curr_shipping_tax_rate', $shippingTax);
                        $this->collectorSession->setVariable('curr_shipping_price', $rate->getPrice());
                        $this->collectorSession->setVariable('curr_shipping_tax', 0);

                        $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
                        $this->shippingRate->setCode($rate->getCode())->getPrice();
                        try {
                            $this->cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
                        } catch (\Exception $e) {
                        }
                        $this->cart->getQuote()->getShippingAddress()->save();
                        $this->cart->getQuote()->collectTotals();
                        $this->cart->getQuote()->save();
                        $this->cart->getQuote()->getTotals();
                        $first = false;
                        $this->setShippingMethod($rate->getCode());
                        $this->collectorSession->setVariable('curr_shipping_code', $rate->getCode());

                        $this->cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
                        $this->cart->getQuote()->save();
                        break;
                    }
                }
                if (!$first) {
                    break;
                }
            }
        }

        if (empty($this->collectorSession->getVariable('curr_shipping_price'))) {
            $this->collectorSession->getVariable('curr_shipping_price', 0);
        }
        if ($inclFormatting) {
            return $this->pricingHelper->currency($this->collectorSession->getVariable('curr_shipping_price'), true, false);
        } else {
            return $this->collectorSession->getVariable('curr_shipping_price');
        }
    }

    public function getBlockProducts()
    {
        $cartItems = $this->cart->getQuote()->getAllVisibleItems();
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $items = array();

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->getProduct();
            $taxClassId = $product->getTaxClassId();
            $percent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
            $options = "";
            if ($this->getShowOptions()) {
                $options = "<dl>";
                $op = $cartItem->getProduct()->getTypeInstance(true)->getOrderOptions($cartItem->getProduct());
                if ($cartItem->getProductType() == 'configurable') {
                    foreach ($op['attributes_info'] as $option) {
                        $options .= "<dd>";
                        $options .= $option['label'] . ": " . $option['value'];
                        $options .= "</dd>";
                    }
                } else if ($cartItem->getProductType() == 'bundle') {
                    foreach ($op['bundle_options'] as $option) {
                        $options .= "<dd>";
                        $options .= $option['value'][0]['title'];
                        $options .= "</dd>";
                    }
                }
                $options .= '</dl>';
            }

            array_push($items, array(
                'name' => $cartItem->getName(),
                'options' => $options,
                'id' => $cartItem->getId(),
                'unitPrice' => $this->pricingHelper->currency(($cartItem->getPrice() * (1 + ($percent / 100))), true, false),
                'qty' => $cartItem->getQty(),
                'sum' => $this->pricingHelper->currency(($cartItem->getPrice() * $cartItem->getQty() * (1 + ($percent / 100))), true, false),
                'img' => $this->imageHelper->init($product, 'product_page_image_small')->setImageFile($product->getFile())->resize(80, 80)->getUrl()
            ));
        }
        return $items;
    }

    public function getProducts()
    {
        $cartItems = $this->cart->getQuote()->getAllItems();
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $cartTotals = $this->cart->getQuote()->getTotals();
        $items = array('items' => array());
        $bundlesWithFixedPrice = array();

        foreach ($cartItems as $cartItem) {
            if ($cartItem->getProductType() == 'configurable') {
                continue;
            } elseif (in_array($cartItem->getParentItemId(), $bundlesWithFixedPrice)) {
                continue;
            } elseif ($cartItem->getProductType() == 'bundle') {
                $product = $cartItem->getProduct();
                if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                    $bundlesWithFixedPrice[] = $cartItem->getItemId();
                } elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    continue;
                }
            }
            $product = $this->productRepository->get($cartItem->getSku());
            $taxClassId = $product->getTaxClassId();
            $percent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
            $qty = 0;
            if ($cartItem->getParentItem()) {
                $qty = $cartItem->getParentItem()->getQty();
            } else {
                $qty = $cartItem->getQty();
            }
            $price = $cartItem->getPriceInclTax();
            if ($cartItem->getPriceInclTax() == 0) {
                $price = $cartItem->getParentItem()->getPriceInclTax();
            }
            array_push($items['items'], array(
                'id' => $cartItem->getSku(),
                'description' => $cartItem->getName(),
                'unitPrice' => round($price, 2),
                'quantity' => $qty,
                'vat' => $percent
            ));
        }
        $fee = 0;
        if (array_key_exists('fee', $cartTotals)) {
            $fee = $cartTotals['fee']->getData()['value'];
        }
        if (array_key_exists('value_incl_tax', $cartTotals['subtotal']->getData())) {
            $left = $this->cart->getQuote()->getGrandTotal();
            $right = ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice']);

            $this->logger->info(var_export($left, true));
            $this->logger->info(var_export($right, true));
            $this->logger->info(var_export(abs(($left - $right) / $right) < 0.00001, true));


            if ($this->cart->getQuote()->getGrandTotal() < ($cartTotals['subtotal']->getData()['value_incl_tax'] + $fee + $this->getShippingInclTax()['unitPrice'])) {
                if ($this->cart->getQuote()->getCouponCode() != null) {
                    $coupon = $this->cart->getQuote()->getCouponCode();
                } else {
                    $coupon = "no_code";
                }
                $code = array(
                    'id' => 'discount',
                    'description' => $coupon,
                    'quantity' => 1,
                    'unitPrice' => sprintf("%01.2f", $this->cart->getQuote()->getGrandTotal() - ($cartTotals['subtotal']->getData()['value_incl_tax'] + $fee + $this->getShippingInclTax()['unitPrice'])),
                    'vat' => '25',
                );
                array_push($items['items'], $code);
            }
        } else {
            $this->logger->info('GrandTotal:' . $this->cart->getQuote()->getGrandTotal());
            $this->logger->info('Subtotal+unitPrice:' . ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice']));
            $this->logger->info(var_export($this->cart->getQuote()->getGrandTotal() < ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice']), true));

            if ($this->cart->getQuote()->getGrandTotal() < ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice'])) {
                if ($this->cart->getQuote()->getCouponCode() != null) {
                    $coupon = $this->cart->getQuote()->getCouponCode();
                } else {
                    $coupon = "no_code";
                }
                $code = array(
                    'id' => 'discount',
                    'description' => $coupon,
                    'quantity' => 1,
                    'unitPrice' => sprintf("%01.2f", $this->cart->getQuote()->getGrandTotal() - ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice'])),
                    'vat' => '25',
                );
                array_push($items['items'], $code);
            }
        }
        return $items;
    }

    public function getFees()
    {
        $this->cart->getQuote()->collectTotals();
        $cartTotals = $this->cart->getQuote()->getTotals();
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingTaxClass = $this->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        if (empty($cartTotals['shipping']->getData()['title']->getArguments())) {
            if (!empty($this->collectorSession->getVariable('curr_shipping_code'))) {
                if ($this->collectorSession->getVariable('curr_shipping_code') == 0) {
                    $ret = array(
                        'shipping' => array(
                            'id' => "shipping",
                            'description' => $this->collectorSession->getVariable('curr_shipping_code'),
                            'unitPrice' => 0,
                            'vat' => 0
                        )
                    );
                } else {
                    $ret = array(
                        'shipping' => array(
                            'id' => "shipping",
                            'description' => $this->collectorSession->getVariable('curr_shipping_code'),
                            'unitPrice' => $this->collectorSession->getVariable('curr_shipping_code'),
                            'vat' => ($this->collectorSession->getVariable('curr_shipping_code') / ($this->collectorSession->getVariable('curr_shipping_code') - $this->collectorSession->getVariable('curr_shipping_code')) - 1) * 100
                        )
                    );
                }
            } else {
                $ret = array(
                    'shipping' => array(
                        'id' => 'shipping',
                        'description' => 'freeshipping_freeshipping',
                        'unitPrice' => 0,
                        'vat' => '0'
                    )
                );
            }
        } else {
            $ret = array(
                'shipping' => array(
                    'id' => 'shipping',
                    'description' => $this->collectorSession->getVariable('curr_shipping_code'),
                    'unitPrice' => $cartTotals['shipping']->getData()['value'],
                    'vat' => '25'
                )
            );
        }
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $fee = $this->scopeConfig->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b', $storeScope);
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $feeTaxClass = $this->getB2CInvoiceFeeTaxClass();
        $feeTax = $this->taxCalculation->getRate($request->setProductClassId($feeTaxClass));
        if ($fee > 0) {
            $iFee = array(
                'id' => 'invoice_fee',
                'description' => 'Invoice Fee',
                'unitPrice' => $fee,
                'vat' => $feeTax
            );
            $ret['directinvoicenotification'] = $iFee;
        }
        return $ret;
    }

    public function getShippingInclTax()
    {
        $this->cart->getQuote()->collectTotals();
        $cartTotals = $this->cart->getQuote()->getTotals();
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingTaxClass = $this->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $ret = array(
            'description' => $cartTotals['shipping']->getData()['title']->getArguments(),
            'unitPrice' => $cartTotals['shipping']->getData()['value'] * (1 + $shippingTax / 100),
        );
        return $ret;
    }

    public function getWSDL()
    {
        if ($this->getTestMode()) {
            return "https://checkout-api-uat.collector.se/";
        } else {
            return "https://checkout-api.collector.se/";
        }
    }

    public function updateFees()
    {
        $quote = $this->cart->getQuote();
        if (!empty($this->collectorSession->getVariable('collector_private_id'))) {
            $pid = $this->collectorSession->getVariable('collector_private_id');
        } else {
            $pid = $quote->getData('collector_private_id');
        }
        $pusername = $this->getUsername();
        $psharedSecret = $this->getPassword();
        if (!empty($this->collectorSession->getVariable('col_curr_fee'))) {
            if ($this->collectorSession->getVariable('col_curr_fee') == $this->getFees()) {
                return;
            } else {
                $array = $this->getFees();
                $this->collectorSession->setVariable('col_curr_fee', $array);
            }
        } else {
            $array = $this->getFees();
            $this->collectorSession->setVariable('col_curr_fee', $array);
        }
        $storeId = 0;
        if ($this->collectorSession->getVariable('btype') == 'b2b'
            || empty($this->collectorSession->getVariable('btype')) && $this->getCustomerType() == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER) {
            $this->collectorSession->setVariable('btype', 'b2b');
            $storeId = $this->getB2BStoreID();
        } else {
            $this->collectorSession->setVariable('btype', 'b2c');
            $storeId = $this->getB2CStoreID();
        }
        $path = '/merchants/' . $storeId . '/checkouts/' . $pid . '/fees';
        $json = json_encode($array);
        $hash = $pusername . ":" . hash("sha256", $json . $path . $psharedSecret);
        $hashstr = 'SharedKey ' . base64_encode($hash);
        $ch = curl_init($this->getWSDL() . "merchants/" . $storeId . "/checkouts/" . $pid . "/fees");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8', 'Authorization:' . $hashstr));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_exec($ch);
        curl_close($ch);
    }

    public function updateCart()
    {
        $quote = $this->cart->getQuote();
        if (!empty($this->collectorSession->getVariable('collector_private_id'))) {
            $pid = $this->collectorSession->getVariable('collector_private_id');
        } else {
            $pid = $quote->getData('collector_private_id');
        }
        $pusername = $this->getUsername();
        $psharedSecret = $this->getPassword();
        $array = array();
        $array['countryCode'] = $this->getCountryCode();
        $array['items'] = $this->getProducts()['items'];
        $storeId = 0;
        if ($this->collectorSession->getVariable('btype') == 'b2b'
            || empty($this->collectorSession->getVariable('btype')) && $this->getCustomerType() == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER) {
            $this->collectorSession->setVariable('btype', 'b2b');
            $storeId = $this->getB2BStoreID();
        } else {
            $this->collectorSession->setVariable('btype', 'b2c');
            $storeId = $this->getB2CStoreID();
        }
        $path = '/merchants/' . $storeId . '/checkouts/' . $pid . '/cart';
        $json = json_encode($array);
        $hash = $pusername . ":" . hash("sha256", $json . $path . $psharedSecret);
        $hashstr = 'SharedKey ' . base64_encode($hash);
        $ch = curl_init($this->getWSDL() . "merchants/" . $storeId . "/checkouts/" . $pid . "/cart");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8', 'Authorization:' . $hashstr));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_exec($ch);
        curl_close($ch);
    }

    public function getOrderResponse()
    {
        if (!empty($this->collectorSession->getVariable('collector_private_id'))) {
            $pid = $this->collectorSession->getVariable('collector_private_id');
        } else {
            $pid = $this->cart->getQuote()->getData('collector_private_id');
        }
        $storeId = 0;
        if ($this->collectorSession->getVariable('btype') == 'b2b'
            || empty($this->collectorSession->getVariable('btype')) && $this->getCustomerType() == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER) {
            $this->collectorSession->setVariable('btype', 'b2b');
            $storeId = $this->getB2BStoreID();
        } else {
            $this->collectorSession->setVariable('btype', 'b2c');
            $storeId = $this->getB2CStoreID();
        }
        $path = "merchants/" . $storeId . "/checkouts/" . $pid;
        $hash = $this->getUsername() . ":" . hash("sha256", "/{$path}" . $this->getPassword());

        $ch = curl_init($this->getWSDL() . $path);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:SharedKey ' . base64_encode($hash)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $output = curl_exec($ch);
        $data = json_decode($output, true);

        if ($data["data"]) {
            $result['code'] = 1;
            $result['id'] = $data["id"];
            $result['data'] = $data["data"];

        } else {
            $result['code'] = 0;
            $result['error'] = $data["error"];
        }
        return $result;
    }
}