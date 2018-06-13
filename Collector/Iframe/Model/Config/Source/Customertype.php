<?php

namespace Collector\Iframe\Model\Config\Source;

class Customertype implements \Magento\Framework\Option\ArrayInterface
{
    const PRIVATE_CUSTOMER = 1;
    const BUSINESS_CUSTOMER = 2;
    const PRIVATE_BUSINESS_CUSTOMER = 3;

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::PRIVATE_CUSTOMER => __('Private Customers'),
            self::BUSINESS_CUSTOMER => __('Business Customers'),
            self::PRIVATE_BUSINESS_CUSTOMER => __('Private customers & Business customers')
        ];
    }
}