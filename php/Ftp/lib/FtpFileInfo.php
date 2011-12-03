<?php

namespace Utility;

class FtpFileInfo extends \SplFileInfo
{
	private $path;
	private $ftp;
	private $isDir;
	private $children;

	public function __construct($path, FtpIterator $ftpIter)
	{
		$this->ftp = $ftpIter;
		$this->path = $path;
		$this->isDir = ($ftpIter->getFtp()->mdtm($path) == -1);//není kontrola zda soubor existuje
	}

	public function getFilename()
	{
		return $this->path;
	}

	public function isDir()
	{
		return $this->isDir;
	}

	public function isFile()
	{
		return !$this->isDir;
	}

	public function getChildren()
	{
		if(!$this->isDir)
			return FALSE;
		$this->children = new FtpIterator($this->ftp->getFtp(), $this->path);
		$this->children->setParent($this->ftp);
		return $this->children;
	}

	public function isDot()
	{
		return FALSE;
	}

//-----------------NOT SUPPORTED
	public function  isExecutable()
	{
		$this->not();
	}

	public function getATime()
	{
	  $this->not();
	}

	public function getCTime()
	{
	  $this->not();
	}

	private function not()
	{
		throw new \NotSupportedException('For FTP is not supported.');
	}

}
