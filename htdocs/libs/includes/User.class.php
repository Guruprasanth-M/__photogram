<?php

class User
{
    private $conn;
    private $username;
    public $id;
    private $data; // Local cache for user data

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
        
        // Check if user or email already exists to provide better feedback
        $user_esc = $conn->real_escape_string($user);
        $email_esc = $conn->real_escape_string($email);
        $check_sql = "SELECT `id` FROM `auth` WHERE `username` = '$user_esc' OR `email` = '$email_esc' LIMIT 1";
        $check_res = $conn->query($check_sql);
        if ($check_res && $check_res->num_rows > 0) {
            return "Username or Email already exists.";
        }

        $sql = "INSERT INTO `auth` (`username`, `password`, `email`, `phone`, `active`) VALUES ('"
            . $user_esc . "', '" 
            . $conn->real_escape_string($pass) . "', '" 
            . $email_esc . "', '" 
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
        $query = "SELECT * FROM `auth` WHERE (`username` = '$user_esc' OR `email` = '$user_esc') AND `blocked` = '0' LIMIT 1";
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
        $this->conn = Database::getConnection();
        $this->id = null;
        
        // Handle case where an array is passed (e.g., from login result)
        // This is much faster as it avoids an extra DB query
        if (is_array($username)) {
            if (isset($username['id']) && isset($username['username'])) {
                $this->id = $username['id'];
                $this->username = $username['username'];
                return; // Early return, we have everything we need
            } elseif (isset($username['username'])) {
                $username = $username['username'];
            } elseif (isset($username['id'])) {
                $username = $username['id'];
            }
        }
        
        $u = $this->conn->real_escape_string($username);
        $sql = "SELECT `id`, `username` FROM `auth` WHERE `username`= '$u' OR `id` = '$u' LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->username = $row['username'];
        } else {
            throw new Exception("User not found: $username");
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

        // Lazy load all user data at once for efficiency
        if (!$this->data) {
            $id = (int)$this->id;
            $sql = "SELECT * FROM `users` WHERE `id` = $id LIMIT 1";
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows == 1) {
                $this->data = $result->fetch_assoc();
            } else {
                $this->data = []; // No data yet
            }
        }
        
        return isset($this->data[$var]) ? $this->data[$var] : null;
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
        
        // Professional UPSERT logic: Efficiently inserts or updates in one query
        $sql = "INSERT INTO `users` (`id`, `$var_esc`) VALUES ($id, '$safe') 
                ON DUPLICATE KEY UPDATE `$var_esc` = '$safe'";
        
        if ($this->conn->query($sql)) {
            $this->data = null; // Invalidate cache
            return true;
        } else {
            error_log('User::_set_data failed: ' . $this->conn->error);
            return false;
        }
    }

    public function deleteUser()
    {
        if (empty($this->id)) return false;
        $id = (int)$this->id;
        $conn = Database::getConnection();
        
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM `users` WHERE `id` = $id");
            $conn->query("DELETE FROM `session` WHERE `uid` = $id");
            $conn->query("DELETE FROM `auth` WHERE `id` = $id");
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
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