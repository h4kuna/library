<?php

namespace Pay3dSecure;

use Nette\Object, Nette\Environment;

require_once 'lib/SoapClient.php';
require_once 'lib/Signature.php';
require_once 'lib/OrderStateResponse.php';
require_once 'lib/GpWebPayException.php';

/**
 * Description of GpWebPay
 *
 * @author Milan Matějček
 */
class GpWebPay extends Object {
    /** variables of config */
    const PRIVATE_KEY = 'privKey';
    const GP_CERT = 'gpCert';
    const PASSWORD = 'password';
    const MERCHANT_NUM = 'merchantNum';
    const WSDL = 'wsdl';
    const PATH = 'path';
    /** měna se musí násobit 100 pro bránu */
    const MULTIPLY100 = 100;

    /**
     * setup for comunication with gate
     * @vars string
     */
    private $gpCert, $privKey, $password, $merchantNum;
    /** @var OrderStateResponse */
    private $lastResponse;
    /**
     * only wsdl mode
     * @var SoapClient
     */
    private $client = NULL;

    public function __construct($config='webPay') {
        $options = (array) Environment::getConfig($config);
        $this->checkConfig($options, $config);

        $options['classmap'] = (isset($options['classmap'])) ? (array) $options['classmap'] :
            array( 'Response' => __NAMESPACE__ . '\Response',
                   'OrderResponse' => __NAMESPACE__ . '\OrderResponse',
                   'OrderStateResponse' => __NAMESPACE__ . '\OrderStateResponse',);

        $this->gpCert = $options[self::PATH] . \DIRECTORY_SEPARATOR . $options[self::GP_CERT];
        $this->privKey = $options[self::PATH] . \DIRECTORY_SEPARATOR . $options[self::PRIVATE_KEY];
        $this->password = $options[self::PASSWORD];
        $this->merchantNum = $options[self::MERCHANT_NUM];

        $wsdl = $options[self::WSDL];
        unset($options[self::WSDL], $options[self::PRIVATE_KEY], $options[self::GP_CERT],
                $options[self::PASSWORD], $options[self::MERCHANT_NUM]);

        $this->client = new SoapClient($wsdl, $options);
    }

    /** @return SoapClient */
    public function getClient() {
        return $this->client;
    }

    /** @return Response */
    public function getLastResponse() {
        return $this->lastResponse;
    }

    /**
     * vratí zákazníkovy peníze ikdyž jsou už ve stavu CREDITED_BATCH_OPENED
     * @param int $orderNumber
     * @param int $creditNumber
     * @return FALSE|OrderResponse
     */
    public function totalReversal($orderNumber, $amount)
    {
        if($this->queryOrderState($orderNumber))
        {
            switch ($this->lastResponse->getStateCode())
            {
                case OrderStateResponse::$code[7]:
                    $this->depositReversal($orderNumber);
                case OrderStateResponse::$code[4]:
                    return $this->approveReversal($orderNumber);
                case OrderStateResponse::$code[8]:
                    return $this->credit($orderNumber, $amount);
            }
        }
        return FALSE;
    }




//-----------------WSDL METODY--------------------------------------------------
    /**
     * Cancel Autorized Order - Unblocks money on user bank account
     * InStates:  APPROVED
     * OutStates: APPROVE_REVERSED, APPROVED
     *
     * @param int $orderNumber
     * @return FALSE|OrderResponse
     */
    public function approveReversal($orderNumber) {
        return $this->request('approveReversal', $orderNumber);
    }

    /**
     * Returns actual State of Order, OrderStateResponse::getStateCode()
     * @param int $orderNumber
     * @return FALSE|OrderStateResponse
     */
    public function queryOrderState($orderNumber) {
        return $this->request('queryOrderState', $orderNumber);
    }

    /**
     * InStates:  APPROVED
     * OutStates: DEPOSITED – BATCH OPEN
     * @param int $orderNumber
     * @param float $amount
     * @return FALSE|OrderResponse
     */
    public function deposit($orderNumber, $amount) {
        return $this->request2param('deposit', $orderNumber, $amount*self::MULTIPLY100);
    }

    /**
     * Reverse Deposit Order
     * InStates:  DEPOSITED – BATCH OPEN
     * OutStates: APPROVED
     * @param int $orderNumber
     * @return FALSE|OrderResponse
     */
    public function depositReversal($orderNumber) {
        return $this->request('depositReversal', $orderNumber);
    }

    /**
     * Credit deposited Order
     * InStates:  DEPOSITED – BATCH CLOSED, CREDITED – BATCH OPEN OR CLOSED
     * OutStates: CREDITED – BATCH OPEN
     *
     * @param int $orderNumber
     * @param float $amount
     * @return FALSE|OrderResponse
     */
    public function credit($orderNumber, $amount) {
        return $this->request2param('credit', $orderNumber, $amount*self::MULTIPLY100);
    }

    /**
     * Reverse Credit Order
     * InStates:  CREDITED – BATCH OPEN
     * OutStates: DEPOSITED – BATCH CLOSED, CREDITED – BATCH CLOSED
     *
     * @param int $orderNumber
     * @param int $creditNumber
     * @return FALSE|OrderResponse
     */
    public function creditReversal($orderNumber, $creditNumber=1) {
        return $this->request2param('creditReversal', $orderNumber, $creditNumber);
    }

    /**
     * Close Order
     * InStates:  DEPOSITED – BATCH OPEN OR CLOSED, CREDITED – BATCH OPEN OR CLOSED
     * OutStates: ORDER CLOSED
     *
     * @param int $orderNumber
     * @return FALSE|OrderResponse
     */
    public function orderClose($orderNumber) {
        return $this->request('orderClose', $orderNumber);
    }

    /**
     * Delete Order
     * InStates:  REQUESTED, PENDING, DECLINED, UNAPPROVED, APPROVE_REVERSED, ORDER_CLOSE
     * OutStates: DELETED
     *
     * @param int $orderNumber
     * @return FALSE|OrderResponse
     */
    public function delete($orderNumber) {
        return $this->request('delete', $orderNumber);
    }

    /**
     * uzavření dávky objednávek
     * @return FALSE|Response
     */
    public function batchClose() {
        $this->lastResponse = $this->client->batchClose($this->merchantNum,
                $this->getDigitalSignature());
        return $this->lastResponse->check()? $this->lastResponse: FALSE;
    }

    // <editor-fold defaultstate="collapsed" desc="protected">

    /**
     * check config when variables are allright
     * @return void
     * @throws \RuntimeException, \FileNotFoundException
     */
    protected function checkConfig(array $options, $config) {
        if (Environment::isProduction())
            return;

        if (!isset($options[self::WSDL]))
            throw new \RuntimeException('Let\'s fill variable "' . $config . '.' . self::WSDL . '" in config.');

        if (!isset($options[self::MERCHANT_NUM]))
            throw new \RuntimeException('Let\'s fill variable "' . $config . '.' . self::MERCHANT_NUM . '" in config.');

        if (!isset($options[self::PASSWORD]))
            throw new \RuntimeException('Let\'s fill variable "' . $config . '.' . self::PASSWORD . '" in config.');

        if (!isset($options[self::GP_CERT]))
            throw new \RuntimeException('Let\'s fill variable "' . $config . '.' . self::GP_CERT . '" in config.');

        if (!isset($options[self::PRIVATE_KEY]))
            throw new \RuntimeException('Let\'s fill variable "' . $config . '.' . self::PRIVATE_KEY . '" in config.');

        if (!isset($options[self::PATH]))
            throw new \RuntimeException('Let\'s fill variable "' . $config . '.' . self::PATH . '" in config.');

        if (realpath($options[self::PATH]) === FALSE)
            throw new \FileNotFoundException('Path to folder with certifications id bad.');

        $file = $options[self::PATH] . \DIRECTORY_SEPARATOR . $options[self::GP_CERT];
        if (!file_exists($file))
            throw new \FileNotFoundException($file);

        $file = $options[self::PATH] . \DIRECTORY_SEPARATOR . $options[self::PRIVATE_KEY];
        if (!file_exists($file))
            throw new \FileNotFoundException($file);
    }

    /**
     * digitální podpis
     * @return string
     */
    protected function getDigitalSignature(/* ... */) {
        $args = \array_merge(array($this->merchantNum), func_get_args());

        $signature = new Signature($this->privKey);
        return $signature->sign($args, $this->password);
    }

    /**
     * verify of response
     * @param OrderResponse $response
     * @return void
     */
    protected function verifyDigest() {
        if (!($this->lastResponse instanceof Response) || \get_class($this->lastResponse) == 'Response') {
            //chyba OrderResponse , vraci objekt Response ktery nema metodu getVerify
            throw new GpWebPayException('Co daál???');
        }

        if ($this->lastResponse->check()) {
            $s = new Signature($this->gpCert);
            $s->verify($this->lastResponse->getVerify(), $this->lastResponse->getDigets());
            return $this->lastResponse;
        }
        return FALSE;
    }

    /**
     * metody se opakuji: vytvoreni podpisu, dotaz, overeni odpovedi
     * @param string $method
     * @param int $orderNumber
     * @return FALSE|OrderResponse
     */
    protected function request($method, $orderNumber)
    {
        $this->lastResponse = $this->client->$method(
                $this->merchantNum, $orderNumber,
                $this->getDigitalSignature($orderNumber));
        return $this->verifyDigest();
    }

    /**
     * stejne jako ::request() jeden parametr navic
     */
    protected function request2param($method, $orderNumber, $param2)
    {
        $this->lastResponse = $this->client->$method(
                $this->merchantNum, $orderNumber, $param2,
                $this->getDigitalSignature($orderNumber, $param2));
        return $this->verifyDigest();
    }
    // </editor-fold>
}
