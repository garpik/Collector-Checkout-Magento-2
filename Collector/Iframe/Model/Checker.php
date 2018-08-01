<?php


namespace Collector\Iframe\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Checker
 * @package Collector\Iframe\Model
 */
class Checker extends AbstractModel implements \Collector\Iframe\Api\Data\CheckerInterface, \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'collector_order_checker';

    /**
     * @var string
     */
    protected $_cacheTag = 'collector_order_checker';
    /**
     * @var string
     */
    protected $_eventPrefix = 'collector_order_checker';

    protected function _construct()
    {
        $this->_init('Collector\Iframe\Model\ResourceModel\Checker');
    }


    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * @param int|mixed $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

}