<?php


namespace Collector\Iframe\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Fraud
 * @package Collector\Iframe\Model
 */
class Fraud extends AbstractModel implements \Collector\Iframe\Api\Data\FraudInterface, \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'collector_anti_fraud';

    /**
     * @var string
     */
    protected $_cacheTag = 'collector_anti_fraud';
    /**
     * @var string
     */
    protected $_eventPrefix = 'collector_anti_fraud';

    protected function _construct()
    {
        $this->_init('Collector\Iframe\Model\ResourceModel\Fraud');
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