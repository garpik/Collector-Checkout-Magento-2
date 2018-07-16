<?php

namespace Collector\Gateways\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class CancelObserver extends AbstractDataAssignObserver
{

    /**
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $clientFactory;
    /**
     * @var \Collector\Gateways\Helper\Data
     */
    protected $helper;
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;
    protected $collectorConfig;

    /**
     * CancelObserver constructor.
     * @param \Collector\Gateways\Helper\Data $_helper
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Base\Model\Config $collectorConfig
     */
    public function __construct(
        \Collector\Gateways\Helper\Data $_helper,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Base\Model\Config $collectorConfig
    )
    {
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
        $this->logger = $logger;
        $this->helper = $_helper;
        $this->clientFactory = $clientFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        if (strpos($method->getCode(), "collector") !== false) {
            $client = $this->apiRequest->getInvoiceSOAP();
            if ($this->collectorConfig->getEnable()) {
                if ($order->getData('collector_ssn') !== null) {
                    $storeId = $this->collectorConfig->getB2BStoreID();
                } else {
                    $storeId = $this->collectorConfig->getB2CStoreID();
                }
            } else {
                $storeId = $this->collectorConfig->getB2CStoreID();
            }
            $req = [
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->collectorConfig->getCountryCode(),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeId,
            ];
            try {
                $client->CancelInvoice($req);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
        }
    }
}