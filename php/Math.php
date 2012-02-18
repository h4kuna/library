<?php

namespace Utility;

class Math extends NonObject
{
	/**
	 * pole prvocisel
	 */
	static protected $primeNumber = array(2, 3, 5, 7, 11, 13, 17, 19, 23, 29,);

	/**
	 * upravi hodnotu v rozsahu intervalu pokud je mensi nez minimum tak na minimum,
	 * pokud vetsi nez maximum tak na maximum, default is UNSIGNED
	 * @param $number
	 * @param $min false|number
	 * @param $max false|number
	 * @return number
	 */
	static public function interval($number, $min=0, $max=false)
	{
		if ($min === false)
			$min = $number;

		if ($max === false)
			$max = $number;
		return max($min, min($max, $number));
	}

	/**
	 * prevede carky na tecky, to je potreba kdyz prevadi retezec na cislo >> 1,2 na 1.2
	 *
	 * @param string $string
	 * @return string
	 */
	static public function stroke2point($string)
	{
		return str_replace(',', '.', $string);
	}

	/**
	 * spocita faktorial
	 * @param int $n
	 * @return int
	 */
	static public function factorial($n)
	{
		if ($n == 0) {
			return 1;
		}
		if ($n < 0) {
			throw new LogicException("The number cann't negative number.");
		}
		return $n * self::factorial($n - 1);
	}

	/**
	 * mocnina rada kdy v klici se ukazuje mocnina a hodnota je je vysledek
	 * @param int $upNumber -maxminalni hodnota vypoctene mocniny
	 * @param int $line     -mocnina
	 * @return array        -pole mocnin
	 */
	static public function powerLine($upNumber, $line=2)
	{
		$i = 0;
		$power = 1;
		$value = array();

		while ($power <= $upNumber) {
			$value[$i] = $power;
			$i++;
			$power = pow($line, $i);
		}

		return array_reverse($value, true);
	}

	/**
	 * zjistuje, zda je cislo delitelne
	 *
	 * @param int|float $delenec
	 * @param int|float $delitel
	 * @return bool
	 */
	static public function isDivision($delenec, $delitel)
	{
		return ($delenec % $delitel) === 0;
	}

	/**
	 * zjistuje, zda je predhozene cislo prvocislem
	 *
	 * @param int $cislo
	 * @param array $primeNumber - pole prvocisel
	 * @return bool
	 */
	static public function isPrimeNumber($cislo, array $primeNumber=null)
	{
		if ($cislo == 1)
			return false;

		$JePrvocislo = true;

		if ($primeNumber === null)
			$primeNumber = self::$primeNumber;

		foreach ($primeNumber as $prvocislo) {
			if (self::isDivision($cislo, $prvocislo)) {
				$JePrvocislo = false;
				break;
			}

			if ($prvocislo * $prvocislo > $cislo) {
				break;
			}
		}
		return $JePrvocislo;
	}

	/**
	 * zaokrouhlujici metoda na padesatniky
	 * @param number $num
	 * @param number $q
	 * @param fce $fce
	 * @return number
	 */
	static public function round5($num, $q=5, $fce='ceil')
	{
		return $fce($num / $q) * $q;
	}

	/**
	 *
	 * @param int $int
	 * @return int
	 */
	static public function getNextPrimeNumber($int)
	{
		$int = (int) $int;
		if (self::isDivision($int, 2)) {
			$int++;
		}
		self::setPrimeNumbersTo($int);
		do {
			$int+=2;
		} while (!self::isPrimeNumber($int, self::$primeNumber));

		return $int;
	}

	/*
	  static public function setPrimeNumbersTo($int)
	  {
	  $int    =(int)$int;
	  $last   =end(self::$primeNumber)+1;
	  reset(self::$primeNumber);
	  if($int > $last)
	  {
	  $last++;
	  for($last; $last <= $int; $last+=2)
	  {
	  if (self::isPrimeNumber($i, self::$primeNumber))
	  {
	  self::$primeNumber[]    =$last;
	  }
	  }
	  }
	  }
	 */

	/**
	 * vraci pole prvocisel
	 *
	 * @param int $sum          -pocet ktery má vratit
	 * @param bool $delete_2    -ma-li odstranit dvojku, jakozto prvni prvocislo
	 * @return array
	 */
	static public function primeNumber($sum=1, $delete_2=true)
	{
		$prime = self::primeNumber;

		$count = count($prime);

		if ($sum > $count) {
			$up = $sum - $count;

			for ($i = 31; $up != 0; $i+=2) {
				if (self::isPrimeNumber($i, $prime)) {
					$prime[] = $i;
					$up--;
				}
			}
		} else if ($sum < $count) {
			$prime = array_slice($prime, 0, $sum - $count, true);
		}

		if ($delete_2) {
			unset($prime[0]);
		}

		return $prime;
	}

	/**
	 * Returns least common multiple of two numbers
	 * @param a number 1
	 * @param b number 2
	 * @return lcm(a, b)
	 */
	public static function lcm($a, $b)
	{
		if ($a == 0 || $b == 0) {
			return 0;
		}
		return ($a * $b) / self::gcd($a, $b);
	}

	/**
	 * Returns greatest common divisor of the given numbers
	 * @param a number 1
	 * @param b number 2
	 * @return gcd(a, b)
	 */
	public static function gcd($a, $b)
	{
		if ($a < 1 || $b < 1) {
			throw new RuntimeException("a or b is less than 1");
		}
		$remainder = 0;
		do {
			$remainder = $a % $b; //v tento okamzik v posledni iteraci plati ona podminka, ze zbytek == 0
			$a = $b; //ale kvuli dalsi pripadne iteraci posunujeme promenne
			$b = $remainder; //v b je proto 0, v a je gcd
		} while ($b != 0);
		return $a;
	}
}
