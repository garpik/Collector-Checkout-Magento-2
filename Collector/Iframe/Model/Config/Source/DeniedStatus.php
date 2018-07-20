<?php

namespace Collector\Iframe\Model\Config\Source;

use Magento\Sales\Model\Order as StateConst;

class DeniedStatus implements \Magento\Framework\Option\ArrayInterface
{

    protected $statusArray = [];
    protected $allowedState = [
        StateConst::STATE_CLOSED,
        StateConst::STATE_CANCELED,
    ];
    protected $statusCollection;

    protected $statusToStateCollection;

    /**
     * DeniedStatus constructor.
     * @param \Collector\Iframe\Model\ResourceModel\State\CollectionFactory $statusToStateCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
     */
    public function __construct(
        \Collector\Iframe\Model\ResourceModel\State\CollectionFactory $statusToStateCollection,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
    ) {
        $this->statusCollection = $statusCollection;
        $this->statusToStateCollection = $statusToStateCollection->create();
        $this->statusToStateCollection->addFieldToFilter('state', ['in' => $this->allowedState]);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (count($this->statusArray) == 0) {
            $statusLabels = $this->statusCollection->toOptionHash();
            foreach ($this->statusToStateCollection as $item) {
                $this->statusArray[$item->getStatus()] = __($statusLabels[$item->getStatus()]);
            }
        }
        return $this->statusArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
