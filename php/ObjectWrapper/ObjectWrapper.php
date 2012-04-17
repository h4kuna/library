<?php

namespace Utility;

use Nette;

abstract class ObjectWrapper extends Nette\Object
{
	const DS = DIRECTORY_SEPARATOR;

	/** @var resource */
	protected $resource;

	/** @string prefix of function */
	protected $prefix;

	public function __call($name, $args)
	{
		$fname = $this->prefix . $name;
		if (function_exists($fname)) {
			return call_user_func_array($fname, array_merge(array($this->resource), $args));
		}
		throw new \RuntimeException('Call undefined method ' . __CLASS__ . '::' . $name);
	}

}
