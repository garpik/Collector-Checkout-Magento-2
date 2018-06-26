<?php

namespace Collector\Iframe\Controller\Success;
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
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;
    /**
     * @var \Collector\Iframe\Model\State
     */
    protected $orderState;
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;


    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * @var array
     */
    protected $paymentToMethod = [
        'DirectInvoice' => 'collector_invoice',
        'PartPayment' => 'collector_partpay',
        'Account' => 'collector_account',
        'Card' => 'collector_card',
        'Bank' => 'collector_bank',


    ];

    /**
     * Index constructor.
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Sales\Api\Data\OrderInterface $_orderInterface
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Iframe\Model\State $orderState
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Sales\Api\Data\OrderInterface $_orderInterface,
        \Collector\Base\Logger\Collector $logger,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Iframe\Model\State $orderState,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect

    )
    {
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
        $this->response = $response;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->orderSender = $orderSender;
        $this->subscriberFactory = $subscriberFactory;
        $this->orderState = $orderState;
        $this->quoteManagement = $quoteManagement;
        $this->helper = $_helper;
        $this->eventManager = $eventManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $_checkoutSession;
        $this->orderInterface = $_orderInterface;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->storeManager = $_storeManager;
        $this->customerRepository = $_customerRepository;
        $this->customerFactory = $_customerFactory;
        $this->shippingRate = $_shippingRate;
        parent::__construct($context);
    }

    protected function getPaymentMethodByName($name)
    {
        if (isset($this->paymentToMethod[$name])) {
            return $this->paymentToMethod[$name];
        }
        return 'collector_invoice';
    }

    public function execute()
    {
        if (empty($this->collectorSession->getVariable('collector_public_token'))) {
            $this->logger->error('Error while public_token loading');
            $this->messageManager->addError(__('API request error.'));
            return $this->redirect->redirect($this->response, '/');
        }
//        $this->checkoutSession->clearQuote();
//        $this->checkoutSession->clearStorage();

        $response = $this->helper->getOrderResponse();
        $resultPage = $this->resultPageFactory->create();
        if ($response["code"] == 0) {
            $this->logger->error($response['error']);
            $this->messageManager->addError(__('Can not place order.'));
            return $this->redirect->redirect($this->response, '/');
        }
        try {
            $actual_quote = $this->quoteCollectionFactory->create()->addFieldToFilter("reserved_order_id", $response['data']['reference'])->getFirstItem();
            //set payment method
            $paymentMethod = $this->getPaymentMethodByName($response['data']['purchase']['paymentName']);

            $exOrder = $this->orderInterface->loadByIncrementId($response['data']['reference']);
            if ($exOrder->getIncrementId()) {
                throw new \Exception(__('This order is already exists'));
            }
            $shippingCountryId = $this->getCountryCodeByName($response['data']['customer']['deliveryAddress']['country'], $response['data']['countryCode']);
            $billingCountryId = $this->getCountryCodeByName($response['data']['customer']['billingAddress']['country'], $response['data']['countryCode']);

            //check countries
            if ((!$this->collectorConfig->isShippingAddressEnabled() && empty($shippingCountryId)) || empty($billingCountryId)) {
                throw new \Exception(__('Country code is not specified'));
            }
            //print_r($actual_quote->getBillingAddress()->getFirstname());exit;
            //$actual_quote->load($actual_quote->getId());
            if (empty($actual_quote)) {
                throw new \Exception(__('Can\'t load order'));
            }

            //init the store id and website id
            $store = $this->storeManager->getStore();
            $websiteId = $store->getWebsiteId();

            //init the customer
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);

            if (empty($response["data"]["customerType"])) {
                throw new \Exception(__('Incorrect user data'));
            }

            switch ($response["data"]["customerType"]) {
                case "PrivateCustomer":
                    $email = $response['data']['customer']['email'];
                    $firstname = $response['data']['customer']['billingAddress']['firstName'];
                    $lastname = $response['data']['customer']['billingAddress']['lastName'];
                    break;
                case "BusinessCustomer":
                    $email = $response['data']['businessCustomer']['email'];
                    $firstname = $response['data']['businessCustomer']['firstName'];
                    $lastname = $response['data']['businessCustomer']['lastName'];
                    break;
                default:
                    $this->messageManager->addError(__('Incorrect user data'));
                    return $this->redirect->redirect($this->response, '/');
                    break;
            }

            //load customer by email address
            $customer->loadByEmail($email);
            //check the customer
            if (!$customer->getEntityId()) {
                //If not avilable then create this customer
                $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email)
                    ->setPassword($email);
                $customer->save();
            }
            if (!empty($this->collectorSession->getVariable('newsletter_signup'))) {
                $this->subscriberFactory->create()->subscribe($response['data']['customer']['email']);
            }
            $customer->setEmail($email);
            $customer->save();
            //$actual_quote->setCustomerEmail($email);
            //$actual_quote->setStore($store);
            $customer = $this->customerRepository->getById($customer->getEntityId());
            //$actual_quote->setCurrency();
            $actual_quote->assignCustomer($customer);

            //set quote coupon code from session
//            if (!empty($this->collectorSession->getVariable('collector_applied_discount_code'))) {
//                $actual_quote->setCouponCode($this->collectorSession->getVariable('collector_applied_discount_code'));
//            }


            //Set Address to quote @todo add section in order data for seperate billing and handle it
            if (isset($response['data']['businessCustomer']['invoiceAddress'])) {
//                $scompany = $response['data']['businessCustomer']['deliveryAddress']['companyName'];
//                $sfirstname = $response['data']['businessCustomer']['firstName'];
//                $slastname = $response['data']['businessCustomer']['lastName'];
//                if (isset($response['data']['businessCustomer']['deliveryAddress']['address'])) {
//                    $sstreet = $response['data']['businessCustomer']['deliveryAddress']['address'];
//                } else {
//                    $sstreet = $response['data']['businessCustomer']['deliveryAddress']['postalCode'];
//                }
//                $sstreet = $response['data']['businessCustomer']['deliveryAddress']['address'];
//                $scity = $response['data']['businessCustomer']['deliveryAddress']['city'];
//                $spostcode = $response['data']['businessCustomer']['deliveryAddress']['postalCode'];
//                $stelephone = $response['data']['businessCustomer']['mobilePhoneNumber'];

                $bcompany = $response['data']['businessCustomer']['invoiceAddress']['companyName'];
                $bfirstname = $response['data']['businessCustomer']['firstName'];
                $blastname = $response['data']['businessCustomer']['lastName'];
                if (isset($response['data']['businessCustomer']['invoiceAddress']['address'])) {
                    $bstreet = $response['data']['businessCustomer']['invoiceAddress']['address'];
                } else {
                    $bstreet = $response['data']['businessCustomer']['invoiceAddress']['postalCode'];
                }
                $bcity = $response['data']['businessCustomer']['invoiceAddress']['city'];
                $bpostcode = $response['data']['businessCustomer']['invoiceAddress']['postalCode'];
                $btelephone = $response['data']['businessCustomer']['mobilePhoneNumber'];
            } else {
//                $scompany = '';
//                $sfirstname = $response['data']['customer']['deliveryAddress']['firstName'];
//                $slastname = $response['data']['customer']['deliveryAddress']['lastName'];
//                $sstreet = $response['data']['customer']['deliveryAddress']['address'];
//                $scity = $response['data']['customer']['deliveryAddress']['city'];
//                $spostcode = $response['data']['customer']['deliveryAddress']['postalCode'];
//                $stelephone = $response['data']['customer']['mobilePhoneNumber'];

                $bcompany = '';
                $bfirstname = $response['data']['customer']['billingAddress']['firstName'];
                $blastname = $response['data']['customer']['billingAddress']['lastName'];
                $bstreet = $response['data']['customer']['billingAddress']['address'];
                $bcity = $response['data']['customer']['billingAddress']['city'];
                $bpostcode = $response['data']['customer']['billingAddress']['postalCode'];
                $btelephone = $response['data']['customer']['mobilePhoneNumber'];
            }


            $billingAddress = array(
                'company' => $bcompany,
                'firstname' => $bfirstname,
                'lastname' => $blastname,
                'street' => $bstreet,
                'city' => $bcity,
                'country_id' => $response['data']['countryCode'],
                'postcode' => $bpostcode,
                'telephone' => $btelephone
            );
//            $shippingAddressArr = array(
//                'company' => $scompany,
//                'firstname' => $sfirstname,
//                'lastname' => $slastname,
//                'street' => $sstreet,
//                'city' => $scity,
//                'country_id' => $response['data']['countryCode'],
//                'postcode' => $spostcode,
//                'telephone' => $stelephone
//            );
            $actual_quote->getBillingAddress()->addData($billingAddress);
            //$actual_quote->getShippingAddress()->addData($shippingAddressArr);

            // Collect Rates and Set Shipping & Payment Method
            $this->shippingRate->setCode($this->collectorSession->getVariable('curr_shipping_code'))->getPrice();
            //$shippingAddress = $actual_quote->getShippingAddress();
            //@todo set in order data
//            $shippingAddress->setCollectShippingRates(true)
//                ->collectShippingRates()
//                ->setShippingMethod($this->collectorSession->getVariable('curr_shipping_code')); //shipping method

//            $actual_quote->getShippingAddress()->addShippingRate($this->shippingRate);

            $actual_quote->setPaymentMethod($paymentMethod); //payment method
            $actual_quote->getPayment()->importData(['method' => $paymentMethod]);
            $actual_quote->setReservedOrderId($response['data']['reference']);

            //$actual_quote->getBillingAddress()->setEmail($response['data']['customer']['email']);
            //$actual_quote->getShippingAddress()->setEmail($response['data']['customer']['email']);
            $actual_quote->getBillingAddress()->setCustomerId($customer->getId());
            $actual_quote->getShippingAddress()->setCustomerId($customer->getId());

            // Collect total and save
            //$actual_quote->collectTotals();
            // Disable old quote
            $actual_quote->setIsActive(0);
            // Submit the quote and create the order
            $actual_quote->save();
            $this->collectorSession->setVariable('is_iframe', 1);
            $order = $this->quoteManagement->submit($actual_quote);


            $this->orderSender->send($order);
            $order->setData('collector_invoice_id', $response['data']['purchase']['purchaseIdentifier']);
            if ($this->collectorSession->getVariable('btype') == \Collector\Base\Model\Session::B2B) {
                $order->setData('collector_ssn', $response['data']['businessCustomer']['organizationNumber']);
            }
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


            if (!$this->setOrderStatusState($order, $response["data"]["purchase"]["result"])) {
                throw new \Exception(__('Invalid order status'));
            }

            $order->save();
            $this->eventManager->dispatch(
                'checkout_onepage_controller_success_action',
                ['order_ids' => [$order->getId()]]
            );
            $this->checkoutSession->clearStorage();
            $this->checkoutSession->clearQuote();
            return $resultPage;
        } catch (\Exception $e) {
            if ($this->collectorSession->getVariable('btype') == \Collector\Base\Model\Session::B2B) {
                $storeID = $this->collectorConfig->getB2BStoreID();
            } else {
                $storeID = $this->collectorConfig->getB2CStoreID();
            }
            $soap = $this->apiRequest->getInvoiceSOAP(['ClientIpAddress' => $actual_quote->getRemoteIp()]);
            $req = array(
                'CorrelationId' => $response['data']['reference'],
                'CountryCode' => $this->collectorConfig->getCountryCode(),
                'InvoiceNo' => $response['data']['purchase']['purchaseIdentifier'],
                'StoreId' => $storeID,
            );
            try {
                $soap->CancelInvoice($req);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }

            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            $this->messageManager->addError($e->getMessage());
            return $this->redirect->redirect($this->response, '/');
        }
    }

    private function setOrderStatusState(&$order, $result = '')
    {
        try {
            switch ($result) {
                case "OnHold":
                    $status = $this->collectorConfig->getHoldStatus();
                    $state = $this->orderState->load($status)->getState();
                    break;
                case "Preliminary":
                    $status = $this->collectorConfig->getAcceptStatus();
                    $state = $this->orderState->load($status)->getState();
                    break;
                default:
                    $status = $this->collectorConfig->getDeniedStatus();
                    $state = $this->orderState->load($status)->getState();
                    break;
            }
            $order->setState($state)->setStatus($status);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    private function getCountryCodeByName($name, $default)
    {
        $id = $default;
        switch ($name) {
            case 'Sverige' :
                $id = 'SE';
                break;
            case 'Norge' :
                $id = 'NO';
                break;
            case 'Suomi' :
                $id = 'FI';
                break;
            case 'Deutschland':
                $id = 'DE';
                break;
        }
        return $id;
    }
}
