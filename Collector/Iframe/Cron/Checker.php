<?php

namespace Collector\Iframe\Cron;

class Checker
{
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;
    /**
     * @var \Collector\Iframe\Model\ResourceModel\Checker\Collection
     */
    protected $checkerCollection;
    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;
    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $_quoteCollectionFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Checker constructor.
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Iframe\Model\ResourceModel\Checker\Collection $checkerCollection
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Collector\Base\Model\Config $collectorConfig
     */
    public function __construct(
        \Collector\Base\Logger\Collector $logger,
        \Collector\Iframe\Model\ResourceModel\Checker\Collection $checkerCollection,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Collector\Base\Model\Config $collectorConfig
    ) {
        $this->collectorConfig = $collectorConfig;
        $this->apiRequest = $apiRequest;
        $this->_quoteCollectionFactory = $_quoteCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->checkerCollection = $checkerCollection;
        $this->logger = $logger;
    }

    /**
     * Write to system.log
     *
     * @return void
     */

    public function execute()
    {
        $this->checkerCollection->addFieldToFilter('created_at',
            ['to' => new \Zend_Db_Expr('DATE_ADD(NOW(), INTERVAL -2 MINUTE)')]);
        foreach ($this->checkerCollection as $checker) {
            $order = $this->orderFactory->create();
            $order->loadByIncrementId($checker->getIncrementId());
            if (!$order->getId()) {
                $actual_quote = $this->_quoteCollectionFactory->create()->addFieldToFilter(
                    "reserved_order_id",
                    $checker->getIncrementId()
                )->getFirstItem();
                if ($actual_quote->getId()) {
                    $data = $this->apiRequest->callCheckouts(null, $actual_quote->getCollectorPrivateId(),
                        $actual_quote->getCollectorBtype());
                    if ($actual_quote->getCollectorBtype() == \Collector\Base\Model\Session::B2B) {
                        $storeID = $this->collectorConfig->getB2BStoreID();
                    } else {
                        $storeID = $this->collectorConfig->getB2CStoreID();
                    }
                    $soap = $this->apiRequest->getInvoiceSOAP(['ClientIpAddress' => $actual_quote->getRemoteIp()]);
                    $req = array(
                        'CorrelationId' => $data['data']['reference'],
                        'CountryCode' => $this->collectorConfig->getCountryCode(),
                        'InvoiceNo' => $data['data']['purchase']['purchaseIdentifier'],
                        'StoreId' => $storeID,
                    );
                    try {
                        $soap->CancelInvoice($req);
                        $this->logger->info('Canceled invoice ID is ' . $checker->getIncrementId());
                    } catch (\Exception $e) {
                        $this->logger->error('Error while canceling order with InvoiceId ' . $checker->getIncrementId() . ' ' . $e->getMessage());
                    }
                } else {
                    $this->logger->error('Error while checking order with InvoiceId ' . $checker->getIncrementId());
                }
            }
            $checker->delete();
        }
    }
}
