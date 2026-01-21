<?php

class User
{
    private $conn;
    private $username;
    public $id;

    public function __call($name, $arguments)
    {
        $property = preg_replace("/[^0-9a-zA-Z]/", "", substr($name, 3));
        $property = strtolower(preg_replace('/\B([A-Z])/', '_$1', $property));
        if (substr($name, 0, 3) == "get") {
            return $this->_get_data($property);
        } elseif (substr($name, 0, 3) == "set") {
            return $this->_set_data($property, $arguments[0]);
        } else {
            throw new Exception("User::__call() -> $name, function unavailable.");
        }
    }

    public static function signup($user, $pass, $email, $phone)
    {           
        $options = [
            'cost' => 9,
        ];
        $pass = password_hash($pass, PASSWORD_BCRYPT, $options);
        $conn = Database::getConnection();
        $sql = "INSERT INTO `auth` (`username`, `password`, `email`, `phone`, `active`) VALUES ('"
            . $conn->real_escape_string($user) . "', '" 
            . $conn->real_escape_string($pass) . "', '" 
            . $conn->real_escape_string($email) . "', '" 
            . $conn->real_escape_string($phone) . "', '0')";

        try {
            if ($conn->query($sql) === true) {
                return true;
            } else {
                return $conn->error;
            }
        } catch (mysqli_sql_exception $e) {
            return $e->getMessage();
        }
    }

    public static function login($user, $pass)
    {
        $conn = Database::getConnection();
        $user_esc = $conn->real_escape_string($user);
        $query = "SELECT * FROM `auth` WHERE `username` = '" . $user_esc . "' LIMIT 1";
        $result = $conn->query($query);
        if ($result && $result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (isset($row['password']) && password_verify($pass, $row['password'])) {
                return $row;
            }
        }

        return false;
    }

    public function __construct($username)
    {
        // Handle case where an array is passed (e.g., from login result)
        if (is_array($username)) {
            if (isset($username['username'])) {
                $username = $username['username'];
            } elseif (isset($username['id'])) {
                $username = $username['id'];
            } else {
                throw new Exception("Invalid username parameter");
            }
        }
        $this->conn = Database::getConnection();
        $this->username = $username;
        $this->id = null;
        $u = $this->conn->real_escape_string($username);
        $sql = "SELECT `id` FROM `auth` WHERE `username`= '$u' OR `id` = '$u' LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            $this->id = $row['id']; //Updating this from database
        } else {
            throw new Exception("Username does't exist");
        }
    }


    private function _get_data($var)
    {
        if (!$this->conn) {
            $this->conn = Database::getConnection();
        }
        if (empty($this->id)) {
            return null;
        }
        $id = (int)$this->id;
        $var_esc = $this->conn->real_escape_string($var);
        
        // Ensure column exists first to avoid SQL errors
        $colRes = $this->conn->query("SHOW COLUMNS FROM `users` LIKE '" . $var_esc . "'");
        if (!($colRes && $colRes->num_rows == 1)) {
            return null;
        }
        
        $sql = "SELECT `$var` FROM `users` WHERE `id` = $id LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows == 1) {
            $row = $result->fetch_assoc();
            return array_key_exists($var, $row) ? $row[$var] : null;
        }
        return null;
    }

    // FIXED: Works regardless of PRIMARY KEY setup
    private function _set_data($var, $data)
    {
        if (!$this->conn) {
            $this->conn = Database::getConnection();
        }
        if (empty($this->id)) {
            return false;
        }
        $id = (int)$this->id;
        $safe = $this->conn->real_escape_string($data);
        $var_esc = $this->conn->real_escape_string($var);
        
        // Ensure column exists before attempting update
        $colRes = $this->conn->query("SHOW COLUMNS FROM `users` LIKE '" . $var_esc . "'");
        if (!($colRes && $colRes->num_rows == 1)) {
            error_log('User::_set_data missing column: ' . $var);
            return false;
        }
        
        // First, check if row exists
        $check = $this->conn->query("SELECT `id` FROM `users` WHERE `id` = $id LIMIT 1");
        
        if ($check && $check->num_rows > 0) {
            // Row exists - UPDATE it
            $sql = "UPDATE `users` SET `$var`='" . $safe . "' WHERE `id`=$id LIMIT 1";
        } else {
            // Row doesn't exist - INSERT it with defaults for NOT NULL columns
            // Get all columns and provide empty defaults
            $cols = $this->conn->query("SHOW COLUMNS FROM `users`");
            $colNames = [];
            $colVals = [];
            if ($cols) {
                while ($c = $cols->fetch_assoc()) {
                    $colName = $c['Field'];
                    $colNames[] = "`$colName`";
                    if ($colName === 'id') {
                        $colVals[] = $id;
                    } elseif ($colName === $var) {
                        $colVals[] = "'" . $safe . "'";
                    } else {
                        $colVals[] = "''";
                    }
                }
            }
            if (count($colNames) > 0) {
                $sql = "INSERT INTO `users` (" . implode(',', $colNames) . ") VALUES (" . implode(',', $colVals) . ")";
            } else {
                $sql = "INSERT INTO `users` (`id`, `$var`) VALUES ($id, '" . $safe . "')";
            }
        }
        
        try {
            if ($this->conn->query($sql)) {
                return true;
            } else {
                error_log('User::_set_data query failed: ' . $this->conn->error . ' | SQL: ' . $sql);
                return false;
            }
        } catch (mysqli_sql_exception $e) {
            error_log('User::_set_data query error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return false;
        }
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setDob($year, $month, $day)
    {
        if (checkdate($month, $day, $year)) { 
            return $this->_set_data('dob', sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day));
        } else {
            return false;
        }
    }
}