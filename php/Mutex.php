<?php

namespace Utility;

use Nette;

class Mutex extends Nette\Object
{
	const LOCK_FILE = 'mutex';
	private $file;

	/** @var string */
	private $name;
	private $temp;

	public function __construct($tempPath = NULL, $name = self::LOCK_FILE)
	{
		$this->setName($name);
		if($tempPath) {
			$this->setTemp($tempPath);
		}
	}

	public function __destruct()
	{
		!$this->file || fclose($this->file);
	}

	public function lock()
	{
		$this->file || $this->open();
		flock($this->file, LOCK_EX);
	}

	public function unlock()
	{
		flock($this->file, LOCK_UN);
	}

	public function setName($v)
	{
		$this->name = $v;
		return $this;
	}

	public function setTemp($tempPath)
	{
		$path = realpath($tempPath);
		if (!$path) {
			throw new Nette\FileNotFoundException($tempPath);
		}
		$this->temp = $path . DIRECTORY_SEPARATOR . self::LOCK_FILE;
		if (!file_exists($this->temp)) {
			mkdir($this->temp);
		}
		return $this;
	}

	private function open()
	{
		$this->file = fopen($this->temp . DIRECTORY_SEPARATOR . self::LOCK_FILE . $this->name, 'w');
	}

}
