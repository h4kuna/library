<?php

namespace Models;

abstract class ConditionDbModel extends DbModel
{
	private $value;
	protected $columnCondition = 'let\'s fill';
	protected $conditionAccept = FALSE;

	public function insert(array $data, $lastId=FALSE)
	{
		if ($this->conditionAccept && !isset($data[$this->columnCondition])) {
			$data[$this->columnCondition] = $this->getValue();
		}
		return parent::insert($data, $lastId);
	}

	public function findAll($columns='*', $page=NULL, $itemsPerPage=50)
	{
		$sql = parent::findAll($columns, $page, $itemsPerPage);
		if ($this->conditionAccept) {
			$sql->where($this->columnCondition, $this->getValue());
		}
		return $sql;
	}

	protected function getCondition($by, $id)
	{
		$sql = parent::getCondition($by, $id);
		if ($this->conditionAccept) {
			$sql->where($this->columnCondition, $this->getValue());
		}
		return $sql;
	}

	public function getValue()
	{
		if (!$this->value && $this->conditionAccept) {
			$this->value = $this->getConditonValue();
		}
		return $this->value;
	}

	public function setValue($val)
	{
		$this->value = $val;
		return $this;
	}

	abstract protected function getConditonValue();
}
