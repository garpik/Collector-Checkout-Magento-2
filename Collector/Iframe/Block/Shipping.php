<?php

namespace Collector\Iframe\Block;

class Shipping extends \Magento\Framework\View\Element\Template
{
    protected $storeManager;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $serializer = null;
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $configCacheType;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;

    /**
     * Shipping constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Collector\Iframe\Helper\Data $helper
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Collector\Base\Model\Session $_collectorSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Collector\Iframe\Helper\Data $helper,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    )
    {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->configCacheType = $configCacheType;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->checkoutSession = $_checkoutSession;
        $this->collectorSession = $_collectorSession;
        $this->storeManager = $context->getStoreManager();
    }

    public function getStreetLine($lineNumber)
    {
        $street = $this->getAddress()->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }


    public function isVisible()
    {
        return $this->helper->isShippingAddressEnabled();
    }

    public function getAddress()
    {
        return $this->checkoutSession->getQuote()->getShippingAddress();
    }

    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if ($collection === null) {
            $collection = $this->countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }
        return $collection;
    }

    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }
        return $this->serializer;
    }


    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        if ($defValue === null) {
            $defValue = $this->getAddress()->getCountryId();
        }
        $cacheKey = 'COLLECTOR_DIRECTORY_COUNTRY_SELECT_STORE_' . $this->storeManager->getStore()->getCode();
        $cache = $this->configCacheType->load($cacheKey);
        $options = [];
        if ($cache) {
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            $options_source = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
            foreach ($options_source as $option) {
                if (in_array($option['value'], $this->helper->allowedCountries) || $option['value'] == '') {
                    $options[] = $option;
                }
            }
            $this->configCacheType->save($this->getSerializer()->serialize($options), $cacheKey);
        }

        return $this->getLayout()
            ->createBlock(\Magento\Framework\View\Element\Html\Select::class)
            ->setName($name)
            ->setId($id)
            ->setTitle(__($title))
            ->setValue($defValue)
            ->setOptions($options)
            ->setExtraParams('data-validate="{\'validate-select\':true}"')
            ->getHtml();
    }


}