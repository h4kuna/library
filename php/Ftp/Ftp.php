<?php

namespace Utility;

use Nette\Web;

/**
 * wrapper nad Nette\Web\Ftp
 */
class Ftp extends Web\Ftp
{
	const SERVER = 'server';
	const USER = 'user';
	const PASSWORD = 'password';
	const PASSIVE = 'passive';
	const ROOT = 'root';
	const LAZY = 'lazy';

	/**
	 * flag for connect
	 * @var bool
	 */
	private $connected = FALSE;

	/** @var array */
	private $config = array();
//-----------------inherit method
	/**
	 * Checks if file or directory exists.
	 * @param  string
	 * @return bool
	 */
	public function fileExists($file)
	{
		$list = $this->nlist($file);
		if (empty($list))
			return FALSE;
		return is_array($list);
	}

//-----------------my method
	public function __construct(array $config=NULL)
	{
		parent::__construct();
		if ($config)
			$this->connection($config);
	}

	public function __call($name, $args)
	{
		$this->letsConnect();
		if (empty($args))
			switch ($name) {
				case 'nlist':
				case 'rawlist':
				case 'mdtm':
					$args = array('.');
					break;
			}
		return parent::__call($name, $args);
	}

	/**
	 *
	 * @param array $config
	 */
	public function connection(array $config)
	{
		$this->setConfig($config);

		if ($this->config[self::LAZY]) {
			$this->config[self::LAZY] = FALSE;
			return;
		}
		$this->connected = TRUE;
		$port = $timeout = $ssl = NULL;
		@list($host, $ssl, $port, $timeout) = \explode(':', $this->config[self::SERVER]);
		$connect = 'connect';
		if ($ssl)
			$connect = 'ssl_' . $connect;

		$this->{$connect}($host, self::set($port, 21), self::set($timeout, 90));
		$this->login($this->config[self::USER], $this->config[self::PASSWORD]);
		if ($this->config[self::PASSIVE])
			$this->pasv(TRUE);
		else
			$this->pasv(FALSE);

		if (!empty($this->config[self::ROOT]))
			$this->chdir($this->config[self::ROOT]);
		$this->config = array(self::LAZY => FALSE, self::ROOT => $this->config[self::ROOT]);
	}

	/**
	 * vrati se na domovskou slozku
	 * @return bool
	 */
	public function goRoot()
	{
		return $this->chdir($this->config[self::ROOT]);
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return $this->connected;
	}

	/**
	 * přesune soubor na ftp
	 * @param string $from
	 * @param string $to
	 */
	public function move($from, $to)
	{
		$this->letsConnect();
		$home = $this->pwd();
		$end = dirname($to);
		$this->mkDirRecursive($end);
		$this->chdir($end);
		$res = $this->rename($from, \basename($to));
		$this->chdir($home);
	}

	/**
	 * nahraje soubor a kontroluje jeho velikost zda se neposkodil
	 * @param path $file
	 * @param path $to
	 * @return bool
	 */
	public function uploadFile($file, $to)
	{
		$this->letsConnect();
		$result = NULL;
		if (!\file_exists($file))
			throw new FtpException('File does not exists: ' . $file);
		$fSize = filesize($file);
		if ($fSize && !$this->alloc($fSize, &$result))
			throw new FtpException('Can\'t alloc memory for upload, server said: ' . $result . ', file: ' . $file);

		$home = $this->pwd();
		$end = dirname($to);
		$this->mkDirRecursive($end);
		$this->chdir($end);
		$max = 0;
		do {
			if ($max == 3)
				throw new FtpException('Can\'t upload file: "' . $file . '"');
			$baseTo = \basename($to);
			$res = $this->put($baseTo, $file, self::BINARY);
			$sFtp = $this->size($baseTo);
			$sLocal = \filesize($file);
			++$max;
		}while ($sFtp != $sLocal);

		$this->chdir($home);
		return $res;
	}

	public function downloadFile($file, $remoteFile)
	{
		$this->letsConnect();
		if ($this->fileExists($remoteFile)) {
			\Utility\FileTools::mkDir($file);
			$home = $this->pwd();
			$this->chdir(dirname($remoteFile));
			$res = $this->get($file, \basename($remoteFile), self::BINARY);
			$this->chdir($home);
			return $res;
		}
		return FALSE;
	}

	/**
	 * srovna soubory pro zobrazeni
	 * @param array $out
	 */
	public function fileList(array &$out)
	{
		$files = $this->nlist();

		foreach ($files as $file) {
			if ($file == '.' || $file == '..')
				continue;

			if ($this->isDir($file)) {
				$out['dir'][] = $file;
			} else {
				$out['file'][] = $file;
			}
		}

		foreach ($out as &$val) {
			sort($val);
			reset($val);
		}
		unset($val);
	}

//	protected function root()
//	{
//		return $this->config[self::ROOT];
//	}

	/**
	 * pripoji se k ftp pokud jsou nastavene parametry
	 */
	protected function letsConnect()
	{
		$this->connected || $this->connection(array());
	}

	/**
	 * check value in config
	 * @param array $config
	 * @return void
	 */
	private function setConfig(array & $config)
	{
		if (!empty($this->config))
			return;
		$this->config = self::param4Connect('', '', '');

		$count = 0;
		foreach ($this->config as $k => &$v) {
			if ($count < 3 && empty($config[$k]))
				throw new Web\FtpException('Fill required param with key ' . $k);
			elseif (!isset($config[$k]))
				continue;
			$v = $config[$k];
			$count++;
		}
		unset($v);
	}

	public function folderTree($path, array &$tree)
	{
		foreach ($this->nlist($path) as $dir) {
			$base = basename($dir);
			if ($base == '..' || $base == '.')
				continue;
			if ($this->isDir($dir)) {
				$tree[] = $dir;
				$this->folderTree($dir, $tree);
			}
		}
	}

	public function removeFiles(array $directories)
	{
		foreach ($directories as $directory) {
			$this->removeIterator($directory);
		}
	}

	public function removeIterator($directory, $self=FALSE)
	{
		$files = $this->nlist($directory);
		if ($files == FALSE) {
			$files = array();
		}
		foreach ($files as $path) {
			$base = basename($path);

			if ($base == '.' || $base == '..')
				continue;
			if ($this->isDir($path)) {
				$this->removeIterator($path, TRUE);
			} else {
				$this->trydelete($path);
			}
		}

		if ($self) {
			$this->rmdir($directory);
		}
	}

//-----------------STATIC
	/**
	 *
	 * @param string $server server:port:timeout:ssl
	 * @param string $user
	 * @param string $pass
	 * @param string $root
	 * @param bool $passive
	 * @param bool $lazy
	 * @return array
	 */
	public static function param4Connect($server, $user, $pass, $root=NULL, $passive=TRUE, $lazy=TRUE)
	{
		return array(self::SERVER => $server, self::USER => $user, self::PASSWORD => $pass,
				self::ROOT => $root, self::PASSIVE => $passive, self::LAZY => $lazy);
	}

	public static function protocolForm($host, $user, $pass, $path='/', $port=21)
	{
		return "ftp://$user:$pass@$host:" . $port . $path;
	}

	/**
	 * nastaveni vychozich hodnot
	 * @param mix $val
	 * @param mix $def
	 * @return mix
	 */
	private static function set($val, $def=NULL)
	{
		return!empty($val) ? $val : $def;
	}

}
