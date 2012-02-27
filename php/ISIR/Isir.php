<?php

namespace ISIR;

/**
 * @property-read $client
 */
class Isir
{
	/** @var Soap */
	private $client;
	private $lastResponse;

	public function __construct($wsdl)
	{
		$this->client = ($wsdl instanceof \SoapClient) ? $wsdl : new Soap($wsdl);
	}

	public function test()
	{
		return $this->getById(-1);
	}

	public function getByDate(\DateTime $date)
	{
		return $this->request('getIsirPub001', array('Calendar_1' => $date->format(\DATE_ATOM)));
	}

	public function getById($id)
	{
		return $this->request('getIsirPub0012', array('long_1' => $id));
	}

	protected function request($method /* , ... */)
	{
		$args = func_get_args();
		array_shift($args);
		return $this->lastResponse = $this->client->__call($method, $args);
	}

	public function getLastResponse()
	{
		return $this->lastResponse;
	}

}
