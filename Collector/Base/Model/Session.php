<?php

namespace Collector\Base\Model;

class Session extends \Magento\Framework\Session\SessionManager
{

    const B2B = 'b2b';
    const B2C = 'b2c';
    protected $storage;

    public function setVariable($name, $value)
    {
        $this->storage->setData($name, $value);
        return $this;
    }

    public function getVariable($name)
    {
        if (!empty($this->storage->getData($name))) {
            return $this->storage->getData($name);
        }
        return "";
    }
}