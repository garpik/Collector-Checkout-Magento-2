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

    protected $resultPageFactory;
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $_helper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (isset($_GET['OrderNo']) && isset($_GET['InvoiceStatus'])){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($_GET['OrderNo']);
            if ($order->getId()){
                if ($_GET['InvoiceStatus'] == "0"){
                    $status = $this->helper->getHoldStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
                else if ($_GET['InvoiceStatus'] == "1"){
                    $status = $this->helper->getAcceptStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
                else {
                    $status = $this->helper->getDeniedStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
            }
        }
        return $this->resultPageFactory->create();
    }
}
