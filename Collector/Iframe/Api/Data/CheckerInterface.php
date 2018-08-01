<?php

namespace Collector\Iframe\Api\Data;

interface CheckerInterface
{
    const ENTITY_ID = 'id';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);
}
