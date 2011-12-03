<?php
/**
 * podminky:
 * ord - unsigned, unique, NULL
 * level - unsigned
 */

/** Třída pro obecnou práci se stromem
 */
abstract class Tree extends Control
{
    /**
     * name of column
     * @var string
     */
    protected $sqlId    ='id';//classic id
    protected $sqlName  ='name';//name for translate
    protected $sqlLevel ='level';//deep in tree
    protected $sqlOrd   ='order';//order of elements

    /**
     * connection to database
     * @var DibiConnection
     */
    protected $db;

    /**
     * table for working
     * @var string
     */
    protected $table  =false;

    /**
     * @param $table
     * @param $connection
     * @return void
     */
    public function __construct($table = false, $connection = 0)
    {
        parent::__construct();
        if($table != false)
            $this->table =$table;

        $this->db   =dibi::getConnection($connection);
    }

    public function updateLevel($level, $id)
    {
        if($level < 0)
            return false;
        return $this->db->query('UPDATE %n SET %n=%i WHERE %n=%i',
                        $this->table, $this->sqlLevel, $level, $this->sqlId, $id);
    }

    /**
     *
     * @param int $order order in tree
     * @return int id oz tree
     */
    protected function getId($order)
    {
        return $this->db->query('SELECT %n
                                 FROM %n
                                 WHERE %n=%i',
                                 $this->sqlId,
                                 $this->table,
                                 $this->sqlOrd, $order)
                                 ->fetchSingle();
    }

    /**
     * found result
     * @return DibiFluent
     */
    public function getTree($null=false)
    {
        $where  ='1';
        if($null === false)
        {
            $where  ='['. $this->sqlOrd .'] IS NOT NULL';
        }

        return $this->db->select('*')
                    ->from($this->table)
                    ->where($where)
                    ->orderBy('['.$this->sqlOrd .'] ASC');
    }

    /**
     * one row from tree
     * @param $column
     * @param $value
     * @return DibiFluent
     */
    public function getRow($column, $value)
    {
        switch($column)
        {
            case false;
                return false;

            case $this->sqlName:
            case $this->sqlLevel:
            case $this->sqlOrd:
            case $this->sqlId:
                return $this->db->query("SELECT * FROM [$this->table]
                WHERE [$column] = '$value'
                ORDER BY [$this->sqlOrd] ASC
                LIMIT 1")->fetchSingle();
            break;

            default:
                throw new LogicException('Column does not exists "'. $column .'"!');
        }
    }

    /**
     *
     * @param string $column
     * @param string $value
     * @param string $null
     * @return unknown
     */
    public function get4Sync($column=false, $value=false, $null=false)
    {
        $where  ='1';
        if($null === false)
        {
            $where  ="[$this->sqlOrd] IS NOT NULL";
        }

        $result =$this->getRow($column, $value);

        if($result !== false)
        {
            $where  .=" AND [$this->sqlOrd] >= ". $result[ $this->sqlOrd ];
        }

        return $this->db->query("SELECT * FROM [$this->table]
        WHERE $where
        ORDER BY [$this->sqlOrd] ASC")->fetch();
    }

    /**
     * vlozeni noveho zaznamu do stromu
     */
    public function insert(array $arr=array())
    {
        $name = $arr[$this->sqlName];
        $ord  = isset($arr[$this->sqlOrd])? $arr[$this->sqlOrd]: 'NULL';
        $level= isset($arr[$this->sqlLevel])? Math::interval($arr[$this->sqlLevel]): 0;

        $arr[$this->sqlOrd]    =$ord;

        $this->db->begin();

        try
        {
            $this->makePlace($ord);
            $this->db->query('INSERT INTO ['. $this->table .']', $arr);
            $id =$this->db->insertId();
            $this->db->commit();
        }
        catch(DibiDriverException $e)
        {
            $this->db->rollback();
            return $e;
        }

        return $id;
    }

    /**
     * presun zaznamu ve stromu
     *
     */
    public function move($id, $newOrder, $children=true)
    {
        $rows   =$this->getNode($id, $children);
        $max    =end($rows);
        $max    =$max[$this->sqlOrd];
        $min    =$rows[0][$this->sqlOrd];

        if($min == $newOrder)
            return false;

        $this->db->begin();
        try
        {
            $this->db->query('UPDATE ['. $this->table .']
                SET ['. $this->sqlOrd .'] = NULL
                WHERE  ['. $this->sqlOrd .'] BETWEEN '. $min .'
                AND '. $max);

            $this->makePlace($newOrder, count($rows));

            foreach($rows as $value)
            {
                $this->db->query('UPDATE ['. $this->table .']
                SET ['. $this->sqlOrd .'] = '. $newOrder .'
                WHERE  ['. $this->sqlId .'] = '. $value[$this->sqlId]);
                $newOrder++;
            }

            $this->db->commit();

            $this->repairTree();
        }
        catch (DibiDriverException $e)
        {
            $this->db->rollback();
            return $e;
        }

        return true;
    }

    /**
     * pokud existuji potomci vrati je a na prvnim miste rodice
     * @return DibiRow
     */
    public function getNode($id, $children=true)
    {
        $rows  =$this->db->query('SELECT ['. $this->sqlId .'], ['. $this->sqlOrd .'], ['. $this->sqlLevel .']
                        FROM ['. $this->table .']
                        WHERE ['. $this->sqlId .'] = %i
                        LIMIT 1', $id)->fetchAll();

        if($rows === false)
            return false;

        if(!$children)
            return $rows;

        $rows  =$this->db->query('SELECT %n, %n, %n
                        FROM %n
                        WHERE %n >= %i
                        AND %n >= %i
                        ORDER BY %n ASC
                        LIMIT 1',
                        $this->sqlId, $this->sqlOrd, $this->sqlLevel,
                        $this->table,
                        $this->sqlOrd, $rows[0]->{$this->sqlOrd},
                        $this->sqlLevel, $rows[0]->{$this->sqlLevel},
                        $this->sqlOrd);
        $count = 0;
        foreach($rows as $key => $val)
        {
            if($key === 0)
            {
                $ord   = $val->{$this->sqlOrd};
                $level = $val->{$this->sqlLevel};
            }
            elseif(!($level > $val->{$this->sqlLevel}) || !$ord == $val->{$this->sqlOrd}+1)
            {
                break;
            }
            $count++;
        }

        return $rows->fetchAll(null, $count);
    }

    public function getParentTree($id)
    {
        //@TODO better, now are 3 sql remake to 1
        #1
        $result = $this->db->select($this->sqlOrd)
                       ->from($this->table)
                       ->where($this->sqlId .'='. $id)
                       ->FetchSingle();
        #2
        $result = $this->db->select('MAX(['. $this->sqlOrd .']) AS ['. $this->sqlOrd .']')
                       ->from($this->table)
                       ->where('['. $this->sqlOrd .']<='. $result)
                       ->orderBy('['. $this->sqlOrd .'] ASC')
                       ->groupBy('['. $this->sqlLevel .']');

        $ords = null;
        foreach($result as $val)
        {
            $ords .= $val->{$this->sqlOrd}.', ';
        }

        #3
        return $this->getIdByOrd(substr($ords, 0, -2))->fetchAll();
    }

    public function getIdByOrd($str)
    {
        return $this->db->select($this->sqlId)
                 ->from($this->table)
                 ->where('['.$this->sqlOrd .'] IN ('.$str.')');
    }

    /**
     * smaze polozku a opravi strom
     *
     */
    public function delete($id, $children=true)
    {//$children - smazat i deti
        $ord    =$this->getNode($id, $children);

        if($ord === false)
        {
            return ("This id $id - does not exists.");
        }

        try
        {
            $max    =end($ord);
            $this->db->query('DELETE FROM ['. $this->table .']
            WHERE ['. $this->sqlOrd .'] BETWEEN '. $ord[0][$this->sqlOrd] .' AND '. $max[$this->sqlOrd]);
            return $this->repairTree($ord);
        }
        catch (DibiDriverException $e)
        {
            return $e;
        }
    }

    /**
     * vytvori potrebny prostor ve stromu
     * @param $ord
     * @param $place
     * @return bool|array
     */
    public function makeSpace($ord, $place=1)
    {
        $this->db->begin();

        try
        {
            $this->makePlace($ord, $place);
            $this->db->commit();
            return true;
        }
        catch(DibiDriverException $e)
        {
            $this->db->rollback();
            return $e;
        }
    }

    /**
     * zkontroluje zda je prostor pro vlozeni zaznamu a vytvori jej
     * nepracuje v transakci!!! sama o sobe proto nastavena jako protected
     */
    protected function makePlace($ord, $place=1)
    {
        if( !($ord >= 0) || $place < 1 )
        {
            return false;
        }

        $place  +=$ord;

        $rows   =$this->db->query('SELECT ['. $this->sqlId .'] FROM ['. $this->table .']
        WHERE ['. $this->sqlOrd .'] BETWEEN '. $ord .' AND '. ($place - 1) .'
        ORDER BY ['. $this->sqlOrd .'] ASC' )->fetchAll();

        $addition   =count($rows);

        $this->db->query('UPDATE ['. $this->table .']
        SET ['. $this->sqlOrd .'] = ['. $this->sqlOrd .']+'. $addition .'
        WHERE  ['. $this->sqlOrd .'] >= '. $place .'
        ORDER BY ['. $this->sqlOrd .'] DESC');

        foreach($rows as $value)
        {
            $this->db->query('UPDATE ['. $this->table .']
            SET ['. $this->sqlOrd .'] = '. $place .'
            WHERE  ['. $this->sqlId .'] = '. $value[$this->sqlId]);
            $place++;
        }
    }

    /**
     * pokud se sloupec [$this->sqlOrd] = NULL tak se jedna o nezarazenou polozku
     * zkontroluje sloupec ord a sloupec level pripadne je opravi
     */
    public function repairTree($fromOrd=NULL, $toOrd=NULL)
    {//stoupat muze o jednu, klesat libovolne minimalne 0
        $ord    =0;
        $level  =0;
        $where  =null;
        $update ='UPDATE ['. $this->table .'] SET ';

        if($fromOrd > 0)
        {
            $rowUp   =$this->db->query('SELECT ['. $this->sqlId .'], ['. $this->sqlOrd .'], ['. $this->sqlLevel .']
                    FROM ['. $this->table .']
                    WHERE ['. $this->sqlOrd .'] < '. $fromOrd .'
                    ORDER BY ['. $this->sqlOrd .'] DESC
                    LIMIT 1')->fetch();

            $level  =$rowUp[ $this->sqlLevel ] + 1;
            $ord    =$rowUp[ $this->sqlOrd ] + 1;
            $where  =' AND ['. $this->sqlOrd .'] >= '. $ord;
        }

        if($toOrd > 0)
        {
            $rowDown =$this->db->query('SELECT ['. $this->sqlOrd .']
                    FROM ['. $this->table .']
                    WHERE ['. $this->sqlOrd .'] > '. $toOrd .'
                    ORDER BY ['. $this->sqlOrd .'] ASC
                    LIMIT 1')->fetch();

            if( $rowDown[ $this->sqlOrd ] == $toOrd + 1 )
                $where  .=' AND ['. $this->sqlOrd .'] <= '. $toOrd;
        }


        $rows   =$this->db->query('SELECT ['. $this->sqlId .'], ['. $this->sqlOrd .'], ['. $this->sqlLevel .']
        FROM ['. $this->table .']
        WHERE ['. $this->sqlOrd .'] IS NOT NULL'. $where .'
        ORDER BY ['. $this->sqlOrd .'] ASC, ['. $this->sqlLevel .'] ASC' )->fetchAll();

        $this->db->begin();

        try
        {
            foreach($rows as $value)
            {
                if( $value[$this->sqlLevel] > $level || $value[$this->sqlLevel] < 0)
                {//neodpovida level
                    $value[$this->sqlLevel] =Math::interval($value[$this->sqlLevel], 0, $level);
                    $this->db->query($update .'['. $this->sqlLevel .'] = '. $value[$this->sqlLevel] .'
                    WHERE ['. $this->sqlId .'] = '. $value[$this->sqlId]);
                }

                if($ord != $value[$this->sqlOrd])
                {//neodpovida poradi
                    $this->db->query($update .'['. $this->sqlOrd .'] = '. $ord .'
                    WHERE ['. $this->sqlId .'] = '. $value[$this->sqlId]);
                }

                $level  =$value[$this->sqlLevel] + 1;
                $ord++;
            }

            $this->db->commit();
            return true;
        }
        catch(DibiDriverException $e)
        {
            $this->db->rollback();
            return $e;
        }
    }

    /**
     * WARNING only for advance user
     * @return bool
     */
    public function repairId($really=false)
    {
        if($really !== true)
            return false;

        $data = $this->db->query("SELECT [$this->sqlId] FROM [$this->table] WHERE 1 ORDER BY [$this->sqlId] ASC");

        $this->db->begin();

        try
        {
            $i=1;
            while($row = $data->fetch())
            {
                if($row[$this->sqlId] != $i)
                {
                    $this->db->query("UPDATE [$this->table] SET [$this->sqlId] = $i WHERE [$this->sqlId] = %i", $row[$this->sqlId]);
                }
                $i++;
            }
            $this->db->commit();
            return true;
        }
        catch (DibiException $e)
        {
            $this->db->rollback();
            return false;
        }
    }
}

class NewTree extends Control
{
    /**
     * database connection by dibi, all query are use by DibiFluents
     * @var DibiConnection
     */
    protected $db;
    protected $table    = 'tree';
    protected $fields   = array(
            'id'        => 'id',
            'parent_id' => 'parent_id',
            'position'  => 'position',
            'left'      => 'left',
            'right'     => 'right',
            'level'     => 'level'
        );

    //private $buildFields = false;

    public function __construct($table = null, $fields = array(), $connection = 0)
    {
        if($table !== null)
            $this->table = $table;

        if(!empty($fields))
        {
        /*
            $defekt = array_diff_key($fields, $this->fields);
            if(!empty($defekt))
                throw new RuntimeException('BAD KEY IS "'.key($defekt).'", because key must by same as in $this->fields.');
        */
            $this->fields = $fields + array_diff_key($this->fields, $fields);
        }

        $this->db = dibi::getConnection($connection);
    }

    /**
     * function return value about node by id and optional params are column of database
     * @param unsigned int $id
     * @return DibiRow
     */
    public function getNode($id/*, ...*/)
    {
        $rows = func_get_args();
        return $this->db->select(self::selectRows($rows, 1))
                        ->from($this->table)
                        ->where('`'. $this->fields['id'] .'`='. (int)$id )
                        ->fetch();
    }

    /**
     *
     * @param unsigned int $id
     * @param bool $recursive
     * @param bool $fetchAll
     * @return array|DibiResult
     */
    public function getChildren($id, $recursive = false, $fetchAll = true/*, ...*/)
    {
        $col = func_get_args();
        $column = self::selectRows($col, 3);
        if($recursive) {
            $node = $this->getNode($id);
            $rows = $this->db->query('SELECT '. $column .'
                              FROM `'.$this->table.'`
                              WHERE `'.$this->fields['left'].'` >= '.(int) $node[$this->fields['left']].'
                              AND `'.$this->fields['right'].'` <= '.(int) $node[$this->fields['right']].'
                              ORDER BY `'.$this->fields['left'].'` ASC');
        }
        else {
            $rows = $this->db->query('SELECT '. $column .'
                              FROM `'.$this->table.'`
                              WHERE `'.$this->fields['parent_id'].'` = '.(int) $id.'
                              ORDER BY `'.$this->fields['position'].'` ASC');
        }

        if($fetchAll)
            return $rows->fetchAll();
        return $rows;
    }

    public function getPath($id)
    {
        return true;
    }

    public function create($parent, $position)
    {
        return $this->move(0, $parent, $position);
    }



    function move($id, $ref_id, $position = 0, $is_copy = false)
    {
        if((int)$ref_id === 0 || (int)$id === 1) { return false; }
        $sql        = array();                      // Queries executed at the end
        $node       = $this->getNode($id);        // Node data
        $nchildren  = $this->getChildren($id);    // Node children
        $ref_node   = $this->getNode($ref_id);    // Ref node data
        $rchildren  = $this->getChildren($ref_id);// Ref node children

        $ndif = 2;
        $node_ids = array(-1);
        if($node !== false) {
            $node_ids = array_keys($this->_get_children($id, true));
            // TODO: should be !$is_copy && , but if copied to self - screws some right indexes
            if(in_array($ref_id, $node_ids)) return false;
            $ndif = $node[$this->fields["right"]] - $node[$this->fields["left"]] + 1;
        }
        if($position >= count($rchildren)) {
            $position = count($rchildren);
        }

        // Not creating or copying - old parent is cleaned
        if($node !== false && $is_copy == false) {
            $sql[] = "" .
                "UPDATE `".$this->table."` " .
                    "SET `".$this->fields["position"]."` = `".$this->fields["position"]."` - 1 " .
                "WHERE " .
                    "`".$this->fields["parent_id"]."` = ".$node[$this->fields["parent_id"]]." AND " .
                    "`".$this->fields["position"]."` > ".$node[$this->fields["position"]];
            $sql[] = "" .
                "UPDATE `".$this->table."` " .
                    "SET `".$this->fields["left"]."` = `".$this->fields["left"]."` - ".$ndif." " .
                "WHERE `".$this->fields["left"]."` > ".$node[$this->fields["right"]];
            $sql[] = "" .
                "UPDATE `".$this->table."` " .
                    "SET `".$this->fields["right"]."` = `".$this->fields["right"]."` - ".$ndif." " .
                "WHERE " .
                    "`".$this->fields["right"]."` > ".$node[$this->fields["left"]]." AND " .
                    "`".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ";
        }
        // Preparing new parent
        $sql[] = "" .
            "UPDATE `".$this->table."` " .
                "SET `".$this->fields["position"]."` = `".$this->fields["position"]."` + 1 " .
            "WHERE " .
                "`".$this->fields["parent_id"]."` = ".$ref_id." AND " .
                "`".$this->fields["position"]."` >= ".$position." " .
                ( $is_copy ? "" : " AND `".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ");

        $ref_ind = $ref_id === 0 ? (int)$rchildren[count($rchildren) - 1][$this->fields["right"]] + 1 : (int)$ref_node[$this->fields["right"]];
        $ref_ind = max($ref_ind, 1);

        $self = ($node !== false && !$is_copy && (int)$node[$this->fields["parent_id"]] == $ref_id && $position > $node[$this->fields["position"]]) ? 1 : 0;
        foreach($rchildren as $k => $v) {
            if($v[$this->fields["position"]] - $self == $position) {
                $ref_ind = (int)$v[$this->fields["left"]];
                break;
            }
        }
        if($node !== false && !$is_copy && $node[$this->fields["left"]] < $ref_ind) {
            $ref_ind -= $ndif;
        }

        $sql[] = "" .
            "UPDATE `".$this->table."` " .
                "SET `".$this->fields["left"]."` = `".$this->fields["left"]."` + ".$ndif." " .
            "WHERE " .
                "`".$this->fields["left"]."` >= ".$ref_ind." " .
                ( $is_copy ? "" : " AND `".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ");
        $sql[] = "" .
            "UPDATE `".$this->table."` " .
                "SET `".$this->fields["right"]."` = `".$this->fields["right"]."` + ".$ndif." " .
            "WHERE " .
                "`".$this->fields["right"]."` >= ".$ref_ind." " .
                ( $is_copy ? "" : " AND `".$this->fields["id"]."` NOT IN (".implode(",", $node_ids).") ");

        $ldif = $ref_id == 0 ? 0 : $ref_node[$this->fields["level"]] + 1;
        $idif = $ref_ind;
        if($node !== false) {
            $ldif = $node[$this->fields["level"]] - ($ref_node[$this->fields["level"]] + 1);
            $idif = $node[$this->fields["left"]] - $ref_ind;
            if($is_copy) {
                $sql[] = "" .
                    "INSERT INTO `".$this->table."` (" .
                        "`".$this->fields["parent_id"]."`, " .
                        "`".$this->fields["position"]."`, " .
                        "`".$this->fields["left"]."`, " .
                        "`".$this->fields["right"]."`, " .
                        "`".$this->fields["level"]."`" .
                    ") " .
                        "SELECT " .
                            "".$ref_id.", " .
                            "`".$this->fields["position"]."`, " .
                            "`".$this->fields["left"]."` - (".($idif + ($node[$this->fields["left"]] >= $ref_ind ? $ndif : 0))."), " .
                            "`".$this->fields["right"]."` - (".($idif + ($node[$this->fields["left"]] >= $ref_ind ? $ndif : 0))."), " .
                            "`".$this->fields["level"]."` - (".$ldif.") " .
                        "FROM `".$this->table."` " .
                        "WHERE " .
                            "`".$this->fields["id"]."` IN (".implode(",", $node_ids).") " .
                        "ORDER BY `".$this->fields["level"]."` ASC";
            }
            else {
                $sql[] = "" .
                    "UPDATE `".$this->table."` SET " .
                        "`".$this->fields["parent_id"]."` = ".$ref_id.", " .
                        "`".$this->fields["position"]."` = ".$position." " .
                    "WHERE " .
                        "`".$this->fields["id"]."` = ".$id;
                $sql[] = "" .
                    "UPDATE `".$this->table."` SET " .
                        "`".$this->fields["left"]."` = `".$this->fields["left"]."` - (".$idif."), " .
                        "`".$this->fields["right"]."` = `".$this->fields["right"]."` - (".$idif."), " .
                        "`".$this->fields["level"]."` = `".$this->fields["level"]."` - (".$ldif.") " .
                    "WHERE " .
                        "`".$this->fields["id"]."` IN (".implode(",", $node_ids).") ";
            }
        }
        else {
            $sql[] = "" .
                "INSERT INTO `".$this->table."` (" .
                    "`".$this->fields["parent_id"]."`, " .
                    "`".$this->fields["position"]."`, " .
                    "`".$this->fields["left"]."`, " .
                    "`".$this->fields["right"]."`, " .
                    "`".$this->fields["level"]."` " .
                    ") " .
                "VALUES (" .
                    $ref_id.", " .
                    $position.", " .
                    $idif.", " .
                    ($idif + 1).", " .
                    $ldif.
                ")";
        }
        foreach($sql as $q) { $this->db->query($q); }
        $ind = $this->db->insert_id();
        if($is_copy) $this->_fix_copy($ind, $position);
        return $node === false || $is_copy ? $ind : true;
    }


/*    protected function getBuildFields()
    {
        if($this->buildFields === false)
            $this->buildFields = '`'.implode('` , `', $this->fields).'`';

        return $this->buildFields;
    }
*/





    /**
     *
     * @param array $rows array by func_get_args()
     * @param int $firstIs
     * @return string
     */
    protected static function selectRows(array $rows, $firstIs = 0)
    {
        for($i=0; $i<$firstIs; $i++)
            array_shift($rows);
        return (!empty($rows))? '`'. implode('`, `', $rows).'`': '*';
    }
}
