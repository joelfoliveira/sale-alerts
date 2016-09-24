<?php

namespace SaleAlerts;

class Database
{
    public static $db;

    public static function getInstance()
    {
        if(self::$db == null){
            self::$db = new \Slim\PDO\Database(Config::$dbConnectionString, Config::$dbUser, Config::$dbPassword);
        }

        return self::$db;
    }
}