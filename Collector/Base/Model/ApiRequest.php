<?php

namespace Collector\Base\Model;

class ApiRequest
{
    /**
     * @var Config
     */
    protected $collectorConfig;
    /**
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $soapClientFactory;
    /**
     * @var Session
     */
    protected $collectorSession;

    /**
     * ApiRequest constructor.
     * @param Config $collectorConfig
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory
     * @param Session $collectorSession
     */
    public function __construct(
        \Collector\Base\Model\Config $collectorConfig,
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory,
        \Collector\Base\Model\Session $collectorSession
    )
    {
        $this->collectorSession = $collectorSession;
        $this->soapClientFactory = $soapClientFactory;
        $this->collectorConfig = $collectorConfig;
    }

    public function getInvoiceSOAP($header = [])
    {
        $soapClient = $this->soapClientFactory->create($this->collectorConfig->getInvoiceWSDL(), [
            'soap_version' => SOAP_1_1,
            'exceptions' => 1,
            'trace' => true
        ]);
        $header['Username'] = $this->collectorConfig->getUsername();
        $header['Password'] = $this->collectorConfig->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->collectorConfig->getHeaderUrl(), $k, $v);
        }
        $soapClient->__setSoapHeaders($headerList);
        return $soapClient;
    }

    private function getPID($cart = null)
    {
        if (!empty($this->collectorSession->getCollectorPrivateId(''))) {
            return $this->collectorSession->getCollectorPrivateId('');
        }
        if ($cart == null) {
            return '';
        }
        return $cart->getQuote()->getData('collector_private_id');
    }


    public function callCheckouts($cart = null)
    {
        $pid = $this->getPID($cart);
        $storeId = $this->collectorConfig->getB2BrB2CStore();
        $path = "/merchants/" . $storeId . "/checkouts/" . $pid;
        $ch = curl_init($this->collectorConfig->getWSDL() . $path);
        $this->setCurlGET($ch);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:SharedKey ' . $this->collectorConfig->getHash($path)));
        $output = curl_exec($ch);
        return json_decode($output, true);
    }

    public function callCheckoutsCart($params, $cart = null)
    {
        $pid = $this->getPID($cart);
        $storeId = $this->collectorConfig->getB2BrB2CStore();
        $path = '/merchants/' . $storeId . '/checkouts/' . $pid . '/cart';
        $json = json_encode($params);
        $hashstr = 'SharedKey ' . $this->collectorConfig->getHash($path, $json);
        $ch = curl_init($this->collectorConfig->getWSDL() . $path);
        $this->setCurlPUT($ch);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8', 'Authorization:' . $hashstr));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_exec($ch);
        curl_close($ch);
    }

    public function callCheckoutsFees($params, $cart = null)
    {
        $pid = $this->getPID($cart);
        $storeId = $this->collectorConfig->getB2BrB2CStore();
        $path = '/merchants/' . $storeId . '/checkouts/' . $pid . '/fees';
        $json = json_encode($params);
        $hashstr = 'SharedKey ' . $this->collectorConfig->getHash($path, $json);
        $ch = curl_init($this->collectorConfig->getWSDL() . $path);
        $this->setCurlPUT($ch);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8', 'Authorization:' . $hashstr));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_exec($ch);
        curl_close($ch);
    }

    public function getTokenRequest($params = [])
    {
        $path = '/checkout';
        $json = json_encode($params);
        $hashstr = 'SharedKey ' . $this->collectorConfig->getHash($path, $json);
        $ch = curl_init($this->collectorConfig->getWSDL() . $path);
        $this->setCurlPOST($ch);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8', 'Authorization:' . $hashstr));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    private function setCurlGET(&$ch)
    {
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    }

    private function setCurlPUT(&$ch)
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    }

    private function setCurlPOST(&$ch)
    {

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    }
}