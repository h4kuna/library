<?php

namespace Utility;

class FileTools {
	/*
	 * $array_dir    -type: array
	 * $prava        -type: array =nastavi se prava se stejnym klicem jako v predchozi $array_dir
	 *                 -type: string=pro vsechny slozky v poly budou nastavena stejna prava
	 */
	const DS =DIRECTORY_SEPARATOR;

	public $fopen;
	public $zapis;
	public static $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
	static public $search = array('č', 'ď', 'ň', 'ř', 'š', 'ť', 'ž', 'á',
			'é', 'ě', 'í', 'ó', 'ů', 'ú', 'ý', 'Č', 'Ď', 'Ň', 'Ř',
			'Š', 'Ť', 'Ž', 'Á', 'É', 'Ě', 'Í', 'Ó', 'Ů', 'Ú', 'Ý',
			'?');
	static public $change = array('c', 'd', 'n', 'r', 's', 't', 'z', 'a',
			'e', 'e', 'i', 'o', 'u', 'u', 'y', 'C', 'D', 'N', 'R',
			'S', 'T', 'Z', 'A', 'E', 'E', 'I', 'O', 'U', 'U', 'Y',
			'');

	public function __construct($soubor, $prava='r') {
		$this->fopen = @fopen($soubor, $prava);

		if ($this->fopen == FALSE) {
			throw new InvalidStateException('This file "' . $soubor . '" did not open.');
		}
	}

	/// Clean directory
	/** Delete all files in directory
	 * @param $path directory to clean
	 * @param $recursive delete files in subdirs
	 * @param $delDirs delete subdirs
	 * @param $delRoot delete root directory
	 * @access public
	 * @return success
	 */
	public static function cleanDirRecursive($path, $delRoot=TRUE) {
		foreach (\Nette\Utils\Finder::find('*')->in($path) as $item) {
			$p = $item->getPathname();
			if ($item->isDir()) {
				self::cleanDirRecursive($p);
			} else {
				\unlink($p);
			}
		}
		if ($delRoot) {
			\rmdir($path);
		}
		return true;
	}

	/**
	 *
	 * @param path $filePath
	 * @param bool $isFile
	 * @return bool
	 */
	public static function mkDir($filePath, $isFile=TRUE) {
		if ($isFile)
			$filePath = dirname($filePath);
		if (\file_exists($filePath))
			return TRUE;
		self::mkDir(dirname($filePath), FALSE);
		\mkdir($filePath);
	}

	public function close() {
		if ($this->fopen == TRUE) {
			@fclose($this->fopen); //zakomentovano, kdyby byla otevrena url tak to bude zlobit
		}
	}

	public function __destruct() {
		$this->close();
	}

	public function write($text, $length=NULL) {
		return fwrite($this->fopen, $text, $length);
	}

	public function seek($from, $to=SEEK_END) {
		return fseek($this->fopen, $from, $to);
	}

	public function read($length) {
		return fread($this->fopen, $length);
	}

	public static function bytes($number, $unit='MB') {
		$number = (int) $number;

		$exp = array_search($unit, self::$units);

		if ($exp === NULL)
			throw new RuntimeException('You did fill bad unit for convert.');

		return $number * pow(1024, $exp);
	}

	/**
	 * for css
	  div.verticalText
	  {
	  clear:both;
	  text-align:center;
	  }
	 * @param string $text utf-8
	 * @return <type>
	 */
	static public function verticalText($text) {
		if ($text == NULL)
			return NULL;
		return /* '<div class="verticalText">'. */ iconv('ISO-8859-2', 'UTF-8', implode('<br />', str_split(iconv('UTF-8', 'ISO-8859-2', $text)))); //.'</div>';
	}

	/**
	 * funkce overuje zda je slozka zapisovatelna a kdyztak vytvori slozsku s pravy 777
	 *
	 * @return void
	 */
	static public function isWritable() {
		$dir = func_get_args();

		foreach ($dir as $value) {
			if (!file_exists($value)) {
				umask(0000);
				mkdir($value, 0777);
			} elseif (!is_writable($value)) {
				echo 'slozce ' . $value . ' musis nastavit pravo 777';
				exit;
			}
		}
	}

	/**
	 *
	 * @param string $path  -cesta k souboru ci slozce, relativni nebo absolutni
	 * @param bool|string $addSlash -pridat ci nepridat na konec lomitko, TRUE = systemove lomitko, string = libovolna hodnota
	 * @param bool $existControl    -kontrola na existenci
	 * @param bool|string $realPath -prepsat do absolutni cesty, funguje jen kdyz je kontrola na existenci TRUE,
	 * jinak lze nastavi retezcem co se ma z konce $path odstranit
	 * @return string|FALSE
	 */
	static public function lastSlash($path, $addSlash=TRUE, $existControl=TRUE, $realPath=TRUE) {
		if ($existControl === TRUE) {
			if (!is_dir($path))
				$path = dirname($path);

			$real = realpath($path);
			if (!$real)
				return FALSE;

			if ($realPath)
				$path = $real;
		}
		else {
			if (!is_string($realPath))
				$realPath = '\/';
			$path = rtrim($path, $realPath);
		}

		if ($addSlash === TRUE) {
			$path .=self::DS;
		} else if ($addSlash !== FALSE) {
			$path .=$addSlash;
		}

		return $path;
	}

	/**
	 *
	 * @param $file     -dir and file name
	 * @param $second   -time second
	 * @return bool
	 */
	static function oldFiles($file, $second=86400) {
		return (!file_exists($file) || (time() - @filemtime($file) > $second));
	}

	/**
	 * realPath with Exception
	 * @param string $path
	 * @return string
	 */
	public static function realPath($path) {
		$path2 = realpath($path);
		if ($path2 == FALSE)
			throw new DirectoryNotFoundException($path);
		return $path2;
	}

	/**
	 * alias k funkci scandir()
	 * @param $path
	 * @param bool|string $file -regular string for preg_match
	 * @param bool|string $folder   -regular string for preg_match
	 * @param $asArray          -return as array(dir=>..., file=>...); or disable it
	 * @return array
	 */
	static public function scandir($path='./', $file=TRUE, $folder=TRUE, $pathDir=FALSE, $asArray=TRUE) {
		$a = scandir($path);
		$dot = array_search('.', $a);
		unset($a[$dot], $a[$dot + 1]);

		if ($file === FALSE && $folder === FALSE)
			return $a;

		$dirs = array();
		$files = array();

		foreach ($a as $val) {
			$isDir = is_dir($path . $val);
			if ($pathDir)
				$val = $path . $val;

			if ($isDir && $folder == TRUE) {
				self::addAward($dirs, $folder, $val);
			} else if (!$isDir && $file == TRUE) {
				self::addAward($files, $file, $val);
			}
		}

		if ($file === FALSE)
			return array('dir' => $dirs);

		if ($folder === FALSE)
			return array('file' => $files);

		if ($asArray === TRUE)
			return array('dir' => $dirs, 'file' => $files);

		return $dirs + $files;
	}

	static public function scanDirTree($path='./', $file=TRUE, $pathDir=FALSE) {
		$result = self::scandir($path, $file, TRUE, $path);
		if (empty($result['dir']))
			return $result['file'];

		foreach ($result['dir'] as $dir) {
			$result['dir'] = self::scanDirTree($dir . self::DS, $file, $pathDir);
			$result['file'] = array_merge($result['file'], $result['dir']);
		}

		return $result['file'];
	}

	/**
	 * metoda se vaze na scandir
	 * @param array $return
	 * @param reg string $way
	 * @param string $dir
	 * @return void
	 */
	static private function addAward(&$array, $way, $fileName) {
		if ($way === TRUE || preg_match($way, $fileName)) {
			$array[] = $fileName;
		}
	}

	static public function fileSizeTree($dir='./', $size=0) {
		$dirs = array();
		$dirs[0] = 0;
		$handle = opendir($dir);

		if ($handle == TRUE) {
			while (FALSE !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$in = $dir . $file;

					if (is_dir($in)) {
						$dirs['_' . $file] = self::fileSizeTree($in . self::DS);
					} else {//1048576
						$sizeF = filesize($in);

						if ($sizeF / 1048576 > 3)
							$dirs[$file] = $sizeF;
					}
				}
			}

			$dirs[0] = @array_sum($dirs);

			closedir($handle);
		}

		return $dirs;
	}

	/**
	 * funkce vytvori nazev souboru nebo souboru nebo ostrani hacky carky mezery
	 *
	 * @version 6.3.2009
	 * @param string|array $fileName   -stary nazev souboru
	 * @param string|bool  $name       -TRUE=vytvori nahodny nazev, FALSE=upravy stary pro pouziti pro web, string=novy nazev
	 * @param int          $maxLennght -maximalni delka retezce
	 * @return string|array
	 */
	static public function createFileName($fileName, $name=TRUE, $webName=TRUE, $rename=FALSE) {
		foreach ((array) $fileName as $key => $fileN) {
			$fileName[$key] = self::createFileNameSingle($fileN, $name, $webName, $rename);
		}

		return $fileName;
	}

	static public function autoUTF($s) {
		// detect UTF-8
		if (preg_match('#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s))
			return $s;

		// detect WINDOWS-1250
		if (preg_match('#[\x7F-\x9F\xBC]#', $s))
			return iconv('WINDOWS-1250', 'UTF-8', $s);

		// assume ISO-8859-2
		return iconv('ISO-8859-2', 'UTF-8', $s);
	}

	/**
	 * pro prejmenovani jednoho souboru s kontrolou pouzij createFileName();
	 * potom bude fungovat
	 */
	static public function createFileNameSingle($fileN, $name=TRUE, $webName=TRUE, $rename=FALSE) {
		//$fileN      =self::autoUTF($fileN);
		$oldName = $fileN;
		$koncovka = self::fileType($fileN);
		$dir = dirname($fileN) . self::DS;

		if ($name === FALSE) {
			$fileName = c_form::md5Hash() . $koncovka;
		} else {
			if ($webName === TRUE) {
				$fileN = strtolower($fileN);
				$search = array_merge(self::$search, array('-', ' ', ','));
				$change = array_merge(self::$change, array('_', '_', ''));
			} else {
				$search = self::$search;
				$change = self::$change;
			}

			if ($name === TRUE) {
				$name = $fileN;
			}

			$name = str_replace($search, $change, $name);
			$name = trim($name);

			$fileName = $name . $koncovka;
		}

		if ($rename !== FALSE) {
			return rename($oldName, $dir . $fileName);
		}

		return $fileName;
	}

	/**
	 * vrati koncovku souboru
	 * @param $fileName
	 * @param bool $cut -koncovku souboru je mozne useknout
	 * @param $point    -pridani moznych znaku jako .
	 * @return string
	 */
	static public function fileType(&$fileName, $cut=TRUE, $point='.') {
		$nameArray = explode('.', strtolower($fileName));

		$zkr = end($nameArray);

		if ($cut === TRUE) {
			$fileName = substr($fileName, 0, -1 * (strlen($zkr) + 1 ));
		}

		return $point . $zkr;
	}

	/**
	 * Control file name and if exists make a new random name
	 * @version 6.3.2009
	 * @param string       $oldName
	 * @param string       $newName
	 * @return bool
	 */
	static public function controlRenameSingle($oldName, $newName) {
		$rename = FALSE;
		if (file_exists($oldName)) {
			$rename = rename($oldName, $newName);
		}

		return $rename;
	}

	/**
	 * if first and second params are array must same keys
	 *
	 * @version 6.3.2009
	 * @param string|array     $oldName    -old file name one or array
	 * @param string|array     $newName    -new file name one or array
	 * @return string|array|int    -int = -1 it is arrays haven't same keys
	 */
	static public function controlRename($oldName, $newName) {
		$is_array_old = is_array($oldName);
		$is_array_new = is_array($newName);
		$result = FALSE;

		if ($is_array_old && $is_array_new) {
			foreach ($oldName as $key => $value) {
				$result[$key] = self::controlRenameSingle($value, $newName[$key]);
			}
		} else if (!$is_array_new && !$is_array_old) {
			$result = self::controlRenameSingle($oldName, $newName);
		}

		return $result;
	}

	/**
	 * smaze vsechno co je ve slozce rekurzivne
	 * @param $path
	 * @param $search
	 * @param $delete
	 * @return void
	 */
	static public function deleteFiles($path='./', $search='~.~', $delete=FALSE) {
		$dirObj = new DirectoryIterator($path);
		if ($dirObj !== FALSE) {
			while ($dirObj->valid()) {
				if (!$dirObj->isDot()) {
					$is_dir = $dirObj->isDir();

					if (!preg_match($search, $file) && $is_dir === FALSE) {
						if ($delete === TRUE)
							unlink($dirObj->getPathname());
					}
					elseif ($is_dir === TRUE) {
						self::deleteFiles($dirObj->getPathname(), $search, $delete);
					}
				}
				$dirObj->next();
			}
		} else {
			return FALSE;
		}
	}

}

interface IFileManager {

	public function dirManage($path, $dir);

	public function fileManage($path, $file);
}

abstract class FileManager implements IFileManager {

	public $folder = TRUE;
	public $file = TRUE;

	public function structure($path) {
		return $this->makeStructure(FileTools::lastSlash($path));
	}

	protected function makeStructure($path) {
		$tree = array();
		$files = FileTools::scandir($path, $this->file, $this->folder, FALSE);

		foreach ($files['dir'] as $dir) {
			$tree[$dir] = $this->dirManage($path, $dir);
		}

		foreach ($files['file'] as $file) {
			$tree[$file] = $this->fileManage($path, $file);
		}

		return $tree;
	}

	public function dirManage($path, $dir) {
		return $this->makeStructure($path . $dir . FileTools::DS);
	}

}

final class FileTime extends FileManager {

	public function fileManage($path, $file) {
		return filemtime($path . $file);
	}

}

final class FileSize extends FileManager {

	public function fileManage($path, $file) {
		return filesize($path . $file);
	}

}

//-------horni pripad je 2x rychlejsi nezli spodni----------

/*
  interface IFileManager2
  {
  public function dirManage(DirectoryIterator $obj);

  public function fileManage(DirectoryIterator $obj);
  }

  abstract class FileManager2 implements IFileManager2
  {
  public $folder  =TRUE;
  public $file    =TRUE;

  public function structure($path)
  {
  $tree   =array();
  $files  =new DirectoryIterator($path);

  foreach($files as $val)
  {
  if($val->isFile() && $this->file === TRUE)
  {
  $tree[$val->getFilename()]=$this->fileManage($val);
  }
  elseif(!$val->isDot() && $this->folder === TRUE)
  {
  $tree[$val->getFilename()] =$this->dirManage($val);
  }
  }
  return $tree;
  }

  public function dirManage(DirectoryIterator $obj)
  {
  return $this->structure($obj->getPathname());
  }
  }


  final class FileTime2 extends FileManager2
  {
  public function fileManage(DirectoryIterator $obj)
  {
  return $obj->getMTime();
  }
  }

  final class FileSize2 extends FileManager2
  {
  public function fileManage(DirectoryIterator $obj)
  {
  return $obj->getSize();
  }
  }
 */
