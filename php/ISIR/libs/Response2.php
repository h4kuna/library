<?php

namespace ISIR;

class Response2 extends \stdClass implements \Iterator
{
	public $result = array();
	private $counter = 0;
	private $size = 0;
	private $maxId = 0;

	public function current()
	{
		$current = &$this->result[$this->key()];
		if (isset($current->cas)) {
//nasledujici klice odpovidaj sloupcum v db tabulce
			$current = array(
					'id' => $current->id,
					'datetime' => new \DateTime($current->cas),
					'documents_id' => $current->idDokument,
					'note' => $current->poznamka,
					'tag_file' => $current->spisZnacka,
					'type' => $current->typ,
					'typeText' => $current->typText,
					'section' => $current->oddil,
					'position' => $current->poradiVOddilu
			);
		}

		if ($this->maxId < $current['id']) {
			$this->maxId = $current['id'];
		}
		return $current;
	}

	public function next()
	{
		++$this->counter;
		return next($this->result);
	}

	public function key()
	{
		return key($this->result);
	}

	public function valid()
	{
		return $this->counter < $this->size;
	}

	public function rewind()
	{
		if($this->result instanceof \stdClass) {
			$this->result = array($this->result);
		}

		reset($this->result);
		$this->counter = $this->maxId = 0;
		$this->size = $this->count();
	}

	public function count()
	{
		return count($this->result);
	}

	public function getMaxId()
	{
		return $this->maxId;
	}

	public function getCounter()
	{
		return $this->counter;
	}

}
