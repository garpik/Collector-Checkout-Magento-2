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

namespace Collector\Iframe\Controller\Cajax;

class Cajax extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectionSession;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * Cajax constructor.
     * @param \Magento\Framework\View\Result\LayoutFactory $_layoutFactory
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $product
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\View\Result\LayoutFactory $_layoutFactory,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    )
    {
        parent::__construct($context);
        $this->logger = $logger;
        $this->collectionSession = $_collectorSession;
        $this->product = $product;
        $this->cart = $cart;
        $this->formKey = $formKey;
        $this->helper = $_helper;
        $this->layoutFactory = $_layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $updateCart = false;
        $updateFees = false;
        $changeLanguage = false;
        if ($this->getRequest()->isAjax()) {
            $changed = false;
            switch ($this->getRequest()->getParam('type')) {
                case "sub":
                    $allItems = $this->cart->getQuote()->getAllVisibleItems();
                    $id = explode('_', $this->getRequest()->getParam('id'))[1];
                    foreach ($allItems as $item) {
                        if ($item->getId() == $id) {
                            $qty = $item->getQty();
                            if ($qty > 1) {
                                $item->setQty($qty - 1);
                            } else {
                                $this->cart->getQuote()->removeItem($item->getId());
                                if (count($allItems) == 1) {
                                    return $result->setData("redirect");
                                }
                            }
                            $updateCart = true;
                            $updateFees = true;
                            $changed = true;
                        }
                    }
                    $this->cart->save();
                    break;
                case "inc":
                    $allItems = $this->cart->getQuote()->getAllVisibleItems();
                    $id = explode('_', $this->getRequest()->getParam('id'))[1];
                    foreach ($allItems as $item) {
                        if ($item->getId() == $id) {
                            $item->setQty($item->getQty() + 1);
                            $changed = true;
                            $updateCart = true;
                            $updateFees = true;
                        }
                    }
                    $this->cart->save();
                    break;
                case "radio":
                    $changed = true;
                    $updateFees = true;
                    break;
                case "submit":
                    if (!empty($this->collectionSession->getVariable('collector_applied_discount_code'))) {
                        $this->helper->unsetDiscountCode();
                    } else {
                        $this->helper->setDiscountCode($this->getRequest()->getParam('value'));
                    }
                    $changed = true;
                    $updateCart = true;
                    $updateFees = true;
                    break;
                case "newsletter":
                    $this->collectionSession->setVariable('newsletter_signup', $this->getRequest()->getParam('id') == "true");
                    break;
                case "del":
                    $allItems = $this->cart->getQuote()->getAllVisibleItems();
                    $id = explode('_', $this->getRequest()->getParam('id'))[1];
                    foreach ($allItems as $item) {
                        if ($item->getId() == $id) {
                            $this->cart->removeItem($item->getId());
                            if (count($allItems) == 1) {
                                return $result->setData("redirect");
                            }
                            $changed = true;
                            $updateCart = true;
                            $updateFees = true;
                        }
                    }
                    $this->cart->save();
                    break;
                case "update":
                    $changed = true;
                    break;
                case "btype":
                    $this->collectionSession->setVariable('btype', $this->getRequest()->getParam('value'));
                    $this->collectionSession->setVariable('collector_public_token', '');
                    $changeLanguage = true;
                    $changed = true;
                    $updateCart = true;
                    $updateFees = true;
                    break;
                case "updatecustomer":
                    try {
                        $resp = $this->getCheckoutData();
                        if (isset($resp['data']['businessCustomer']['invoiceAddress'])) {
                            $sfirstname = $resp['data']['businessCustomer']['firstName'];
                            $slastname = $resp['data']['businessCustomer']['lastName'];
                            if (isset($resp['data']['businessCustomer']['deliveryAddress']['address'])) {
                                $sstreet = $resp['data']['businessCustomer']['deliveryAddress']['address'];
                            } else {
                                $sstreet = $resp['data']['businessCustomer']['deliveryAddress']['postalCode'];
                            }
                            $scity = $resp['data']['businessCustomer']['deliveryAddress']['city'];
                            $spostcode = $resp['data']['businessCustomer']['deliveryAddress']['postalCode'];

                            $bfirstname = $resp['data']['businessCustomer']['firstName'];
                            $blastname = $resp['data']['businessCustomer']['lastName'];
                            if (isset($resp['data']['businessCustomer']['invoiceAddress']['address'])) {
                                $bstreet = $resp['data']['businessCustomer']['invoiceAddress']['address'];
                            } else {
                                $bstreet = $resp['data']['businessCustomer']['invoiceAddress']['postalCode'];
                            }
                            $bcity = $resp['data']['businessCustomer']['invoiceAddress']['city'];
                            $bpostcode = $resp['data']['businessCustomer']['invoiceAddress']['postalCode'];
                            $btelephone = $resp['data']['businessCustomer']['mobilePhoneNumber'];
                        } else {
                            $sfirstname = $resp['data']['customer']['deliveryAddress']['firstName'];
                            $slastname = $resp['data']['customer']['deliveryAddress']['lastName'];
                            $sstreet = $resp['data']['customer']['deliveryAddress']['address'];
                            $scity = $resp['data']['customer']['deliveryAddress']['city'];
                            $spostcode = $resp['data']['customer']['deliveryAddress']['postalCode'];

                            $bfirstname = $resp['data']['customer']['billingAddress']['firstName'];
                            $blastname = $resp['data']['customer']['billingAddress']['lastName'];
                            $bstreet = $resp['data']['customer']['billingAddress']['address'];
                            $bcity = $resp['data']['customer']['billingAddress']['city'];
                            $bpostcode = $resp['data']['customer']['billingAddress']['postalCode'];
                            $btelephone = $resp['data']['customer']['mobilePhoneNumber'];
                        }
                        $this->cart->getQuote()->getBillingAddress()->addData(array(
                            'firstname' => $bfirstname,
                            'lastname' => $blastname,
                            'street' => $bstreet,
                            'city' => $bcity,
                            'postcode' => $bpostcode,
                            'telephone' => $btelephone
                        ));
                        $this->cart->getQuote()->getShippingAddress()->addData(array(
                            'firstname' => $sfirstname,
                            'lastname' => $slastname,
                            'street' => $sstreet,
                            'city' => $scity,
                            'postcode' => $spostcode
                        ));
                        $this->cart->getQuote()->getShippingAddress()->save();
                        $this->cart->getQuote()->collectTotals();
                        $this->cart->getQuote()->save();
                        $this->helper->getShippingMethods();
                        $updateCart = true;
                        $updateFees = true;
                    } catch (\Exception $e) {
                    }
                    break;


            }
            if ($changed) {
                if ($updateCart) {
                    $this->helper->updateCart();
                }
                if ($updateFees) {
                    $this->helper->updateFees();
                }
                $page = $this->resultPageFactory->create();
                $layout = $page->getLayout();
                $block = $layout->getBlock('collectorcart');
                $block->setTemplate('Collector_Iframe::Cart.phtml');
                $html = $block->toHtml();
                if ($changeLanguage) {
                    $checkoutBlock = $layout->getBlock('collectorcheckout');
                    $checkoutBlock->setTemplate('Collector_Iframe::Checkout.phtml');
                    $checkoutHtml = $checkoutBlock->toHtml();
                    $return = array(
                        'cart' => $html,
                        'checkout' => $checkoutHtml
                    );
                    return $result->setData($return);
                } else {
                    $return = array(
                        'cart' => $html
                    );
                    return $result->setData($return);
                }
            }
        }
        return $result->setData("");
    }


    private function getCheckoutData()
    {
        $pid = $this->collectionSession->getVariable('collector_private_id');
        $pusername = $this->helper->getUsername();
        $psharedSecret = $this->helper->getPassword();
        $array = array();
        $array['countryCode'] = $this->helper->getCountryCode();
        $storeId = 0;
        if (!empty($this->collectionSession->getVariable('btype'))) {
            if ($this->collectionSession->getVariable('btype') == 'b2b') {
                $storeId = $this->helper->getB2BStoreID();
            } else {
                $storeId = $this->helper->getB2CStoreID();
            }
        } else {
            switch ($this->getCustomerType()) {
                case \Collector\Iframe\Model\Config\Source\Customertype::PRIVATE_CUSTOMER:
                    $this->collectionSession->setVariable('btype', 'b2c');
                    $storeId = $this->helper->getB2CStoreID();
                    break;
                case \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER:
                    $this->collectionSession->setVariable('btype', 'b2b');
                    $storeId = $this->helper->getB2BStoreID();
                    break;
                case \Collector\Iframe\Model\Config\Source\Customertype::PRIVATE_BUSINESS_CUSTOMER:
                    $this->collectionSession->setVariable('btype', 'b2c');
                    $storeId = $this->helper->getB2CStoreID();
                    break;
            }
        }
        $path = '/merchants/' . $storeId . '/checkouts/' . $pid;
        $hash = $pusername . ":" . hash("sha256", $path . $psharedSecret);
        $hashstr = 'SharedKey ' . base64_encode($hash);
        $ch = curl_init($this->helper->getWSDL() . "merchants/" . $storeId . "/checkouts/" . $pid);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('charset=utf-8', 'Authorization:' . $hashstr));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $data = json_decode($output, true);
        curl_close($ch);
        return $data;
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
