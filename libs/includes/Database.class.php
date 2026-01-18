<?php

class Database
{
    public static $conn = null;
    public static function getConnection()
    {
        if (Database::$conn == null) {
            $servername = get_config('db_server') ?: '127.0.0.1';
            $username = get_config('db_username') ?: 'root';
            $password = get_config('db_password') ?: '';
            $dbname = get_config('db_name') ?: '';
            // Create connection (defensive)
            try {
                $connection = new mysqli($servername, $username, $password, $dbname);
            } catch (mysqli_sql_exception $e) {
                throw new Exception('Database connection error: ' . $e->getMessage());
            }
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