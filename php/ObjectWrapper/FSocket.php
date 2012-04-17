<?php

namespace Utility;

/**
 * Open Internet or Unix domain socket connection
 */
class FSocket extends ObjectWrapper
{
	protected $prefix = 'f';
	protected $errno;
	protected $errstr;

	public function open($hostname, $port = -1, $timeOut = 0)
	{
		if ($timeOut < 1) {
			$timeOut = ini_get('default_socket_timeout');
		}
		$this->resource = fsockopen($hostname, $port, $this->errno, $this->errstr, $timeOut);
		if (!$this->resource) {
			$this->exception();
		}
	}

	public function getErrstr()
	{
		return $this->errstr;
	}

	public function getErrno()
	{
		return $this->errno;
	}

	public function getError()
	{
		return "#{$this->errno}, " . $this->errstr;
	}

	public function __destruct()
	{
		$this->close();
	}

	protected function exception()
	{
		throw new \RuntimeException($this->errstr, $this->errno);
	}

}
