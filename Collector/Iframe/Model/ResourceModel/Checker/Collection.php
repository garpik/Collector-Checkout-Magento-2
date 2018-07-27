<?php

namespace Collector\Iframe\Model\ResourceModel\Checker;

/**
 * Class Collection
 * @package Collector\Iframe\Model\ResourceModel\Checker
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
            'Collector\Iframe\Model\Checker',
            'Collector\Iframe\Model\ResourceModel\Checker'
        );
        $this->_map['fields']['entity_id'] = 'main_table.' . $this->_idFieldName;
    }

}