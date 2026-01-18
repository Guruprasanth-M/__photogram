<?php

class Database
{
    public static $conn = null;
    public static function getConnection()
    {
        if (Database::$conn == null) {
            $servername = "test";
            $username = "test";
            $password = "test.test.test:test";
            $dbname = "test";
        
            // Create connection
            $connection = new mysqli($servername, $username, $password, $dbname);
            // Check connection
                if ($connection->connect_error) {
                    throw new Exception("Connection failed: " . $connection->connect_error);
                } else {
                    Database::$conn = $connection; // store connection
                    return Database::$conn;
                }
        } else {
                return Database::$conn;
        }
    }
}
