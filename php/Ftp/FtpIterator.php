<?php

namespace Utility;

require_once 'Ftp.php';

/**
 * Description of FtpIterator
 *
 * @author Hakuna
 */
class FtpIterator extends \ArrayIterator implements \RecursiveIterator
{
	/** @var Ftp */
	private $ftp;

	private $parent = NULL;

	public function __construct(Ftp $ftp, $path=NULL, $flag=NULL)
	{
		$this->ftp = $ftp;
		$array = $this->ftp->nlist($path);
		\array_walk($array, array($this, 'setFtpFileInfo'));
		parent::__construct($array);
	}

	public function hasChildren($allow_links=NULL)
	{
		return $this->current()->isDir();
	}

	public function getFtp()
	{
		return $this->ftp;
	}


	public function getChildren()
	{
		return $this->current()->getChildren();
	}

	public function setParent(FtpIterator $obj)
	{
		$this->parent = $obj;
	}

	public function setFtpFileInfo(&$v, $k)
	{
		$v = new FtpFileInfo($v, $this);
	}
}
