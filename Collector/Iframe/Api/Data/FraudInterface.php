<?php

namespace Collector\Iframe\Api\Data;

interface FraudInterface
{
    const ENTITY_ID = 'id';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param string $id
     * @return \Collector\Iframe\Api\Data\StateInterface
     */
    public function setId($id);
}
