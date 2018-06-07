<?php
namespace perudesarrollo\AeMotor;

class Exception extends \Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
