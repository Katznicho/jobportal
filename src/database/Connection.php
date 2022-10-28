<?php

namespace App\Database;

use mysqli;

class Connection
{
    protected static  $host = 'localhost';
    protected static  $user = 'root';
    //protected static  $password = '!Log19tan88';
    protected static  $password = '';
    protected static  $database = 'ssenhogv_manager';
    private static $con;

    public static function connect()
    {
        if (!static::$con) {
            static::$con = new mysqli(static::$host, static::$user, static::$password, static::$database);
        }
        return static::$con;
    }
    public static function switchDatabase($database)
    {
        static::$database = $database;
        static::connect();
    }
    public static function  close(): void
    {
        if (static::$con) {
            static::$con->close();
        }
    }
}
