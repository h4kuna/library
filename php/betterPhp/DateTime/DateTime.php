<?php

namespace Utility;

use Nette;

class DateTime extends Nette\DateTime
{
	const SQL_DATE = 'Y-m-d';
	const SQL_DATETIME = 'Y-m-d H:i:s';
	const CZECH_DATE = 'j. n. Y';
	const CZECH_DATE_ZERO = 'd.m.Y';

	protected $outFormat = self::SQL_DATE;

	public function __construct($time = 'now', $object = NULL)
	{
		parent::__construct($this->fixRelativeMove($time), $object);
	}

	public function setOutFormat($val)
	{
		$this->outFormat = $val;
	}

	public function __toString()
	{
		return $this->format($this->outFormat);
	}

	public function modify($modify)
	{
		return parent::modify($this->fixRelativeMove($modify));
	}

	private function fixRelativeMove($time)
	{
		$time = strtolower($time);
		if (strstr($time, 'week') !== FALSE && !date('w')) {
			$found = array();
			$x = ' week';
			preg_match('~(\w*)' . $x . '~U', $time, $found);
			static $change = array('previous' => '-2', 'this' => 'previous', 'next' => 'this');
			if (!is_numeric($found[1]) && isset($change[$found[1]])) {
				$time = str_replace($found[1] . $x, $change[$found[1]] . $x, $time);
			}
		}
		return $time;
	}

}
