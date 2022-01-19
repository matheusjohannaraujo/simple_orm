<?php

namespace Models;

use Lib\Model;
      
class User extends Model
{

    public $name = "VARCHAR(100) NOT NULL";
    public $email = "TEXT NOT NULL";
    public $age = "INT(3) NOT NULL";

}
