<?php

namespace Lib;

use PDO;
use Exception;
use Lib\DBDriver;

class DB
{

    public static $DB_RUN_CREATE = null;
    public static $DB_CONNECTION = null;
    public static $DB_HOST = null;
    public static $DB_PORT = null;
    public static $DB_ENGINE = null;
    public static $DB_CHARSET = null;
    public static $DB_CHARSET_COLLATE = null;
    public static $DB_DATABASE = null;
    public static $DB_USERNAME = null;
    public static $DB_PASSWORD = null;
    public static $pdo = null;

    public static function config(array $params)
    {
        try {
            self::$DB_RUN_CREATE = $params["DB_RUN_CREATE"] ?? false;
            self::$DB_CONNECTION = $params["DB_CONNECTION"];
            self::$DB_HOST = $params["DB_HOST"];
            self::$DB_PORT = $params["DB_PORT"];
            self::$DB_ENGINE = $params["DB_ENGINE"];
            self::$DB_CHARSET = $params["DB_CHARSET"];
            self::$DB_CHARSET_COLLATE = $params["DB_CHARSET_COLLATE"];
            if (self::$DB_RUN_CREATE && !isset($params["DB_DATABASE"])) {
                self::$DB_DATABASE = $params["DB_DATABASE"];    
            }
            self::$DB_DATABASE = $params["DB_DATABASE"] ?? "";
            self::$DB_USERNAME = $params["DB_USERNAME"];
            self::$DB_PASSWORD = $params["DB_PASSWORD"];
        } catch (Exception $e) {
            die("DB.config() - Error: " . $e->getMessage());
        }
    }

    public static function create()
    {
        if (self::$pdo && self::$DB_RUN_CREATE) {
            self::$pdo->query("CREATE DATABASE IF NOT EXISTS " . self::$DB_DATABASE . " DEFAULT CHARACTER SET " . self::$DB_CHARSET . " COLLATE " . self::$DB_CHARSET_COLLATE . "; USE " . self::$DB_DATABASE . "; COMMIT");
        }
    }

    public static function pdo()
    {
        if (self::$pdo) {
            self::$pdo = null;
        }
        $dbDriver = new DBDriver();
        $dbDriver->setSgbd(self::$DB_CONNECTION);
        $dbDriver->setHost(self::$DB_HOST);
        $dbDriver->setPort(self::$DB_PORT);
        $dbDriver->setCharset(self::$DB_CHARSET);
        $dbDriver->setDbname(self::$DB_RUN_CREATE ? "" : self::$DB_DATABASE);
        $dbDriver->setUser(self::$DB_USERNAME);
        $dbDriver->setPass(self::$DB_PASSWORD);
        self::$pdo = $dbDriver->getPdo();
        self::create();
        return self::$pdo;
    }

    public static function select($query, $data = [], $class = null)
    {
        $result = ["stmt" => null, "pdo" => null, "select" => []];
        try {
            $pdo = self::pdo();
            if ($pdo) {
                $stmt = $pdo->prepare($query);
                if ($stmt->execute($data) && ($count = $stmt->rowCount()) > 0) {
                    if ($class !== null) {
                        $result["select"] = $stmt->fetchAll(PDO::FETCH_CLASS, $class);
                    } else {
                        $result["select"] = $stmt->fetchAll();
                    }
                    $result["stmt"] = $stmt;
                    $result["pdo"] = $pdo;
                }
            }
        } catch (Exception $e) {
            if ($e->getCode() != "42S02") {
                die("DB.select() - Error: " . $e->getMessage());
            }            
        }        
        return $result;
    }

    public static function insert($query, $data = [])
    {
        $result = ["stmt" => null, "pdo" => null, "id" => [null]];
        try {
            $pdo = self::pdo();
            if ($pdo) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare($query);
                if ($stmt->execute($data) && ($count = $stmt->rowCount()) > 0) {
                    for ($i = 0; $i < $count; $i++) {
                        $result["id"][$i] = $pdo->lastInsertId();
                    }
                    $pdo->commit();
                    $result["stmt"] = $stmt;
                    $result["pdo"] = $pdo;
                }
            }
        } catch (Exception $e) {
            die("DB.insert() - Error: " . $e->getMessage());
        }
        return $result;
    }

    public static function query($query, $data = [])
    {
        $result = ["stmt" => null, "pdo" => null, "count" => 0];        
        try {
            $pdo = self::pdo();
            if ($pdo) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare($query);
                if ($stmt->execute($data) && $stmt->rowCount() > 0) {
                    $result["count"] = $stmt->rowCount();
                    $pdo->commit();
                    $result["stmt"] = $stmt;
                    $result["pdo"] = $pdo;
                }
            }
        } catch (Exception $e) {
            die("DB.query() - Error: " . $e->getMessage());
        }
        return $result;
    }

    public static function describe($tb_name)
    {
        return self::select("DESCRIBE $tb_name")['select'];
    }

    public static function show_create($tb_name)
    {
        return self::select("SHOW CREATE TABLE $tb_name")['select'];
    }

    public static function drop_table($tb_name)
    {
        return self::query("DROP TABLE $tb_name");
    }

    public function truncate_table($tb_name)
    {
        return self::query("TRUNCATE $tb_name");
    }

}
