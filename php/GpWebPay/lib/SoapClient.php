<?php

namespace Pay3dSecure;
use Nette\Environment;

/**
 *
 * @author Milan Matějček
 */
class SoapClient extends \SoapClient
{
    public function __construct($wsdl, $options)
    {
        if(!Environment::isProduction()) {
            $options = array('cache_wsdl' => \WSDL_CACHE_NONE,
                       'trace' => 1, 'exceptions' => 1) + $options;
        }

        parent::__construct($wsdl, $options +
                array('encoding'=>'UTF-8', 'soap_version'=>\SOAP_1_1, 'exceptions' => 1));
    }
}
