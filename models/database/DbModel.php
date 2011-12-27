<?php

namespace Models;

use Nette\Utils\Strings;
use Nette;

/**
 * Description of DbModel
 * @property-read Nette\Database\Table\Selection $db
 */
abstract class DbModel extends BaseModel implements IDbModel
{
	/** @var Nette\Database\Table\Selection */
	private $db;

	/** @var string */
	protected $table = 'CHANGE IT';
	protected $primary;
	private static $inTransaction = FALSE;

	/**
	 * moznosti zapisu
	 * column => ':fce' // doplni na \Validators::fce($data, $column)
	 * column => '->fce' // doplni na $this->fce($data, $column)
	 * column => 'Class::fce' // zavola staticky Class::fce($data, $column)
	 * column => array(':fce1', 'fce2') // zavola funkce postupne nad timto sloupcem, alias array(':fce1' => NULL, ':fce2' => NULL)
	 * column => array(':fce' => array('param1', 'param2')) doplni na \Validators::fce($data, $column, $param1, $param2), s jednim parametrem array(':fce' => 'param1')
	 * !column => validace bude ignorována
	 */
	protected $mapper = array();

	/** @var Nette\Database\Connection */
	protected $conn;

	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);
		$this->conn = $this->container->database;
		$this->db = $this->conn->table($this->table);
		$this->conn->setCacheStorage($this->container->cacheStorage);

		if ($this->primary === NULL) {
			$this->primary = $this->db->primary;
		}
		//$this->setMapper();
	}

	/**
	 * @example $this->find(1, 'col1, col2', 'column'); alias  $this->findByColumn(1, 'col1, col2');
	 * @param type $name
	 * @param type $args
	 * @return type
	 */
	public function __call($name, $args)
	{
		$exp = explode('By', $name);
		if (count($exp) == 2) {
			$exp[1] = ltrim(preg_replace('~([A-Z])~', '_$1', $exp[1]), '_');
			$args += array(1 => '', 2 => strtolower($exp[1]));
			return call_user_func_array(array($this, $exp[0]), $args);
		}
		return parent::__call($name, $args);
	}

	public function shave(array $data)
	{
		return array_intersect_key($data, $this->mapper);
	}

	public function update(array $data, $id, $by=NULL)
	{
		if ($by == FALSE) {
			$by = $this->primary;
		}

		$this->prepareData($data);
		unset($data[$by]);

		if ($id === FALSE) {
			return $this->getDb()->update($data);
		}
		return $this->getDb()->where($by, $id)->update($data);
	}

	public function insert(array $data, $lastId=FALSE)
	{
		$this->prepareData($data);
		$res = $this->db->insert($data);
		return ($lastId) ? $this->lastInsertId() : $res;
	}

	public function delete($id, $column=NULL, $by=NULL)
	{
		$out = NULL;
		if ($column && $id) {
			//@todo mozna fetch :)
			$out = $this->find($id, $column);
		}

		if (!$by) {
			$by = $this->primary;
		} else {
			$data = array($by => &$id);
			$this->prepareData($data);
		}

		try {
			$delete = $this->db->where($by, $id)->delete();
			return ($out !== NULL) ? $out : $delete;
		} catch (\PDOException $e) {
			if ($e->getCode() == 23503) {
				return $e;
			}
			throw $e;
		}
	}

	public function find($id, $columns='*', $by=NULL)
	{
		if (!$by) {
			$by = $this->primary;
		} else {
			$data = array($by => &$id);
			$this->prepareData($data);
		}
		if (!$columns) {
			$columns = '*';
		}

		return $this->findAll($columns)->where($by, $id);
	}

	public function fetch($id, $columns='*', $by=NULL)
	{
		return $this->find($id, $columns, $by)->fetch();
	}

	public function findAll($columns='*', $page=NULL, $itemsPerPage=50)
	{
		$res = $this->getDb()->select($columns);
		if ($page > 0) {
			return $res->page($page, $itemsPerPage);
		}
		return $res;
	}

	public function count()
	{
		$res = $this->findAll('COUNT(*) AS c')->fetch();
		return intval($res->c);
	}

//-----------------transaction

	public function begin($savePoint = FALSE)
	{
		if ($savePoint) {
			$this->conn->exec('SAVEPOINT ' . $savePoint);
		} elseif (!self::$inTransaction) {
			self::$inTransaction = TRUE;
			$this->conn->beginTransaction();
		}

		return TRUE;
	}

	public function commit($savePoint = FALSE)
	{
		if ($savePoint) {
			$this->conn->exec('RELEASE SAVEPOINT ' . $savePoint);
		} elseif (self::$inTransaction) {
			$this->conn->commit();
			self::$inTransaction = FALSE;
		}

		return self::$inTransaction;
	}

	public function rollback($savePoint = FALSE)
	{
		if ($savePoint) {
			$this->exec('ROLLBACK TO SAVEPOINT ' . $savePoint);
		} elseif (self::$inTransaction) {
			self::$inTransaction = FALSE;
			$this->conn->rollback();
		}
		return self::$inTransaction;
	}

	public function getTable($model = NULL)
	{
		if ($model === NULL)
			return $this->table;

		return $this->models->{$model}->getTable();
	}

	/** @return Nette\Database\Table\Selection */
	public function getDb()
	{
		return clone $this->db;
	}

	public function getPrimary()
	{
		return $this->primary;
	}

	public function lastInsertId()
	{
		$seq = NULL;
		if ($this->conn->getSupplementalDriver() instanceof Nette\Database\Drivers\PgSqlDriver) {
			$seq = $this->table . '_' . $this->primary . '_seq';
		}
		return $this->conn->lastInsertId($seq);
	}

//nastroje na upravu hodnot pred ulozenim do db
	final protected function prepareData(array &$data)
	{
		foreach ($this->mapper as $column => $fce) {

			if (isset($data['!' . $column])) {
				$data[$column] = $data['!' . $column];
				unset($data['!' . $column]);
				continue;
			}

			if (!isset($data[$column]) || !$fce) {
				continue;
			}

			if (!is_array($fce)) {
				$fce = array($fce => NULL);
			}

			foreach ($fce as $f => $args) {
				if (is_int($f)) {
					$f = $args;
				}

				if (!$f) {
					continue;
				}

				$str = substr($f, 0, 2);
				if ($str == '::') {
					$f = '\\' . get_class($this) . $f;
				} elseif (substr($str, 0, 1) == ':') {
					$f = '\Models\Validators:' . $f;
				} elseif ($str == '->') {
					$f = callback($this, $f);
				}

				$data[$column] = call_user_func_array($f, array($data, $column, $args));
			}
		}
	}

	protected function fetchSingle($res)
	{
		if (!$res)
			return $res;
		$data = $res->toArray();
		if (count($data) == 1) {
			return current($data);
		}
		return $data;
	}

//	private function setMapper()
//	{
//		$cache = $this->cache;
//		$key = serialize($this->mapper);
//
//		if (isset($cache[$key])) {
//			$this->mapper = $cache[$key];
//			return;
//		}
//
//		$res = $this->findAll()->limit(1);
//		if (($col = $res->fetch()) == FALSE) {
//			pd($res);
//		}
//
//		foreach ($col->toArray() as $k => $v) {
//			if (isset($this->mapper[$k])) {
//				continue;
//			}
//			$this->mapper[$k] = NULL;
//		}
//
//		$cache->save($key, $this->mapper, array(\Nette\Caching\Cache::EXPIRE => 'tomorrow'));
//	}
}
