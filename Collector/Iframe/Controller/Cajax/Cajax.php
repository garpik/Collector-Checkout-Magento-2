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
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

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
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\ApiRequest $apiRequest
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
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    )
    {
        parent::__construct($context);
        $this->apiRequest = $apiRequest;
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
                case "shippingValidate":
                    $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
                    $errors = $this->cart->getQuote()->getShippingAddress()->validate();
                    if ($errors == true) {
                        $errors = [];
                    }
                    if (!in_array($this->cart->getQuote()->getShippingAddress()->getCountryId(), $this->helper->allowedCountries) && $this->getRequest()->getParam('ignore_country') == false) {
                        $errors[] = ('This country is not allowed');
                    }
                    return $result->setData(['error' => count($errors) > 0 ? 1 : 0, 'messages' => implode("\n", $errors)]);
                case "shippingAddress":
                    $customizeAttribute = ['street_1', 'street_2'];
                    $name = $this->getRequest()->getParam('name');
                    $value = $this->getRequest()->getParam('value');
                    if (empty($name)) {
                        return $result->setData(false);
                    }
                    $this->cart->getQuote()->getShippingAddress()->setSameAsBilling(0);
                    if (in_array($name, $customizeAttribute)) {
                        switch ($name) {
                            case 'street_1':
                                $street = $this->cart->getQuote()->getShippingAddress()->getStreet();
                                $street[0] = $value;
                                $this->cart->getQuote()->getShippingAddress()->setStreet($street);
                                break;
                            case 'street_2':
                                $street = $this->cart->getQuote()->getShippingAddress()->getStreet();
                                $street[1] = $value;
                                $this->cart->getQuote()->getShippingAddress()->setStreet($street);
                                break;
                        }
                    } else {
                        $this->cart->getQuote()->getShippingAddress()->setData($name, $value);
                    }
                    $this->cart->getQuote()->getShippingAddress()->save();
                    $this->cart->getQuote()->getBillingAddress()->save();
                    $this->cart->getQuote()->collectTotals();
                    $this->cart->getQuote()->save();
                    $changed = true;
                    $updateFees = true;
                    $updateCart = true;
                    break;
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
                    if (!empty($this->getRequest()->getParam('id'))) {
                        $this->helper->setShippingMethod($this->getRequest()->getParam('id'));
                    }
                    $changed = true;
                    $updateFees = true;
                    break;
                case "submit":
                    if (!empty($this->collectionSession->getCollectorAppliedDiscountCode())) {
                        $this->helper->unsetDiscountCode();
                    } else {
                        $this->helper->setDiscountCode($this->getRequest()->getParam('value'));
                    }
                    $changed = true;
                    $updateCart = true;
                    $updateFees = true;
                    break;
                case "newsletter":
                    $this->collectionSession->setNewsletterSignup($this->getRequest()->getParam('value') == "true");
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
                    $this->collectionSession->setBtype($this->getRequest()->getParam('value'));
                    $this->collectionSession->setCollectorPublicToken('');
                    $changeLanguage = true;
                    $changed = true;
                    $updateCart = true;
                    $updateFees = true;
                    break;
                case "updatecustomer":
                    try {
                        $resp = $this->getCheckoutData();
                        if (isset($resp['data']['businessCustomer']['invoiceAddress'])) {
                            $shipping = [
                                'firstname' => $resp['data']['customer']['businessCustomer']['firstName'],
                                'lastname' => $resp['data']['customer']['businessCustomer']['lastName'],
                                'city' => $resp['data']['customer']['businessCustomer']['city'],
                                'postcode' => $resp['data']['customer']['businessCustomer']['postalCode']
                            ];
                            if (isset($resp['data']['businessCustomer']['deliveryAddress']['address'])) {
                                $shipping['street'] = $resp['data']['businessCustomer']['deliveryAddress']['address'];
                            } else {
                                $shipping['street'] = $resp['data']['businessCustomer']['deliveryAddress']['postalCode'];
                            }
                            $billing = ['firstname' => $resp['data']['businessCustomer']['firstName'],
                                'lastname' => $resp['data']['businessCustomer']['lastName'],
                                'city' => $resp['data']['businessCustomer']['invoiceAddress']['city'],
                                'postcode' => $resp['data']['businessCustomer']['invoiceAddress']['postalCode'],
                                'telephone' => $resp['data']['businessCustomer']['mobilePhoneNumber']
                            ];
                            if (isset($resp['data']['businessCustomer']['invoiceAddress']['address'])) {
                                $billing['street'] = $resp['data']['businessCustomer']['invoiceAddress']['address'];
                            } else {
                                $billing['street'] = $resp['data']['businessCustomer']['invoiceAddress']['postalCode'];
                            }
                        } else {
                            $shipping = [
                                'firstname' => $resp['data']['customer']['deliveryAddress']['firstName'],
                                'lastname' => $resp['data']['customer']['deliveryAddress']['lastName'],
                                'street' => $resp['data']['customer']['deliveryAddress']['address'],
                                'city' => $resp['data']['customer']['deliveryAddress']['city'],
                                'postcode' => $resp['data']['customer']['deliveryAddress']['postalCode']
                            ];
                            $billing = ['firstname' => $resp['data']['customer']['billingAddress']['firstName'],
                                'lastname' => $resp['data']['customer']['billingAddress']['lastName'],
                                'street' => $resp['data']['customer']['billingAddress']['address'],
                                'city' => $resp['data']['customer']['billingAddress']['city'],
                                'postcode' => $resp['data']['customer']['billingAddress']['postalCode'],
                                'telephone' => $resp['data']['customer']['mobilePhoneNumber']
                            ];
                        }
                        $this->cart->getQuote()->getBillingAddress()->addData($billing)->save();
                        $this->cart->getQuote()->getShippingAddress()->addData($shipping)->save();
                        $this->cart->getQuote()->collectTotals();
                        $this->cart->getQuote()->save();
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

                $shippingBlock = $layout->getBlock('collectorcart');
                $shippingBlock->setTemplate('Collector_Iframe::Shipping-methods.phtml');
                $shippingHtml = $shippingBlock->toHtml();

                if ($changeLanguage) {
                    $checkoutBlock = $layout->getBlock('collectorcheckout');
                    $checkoutBlock->setTemplate('Collector_Iframe::Checkout.phtml');
                    $checkoutHtml = $checkoutBlock->toHtml();
                    $return = array(
                        'cart' => $html,
                        'checkout' => $checkoutHtml,
                        'shipping' => $shippingHtml
                    );
                    return $result->setData($return);
                } else {
                    $return = array(
                        'cart' => $html,
                        'shipping' => $shippingHtml
                    );
                    return $result->setData($return);
                }
            }
        }
        return $result->setData("");
    }


    private function getCheckoutData()
    {
        return $this->apiRequest->callCheckouts($this->cart);
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
