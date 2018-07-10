<?php

namespace Collector\Iframe\Plugin;

class Url
{
    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Url constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Collector\Base\Model\Config $collectorConfig
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Collector\Base\Model\Config $collectorConfig
    )
    {
        $this->collectorConfig = $collectorConfig;
        $this->urlBuilder = $urlBuilder;
    }

    public function afterGetCheckoutUrl($subject = null, $result)
    {
        if ($this->collectorConfig->getEnable()) {
            return $this->urlBuilder->getUrl('collectorcheckout');
        }
        return $result;
    }
}
