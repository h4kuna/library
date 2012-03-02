<?php

namespace Models;

use Nette\Utils\Strings;

/**
 * automatické jednoduché validátory dat pro db
 */
class Validators extends \Utility\NonObject
{

	public static function webalize(array $array, $key)
	{
		return Strings::webalize($array[$key]);
	}

//	public static function copy($array, $key)
//	{
//		//@todo
//		$array[$arg[1]] = $array[$arg[0]];
//	}
//
//	public static function texy($array, $key)
//	{
//		//@todo
//		$array[$arg[1]] = $array[$arg[0]];
//	}

	public static function lower(array $array, $key)
	{
		return Strings::lower($array[$key]);
	}

	public static function serialize(array $array, $key)
	{
		return (empty($array[$key])) ? NULL : serialize($array[$key]);
	}

	public static function hash(array $array, $key)
	{
		return hash('sha512', $array[$key]);
	}

	public static function sNull(array $array, $key)
	{
		return empty($array[$key]) ? NULL : $array[$key];
	}

	public static function float(array $array, $key)
	{
		return floatval(str_replace(array(','), array('.'), $array[$key]));
	}

	public static function fNull(array $array, $key)
	{
		$array[$key] = self::float($array, $key);
		return empty($array[$key]) ? NULL : $array[$key];
	}

	public static function int(array $array, $key)
	{
		return intval($array[$key]);
	}

	public static function iNull(array $array, $key)
	{
		$array[$key] = self::int($array, $key);
		return empty($array[$key]) ? NULL : $array[$key];
	}

	public static function compareDate(array $data, $col, $dateTo)
	{
		if ($data[$col] && $data[$dateTo] && $data[$col] >= $data[$dateTo]) {
			throw new \Nette\InvalidStateException('Date from is higger tned date to.', 1);
		}
		return $data[$col];
	}

	public static function bool(array $array, $key)
	{
		return (bool) $array[$key];
	}

	public static function dateTime(array $array, $key)
	{
		if (!($array[$key] instanceof \DateTime)) {
			$array[$key] = new \DateTime($array[$key]);
		}
		return $array[$key]->format(\DateTime::ISO8601);
	}

	public static function date(array $array, $key)
	{
		if (!($array[$key] instanceof \DateTime)) {
			$array[$key] = new \DateTime($array[$key]);
		}

		return $array[$key]->format('Y-m-d');
	}

	/** rodné číslo */
	public static function icNumber(array $array, $key)
	{
		$array[$key] = preg_replace('~[^0-9]~', '', $array[$key]);
		return ltrim($array[$key], '0');
	}

	public static function rcNumber(array $array, $key)
	{
		$array[$key] = preg_replace('~[^0-9/]~', '', $array[$key]);
		return $array[$key];
	}

	public static function implodeRc(array $array, $key)
	{
		if (!is_array($array[$key])) {
			return $array[$key];
		}

		$rc = trim(implode('/', $array[$key]));
		if ($rc == '/') {
			return '';
		}
		return $rc;
	}

	public static function insNumber(array $array, $key)
	{
		$array[$key] = preg_replace('~[^0-9/]~', '', $array[$key]);
		return 'INS ' . $array[$key];
	}

	public static function email(array $array, $key)
	{
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$localPart = "(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)"; // quoted or unquoted
		$chars = "a-z0-9\x80-\xFF"; // superset of IDN
		$domain = "[$chars](?:[-$chars]{0,61}[$chars])"; // RFC 1034 one domain component
		if (preg_match("(^$localPart@(?:$domain?\\.)+[-$chars]{2,19}\\z)i", $array[$key])) {
			return $array[$key];
		}
		throw new \Nette\InvalidStateException('Non valid email.', 2);
	}

	public static function ip4and6(array $array, $key)
	{
		$num = explode('.', $array[$key]);
		$max = 255;
		if (count($num) != 4) {
			$num = explode(':', $array[$key]);
			if (count($num) != 8) {
				throw new \Nette\InvalidStateException('Non valid ip address.', 3);
			}
			$max = 0xffff;
		}

		foreach ($num as $item) {
			if ($max == 0xffff) {
				$item = hexdec($item);
			}

			if (0 > $item || $item > $max) {
				throw new \Nette\InvalidStateException('Non valid ip address.', 3);
			}
		}
		return $array[$key];
	}

}
