<?php

namespace Models;

use Nette;

/**
 * Description of DbModel
 * @property-read Nette\Database\Table\Selection $db
 */
abstract class DbModel extends BaseModel implements IDbModel {

    /** @var Nette\Database\Table\Selection */
    private $db;

    /** @var string */
    protected $table = 'CHANGE IT';

    /** @var string */
    protected $primary;
    private $sqlCalc = 0;

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
    private $staticColumn = array();

    public function __construct(Nette\DI\Container $container) {
        parent::__construct($container);
        $this->conn = $this->container->database;
        $this->db = $this->conn->table($this->table);

        if ($this->primary === NULL) {
            $this->primary = $this->db->primary;
        }
    }

    /**
     * @example $this->find(1, 'col1, col2', 'column'); alias  $this->findByColumn(1, 'col1, col2');
     * @param type $name
     * @param type $args
     * @return type
     */
    public function __call($name, $args) {
        $exp = explode('By', $name);
        if (count($exp) == 2) {
            $exp[1] = substr(preg_replace('~([A-Z])~', '_$1', $exp[1]), 1);
            $args += array(1 => '', 2 => strtolower($exp[1]));
            return call_user_func_array(array($this, $exp[0]), $args);
        }
        return parent::__call($name, $args);
    }

    /**
     * nastavi povinny sloupce
     * @param type $col
     * @param type $val
     */
    public function setStaticColumn($col, $val = NULL) {
        if (is_array($col)) {
            foreach ($col as $k => $v) {
                $this->staticColumn[$k] = $v;
            }
        } else {
            $this->staticColumn[$col] = $val;
        }
    }

//-----------------elementární metody

    /**
     *
     * @param array $data
     * @param type $id
     * @param type $by
     * @return type
     */
    public function update(array $data, $id, $by = NULL) {
        $this->prepareData($data);
        return $this->getCondition($id, $by)->update($data);
    }

    public function insert(array $data, $lastId = FALSE) {
        $this->prepareData($data);

        try {
            $res = $this->getDb()->insert($data);
            if ($lastId) {
                $id = $res[$this->primary];
            }
        } catch (\PDOException $e) {
            if ($e->getCode() != 23000 || $lastId != TRUE) {
                throw $e;
            }
            $v = $this->getVersion();
            $found = array();

            if ($v < 5.1) {
                if (!preg_match('~\'(.*)\'~U', $e->getMessage(), $found)) {
                    throw $e;
                }
                $found = array_search($found[1], $data);
            } else {
                if (!preg_match('~Duplicate entry \'(.+)\' for key \'(.+)\'$~U', $e->getMessage(), $found)) {
                    throw $e;
                }
                $found = ($found[2] == 'PRIMARY') ? $this->primary : $found[2];
            }

            //je to danne do pole aby bylo pozna ze nebyl zaznam vlozen/upraven
            if (!isset($data[$found])) {
                return $data;
            }

            $data = $this->fetch($data[$found], '*', $found);
            $id = array('duplicity' => $data->{$found},
                'column' => $found,
                'all' => $data->toArray());
        }

        return ($lastId) ? $id : $res;
    }

    public function delete($id, $column = NULL, $by = NULL) {
        $out = NULL;
        if ($column && $id) {
            $out = $this->fetch($id, $column);
        }

        $delete = $this->getCondition($id, $by)->delete();
        return ($out !== NULL) ? $out : $delete;
    }

    /**
     *
     * @param type $id
     * @param type $columns
     * @param type $by
     * @return \Nette\Database\Table\Selection
     */
    public function find($id, $columns = '*', $by = NULL) {
        return $this->findAll($columns, $id, $by);
    }

    /**
     *
     * @param type $id
     * @param type $columns
     * @param type $by
     * @return \Nette\Database\Table\ActiveRow
     */
    public function fetch($id, $columns = '*', $by = NULL) {
        return $this->find($id, $columns, $by)->fetch();
    }

    /**
     *
     * @param type $columns
     * @param type $parameters
     * @param type $condition
     * @return \Nette\Database\Table\Selection
     */
    public function findAll($columns = '', $parameters = NULL, $condition = NULL) {

        if (!$columns) {
            $columns = '*';
        }

        if ($this->sqlCalc) {
            $columns = 'SQL_CALC_FOUND_ROWS ' . $columns;
        }

        return $this->getCondition($parameters, $condition)->select($columns);
    }

    /**
     *
     * @param type $by
     * @param type $id
     * @return Nette\Database\Table\Selection
     */
    private function getCondition($parameters, $condition) {

        if ($condition === NULL) {
            $condition = $this->primary;
        }

        $sql = $this->getDb();

        if ($parameters) {
            $sql->where($condition, $parameters);
        }

        foreach ($this->staticColumn as $k => $v) {
            $sql->where($k, $v);
        }

        return $sql;
    }

    /**
     * faster?? I mean NO!!
     * switch countig for use SQL_CALC_FOUND_ROWS
     * call this method before findAll
     * @example
     * $model->willCount(); //maybe faster :)
     * $model->findAll('*', 1);
     * dump($model->count());
     */
    public function willCount() {
        $this->sqlCalc = TRUE;
    }

    public function count() {
        if ($this->sqlCalc) {
            $this->sqlCalc = FALSE;
            $sql = $this->conn->query('SELECT FOUND_ROWS() AS c');
        } else {
            $sql = self::findAll('COUNT(*) AS c');
        }

        return intval($sql->fetch()->c);
    }

    /**
     * orizne pole na požadované sloupce
     * @param array $data
     * @return type
     */
    public function shave(array $data) {
        return array_intersect_key($data, $this->mapper);
    }

//-----------------

    public function estimateCount() {
        if (!($this->conn->getSupplementalDriver() instanceof \Nette\Database\Drivers\MySqlDriver)) {
            throw new \RuntimeException('Now is only for MySql!');
        }
        $res = $this->conn->fetch('SHOW TABLE STATUS LIKE \'' . $this->table . '\'');
        return ($res['Engine'] == 'InnoDB') ? $res['Rows'] : NULL;
    }

    public function getFields() {
        $col = NULL;
        if (func_num_args()) {
            $col = 'FROM ' . implode(' ', func_get_args());
        }
        return $this->conn->query('SHOW COLUMNS ' . $col . ' FROM ' . $this->table . ';')->fetchAll();
    }

    public function getVersion() {
        $cache = $this->getCache('Models');
        $key = 'version';
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        return $cache->save($key, floatval(substr($this->conn->query('SELECT version() AS v')->fetch()->v, 0, 3)), array(self::EXPIRE => 'tomorrow'));
    }

//-----------------transaction

    public function begin() {
        if (!$this->conn->inTransaction()) {
            return $this->conn->beginTransaction();
        }
        return TRUE;
    }

    public function commit() {
        if ($this->conn->inTransaction()) {
            return $this->conn->commit();
        }
        return FALSE;
    }

    public function rollback() {
        if ($this->conn->inTransaction()) {
            return $this->conn->rollBack();
        }
        return FALSE;
    }

//-----------------

    public function getTable($model = NULL) {
        if ($model === NULL)
            return $this->table;

        return $this->models->{$model}->getTable();
    }

    /** @return Nette\Database\Table\Selection */
    public function getDb($clone = TRUE) {
        return $clone ? clone $this->db : $this->db;
    }

    public function getPrimary() {
        return $this->primary;
    }

    public function lastInsertId() {
        $seq = NULL;
        if ($this->conn->getSupplementalDriver() instanceof Nette\Database\Drivers\PgSqlDriver) {
            $seq = $this->table . '_' . $this->primary . '_seq';
        }
        return $this->conn->lastInsertId($seq);
    }

    public function getConnection() {
        return $this->conn;
    }

//nastroje na upravu hodnot pred ulozenim do db
    protected function prepareData(array &$data) {
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
                    $f = callback($this, substr($f, 2));
                }

                $data[$column] = call_user_func_array($f, array($data, $column, $args));
            }
        }
    }

    protected function fetchArray(\Nette\Database\Table\Selection $sql, $key = NULL) {
        $out = array();
        $c = 0;
        foreach ($sql as $v) {
            $k = $key === NULL ? $c : $v->{$key};
            $out[$k] = $v->toArray();
            ++$c;
        }
        return $out;
    }

//-----------------

    /**
     * literal
     * @param type $v
     * @return \Nette\Database\SqlLiteral
     */
    protected function l($v) {
        return new \Nette\Database\SqlLiteral($v);
    }

}
