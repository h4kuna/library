<?php

namespace Utility;

use Nette;

/**
 * @property-read value
 */
class Units extends Nette\Object
{
	static private $units = array('', 'K', 'M', 'G', 'T');
	private $actualUnit;
	private $outUnit;
	protected $base = 1000;
	protected $unit = 'g'; //gram

	public function __construct($number=NULL, $outUnit=NULL)
	{
		if ($number) {
			$this->setActualUnit($number);
		}

		if ($outUnit) {
			$this->setOutUnit($outUnit);
		}
	}

	public function setOutUnit($outUnit)
	{
		$this->outUnit = $this->unitInfo($outUnit);
	}

	/**
	 * means gram, litr, byte
	 */
	public function setUnit($v)
	{
		$this->unit = $v;
	}

	/**
	 *
	 * @param string $number 128M
	 */
	public function setActualUnit($number)
	{
		$unit = substr($number, -1);
		if (is_numeric($unit)) {
			$unit = '';
		} else {
			$number = substr($number, 0, -1);
		}

		$this->actualUnit = $this->unitInfo($unit, $number);
	}

	public function getValue()
	{
		return ($this->actualUnit['value'] * pow($this->base, $this->actualUnit['key'] - $this->outUnit['key'])) . $this->outUnit['unit'] . $this->unit;
	}

	private function unitInfo($unit, $value = 0)
	{
		$unit = strtoupper($unit);
		$key = array_search($unit, self::$units);
		if ($key === FALSE) {
			throw new \RuntimeException('Unit is\'t supported, yet. ' . $unit);
		}
		return array('key' => $key, 'unit' => $unit, 'value' => intval($value));
	}

	public static function recount($number, $outUnit)
	{
		$unit = new static($number, $outUnit);
		return $unit->getValue();
	}

}