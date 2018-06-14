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

    /**
     * CancelObserver constructor.
     * @param \Collector\Gateways\Helper\Data $_helper
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory
     * @param \Collector\Base\Logger\Collector $logger
     */
    public function __construct(
        \Collector\Gateways\Helper\Data $_helper,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Collector\Base\Logger\Collector $logger
    )
    {
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
            $client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), [
                'soap_version' => SOAP_1_1,
                'exceptions' => 1,
                'trace' => true
            ]);
            $headerList = [
                new \SoapHeader($this->helper->getHeaderUrl(), 'Username', $this->helper->getUsername()),
                new \SoapHeader($this->helper->getHeaderUrl(), 'Password', $this->helper->getPassword())
            ];
            if ($this->helper->getEnable()) {
                if ($order->getData('collector_ssn') !== null) {
                    $storeId = $this->helper->getB2BStoreID();
                } else {
                    $storeId = $this->helper->getB2CStoreID();
                }
            } else {
                $storeId = $this->helper->getB2CStoreID();
            }
            $client->__setSoapHeaders($headerList);
            $req = [
                'CorrelationId' => $order->getIncrementId(),
                'CountryCode' => $this->helper->getCountryCode(),
                'InvoiceNo' => $order->getData('collector_invoice_id'),
                'StoreId' => $storeId,
            ];
            $this->logger->info(var_export($req));
            try {
                $client->CancelInvoice($req);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
        }
    }
}