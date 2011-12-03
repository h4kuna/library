<?php

namespace Pay3dSecure;
use Nette\Object;
/**
 * Podpis a ověření certifikátu pro komunikaci s 3D-secure
 *
 * @author Milan Matějček
 */
class Signature extends Object
{
	private $privateCer;

    /**
     * @param string $privateCer - test_key.pem
     */
    public function  __construct($privateCer)
    {
        $this->privateCer = file_get_contents($privateCer);
    }

    /**
     * podpis
     * @param string|array $args
     * @param string $password
     * @return string
     * @throws GpWebPayException
     */
    public function sign($args, $password)
    {
        if(is_array($args))
            $args = implode('|', $args);

        $keyId = openssl_get_privatekey($this->privateCer, $password);
        if($keyId === FALSE)
            throw new GpWebPayException('Certification was incorrect.');
        $signature = NULL;
        if(openssl_sign($args, $signature, $keyId) === FALSE)
            throw new GpWebPayException('Failed to open Certificate.');
        openssl_free_key($keyId);
        return base64_encode($signature);
    }

    /**
     * ověření
     * @param string|array $args
     * @param string $signature
     * @return void
     * @throws GpWebPayException
     */
    public function verify($args, $signature)
    {
        if(is_array($args))
            $args = implode('|', $args);

        $keyId = openssl_get_publickey($this->privateCer);
        if($keyId === FALSE)
            throw new GpWebPayException('Certification was incorrect.');
        $result = openssl_verify($args, base64_decode($signature), $keyId);
        openssl_free_key($keyId);
        if($result == FALSE)
            throw new GpWebPayException ('Verify of certification was incorrect.');
    }

    public function getPrivateCer()
    {
        return $this->privateCer;
    }
}
