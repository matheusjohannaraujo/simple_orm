<?php

namespace Lib;

use PDO;
use Exception;

class DBDriver
{

    private $sgbd = 'mysql';
    private $host = 'localhost';
    private $port = '3306';
    private $user = 'root';
    private $pass = '';
    private $dbname = '';
    private $charset = 'utf8';
    private $pdo = null;

    public function setSgbd($value)
    {
        $this->sgbd = $value;
    }

    public function getSgbd()
    {
        return $this->sgbd;
    }

    public function setHost($value)
    {
        $this->host = $value;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setPort($value)
    {
        $this->port = $value;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setUser($value)
    {
        $this->user = $value;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setPass($value)
    {
        $this->pass = $value;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function setDbname($value)
    {
        $this->dbname = $value;
    }

    public function getDbname()
    {
        return $this->dbname;
    }

    public function setCharset($value)
    {
        $this->charset = $value;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setPdo()
    {
        if ($this->pdo === null && $this->getSgbd() !== null && $this->getHost() !== null && $this->getPort() !== null && $this->getDbname() !== null && $this->getCharset() !== null && $this->getUser() !== null && $this->getPass() !== null) {
            try {
                $this->pdo = new PDO($this->getSgbd() . ":host=" . $this->getHost() . ";port=" . $this->getPort() . ";dbname=" . $this->getDbname() . ";charset=" . $this->getCharset(), $this->getUser(), $this->getPass(), array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
                $this->pdo->exec("SET GLOBAL max_allowed_packet=16777216");
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_TIMEOUT, 300000);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
            } catch (Exception $e) {
                die("DBDriver.setPdo() - Error: connection failure. Code=" . $e->getCode()/* . " | " . $e->getMessage()*/);
            }
        } else {
            die("DBDriver.setPdo() - Error: configuration is null");
        }
    }

    public function getPdo()
    {
        if ($this->pdo === null) {
            $this->setPdo();
        }
        return $this->pdo;
    }

}
