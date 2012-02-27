<?php

namespace Models;

use Nette\DI\Container,
		Nette\Diagnostics\Debugger,
	  Nette\Database\SqlLiteral;

/**
 * @property-read $tree
 */
abstract class TreeModel extends DbModel
{
	const ID = 'id';
	const PARENT_ID = 'parent_id';
	const LEFT = 'lft';
	const RIGHT = 'rgt';
	const DEEP = 'DEEP';

	/** @var TreeObject */
	private $tree;

	/**
	 * key => name column from db
	 * mandatory column
	 * @var type
	 */
	protected $field = array(self::ID => NULL, self::PARENT_ID => self::PARENT_ID,
			self::LEFT => self::LEFT, self::RIGHT => self::RIGHT, self::DEEP => self::DEEP);

	public function __construct(Container $container)
	{
		parent::__construct($container);
		if ($this->field[self::ID] == FALSE) {
			$this->field[self::ID] = $this->primary;
		}
		$this->checkColumn();
	}

	public function addSon($id, array $data, $lastId = FALSE)
	{
		return $this->addNode($id, $data, $lastId);
	}

	public function addAfter($id, array $data, $lastId = FALSE)
	{
		return $this->addNode($id, $data, $lastId, 'after');
	}

	public function addBefore($id, array $data, $lastId = FALSE)
	{
		return $this->addNode($id, $data, $lastId, 'before');
	}

	public function deleteNode($id)
	{
		$r = $this->field[self::RIGHT];
		$l = $this->field[self::LEFT];
		$d = $this->field[self::DEEP];
		$this->begin();
		$row = $this->conn->query('SELECT ' . $this->column($l, $r) . ' FROM ' . $this->table
										. ' WHERE ' . $this->field[self::ID] . '= ? FOR UPDATE', $id)->fetch();
		if (!$row) {
			$this->rollback();
			throw new \RuntimeException('Parent doesn\'t exists.');
		}

		$res = $this->db->where($l . ' >= ' . $row[$l])->where($r . ' <= ' . $row[$r])->delete();

		$diff = $row[$r] - $row[$l] + 1;
		$this->conn->query('UPDATE ' . $this->table . ' SET ' . $l . ' = ' . $l . ' - ' . $diff . ' WHERE ' .
						$l . ' > ?;', $row[$r]);
		$this->conn->query('UPDATE ' . $this->table . ' SET ' . $r . ' = ' . $r . ' - ' . $diff . ' WHERE ' .
						$r . ' > ?;', $row[$r]);
		$this->commit();
		return $res;
	}

	public function addNode($id, array $data, $lastId = FALSE, $where = 'son')
	{
		$this->clearData($data);
		$r = $this->field[self::RIGHT];
		$l = $this->field[self::LEFT];
		$d = $this->field[self::DEEP];
		$p = $this->field[self::PARENT_ID];
		$this->begin();
		$row = $this->conn->query('SELECT ' . $this->column($r, $d, $p, $l) . ' FROM ' . $this->table
										. ' WHERE ' . $this->field[self::ID] . '= ? FOR UPDATE', $id)->fetch();
		if (!$row) {
			$this->rollback();
			throw new \RuntimeException('Parent doesn\'t exists.');
		}
		
		$data += (array) $row;
		switch ($where) {
			case 'son':
				$rUpdate = $data[$l] = $row[$r];
				++$data[$r];
				$data[$p] = $id;
				++$data[$d];
				break;
			case 'after':
				$data[$l] = $row[$r] + 1;
				$rUpdate = $data[$r] += 2;
				break;
			case 'before':
				$rUpdate = $data[$r] = $row[$l] + 1;
				break;
			default:
				throw new \Nette\NotImplementedException('Bad choise.');
		}

		$this->conn->query('UPDATE ' . $this->table . ' SET ' . $l . ' = ' . $l . ' + 2 WHERE ' .
						$l . ' >= ?;', $data[$l]);
		$this->conn->query('UPDATE ' . $this->table . ' SET ' . $r . ' = ' . $r . ' + 2 WHERE ' .
						$r . ' >= ?;', $rUpdate);

		try {
			$res = $this->insert($data, $lastId);
		} catch (\PDOException $e) {
			$this->rollback();
			if ($e->getCode() == 23000) {
				return $e;
			}
			throw $e;
		}
		$this->commit();
		return $res;
	}

//-----------------working method
	public function getTree()
	{
		if ($this->tree) {
			return $this->tree;
		}
		return $this->tree = new TreeObject($this->field);
	}

	/**
	 * incializace stromu, vraci pocet zaznamu v tabulce
	 * 0 - proslo inicializaci
	 * 0 < - uz je inicializovano
	 * @param array $data
	 * @return int
	 */
	public function init(array $data)
	{
		$this->begin();
		$c = $this->count();
		if ($c) {
			$this->rollback();
			return $c;
		}
		$data[$this->field[self::RIGHT]] = 2;
		$data[$this->field[self::LEFT]] = 1;
		$data[$this->field[self::DEEP]] = 0;
		$data[$this->field[self::PARENT_ID]] = NULL;

		$this->insert($data);
		$this->commit();
		return 1;
	}

	/**
	 * pridani hodnoty na konec stromu
	 * @param array $data
	 * @param type $lastId
	 * @return type
	 */
	public function append(array $data, $lastId = FALSE)
	{
		$this->clearData($data);
		$r = $this->field[self::RIGHT];
		$l = $this->field[self::LEFT];
		$this->begin();
		$row = $this->findAll('IFNULL(MAX(' . $r . '), 0) + 1 AS ' . $l . ', ' .
										'IFNULL(MAX(' . $r . '), 0) + 2 AS ' . $r)->fetch()->toArray();
		//deep has default 0 in database
		$out = $this->insert($data + $row, $lastId);
		$this->commit();
		return $out;
	}

	/**
	 * drobečková navigace
	 * @param type $id
	 * @param type $column
	 * @return type
	 */
	public function crumbNavigation($id, $column='*')
	{
		$l = $this->field[self::LEFT];
		$r = $this->field[self::RIGHT];
		$row = $this->fetch($id, $this->column($l, $r));
		if (!$row) {
			return array();
		}
		$this->findAll($column)->where($l . ' < ' . $row[$l])->where($r . ' > ' . $row[$r]);
	}

//-----------------inherit
	public function findAll($columns='*', $page=NULL, $itemsPerPage=50)
	{
		return parent::findAll($columns)->order($this->field[self::LEFT]);
	}

//-----------------getter for help
	public function getRight()
	{
		return $this->field[self::RIGHT];
	}

	public function getLeft()
	{
		return $this->field[self::LEFT];
	}

	public function getDeep()
	{
		return $this->field[self::DEEP];
	}

	public function getParent()
	{
		return $this->field[self::PARENT_ID];
	}

//-----------------helper

	public function dump($output = TRUE)
	{
		$res = $this->findAll();
		if ($output) {
			foreach ($res as $val) {
				Debugger::dump($val, TRUE);
			}
		}
		return $res;
	}

	final protected function clearData(array &$data)
	{
		unset($data[$this->field[self::LEFT]], $data[$this->field[self::RIGHT]], $data[$this->field[self::DEEP]], $data[$this->field[self::PARENT_ID]]);
	}

	final protected function column($col = FALSE)
	{
		$column = $this->field;
		if ($col) {
			$column = func_get_args();
		}
		return '`' . implode('`, `', $column) . '`';
	}

	/**
	 * kontrola na vyplnene po
	 */
	private function checkColumn()
	{
		$col = array(self::ID, self::LEFT, self::DEEP, self::PARENT_ID, self::RIGHT);
		foreach ($col as $v) {
			if (!isset($this->field[$v])) {
				throw new \RuntimeException('Must be filled mandatory column! Now missing column ' . $v . '.');
			}
		}
	}

}

class TreeObject extends \Nette\Object //implements \Iterator
{
	private $fields;

	public function __construct(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * must by ordered by left
	 * @param \Nette\Database\Table\Selection $sql
	 */
	public function setSql(\Nette\Database\Table\Selection $sql)
	{
		foreach ($sql as $row) {
			$this->fill($row);
		}
	}

	private function fill($data)
	{

		pd($data);
	}

}