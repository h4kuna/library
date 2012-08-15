<?php

namespace Models;

class DbModel extends BaseModel {

    /** @var string */
    protected $table = 'CHANGE IT';

    /** @var string */
    protected $primary;
    protected $sqlCalc = 0;
    private $db;
    protected $staticColumn = array();
    protected $conn;

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

    public function setDb($v) {
        if (!$this->db) {
            $this->db = $v;
        }
    }

    //-----------------elementární metody

    public function update(array $data, $id, $by = NULL) {
        $this->prepareData($data);
        return $this->getCondition($by, $id)->update($data);
    }

    public function delete($id, $column = NULL, $by = NULL) {
        $out = NULL;
        if ($column && $id) {
            $out = $this->fetch($id, $column);
        }

        $delete = $this->getCondition($by, $id)->delete();
        return ($out !== NULL) ? $out : $delete;
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

    public function getDb($clone = TRUE) {
        return $clone ? clone $this->db : $this->db;
    }

    public function getTable($model = NULL) {
        if ($model === NULL)
            return $this->table;

        return $this->context->{$model}->getTable();
    }

    public function getPrimary() {
        return $this->primary;
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * orizne pole na požadované sloupce
     * @param array $data
     * @return type
     */
    public function shave(array $data) {
        return array_intersect_key($data, $this->mapper);
    }

    protected function getCondition($condition, $parameters) {

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

}
