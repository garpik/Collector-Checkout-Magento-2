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

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Collector\Base\Helper\Prices
     */
    protected $collectorPriceHelper;

    protected $shippingCollected = false;
    public $allowedCountries = [
        'NO',
        'SE',
        'FI',
        'DE'
    ];
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Message\ManagerInterface $_messageManager
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Base\Helper\Prices $collectorPriceHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Message\ManagerInterface $_messageManager,
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Base\Helper\Prices $collectorPriceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collectorPriceHelper = $collectorPriceHelper;
        $this->checkoutHelper = $checkoutHelper;
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
        return $this->storeManager->getStore()->getBaseUrl() . "collectorcheckout/success/";
    }

    public function getNotificationUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() .
            "collectorcheckout/CollectorInvoiceStatus?OrderNo=" .
            $this->cart->getQuote()->getReservedOrderId();
    }

    public function getDiscount()
    {
        return $this->collectorPriceHelper->getQuoteDiscount($this->cart->getQuote(), true);
    }

    public function hasDiscount()
    {
        return $this->collectorPriceHelper->hasQuoteDiscount($this->cart->getQuote());
    }

    public function getTax()
    {
        return $this->collectorPriceHelper->getQuoteTaxValue($this->cart->getQuote(), true);
    }

    public function getGrandTotal()
    {
        return $this->collectorPriceHelper->getQuoteGrandTotal($this->cart->getQuote(), true);
    }

    public function getShippingMethods()
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        if (!$this->shippingCollected) {
            $this->shippingCollected = true;
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        }
        $shippingTaxClass = $this->collectorConfig->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $shippingMethods = [];
        $first = true;

        $methods = $shippingAddress->getGroupedAllShippingRates();

        $selectedIsActive = false;
        if (!empty($shippingAddress->getShippingMethod())) {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    if ($rate->getCode() == $shippingAddress->getShippingMethod()) {
                        $selectedIsActive = true;
                    }
                }
            }
        }

        foreach ($methods as $method) {
            foreach ($method as $rate) {
                $shipMethod = [
                    'first' => !$selectedIsActive && $first
                        || $selectedIsActive && $rate->getCode() == $shippingAddress->getShippingMethod(),
                    'code' => $rate->getCode(),
                    'content' => ''
                ];
                if (!$selectedIsActive && $first
                    || $selectedIsActive && $rate->getCode() == $shippingAddress->getShippingMethod()
                ) {
                    $first = false;
                    $this->setShippingMethod($rate->getCode());
                }
                $shipMethod['content'] = $rate->getMethodTitle() . ": "
                    . $this->pricingHelper->currency(
                        $rate->getPrice() + ($this->scopeConfig->getValue('tax/cart_display/shipping') == 1 ? 0 : $rate->getPrice() * $shippingTax / 100),
                        true,
                        false
                    );

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
            $this->collectorSession->setCollectorAppliedDiscountCode($code);
            $this->cart->getQuote()->setData('collector_applied_discount_code', $code);
            $this->cart->getQuote()->save();
            $this->messageManager->addSuccess(__('You used coupon code "%1".', $code));
        } else {
            $this->messageManager->addError(__('The coupon code "%1" is not valid.', $code));
        }
    }

    public function unsetDiscountCode()
    {
        $this->collectorSession->setCollectorAppliedDiscountCode('');
        $this->cart->getQuote()->setData('collector_applied_discount_code', null);
        $this->cart->getQuote()->save();
        $this->messageManager->addSuccess(__('You canceled the coupon code.'));
        $this->checkoutSession->getQuote()->setCouponCode()->collectTotals()->save();
    }

    public function getShippingMethod()
    {
        return $this->cart->getQuote()->getShippingAddress()
            ->getShippingMethod();
    }

    public function setShippingMethod($methodInput = '')
    {
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $methods = $shippingAddress->getGroupedAllShippingRates();
        foreach ($methods as $method) {
            foreach ($method as $rate) {
                if ($rate->getCode() == $methodInput || empty($methodInput)) {
                    $this->cart->getQuote()->getShippingAddress()->setShippingMethod($rate->getCode());
                    $this->shippingRate->setCode($rate->getCode());
                    try {
                        $this->cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
                    } catch (\Exception $e) {
                    }
                    $this->cart->getQuote()->save();
                    break;
                }
            }
        }

        return $this->collectorPriceHelper->getQuoteShippingPrice($this->cart->getQuote(), true);
    }


    public function getShippingPrice($inclFormatting = true)
    {
        if (empty($this->cart->getQuote()->getShippingAddress()->getShippingMethod())) {
            $this->getShippingMethod();
        }
        return $this->collectorPriceHelper->getQuoteShippingPrice($this->cart->getQuote(), $inclFormatting);
    }

    public function getBlockProducts()
    {
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $items = [];

        foreach ($this->cart->getQuote()->getAllVisibleItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $taxClassId = $product->getTaxClassId();
            //$percent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
            $options = [];
            $op = $cartItem->getProduct()->getTypeInstance(true)->getOrderOptions($cartItem->getProduct());
            if ($cartItem->getProductType() == 'configurable') {
                foreach ($op['attributes_info'] as $option) {
                    $options[] = $option['label'] . ": " . $option['value'];
                }
            } else {
                if ($cartItem->getProductType() == 'bundle') {
                    foreach ($op['bundle_options'] as $option) {
                        $options[] = $option['value'][0]['title'];
                    }
                }
            }
            array_push(
                $items,
                array(
                    'name' => $cartItem->getName(),
                    'options' => $options,
                    'id' => $cartItem->getId(),

                    'unitPrice' =>
                        $this->checkoutHelper->formatPrice(
                            $this->scopeConfig->getValue('tax/cart_display/price') == 1 ?
                                $cartItem->getPrice() :
                                $cartItem->getPriceInclTax()
                        ),
                    'qty' => $cartItem->getQty(),
                    'sum' => $this->pricingHelper->currency(
                        $this->scopeConfig->getValue('tax/cart_display/price') == 1 ?
                            $cartItem->getRowTotal() :
                            $cartItem->getRowTotalInclTax(),
                        true,
                        false
                    ),
                    'img' => $this->imageHelper->init(
                        $product,
                        'product_page_image_small'
                    )->setImageFile($product->getFile())->resize(80, 80)->getUrl()
                )
            );
        }
        return $items;
    }


    public function getProducts()
    {
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $cartTotals = $this->collectorPriceHelper->getQuoteTotalsArray($this->cart->getQuote(), false);
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
                'unitPrice' => round($this->apiRequest->convert($price, 'SEK'), 2),
                'quantity' => $qty,
                'vat' => $percent
            ));
        }
        $totals =
            (!empty($cartTotals['subtotal']['value']) ? $cartTotals['subtotal']['value'] : 0)
            + (!empty($cartTotals['fee']['value']) ? $cartTotals['fee']['value'] : 0)
            + (!empty($cartTotals['shipping']['value']) ? $cartTotals['shipping']['value'] : 0);
        if ($this->cart->getQuote()->getGrandTotal() < $totals) {
            $coupon = "no_code";
            if ($this->cart->getQuote()->getCouponCode() != null) {
                $coupon = $this->cart->getQuote()->getCouponCode();
            }
            $code = array(
                'id' => 'discount',
                'description' => $coupon,
                'quantity' => 1,
                'unitPrice' => sprintf(
                    "%01.2f",
                    $this->apiRequest->convert($this->cart->getQuote()->getGrandTotal() - $totals, 'SEK')
                ),
                'vat' => '25',
            );
            array_push($items, $code);
        }

        return $items;
    }

    public function getFees()
    {
        $this->cart->getQuote()->collectTotals();
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $fee = $this->collectorConfig->getInvoiceB2BFee();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $feeTaxClass = $this->collectorConfig->getB2CInvoiceFeeTaxClass();
        $feeTax = $this->taxCalculation->getRate($request->setProductClassId($feeTaxClass));

        $ret = [];
        if ($fee > 0) {
            $ret['directinvoicenotification'] = [
                'id' => 'invoice_fee',
                'description' => 'Invoice Fee',
                'unitPrice' => $this->apiRequest->convert($fee, 'SEK', 'base'),
                'vat' => $feeTax
            ];
        }
        if (!empty($shippingAddress->getShippingMethod())) {
			if ($this->apiRequest->convert($shippingAddress->getShippingInclTax(), 'SEK') !== NULL){
				$ret ['shipping'] = [
					'id' => "shipping",
					'description' => $shippingAddress->getShippingMethod(),
					'unitPrice' => $this->apiRequest->convert($shippingAddress->getShippingInclTax(), 'SEK'),
					'vat' => 0
				];
			}
			else {
				$ret ['shipping'] = [
					'id' => "shipping",
					'description' => $shippingAddress->getShippingMethod(),
					'unitPrice' => 0,
					'vat' => 0
				];
			}
        } else {
            $ret['shipping'] = [
                'id' => 'shipping',
                'description' => 'freeshipping_freeshipping',
                'unitPrice' => 0,
                'vat' => '0'
            ];
        }
        return $ret;
    }

    public function updateFees()
    {
        $this->apiRequest->callCheckoutsFees($this->getFees(), $this->cart);
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
        $result = [];
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
