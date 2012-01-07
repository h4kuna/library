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
			throw new \Nette\InvalidStateException('Date from is higger tned date to.');
		}
		return $data[$col];
	}

	public static function bool(array $array, $key)
	{
		return (bool) $array[$key];
	}

	public static function dateTime(array $array, $key)
	{
		if(!($array[$key] instanceof \DateTime)) {
			$array[$key] = new \DateTime($array[$key]);
		}
		return $array[$key]->format(\DateTime::ISO8601);
	}

	public static function date(array $array, $key)
	{
		if(!($array[$key] instanceof \DateTime)) {
			$array[$key] = new \DateTime($array[$key]);
		}

		return $array[$key]->format('Y-m-d');
	}

}
