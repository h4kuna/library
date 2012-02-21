<?php

namespace ISIR;

class Soap extends \SoapClient
{

	public function __construct($wsdl)
	{
		$options = array(
				'soap_version' => \SOAP_1_2,
				'cache_wsdl' => \WSDL_CACHE_BOTH,
				'encoding' => 'UTF-8',
				'exceptions' => 1,
				'classmap' => array('getIsirPub0012Response' => __NAMESPACE__ . '\Response2',
						'getIsirPub001Response' => __NAMESPACE__ . '\Response1',
				)
		);

		$max = 3;
		$i = 0;
		do {
			try {
				parent::__construct($wsdl, $options);
				$i = $max;
			} catch (\SoapFault $e) {
				if (FALSE === strstr($e->getMessage(), 'Parsing Schema')) {
					throw $e;
				}
			}
			++$i;
		} while ($i < $max);
	}

}
