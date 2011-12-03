<?php

namespace Utility;

use dibi;
use Nette\Environment;

class DBInterface
{
    /**
     *
     * @var DibiConnection
     */
    protected $db;

    public static $model = 'Models';

    /**
     *
     * @var string
     */
    protected $name;

    protected $table;

    protected $prefix;

    protected $temp;

    /**
     *
     * @param $name
     * @param $table
     * @param $connection
     * @return void
     */
    public function __construct($table, $name = null, $prefix = null, $connection = NULL)
    {
        $this->db = dibi::getConnection($connection);
        $this->table = $table;
        if($prefix === null)
            $prefix = substr($table, 0, 1);

        $prefix = strtoupper($prefix) .'_';

        if($prefix === 'T_')
            throw new RuntimeException('Prefix musn\'t "T" select another.');

        $this->prefix = $prefix;

        if($name === null)
            $name = 'I'.ucfirst(strtolower($table));
        $this->name = $name;
    }

    public function getTemp()
    {
        if(empty($this->temp))
            $this->temp = Environment::getVariable('appDir') .'/models/interfaces/';
        return $this->temp;
    }

    /**
     *
     * @param bool $save - ma se ulozit soubor do slozky?
     */
    public function render($save = false)
    {
        $res = $this->getInfo();
        $indent = str_repeat(' ', 4). 'const ';
        $file = "<?php\n\nnamespace ".self::$model.";\n\n";
        $file .= 'interface '. $this->name ."\n{\n". $indent .'T_'. strtoupper($this->table) ." = '$this->table';\n";
        $endFile = "\n//------------enum value\n";
        foreach($res as $val)
        {
            $file .= $indent . $this->prefix . strtoupper($val['Field']) ." = '". $val['Field'] ."';\n";
            if(substr($val['Type'], 0, 4) == 'enum')
            {
                $preg = array();
                preg_match('~\((.*)\)$~', $val['Type'], $preg);
                $preg = explode(',', $preg[1]);
                foreach($preg as $val)
                {
                    $endFile .= $indent . $this->prefix . strtoupper($val) .' = '. $val .";\n";
                }
            }
        }
        $file .= $endFile.'}';

        echo nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($file)));

        if($save)
            file_put_contents($this->getTemp() . $this->name .'.php', $file);
        exit;
    }

    protected function getInfo()
    {
        switch($this->db->config['driver'])
        {
            case 'sqlite3':
                return $this->getSqLite();
            case 'postgre':
                return $this->db->query('SELECT column_name AS %n, data_type AS %n FROM information_schema.columns WHERE table_name = %s', 'Field', 'Type', $this->table);
            default:
                return $this->db->query('SHOW COLUMNS FROM %n;', $this->table)->fetchAll();
        }
    }

    protected function getPostgre()
    {
        $res = '';
        $out = array();
        foreach($res as $val)
        {
            $out[] = array(
                'Field' => $value['column_name'],
                'Type' => $value['data_type'],
            );

        }
        unset($val);
        return $out;
    }

    protected function getSqLite()
    {
        $res = $this->db->query('PRAGMA table_info(%n);', $this->table)->fetchAll();

        $out = array();
        foreach($res as $value)
        {
            $out[] = array(
                'Field' => $value['name'],
                'Type' => $value['type'],
                'Null' => $value['notnull'],
                'Default' => $value['dflt_value'],
            );
        }
        return $out;
/*
   0 => DibiRow(6) {
      "Field" => "id_address" (10)
      "Type" => "int(10) unsigned" (16)
      "Null" => "NO" (2)
      "Key" => "PRI" (3)
      "Default" => NULL
      "Extra" => "auto_increment" (14)
   }
 *
 * sqlite
   0 => DibiRow(6) {
      "cid" => 0
      "name" => "GUID" (4)
      "type" => "CHAR(38)" (8)
      "notnull" => 0
      "dflt_value" => NULL
      "pk" => 1
   }
 */
    }
}
