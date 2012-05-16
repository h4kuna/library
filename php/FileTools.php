<?php

namespace Utility;

use Nette;

class FileTools extends Nette\Object
{

	/// Clean directory
	/** Delete all files in directory
	 * @param $path directory to clean
	 * @param $recursive delete files in subdirs
	 * @param $delDirs delete subdirs
	 * @param $delRoot delete root directory
	 * @access public
	 * @return success
	 */
	public static function cleanDirRecursive($path, $delRoot = TRUE)
	{
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
	 * @param string $path  -cesta k souboru ci slozce, relativni nebo absolutni
	 * @param bool|string $addSlash -pridat ci nepridat na konec lomitko, TRUE = systemove lomitko, string = libovolna hodnota
	 * @param bool $existControl    -kontrola na existenci
	 * @param bool|string $realPath -prepsat do absolutni cesty, funguje jen kdyz je kontrola na existenci TRUE,
	 * jinak lze nastavi retezcem co se ma z konce $path odstranit
	 * @return string|FALSE
	 */
	static public function lastSlash($path, $addSlash = TRUE, $existControl = TRUE, $realPath = TRUE)
	{
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
	 * realPath with Exception
	 * @param string $path
	 * @return string
	 */
	static public function realPath($path)
	{
		$path2 = realpath($path);
		if ($path2 == FALSE)
			throw new \Nette\DirectoryNotFoundException($path);
		return $path2;
	}

	static public function autoUTF($s)
	{
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
	 * vrati koncovku souboru
	 * @param $fileName
	 * @param bool $cut -koncovku souboru je mozne useknout
	 * @param $point    -pridani moznych znaku jako .
	 * @return string
	 */
	static public function fileType(&$fileName, $cut = TRUE, $point = '.')
	{
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
	static public function controlRenameSingle($oldName, $newName)
	{
		$rename = FALSE;
		if (file_exists($oldName)) {
			$rename = rename($oldName, $newName);
		}

		return $rename;
	}

	/**
	 * smaze vsechno co je ve slozce rekurzivne
	 * @param $path
	 * @param $search
	 * @param $delete
	 * @return void
	 */
	static public function deleteFiles($path = './', $search = '~.~', $delete = FALSE)
	{
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

	/**
	 *
	 * @param path $filePath
	 * @param bool $isFile
	 * @return bool
	 */
	public static function mkDir($filePath)
	{
		if (is_file($filePath)) {
			$filePath = dirname($filePath);
		}
		if (file_exists($filePath)) {
			return TRUE;
		}
		self::mkDir(dirname($filePath));
		mkdir($filePath, 0777);
	}

	/**
	 *
	 * @param \Nette\Http\FileUpload $file
	 * @param string $path
	 * @return TRUE
	 */
	public static function save(\Nette\Http\FileUpload $file, $path)
	{
		self::mkDir($path);
		$path = realpath($path) . DIRECTORY_SEPARATOR;
		$name = $file->sanitizedName;
		do {
			$pathName = $path . $name;
			$name = \Nette\Utils\Strings::random(5) . $name;
		} while (file_exists($pathName));

		$file->move($pathName);
		return basename($pathName);
	}

}
