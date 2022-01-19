<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
*/

namespace Lib;

use Lib\DB;
      
class Model
{

    public $id = "INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT";
    public $created_at = "DATETIME NOT NULL";
    public $updated_at = "DATETIME NULL";
    public $PRE_FIX_TB = "tb_";
    public $POS_FIX_TB = "s";
    public $PRE_FIX_COL = "";
    public $POS_FIX_COL = "";
    private $VAR_DEFS = null;

    public function __construct()
    {
        $this->VAR_DEFS = $this->keys_values("{{", "}}");
        $this->reset();
        $this->create($this->VAR_DEFS);
    }
    
    public function set($name, $value)
    {        
        return $this->$name = $value;
    }

    public function get($name)
    {        
        return $this->$name;
    }

    public function reset()
    {
        foreach ($this->keys() as $key) {            
            $this->set($key, null);
        }
    }

    public function class_name($resume = true)
    {
        $path_class = get_class($this);
        $index = strripos($path_class, "\\");
        if ($index && $index >= 0 && $resume) {
            $path_class = substr($path_class, $index + 1);
        }
        return $path_class;
    }

    public function class_var()
    {
        $array = object_to_array($this);
        foreach ($array as $key => $value) {
            $keylower = strtolower($key);            
            if ($keylower == $key) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public function class_var_unset()
    {
        foreach ($this->class_var() as $key => $value) {
            unset($this->$key);
        }
    }

    private function oa_simbol($simbolInit = "", $simbolEnd = "")
    {
        $array = object_to_array($this);
        foreach ($array as $key => $value) {
            $keylower = strtolower($key);                
            unset($array[$key]);
            if ($keylower != $key) {
                continue;
            }
            $array[$simbolInit . $key . $simbolEnd] = $value;
        }
        return $array;
    }

    public function keys($simbolInit = "", $simbolEnd = "")
    {
        return array_keys($this->oa_simbol($simbolInit, $simbolEnd));
    }    

    public function keys_values($simbolInit = "", $simbolEnd = "")
    {
        return $this->oa_simbol($simbolInit, $simbolEnd);
    }

    private function parseTupleToInstance(&$inst, &$tuple)
    {
        $sizePRE_FIX_COL = strlen($this->PRE_FIX_COL);
        $sizePOS_FIX_COL = strlen($this->POS_FIX_COL);
        foreach ($tuple as $key => $value) {
            $key = substr($key, $sizePRE_FIX_COL);
            $key = substr($key, 0, strlen($key) - $sizePOS_FIX_COL);
            $inst->set($key, $value);
        }
        return $inst;
    }

    private function query_params($query_params)
    {
        $keys = $this->keys("{{", "}}");
        $keys_tb = $this->keys($this->PRE_FIX_COL, $this->POS_FIX_COL);
        foreach ($keys as $index => $key) {
            $query_params = str_replace([$key, strtoupper($key)], $keys_tb[$index], $query_params);
        }
        return $query_params;
    }

    public function tb_name()
    {
        return $this->PRE_FIX_TB . decamelize($this->class_name()) . $this->POS_FIX_TB;
    }

    public function describe()
    {
        return DB::describe($this->tb_name());
    }

    public function show_create()
    {
        return DB::show_create($this->tb_name());
    }

    public function rename($tb_name_old, $tb_name_new)
    {
        return DB::query("ALTER TABLE $tb_name_old RENAME TO $tb_name_new");
    }

    public function diff_col_names(){
        $tb_describe = $this->describe();
        $tb_colums = $this->keys($this->PRE_FIX_COL, $this->POS_FIX_COL);
        if (count($tb_describe) != count($tb_colums)) {
            return true;
        } else {
            foreach ($tb_describe as $key => $value) {
                if (!in_array($value["Field"], $tb_colums)) {
                    return true;
                }                
            }
        }
        return false;
    }

    public function create($var_defs)
    {
        if ($this->diff_col_names()) {
            $tb_name = $this->tb_name();
            dumpl("There was a change in model `" . $this->class_name() . "`");
            if (count($this->describe()) > 0) {
                $tb_name_backup = $tb_name . "_" . date("Y_m_d_H_i_s");
                $this->rename($tb_name, $tb_name_backup);
                dumpl("Table `$tb_name` has been renamed to `$tb_name_backup`");
            }
            $keys = [
                "{{id}} " . $var_defs["{{id}}"]
            ];
            foreach ($var_defs as $key => $value) {
                if ($key === "{{id}}") {
                    continue;
                } else if ($value !== null) {
                    $keys[] = $key . " " . $value;
                    continue;
                }
                $keys[] = "$key TEXT NOT NULL $value";            
            }
            $DB_ENGINE = DB::$DB_ENGINE;
            $DB_CHARSET = DB::$DB_CHARSET;
            $DB_CHARSET_COLLATE = DB::$DB_CHARSET_COLLATE;
            $sql = "CREATE TABLE IF NOT EXISTS $tb_name (" . implode(", ", $keys) . ") ENGINE=$DB_ENGINE CHARSET=$DB_CHARSET COLLATE $DB_CHARSET_COLLATE";
            $sql = $this->query_params($sql);
            DB::query($sql);
            dumpl("A new table called `$tb_name` was created", $sql);
            return true;
        }
        return false;
    }

    public function query($sql)
    {
        return DB::query($sql);
    }    

    public function drop()
    {
        return DB::drop_table($this->tb_name());
    }

    public function truncate($force = false)
    {
        if (!$force) {
            return DB::truncate_table($this->tb_name());
        }
        return [$this->drop(), $this->create($this->VAR_DEFS)];
    }

    public function table_order_id()
    {
        $tb_name = $this->tb_name();
        $sql = "SELECT COUNT({{id}}) AS count FROM $tb_name";
        $sql = $this->query_params($sql);
        $count = ((int) DB::select($sql)['select'][0]['count']) + 1;
        $sql = "SET @count = 0; UPDATE $tb_name SET {{id}} = @count:= @count + 1; ALTER TABLE $tb_name AUTO_INCREMENT=$count;";
        $sql = $this->query_params($sql);
        return DB::query($sql);
    }

    public function save()
    {
        $keys_values = $this->keys_values(":");
        if ($this->findId(null, false) === null) {
            $keys = implode(", ", $this->keys($this->PRE_FIX_COL, $this->POS_FIX_COL));
            $values = implode(", ", $this->keys(":"));
            $keys_values[":created_at"] = date("Y-m-d H:i:s");
            $this->created_at = $keys_values[":created_at"];
            $sql = "INSERT INTO " . $this->tb_name() . " (" . $keys . ") VALUES (" . $values . ")"; 
            $result = DB::insert($sql, $keys_values);
            if ($this->id === null) {
                $this->id = $result["id"][0];
            }
            return $result;
        } else {
            $keys = $this->keys();
            $keys_values[":updated_at"] = date("Y-m-d H:i:s");
            $this->updated_at = $keys_values[":updated_at"];
            $sql = "UPDATE " . $this->tb_name() . " SET ";
            foreach ($keys as $index => $key) {
                if ($keys_values[":$key"] === null || $key == "created_at" || $key == "id") {
                    if ($key != "id") {
                        unset($keys_values[":$key"]);    
                    }
                    unset($keys[$index]);
                    continue;
                }
                $sql .= "{{" . $key . "}} = :$key, ";
            }
            $sql = substr($sql, 0, strlen($sql) - 2);
            $sql .= " WHERE {{id}} = :id";
            $sql = $this->query_params($sql);
            return DB::query($sql, $keys_values);
        }
        return false;
    }

    public function all($where = null, $where_values = [])
    {
        $sql = "SELECT * FROM " . $this->tb_name();
        if ($where !== null) {
            $keys = $this->keys("{{", "}}");
            $keys_tb = $this->keys($this->PRE_FIX_COL, $this->POS_FIX_COL);
            foreach ($keys as $index => $key) {
                $where = str_replace($key, $keys_tb[$index], $where);
            }            
            $sql .= " WHERE $where";
        }
        $select = DB::select($sql, $where_values)['select'];
        //dumpd($sql, $where_values, $select);
        if (count($select) > 0) {
            $class = get_class($this);
            foreach ($select as $key => $value) {                
                $inst = (new $class);
                $select[$key] = $this->parseTupleToInstance($inst, $value);
                //$inst->class_var_unset();
            }
            return $select;
        }
        return [];
    }

    public function where($where = null, $where_values = [])
    {
        $result = $this->all($where, $where_values);
        return count($result) > 0 ? $result : null;
    }

    public function findId($id = null)
    {
        if ($id === null) {
            $id = $this->id;
        }
        $result = $this->where("{{id}} = :id LIMIT 1", [":id" => $id]);
        return ($result !== null && count($result)) == 1 ? $result[0] : null;
    }
    
    public function delete($id = null)
    {
        if ($id === null) {
            $id = $this->id;
        }
        $sql = "DELETE FROM " . $this->tb_name() . " WHERE {{id}} = :id";
        $sql = $this->query_params($sql);
        return DB::query($sql, [":id" => $id]);
    }

}
