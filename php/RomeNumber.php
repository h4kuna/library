<?php

/*
  echo RomeNumber::getRomeNumber(1968) . "\n"; //
  echo RomeNumber::getArabicNumber('MCMLXVIII') . "\n"; // 1968
 */

class RomeNumber extends NonObject
{
	static private $cislice = array(
			"M" => 1000, "CM" => 900,
			"D" => 500, "CD" => 400,
			"C" => 100, "XC" => 90,
			"L" => 50, "XL" => 40,
			"X" => 10, "IX" => 9,
			"V" => 5, "IV" => 4,
			"I" => 1
	);

	/**
	 * prevede cislo na arabske cislo
	 * @param int $cislo
	 * @return string
	 */
	static function getRomeNumber($cislo)
	{
		$return = null;
		foreach (self::$cislice as $key => $val) {
			$return .= str_repeat($key, floor($cislo / $val));
			$cislo %= $val;
		}
		return $return;
	}

	/**
	 * prevede arabske cislo na rimnske
	 * @param string $rimske
	 * @return int
	 */
	static function getArabicNumber($rimske)
	{
		$rimske = str_split(strtoupper((string) $rimske));
		$return = 0;
		$posun = false;

		foreach ($rimske as $key => $val) {
			if ($posun === true) {
				$posun = false;
				continue;
			}

			if (isset($rimske[$key + 1]) && isset(self::$cislice[$val . $rimske[$key + 1]])) {
				$return +=self::$cislice[$val . $rimske[$key + 1]];
				$posun = true;
			} else {
				$return +=self::$cislice[$val];
			}
		}
		return $return;
	}

}
