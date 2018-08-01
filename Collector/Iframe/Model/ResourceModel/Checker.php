<?php

namespace Collector\Iframe\Model\ResourceModel;

/**
 * Class Checker
 * @package Collector\Iframe\Model\ResourceModel
 */
class Checker extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var bool
     */
    protected $_useIsObjectNew = false;
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('collector_order_checker', 'id');
    }

    /**
     * @param $isNew
     */
    public function useIsObjectNew($isNew)
    {
        $this->_useIsObjectNew = $isNew;
    }

}
