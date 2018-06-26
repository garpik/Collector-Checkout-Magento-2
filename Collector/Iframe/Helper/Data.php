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
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;
    public $allowedCountries = [
        'NO',
        'SE',
        'FI',
        'DE'
    ];

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
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Magento\Framework\Message\ManagerInterface $_messageManager
     * @param \Collector\Base\Model\Config $collectorConfig
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
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Framework\Message\ManagerInterface $_messageManager,
        \Collector\Base\Model\Config $collectorConfig
    )
    {
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
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


    public function getSuccessPageUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . "collectorcheckout/success";
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
        return $this->cart->getQuote()->getSubtotal() != $this->cart->getQuote()->getSubtotalWithDiscount();
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
        $currentStoreId = $this->storeManager->getStore()->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $shippingTaxClass = $this->collectorConfig->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $shippingMethods = [];
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

        foreach ($methods as $method) {
            foreach ($method as $rate) {
                $shipMethod = [
                    'first' => $first,
                    'code' => $rate->getCode(),
                    'content' => ''
                ];
                if (!$selectedIsActive && $first
                    || $selectedIsActive && $rate->getCode() == $this->collectorSession->getVariable('curr_shipping_code')
                ) {
                    $first = false;
                    $this->setShippingMethod($rate->getCode());
                }
                if ($shippingTax == 0) {
                    $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency($rate->getPrice(), true, false);
                } else {
                    $shipMethod['content'] = $rate->getMethodTitle() . ": " . $this->pricingHelper->currency(($rate->getPrice() * (1 + ($shippingTax / 100))), true, false);
                }
                array_push($shippingMethods, $shipMethod);
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
        $this->checkoutSession->getQuote()->setCouponCode(NULL)->collectTotals()->save();
    }

    public function setShippingMethod($methodInput = '')
    {
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $methods = $shippingAddress->getGroupedAllShippingRates();
        foreach ($methods as $method) {
            foreach ($method as $rate) {
                if ($rate->getCode() == $methodInput || empty($methodInput)) {
                    $this->collectorSession->setVariable('curr_shipping_price', $rate->getPrice());
                    $this->collectorSession->setVariable('curr_shipping_tax', 0);
                    $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
                    $this->shippingRate->setCode($rate->getCode());
                    try {
                        $this->cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
                    } catch (\Exception $e) {
                    }
                    $this->cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
                    $this->cart->getQuote()->collectTotals();
                    $this->cart->getQuote()->save();
                    $this->collectorSession->setVariable('curr_shipping_code', $rate->getCode());
                    break;
                }
            }
        }
        if (empty($this->collectorSession->getVariable('curr_shipping_price'))) {
            $this->collectorSession->setVariable('curr_shipping_price', 0);
        }
        return $this->pricingHelper->currency($this->collectorSession->getVariable('curr_shipping_price'), true, false);
    }

    public function getShippingPrice($inclFormatting = true)
    {
        $this->setShippingMethod($this->collectorSession->getVariable('curr_shipping_code'));
        if ($inclFormatting) {
            return $this->pricingHelper->currency($this->collectorSession->getVariable('curr_shipping_price'), true, false);
        }
        return $this->collectorSession->getVariable('curr_shipping_price');

    }

    public function getBlockProducts()
    {
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $items = [];

        foreach ($this->cart->getQuote()->getAllVisibleItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $taxClassId = $product->getTaxClassId();
            $percent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
            $options = [];
            $op = $cartItem->getProduct()->getTypeInstance(true)->getOrderOptions($cartItem->getProduct());
            if ($cartItem->getProductType() == 'configurable') {
                foreach ($op['attributes_info'] as $option) {
                    $options[] = $option['label'] . ": " . $option['value'];;
                }
            } else if ($cartItem->getProductType() == 'bundle') {
                foreach ($op['bundle_options'] as $option) {
                    $options[] = $option['value'][0]['title']
                }
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
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $cartTotals = $this->cart->getQuote()->getTotals();
        $items = [];
        $bundlesWithFixedPrice = [];

        foreach ($this->cart->getQuote()->getAllItems() as $cartItem) {
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
            array_push($items, array(
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
            $totals = $cartTotals['subtotal']->getData()['value_incl_tax'] + $fee + $this->getShippingInclTax()['unitPrice'];
        } else {
            $totals = $cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice'];
        }
        $this->logger->info('GrandTotal:' . $this->cart->getQuote()->getGrandTotal());
        $this->logger->info('Subtotal+unitPrice:' . $totals);
        $this->logger->info(var_export($this->cart->getQuote()->getGrandTotal() < $totals, true));
        if ($this->cart->getQuote()->getGrandTotal() < $totals) {
            $coupon = "no_code";
            if ($this->cart->getQuote()->getCouponCode() != null) {
                $coupon = $this->cart->getQuote()->getCouponCode();
            }
            $code = array(
                'id' => 'discount',
                'description' => $coupon,
                'quantity' => 1,
                'unitPrice' => sprintf("%01.2f", $this->cart->getQuote()->getGrandTotal() - $totals),
                'vat' => '25',
            );
            array_push($items, $code);
        }

        return $items;
    }

    public function getFees()
    {
        $this->cart->getQuote()->collectTotals();
        $cartTotals = $this->cart->getQuote()->getTotals();
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
        $fee = $this->collectorConfig->getInvoiceB2BFee();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $feeTaxClass = $this->collectorConfig->getB2CInvoiceFeeTaxClass();
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
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($this->collectorConfig->getShippingTaxClass()));
        $ret = [
            'description' => $cartTotals['shipping']->getData()['title']->getArguments(),
            'unitPrice' => $cartTotals['shipping']->getData()['value'] * (1 + $shippingTax / 100),
        ];
        return $ret;
    }


    public function updateFees()
    {
        $fees = $this->getFees();
        if ($this->collectorSession->getVariable('col_curr_fee') == $fees) {
            return;
        }
        $this->collectorSession->setVariable('col_curr_fee', $fees);
        $this->apiRequest->callCheckoutsFees($fees, $this->cart);
    }

    public function updateCart()
    {
        $this->apiRequest->callCheckoutsCart([
            'countryCode' => $this->collectorConfig->getCountryCode(),
            'items' => $this->getProducts()
        ], $this->cart);
    }


    public function getOrderResponse()
    {
        $data = $this->apiRequest->callCheckouts($this->cart);
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