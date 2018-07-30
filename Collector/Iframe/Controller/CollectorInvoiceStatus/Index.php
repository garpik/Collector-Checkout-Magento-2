<?php
/**
 * A Magento 2 module named Collector/Iframe
 * Copyright (C) 2017 Collector
 *
 * This file is part of Collector/Iframe.
 *
 * Collector/Iframe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collector\Iframe\Controller\CollectorInvoiceStatus;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepositoryInterface;
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagementInterface;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $collectorLogger;

    /**
     * @var \Collector\Iframe\Model\FraudFactory
     */
    protected $fraudFactory;
    /**
     * @var \Collector\Iframe\Model\CheckerFactory
     */
    protected $checkerFactory;
	/**
     * @var \Collector\Base\Logger\Collector
     */
    protected $config;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\Api\FilterBuilder $_filterBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param \Magento\Sales\Api\Data\OrderInterface $_orderInterface
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Iframe\Model\FraudFactory $fraudFactory
     * @param \Collector\Iframe\Model\CheckerFactory $checkerFactory
	 * @param \Collector\Base\Model\Config $_config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\Api\FilterBuilder $_filterBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magento\Sales\Api\Data\OrderInterface $_orderInterface,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\App\Request\Http $request,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Iframe\Model\FraudFactory $fraudFactory,
        \Collector\Iframe\Model\CheckerFactory $checkerFactory,
		\Collector\Base\Model\Config $_config
    ) {
        $this->checkerFactory = $checkerFactory;
		$this->config = $_config;
        $this->fraudFactory = $fraudFactory;
        $this->collectorLogger = $logger;
        $this->apiRequest = $apiRequest;
        $this->request = $request;
        $this->collectorSession = $_collectorSession;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $_helper;
        $this->filterBuilder = $_filterBuilder;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $_checkoutSession;
        $this->orderInterface = $_orderInterface;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->storeManager = $_storeManager;
        $this->customerRepository = $_customerRepository;
        $this->customerFactory = $_customerFactory;
        $this->shippingRate = $_shippingRate;
        $this->cartRepositoryInterface = $_cartRepositoryInterface;
        $this->cartManagementInterface = $_cartManagementInterface;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!empty($this->request->getParam('OrderNo'))) {
            if (!empty($this->request->getParam('InvoiceStatus'))) {
                $order = $this->orderInterface->loadByIncrementId($this->request->getParam('OrderNo'));
                if ($order->getId()) {
                    if ($this->request->getParam('InvoiceStatus') == "0") {
                        $status = $this->helper->getHoldStatus();
                        $order->setState($status)->setStatus($status);
                        $order->save();
                    } else {
                        if ($this->request->getParam('InvoiceStatus') == "1") {
                            $status = $this->helper->getAcceptStatus();
                            $order->setState($status)->setStatus($status);
                            $order->save();
                        } else {
                            $status = $this->helper->getDeniedStatus();
                            $order->setState($status)->setStatus($status);
                            $order->save();
                        }
                    }
                }
                $fraud = $this->fraudFactory->create();
                $fraud->setIncrementId($this->request->getParam('OrderNo'));
                $fraud->setStatus($this->request->getParam('InvoiceStatus'));
                $fraud->setIsAntiFraud(1);
                $fraud->save();

            }
            $checker = $this->checkerFactory->create();
            $checker->setData('increment_id', $this->request->getParam('OrderNo'));
            $checker->save();
        }
        return $this->resultPageFactory->create();
    }

    public function createOrder($quote)
    {
        $response = $this->getResp($quote->getData('collector_private_id'), $quote->getData('collector_btype'));
        $resultPage = $this->resultPageFactory->create();
        try {
            switch ($response['data']['purchase']['paymentName']) {
                case 'DirectInvoice':
                    $paymentMethod = 'collector_invoice';
                    break;
                case 'PartPayment':
                    $paymentMethod = 'collector_partpay';
                    break;
                case 'Account':
                    $paymentMethod = 'collector_account';
                    break;
                case 'Card':
                    $paymentMethod = 'collector_card';
                    break;
                case 'Bank':
                    $paymentMethod = 'collector_bank';
                    break;
                default:
                    $paymentMethod = 'collector_invoice';
                    break;
            }
            $exOrder = $this->orderInterface->loadByIncrementId($response['data']['reference']);
            if ($exOrder->getIncrementId()) {
                return $resultPage;
            }

            $shippingCountryId = $this->getCountryCodeByName(
                $response['data']['customer']['deliveryAddress']['country'],
                $response['data']['countryCode']
            );
            $billingCountryId = $this->getCountryCodeByName(
                $response['data']['customer']['billingAddress']['country'],
                $response['data']['countryCode']
            );

            $shippingCode = $quote->getData('curr_shipping_code');

            $actual_quote = $this->quoteCollectionFactory->create()->addFieldToFilter(
                "reserved_order_id",
                $response['data']['reference']
            )->getFirstItem();

            //init the store id and website id @todo pass from array
            $store = $this->storeManager->getStore();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            //init the customer
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($response['data']['customer']['email']); // load customer by email address
            //check the customer
            if (!$customer->getEntityId()) {
                //If not avilable then create this customer
                $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($response['data']['customer']['billingAddress']['firstName'])
                    ->setLastname($response['data']['customer']['billingAddress']['lastName'])
                    ->setEmail($response['data']['customer']['email'])
                    ->setPassword($response['data']['customer']['email']);
                $customer->save();
            }
            $customer->setEmail($response['data']['customer']['email']);
            $customer->save();
            $actual_quote->setCustomerEmail($response['data']['customer']['email']);
            $actual_quote->setStore($store);
            $customer = $this->customerRepository->getById($customer->getEntityId());
            $actual_quote->setCurrency();
            $actual_quote->assignCustomer($customer);
            if (!empty($this->collectorSession->getCollectorAppliedDiscountCode())) {
                $actual_quote->setCouponCode($this->collectorSession->getCollectorAppliedDiscountCode());
            }
            //Set Address to quote @todo add section in order data for seperate billing and handle it

            $billingAddress = array(
                'firstname' => $response['data']['customer']['billingAddress']['firstName'],
                'lastname' => $response['data']['customer']['billingAddress']['lastName'],
                'street' => $response['data']['customer']['billingAddress']['address'],
                'city' => $response['data']['customer']['billingAddress']['city'],
                'country_id' => $billingCountryId,
                'postcode' => $response['data']['customer']['billingAddress']['postalCode'],
                'telephone' => $response['data']['customer']['mobilePhoneNumber']
            );
            $shippingAddressArr = array(
                'firstname' => $response['data']['customer']['deliveryAddress']['firstName'],
                'lastname' => $response['data']['customer']['deliveryAddress']['lastName'],
                'street' => $response['data']['customer']['deliveryAddress']['address'],
                'city' => $response['data']['customer']['deliveryAddress']['city'],
                'country_id' => $shippingCountryId,
                'postcode' => $response['data']['customer']['deliveryAddress']['postalCode'],
                'telephone' => $response['data']['customer']['mobilePhoneNumber']
            );
            $actual_quote->getBillingAddress()->addData($billingAddress);
            $actual_quote->getShippingAddress()->addData($shippingAddressArr);

            // Collect Rates and Set Shipping & Payment Method
            $this->shippingRate->setCode($shippingCode)->getPrice();
            $shippingAddress = $actual_quote->getShippingAddress();
            //@todo set in order data
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($shippingCode); //shipping method
            $actual_quote->getShippingAddress()->addShippingRate($this->shippingRate);
            $actual_quote->setPaymentMethod($paymentMethod); //payment method
            $actual_quote->getPayment()->importData(['method' => $paymentMethod]);
            $actual_quote->setReservedOrderId($response['data']['reference']);
            // Collect total and save
            $actual_quote->collectTotals();
            // Submit the quote and create the order
            $actual_quote->save();
            $cart = $this->cartRepositoryInterface->get($actual_quote->getId());
            $cart->setCustomerEmail($response['data']['customer']['email']);
            $cart->setCustomerId($customer->getId());
            $cart->getBillingAddress()->addData($billingAddress);
            $cart->getShippingAddress()->addData($shippingAddressArr);
            $cart->getBillingAddress()->setEmail($response['data']['customer']['email']);
            $cart->getShippingAddress()->setEmail($response['data']['customer']['email']);
            $cart->setCustomerEmail($response['data']['customer']['email']);
            $cart->save();
            $cart->setCustomerId($customer->getId());
            $cart->setCustomerEmail($response['data']['customer']['email']);
            $cart->getBillingAddress()->setCustomerId($customer->getId());
            $cart->getShippingAddress()->setCustomerId($customer->getId());
            $cart->assignCustomer($customer);
            $cart->save();
            $this->collectorSession->setIsIframe(true);

            $order_id = $this->cartManagementInterface->placeOrder($cart->getId());
            $order = $this->order->load($order_id);
            $emailSender = $this->orderSender->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($order);
            $order->setData('collector_invoice_id', $response['data']['purchase']['purchaseIdentifier']);
            $fee = 0;
            foreach ($response['data']['order']['items'] as $item) {
                if ($item['id'] == 'invoice_fee') {
                    $fee = $item['unitPrice'];
                }
            }
            $order->setData('fee_amount', $fee);
            $order->setData('base_fee_amount', $fee);

            $order->setGrandTotal($order->getGrandTotal() + $fee);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $fee);


            if ($response["data"]["purchase"]["result"] == "OnHold") {
                $status = $this->helper->getHoldStatus();
                $order->setState($status)->setStatus($status);
                $order->save();
            } else {
                if ($response["data"]["purchase"]["result"] == "Preliminary") {
                    $status = $this->helper->getAcceptStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                } else {
                    $status = $this->helper->getDeniedStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
            }

            $this->eventManager->dispatch(
                'checkout_onepage_controller_success_action',
                ['order_ids' => [$order->getId()]]
            );

            $this->checkoutSession->clearStorage();
            $this->checkoutSession->clearQuote();
            return $resultPage;
        } catch (\Exception $e) {
            $this->collectorLogger->debug("checkout error: " . $e->getMessage());
            return $resultPage;
        }
    }

    public function getResp($privId, $btype)
    {
        if ($privId) {
            $data = $this->apiRequest->callCheckouts(null, $privId, $btype);
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
        return [];
    }

    private function getCountryCodeByName($name, $default)
    {
        $id = $default;
        switch ($name) {
            case 'Sverige':
                $id = 'SE';
                break;
            case 'Norge':
                $id = 'NO';
                break;
            case 'Suomi':
                $id = 'FI';
                break;
            case 'Deutschland':
                $id = 'DE';
                break;
        }
        return $id;
    }
}
