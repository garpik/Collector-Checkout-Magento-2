<?php
namespace Collector\Iframe\Controller\Notification;
class Index extends \Magento\Framework\App\Action\Action {
	
	protected $resultPageFactory;
    protected $jsonHelper;
	protected $layoutFactory;
	protected $resultJsonFactory;
	protected $helper;
    protected $formKey;
	protected $orderInterface;
	protected $quoteCollectionFactory;
	protected $storeManager;
	protected $customerFactory;
	protected $customerRepository;
	protected $shippingRate;
    protected $checkoutSession;
	protected $cartRepositoryInterface;
	protected $cartManagementInterface;

    /**
     * Index constructor.
     * @param \Magento\Framework\View\Result\LayoutFactory $_layoutFactory
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param \Magento\Sales\Api\Data\OrderInterface $_orderInterface
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
	public function __construct(
		\Magento\Framework\View\Result\LayoutFactory $_layoutFactory,
		\Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface,
		\Magento\Quote\Api\CartManagementInterface $_cartManagementInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Store\Model\StoreManagerInterface $_storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
		\Magento\Checkout\Model\Session $_checkoutSession,
		\Magento\Customer\Model\CustomerFactory $_customerFactory,
		\Magento\Sales\Api\Data\OrderInterface $_orderInterface,
		\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
		\Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->formKey = $formKey;
		$this->helper = $_helper;
		$this->layoutFactory = $_layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $_checkoutSession;
		$this->orderInterface = $_orderInterface;
		$this->resultJsonFactory = $resultJsonFactory;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
		$this->storeManager = $_storeManager;
		$this->customerRepository = $_customerRepository;
		$this->customerFactory = $_customerFactory;
		$this->shippingRate = $_shippingRate;
		$this->cartRepositoryInterface = $_cartRepositoryInterface;
		$this->cartManagementInterface = $_cartManagementInterface;
        parent::__construct($context);
    }
	
    public function execute(){
		$resultPage = $this->resultPageFactory->create();
        return $resultPage;
	}
}