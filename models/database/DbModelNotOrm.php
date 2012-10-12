<?php

namespace Models;

/**
 * @author Milan Matějček
 */
abstract class DbModelNotOrm extends DbModel implements IDbModel {

    /** @var string */
    protected $primary = 'id';

    /** @var NotORM */
    protected $conn;

    public function __construct(\Nette\DI\Container $container) {
        parent::__construct($container);
        $this->conn = $container->createDatabaseNotorm($this->primary);
        $this->db = $this->conn->{$this->table};
    }

    public function insert(array $data, $lastId = FALSE) {
        if ($lastId === 'multi') {
            foreach ($data as &$v) {
                $this->checkInsetData($v);
            }
            unset($v);
            return call_user_func_array(callback($this->getDb(), 'insert'), $data);
        }

        $this->checkInsetData($data);
        $res = $this->getDb()->insert($data);
        return $lastId ? $res[$this->primary] : $res;
    }

    private function checkInsetData(array &$data) {
        $this->prepareData($data);
        foreach ($this->staticColumn as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }
    }

    /**
     * omezuje dotaz podminkou
     * @param type $id
     * @param string $columns
     * @param type $by
     * @return NotORM_Result
     */
    public function find($id, $columns = '*', $by = NULL) {
        return parent::find($id, $columns, $by);
    }

    /**
     * omezuje dotaz podminkou a vrati radek
     * @param type $id
     * @param type $columns
     * @param type $by
     * @return NotORM_Row
     */
    public function fetch($id, $columns = '*', $by = NULL) {
        return parent::fetch($id, $columns, $by);
    }

    /**
     * vybira vsechny radky z tabulky
     * @param type $columns
     * @param type $page
     * @param type $itemsPerPage
     * @return NotORM_Result
     */
    public function findAll($columns = '', $condition = NULL, $parameters = NULL) {
        return parent::findAll($columns, $condition, $parameters);
    }

    /**
     *
     * @param type $sql
     * @return \PDOStatement
     */
    public function query($sql) {
        $pdo = $this->context->pdo;
        $sth = $pdo->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $this->pdoBindParam(array_slice(func_get_args(), 1), $sth)->execute();
        return $sth;
//        if (strtolower(substr($sql, 0, 3)) == 'call') {
//            return $sth->execute();
//        }
        //return $pdo->query($sth);
    }

    private function pdoBindParam(array $params, \PDOStatement $sth) {
        foreach ($params as $k => $v) {
            $sth->bindParam($k + 1, $v, $this->pdoType($v));
        }
        return $sth;
    }

    private function pdoType($v) {
        switch (gettype($v)) {
            case 'boolean':
                return \PDO::PARAM_BOOL;
            case 'integer':
                return \PDO::PARAM_INT;
            case 'NULL':
                return \PDO::PARAM_NULL;
            default:
                return \PDO::PARAM_STR;
        }
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

//-----------------transaction

    public function begin() {
        if (!$this->context->pdo->inTransaction()) {
            $this->conn->transaction = 'begin';
        }
    }

    public function commit() {
        $this->conn->transaction = 'commit';
    }

    public function rollback() {
        $this->conn->transaction = 'rollback';
    }

//-----------------gettery
    /**
     * prevede dotaz na pozadovane pole
     * @param \NotORM_Result $sql
     * @param type $key
     * @param type $array
     * @return type
     */
    public function fetchArray(\NotORM_Result $sql, $key = NULL, $array = FALSE) {
        $out = array();
        $c = 0;
        foreach ($sql as $v) {
            $k = $key === NULL ? $c : $v[$key];
            $out[$k] = ($array) ? $v->getIterator()->getArrayCopy() : $v;
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
        $l = new \NotORM_Literal($v);
        $param = func_get_args();
        array_shift($param);
        $l->parameters = $param;
        return $l;
    }

    public function getLastError() {
        $res = $this->query('CALL error_get_last()')->fetch();
        if (!$res['code']) {
            return FALSE;
        }
        return $res;
    }

}