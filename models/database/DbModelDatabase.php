<?php

namespace Models;

use Nette;

/**
 * Description of DbModel
 * @property-read Nette\Database\Table\Selection $db
 */
abstract class DbModelDatabase extends DbModel implements IDbModel {

    /** @var Nette\Database\Connection */
    protected $conn;

    public function __construct(Nette\DI\Container $context) {
        parent::__construct($context);
        $this->conn = $context->database;
        $this->db = $this->conn->table($this->table);
        if ($this->primary === NULL) {
            $this->primary = $this->db->primary;
        }
    }

//-----------------elementární metody
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

    public function count() {
        if ($this->sqlCalc) {
            $this->sqlCalc = FALSE;
            $sql = $this->conn->query('SELECT FOUND_ROWS() AS c');
        } else {
            $sql = self::findAll('COUNT(*) AS c');
        }

        return intval($sql->fetch()->c);
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
    public function l($v = 'NOW()') {
        return new \Nette\Database\SqlLiteral($v);
    }

}
