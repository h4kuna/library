<?php

namespace Utility;

use Nette;

/**
 * Description of CsvExport
 *
 * @author Hakuna
 */
class CsvExport extends Nette\Object
{
	private $data;
	private $delimiter = ';';

	public function setDelimiter($s)
	{
		$this->delimiter = $s;
	}

	public function setData($data)
	{
		if (!is_array($data) && !($data instanceof \ArrayAccess)) {
			dump($data);
			throw new \RuntimeException('Data must be iteratorable.');
		}
		$this->data = $data;
	}

	public function toFile($path)
	{
		throw new Nette\NotImplementedException();
	}

	public function send($fileName)
	{
//		header('Content-type: text/csv');
//		header('Content-disposition: attachment;filename=' . $fileName . '.csv');
		echo $this->export();
		exit;
	}

	protected function export()
	{
		$body = NULL;
		foreach ($this->data as $val) {
			if ($body === NULL) {
				if (!is_array($val)) {
					$val = $val->toArray();
				}
				$body .= $this->row(array_keys((array) $val));
			}
			$body .= $this->row($val);
		}
		return $body;
	}

	protected function row($data)
	{
		if(!is_array($data)) {
			$data = $data->toArray();
		}
		return implode($this->delimiter, $data) . "\n";
	}

}
