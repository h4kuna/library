<?php

namespace Utility;

use Nette;

class Mutex extends Nette\Object
{
	const LOCK_FILE = 'mutex';
	private $file;

	public function __construct($tempPath, $name = NULL)
	{
		$path = realpath($tempPath);
		if (!$path) {
			throw new Nette\FileNotFoundException($tempPath);
		}
		$this->file = fopen($path . DIRECTORY_SEPARATOR . self::LOCK_FILE . $name, 'w');
	}

	public function lock()
	{
		flock($this->file, LOCK_EX);
	}

	public function unlock()
	{
		flock($this->file, LOCK_UN);
	}

	public function __destruct()
	{
		fclose($this->file);
	}

}
