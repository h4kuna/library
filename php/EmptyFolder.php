<?php

namespace Utility;

use Nette\Utils\Finder;

/**
 * odstraní prázdné složky
 * @example
 * $p = new EmptyFolder('D:\Pictures');
 * $p->clearBadFile()->find();
 */
class EmptyFolder
{
	private $path;
	private $delete = TRUE;
	private $parent;
	private $debuggerPath;

	public function __construct($path, $parent = NULL)
	{
		$this->parent = $parent;
		if (!($path instanceof \SplFileInfo)) {
			$path = new \SplFileInfo($path);
		}
		$this->path = $path;
		$this->debuggerPath = $this->path->getPathname();
	}

	public function getPath()
	{
		return $this->path->getPathname();
	}

	public function lock()
	{
		if ($this->delete === FALSE) {
			return;
		}
		$this->delete = FALSE;
		if ($this->parent) {
			$this->parent->lock();
		}
	}

	public function find()
	{
		foreach (new FilesystemIterator($this->getPath()) as $k => $v) {
			if ($v->isDir()) {
				$sub = new EmptyFolder($v, $this);
				if ($sub->find()) {
					rmdir($v->getPathName());
					p($v->getPathName());
				} else {
					$this->lock();
				}
			} elseif ($this->parent !== NULL) {
				return FALSE;
			}
		}

		return $this->delete;
	}

	public function clearBadFile(array $mask = array('Thumbs.db'))
	{
		foreach (Finder::findFiles($mask)->from($this->getPath()) as $file => $obj) {
			unlink($file);
			p($file);
		}
		return $this;
	}

}