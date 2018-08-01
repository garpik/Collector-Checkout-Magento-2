<?php

namespace Collector\Iframe\Model\ResourceModel\Fraud;

/**
 * Class Collection
 * @package Collector\Iframe\Model\ResourceModel\Fraud
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Collector\Iframe\Model\Fraud',
            'Collector\Iframe\Model\ResourceModel\Fraud'
        );
        $this->_map['fields']['entity_id'] = 'main_table.' . $this->_idFieldName;
    }

}