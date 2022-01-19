<?php

require_once "vendor/autoload.php";

use \Lib\DB;
use \Models\User;

DB::config([
    "DB_RUN_CREATE" => true,
    "DB_CONNECTION" => "mysql",
    "DB_HOST" => "localhost",
    "DB_PORT" => 3306,
    "DB_ENGINE" => "MyISAM",
    "DB_CHARSET" => "utf8",
    "DB_CHARSET_COLLATE" => "utf8_unicode_ci",
    "DB_USERNAME" => "root",
    "DB_PASSWORD" => "",
    "DB_DATABASE" => "mydatabase"
]);

//dumpl("PDO", DB::pdo());

// FIND ID
$user = new User;
$result = $user->findId(1);
dumpl("FIND", $result);

if ($result === null) {
    // INSERT
    $user = new User;
    $user->name = "Matheus";
    $user->email = "matheus@mail.com";
    $user->age = 23;
    dumpl($user->save());
    dumpl("INSERT", $user->save());
} else {
    // UPDATE
    $user = new User;
    $user->id = 1;
    $user->name = "Matheus Johann " . md5(rand(0, 9999999));
    $user->age = $result->age + 1;
    dumpl("UPDATE", $user->save());

    if ($result->age == 25) {
        // DELETE
        $user = new User;
        dumpl("DELETE", $user->delete(1));
    }
}

// ALL
$user = new User;
$result = $user->all();
dumpl("ALL", $result);

if (count($result) > 2) {
    // TRUNCATE
    $user = new User;    
    dumpl("TRUNCATE", $user->truncate(true));
}
