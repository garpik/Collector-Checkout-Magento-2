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
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;
	
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
	
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
	
	protected $addressFactory;

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
	 * @param \Magento\Customer\Model\Session $customerSession
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
		\Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
		\Magento\Customer\Model\Session $customerSession
    )
    {
		$this->customerSession = $customerSession;
		$this->addressFactory = $addressFactory;
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
        return isset($this->paymentToMethod[$name]) ? $this->paymentToMethod[$name] : 'collector_invoice';
    }

    public function execute()
    {
        if (empty($this->collectorSession->getCollectorPublicToken(''))) {
            $this->logger->error('Error while public_token loading');
            $this->messageManager->addError(__('API request error.'));
            return $this->redirect->redirect($this->response, '/');
        }
        $response = $this->helper->getOrderResponse();
        $resultPage = $this->resultPageFactory->create();
		$createAccount = $this->collectorConfig->createAccount();
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
			if($this->customerSession->isLoggedIn()) {
				$createAccount = true;
				$email = $this->customerSession->getCustomer()->getEmail();
			}
			if (!$this->collectorConfig->isShippingAddressEnabled()) {
                if (isset($response['data']['businessCustomer']['invoiceAddress'])) {
                    $shippingAddressArr = [
                        'company' => $response['data']['businessCustomer']['deliveryAddress']['companyName'],
                        'firstname' => $response['data']['businessCustomer']['firstName'],
                        'lastname' => $response['data']['businessCustomer']['lastName'],
                        'street' => $response['data']['businessCustomer']['deliveryAddress']['address'],
                        'city' => $response['data']['businessCustomer']['deliveryAddress']['city'],
                        'postcode' => $response['data']['businessCustomer']['deliveryAddress']['postalCode'],
                        'telephone' => $response['data']['businessCustomer']['mobilePhoneNumber'],
                        'country_id' => $response['data']['countryCode'],
                        'same_as_billing' => 0
                    ];
                } else {
                    $shippingAddressArr = [
                        'company' => '',
                        'firstname' => $response['data']['customer']['deliveryAddress']['firstName'],
                        'lastname' => $response['data']['customer']['deliveryAddress']['lastName'],
                        'street' => $response['data']['customer']['deliveryAddress']['address'],
                        'city' => $response['data']['customer']['deliveryAddress']['city'],
                        'postcode' => $response['data']['customer']['deliveryAddress']['postalCode'],
                        'telephone' => $response['data']['customer']['mobilePhoneNumber'],
                        'country_id' => $response['data']['countryCode'],
                        'same_as_billing' => 0
                    ];

                }
                $actual_quote->getShippingAddress()->addData($shippingAddressArr);

                // Collect Rates and Set Shipping & Payment Method
                $this->shippingRate->setCode($actual_quote->getShippingAddress()->getShippingMethod())->getPrice();
                $shippingAddress = $actual_quote->getShippingAddress();
                //@todo set in order data
//                $shippingAddress->setCollectShippingRates(true)
//                    ->collectShippingRates(); //shipping method

                $actual_quote->getShippingAddress()->addShippingRate($this->shippingRate);
                $actual_quote->getShippingAddress()->save();
            }
            if (isset($response['data']['businessCustomer']['invoiceAddress'])) {
                $billingAddress = array(
                    'company' => $response['data']['businessCustomer']['invoiceAddress']['companyName'],
                    'firstname' => $response['data']['businessCustomer']['firstName'],
                    'lastname' => $response['data']['businessCustomer']['lastName'],
                    'street' => isset($response['data']['businessCustomer']['invoiceAddress']['address']) ?
                        $response['data']['businessCustomer']['invoiceAddress']['address'] : $response['data']['businessCustomer']['invoiceAddress']['postalCode'],
                    'city' => $response['data']['businessCustomer']['invoiceAddress']['city'],
                    'country_id' => $response['data']['countryCode'],
                    'postcode' => $response['data']['businessCustomer']['invoiceAddress']['postalCode'],
                    'telephone' => $response['data']['businessCustomer']['mobilePhoneNumber']
                );

            } else {
                $billingAddress = array(
                    'company' => '',
                    'firstname' => $response['data']['customer']['billingAddress']['firstName'],
                    'lastname' => $response['data']['customer']['billingAddress']['lastName'],
                    'street' => $response['data']['customer']['billingAddress']['address'],
                    'city' => $response['data']['customer']['billingAddress']['city'],
                    'country_id' => $response['data']['countryCode'],
                    'postcode' => $response['data']['customer']['billingAddress']['postalCode'],
                    'telephone' => $response['data']['customer']['mobilePhoneNumber']
                );
            }
            //load customer by email address
            $customer->loadByEmail($email);
            //check the customer
            if (!$customer->getEntityId() && $createAccount) {
                //If not avilable then create this customer
                $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email)
                    ->setPassword($email);
                $customer->save();
				
				if (isset($shippingAddressArr)){
					$cShippingAddress = $this->addressFactory->create();
					$cShippingAddress->setCustomerId($customer->getId());
					$cShippingAddress->setFirstname($firstname);
					$cShippingAddress->setLastname($lastname);
					$cShippingAddress->setCountryId($response['data']['countryCode']);
					$cShippingAddress->setPostcode($shippingAddressArr['postcode']);
					$cShippingAddress->setCity($shippingAddressArr['city']);
					$cShippingAddress->setTelephone($shippingAddressArr['telephone']);
					if ($shippingAddressArr['company'] != ''){
						$cShippingAddress->setCompany($shippingAddressArr['company']);
					}
					$cShippingAddress->setStreet($shippingAddressArr['street']);
					$cShippingAddress->setIsDefaultShipping('1');
					$cShippingAddress->setSaveInAddressBook('1');
					$cShippingAddress->save();
				}
				$cBillingAddress = $this->addressFactory->create();
				$cBillingAddress->setCustomerId($customer->getId());
				$cBillingAddress->setFirstname($firstname);
				$cBillingAddress->setLastname($lastname);
				$cBillingAddress->setCountryId($response['data']['countryCode']);
				$cBillingAddress->setPostcode($billingAddress['postcode']);
				$cBillingAddress->setCity($billingAddress['city']);
				$cBillingAddress->setTelephone($billingAddress['telephone']);
				if ($billingAddress['company'] != ''){
					$cBillingAddress->setCompany($billingAddress['company']);
				}
				$cBillingAddress->setStreet($billingAddress['street']);
				$cBillingAddress->setIsDefaultBilling('1');
				$cBillingAddress->setSaveInAddressBook('1');
				$cBillingAddress->save();
				
            }
            if (!empty($this->collectorSession->getNewsletterSignup(''))) {
                $this->subscriberFactory->create()->subscribe($response['data']['customer']['email']);
            }
			if ($createAccount){
				$customer->setEmail($email);
				$customer->save();
				$customer = $this->customerRepository->getById($customer->getEntityId());
				$actual_quote->assignCustomer($customer);
			}

            //Set Address to quote @todo add section in order data for seperate billing and handle it
            
            $actual_quote->getBillingAddress()->addData($billingAddress);
            $actual_quote->setPaymentMethod($paymentMethod); //payment method
            $actual_quote->getPayment()->importData(['method' => $paymentMethod]);
            $actual_quote->setReservedOrderId($response['data']['reference']);
			if ($createAccount){
				$actual_quote->getBillingAddress()->setCustomerId($customer->getId());
				$actual_quote->getShippingAddress()->setCustomerId($customer->getId());
			}

            $fee = 0;

            foreach ($response['data']['order']['items'] as $item) {
                if ($item['id'] == 'invoice_fee') {
                    $fee = $this->apiRequest->convert($item['unitPrice'], NULL, 'SEK');
                }
            }

            $actual_quote->setFeeAmount($fee);
            $actual_quote->setBaseFeeAmount($fee);

            // Disable old quote
            $actual_quote->setIsActive(0);
			
			if (!$createAccount){
				$actual_quote->setCustomerId(null);
				$actual_quote->setCustomerEmail($email);
				$actual_quote->setCustomerIsGuest(true);
				$actual_quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
				$actual_quote->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST);
			}
			
            // Submit the quote and create the order
            $actual_quote->save();
            $this->collectorSession->setIsIframe(1);
            $order = $this->quoteManagement->submit($actual_quote);
            $this->orderSender->send($order);
            $order->setCollectorInvoiceId($response['data']['purchase']['purchaseIdentifier']);

            if ($this->collectorSession->getBtype('') == \Collector\Base\Model\Session::B2B) {
                $order->setCollectorSsn($response['data']['businessCustomer']['organizationNumber']);
            }


            $order->setFeeAmount($fee);
            $order->setBaseFeeAmount($fee);

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
            if ($this->collectorSession->getBtype('') == \Collector\Base\Model\Session::B2B) {
                $storeID = $this->collectorConfig->getB2BStoreID();
            } else {
                $storeID = $this->collectorConfig->getB2CStoreID();
            }
            if (isset($actual_quote)) {
                $soap = $this->apiRequest->getInvoiceSOAP(['ClientIpAddress' => $actual_quote->getRemoteIp()]);
                $actual_quote->setReservedOrderId(0);
                $actual_quote->reserveOrderId();
                $actual_quote->save();
                $this->collectorSession->setCollectorPublicToken('');
                $this->collectorSession->setCollectorDataVariant('');
                $req = array(
                    'CorrelationId' => $response['data']['reference'],
                    'CountryCode' => $this->collectorConfig->getCountryCode(),
                    'InvoiceNo' => $response['data']['purchase']['purchaseIdentifier'],
                    'StoreId' => $storeID,
                );
                try {
                    $soap->CancelInvoice($req);
                    // Disable old quote
                    $actual_quote->setIsActive(1);
                    // Submit the quote and create the order
                    $actual_quote->save();
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->logger->error($e->getTraceAsString());
                }
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
                    $activeStatus = $this->collectorConfig->getAcceptStatus();
                    $activeState = $this->orderState->load($activeStatus)->getState();
                    $order->setHoldBeforeState($activeState)->setHoldBeforeStatus($activeStatus);

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
