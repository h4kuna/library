<?php

/**
 * Description of CsvExport
 *
 * @author Hakuna
 */
class CsvExport extends NObject
{
	private $callback;

	/** @DibiResult */
	private $result;

	private $data;

	private $delimiter = ';';

	public function __construct(DibiResult $result, $callback=NULL)
	{
		$this->setCallback($callback);
		$this->result = $result;
	}

	public function setCallback($val)
	{
		if($callback !== NULL && !is_callable($callback))
		 throw new RuntimeException($callback .' is not callable.');
		$this->callback = $callback;
	}

	public function export($path=NULL, $delimiter=';')
	{
		$this->delimiter = $delimiter;//@todo doupravit

		$this->data = implode($this->delimiter, array_keys((array)$this->result->fetch())) ."\n";

		if($this->callback !== NULL)
		{
			$this->data .= call_user_func_array($this->callback, array($this->result));
		}
		else
		{
			foreach($this->result as $val)
			{
				$this->data .= implode($this->delimiter, (array)$val) . "\n";
			}
		}

		file_put_contents(($path === NULL)? ('./'. time() .'.csv'): $path, str_replace('.', ',', $this->data));
	}
}
