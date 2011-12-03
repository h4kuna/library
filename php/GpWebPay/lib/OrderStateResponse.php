<?php

namespace Pay3dSecure;

require_once 'OrderResponse.php';

/**
 * Description of OrderStateResponse
 *
 * @author Milan Matějček
 */
class OrderStateResponse extends OrderResponse implements IResponse
{
    // <editor-fold defaultstate="collapsed" desc="state of order">
    public static $code = array(
        1  => 'REQUESTED',  //Neukončena
        2  => 'PENDING',    //Neukončena
        3  => 'CREATED',    //Neukončena
        4  => 'APPROVED',   //Autorizována
        5  => 'APPROVE_REVERSED', //Reverzována
        6  => 'UNAPPROVED', //Neautorizována
        7  => 'DEPOSITED_BATCH_OPENED', //Uhrazena
        8  => 'DEPOSITED_BATCH_CLOSED', //Zpracována
        9  => 'ORDER_CLOSED', //Uzavřena
        10 => 'DELETED',    //Vymazána
        11 => 'CREDITED_BATCH_OPENED',  //Kreditována
        12 => 'CREDITED_BATCH_CLOSED',  //Kreditována
        13 => 'DECLINED',   //Zamítnuta
    );
    // </editor-fold>

    protected $state;

    public function getVerify()
    {
        return array($this->orderNumber, $this->state, $this->primaryReturnCode, $this->secondaryReturnCode);
    }

    /**
     * prevede stavové číslo na text
     * @return string
     */
    public function getStateCode()
    {
        return self::$code[$this->state];
    }

    /**
     * číslo stavu
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }
}
