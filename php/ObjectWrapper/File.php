<?php

namespace Utility;

class File extends ObjectWrapper
{
	protected $prefix = 'f';
	protected $options = array('fileName' => NULL, 'mode' => NULL, 'useIncludePath' => FALSE, 'context' => NULL);

	public function __construct($fileName = NULL, $mode = NULL, $useIncludePath = NULL, $context = NULL)
	{
		$this->setOption($fileName, $mode, $useIncludePath, $context);
	}

	public function setFileName($path)
	{
		$this->options['fileName'] = $path;
	}

	public function setMode($s)
	{
		$this->options['mode'] = $s;
	}

	public function setIncludePath()
	{
		$this->options['useIncludePath'] = 1;
	}

	public function setContext($s)
	{
		$this->options['context'] = $s;
	}

	public function setOption($fileName = NULL, $mode = NULL, $useIncludePath = NULL, $context = NULL)
	{
		if ($fileName) {
			$this->setFileName($fileName);
		}

		if ($mode) {
			$this->setMode($mode);
		}

		if ($useIncludePath) {
			$this->setIncludePath();
		}

		if ($context) {
			$this->setContext($context);
		}
	}

	public function open()
	{
		touch($this->options['fileName']);
		$this->resource = @fopen($this->options['fileName'], $this->options['mode']);
		if (!$this->resource) {
			throw new \RuntimeException('This file "' . $this->options['fileName'] . '" did not open.');
		}
	}

	public function write($s, $length = -1)
	{
		$this->resource || $this->setMode('w') || $this->open();
		return fwrite($this->resource, $s, $length);
	}

	public function read($length = 0)
	{
		$this->resource || $this->setMode('r') || $this->open();
		return fread($this->resource, $length);
	}

	public function __destruct()
	{
		if($this->resource) {
			$this->close();
		}
	}

}
