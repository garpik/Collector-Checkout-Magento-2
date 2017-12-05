<?php
namespace Collector\Gateways\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class RefundObserver extends AbstractDataAssignObserver{
    public function execute(Observer $observer){
		file_put_contents("test", "test22\n", FILE_APPEND);
		if ($observer->getCreditMemo() != null)
			file_put_contents("test", "observer credit memo id: " . $observer->getCreditMemo()->getId() . "\n", FILE_APPEND);
    }
}