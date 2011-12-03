<?php

namespace Pay3dSecure;

require_once 'Response.php';

/**
 * Description of OrderResponse
 *
 * @author Milan Matějček
 */
class OrderResponse extends Response implements IResponse
{
    protected $orderNumber;
    protected $digest;

    /**
     * @return string
     * @throws GpWebPayException
     */
    public function getDigets()
    {
        if(empty($this->digest))
            throw new GpWebPayException(__CLASS__ .'::$digest is empty. May be bad response.');
        return $this->digest;
    }

    public function getVerify()
    {
        return array($this->orderNumber, $this->primaryReturnCode, $this->secondaryReturnCode);
    }
}
