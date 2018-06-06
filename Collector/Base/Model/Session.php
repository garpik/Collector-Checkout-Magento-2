<?php

namespace Collector\Base\Model;

class Session extends \Magento\Framework\Session\SessionManager
{
    protected $storage;

    public function setVariable($name, $value)
    {
        $this->storage->setData($name, $value);
    }

    public function getVariable($name)
    {
        if ($this->storage->getData($name)) {
            return $this->storage->getData($name);
        }
        return null;
    }
}