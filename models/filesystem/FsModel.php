<?php

namespace Models;

/**
 * filesystem
 * Enter description here ...
 * @author Hakuna
 *
 */
class FsModel extends BaseModel {

	static private $filePath = array();
	static private $webPath = array();

	public function filePath($configVar) {
		if(!isset(self::$filePath[$configVar])) {
			$data = & $this->container->params;
			self::$filePath[$configVar] = $data['wwwDir'] . DIRECTORY_SEPARATOR . $data[$configVar] . DIRECTORY_SEPARATOR;
		}

		return self::$filePath[$configVar];
	}

	public function webPath($configVar) {
		if(!isset(self::$webPath[$configVar])) {
			self::$webPath[$configVar] = $this->container->httpRequest->url->scriptPath .
							$this->container->params[$configVar] .'/';
		}

		return self::$webPath[$configVar];
	}
}
