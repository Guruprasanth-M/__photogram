<?php

class UserSession
{
    /**
     * This function will return a session ID if username and password is correct.
     *
     * @return SessionID
     */
    public static function authenticate($user, $pass)
    {
        $loginResult = User::login($user, $pass);
        if ($loginResult) {
            // login() returns array or username string; extract username
            if (is_array($loginResult) && isset($loginResult['username'])) {
                $username = $loginResult['username'];
            } else {
                $username = $loginResult;
            }
            $userObj = new User($username);
            $conn = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $token = md5(rand(0, 9999999) . $ip . $agent . time());
            $sql = "INSERT INTO `session` (`uid`, `token`, `login_time`, `ip`, `user_agent`, `active`)
            VALUES ('" . $userObj->id . "', '" . $conn->real_escape_string($token) . "', now(), '" . $conn->real_escape_string($ip) . "', '" . $conn->real_escape_string($agent) . "', '1')";
            if ($conn->query($sql)) {
                Session::set('session_token', $token);
                return $token;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
    * Authorize function have has 4 level of checks 
        1.Check that the IP and User agent field is filled.
        2.Check if the session is correct and active.
        3.Check that the current IP is the same as the previous IP
        4.Check that the current user agent is the same as the previous user agent

        @return true else false;
    */
    public static function authorize($token)
    {
        try {
            $session = new UserSession($token);
            if (isset($_SERVER['REMOTE_ADDR']) and isset($_SERVER["HTTP_USER_AGENT"])) {
                if ($session->isValid() and $session->isActive()) {
                    if ($_SERVER['REMOTE_ADDR'] == $session->getIP()) {
                        if ($_SERVER['HTTP_USER_AGENT'] == $session->getUserAgent()) {
                            return true;
                        } else throw new Exception("User agent does't match");
                    } else throw new Exception("IP does't match");
                } else {
                    $session->removeSession();
                    throw new Exception("Invalid session");
                }
            } else throw new Exception("IP and User_agent is null");
        } catch (Exception $e) {
            return false;
        }
    }

    public function __construct($token)
    {
        $this->conn = Database::getConnection();
        $this->token = $token;
        $this->data = null;
        $tokenEsc = $this->conn->real_escape_string($token);
        $sql = "SELECT * FROM `session` WHERE `token`='$tokenEsc' LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            $this->data = $row;
            $this->uid = $row['uid']; //Updating this from database
        } else {
            throw new Exception("Session is invalid.");
        }
    }

    public function getUser()
    {
        return new User($this->uid);
    }

    /**
     * Check if the validity of the session is within one hour, else it inactive.
     *
     * @return boolean
     */
    public function isValid()
    {
        if (isset($this->data['login_time'])) {
            $login_time = DateTime::createFromFormat('Y-m-d H:i:s', $this->data['login_time']);
            if (3600 > time() - $login_time->getTimestamp()) {
                return true;
            } else {
                return false;
            }
        } else throw new Exception("login tiem is null");
    }

    public function getIP()
    {
        return isset($this->data["ip"]) ? $this->data["ip"] : false;
    }

    public function getUserAgent()
    {
        return isset($this->data["user_agent"]) ? $this->data["user_agent"] : false;
    }

    public function deactivate()
    {
        if (!$this->conn)
            $this->conn = Database::getConnection();
        $sql = "UPDATE `session` SET `active` = 0 WHERE `uid`=$this->uid";

        return $this->conn->query($sql) ? true : false;
    }

    public function isActive()
    {
        if (isset($this->data['active'])) {
            return $this->data['active'] ? true : false;
        }
    }

    //This function remove current session from DB
    public function removeSession()
    {
        if (!$this->conn) $this->conn = Database::getConnection();
        
        $deleted = false;
        
        // Try to delete by id first
        if (isset($this->data['id'])) {
            $id = (int)$this->data['id'];
            $sql = "DELETE FROM `session` WHERE `id` = $id";
            $result = $this->conn->query($sql);
            if ($result && $this->conn->affected_rows > 0) {
                $deleted = true;
                error_log("UserSession::removeSession - Deleted by id: $id");
            } else {
                error_log("UserSession::removeSession - Delete by id failed. id=$id, affected_rows=" . $this->conn->affected_rows . ", error=" . $this->conn->error);
            }
        } else {
            error_log("UserSession::removeSession - No id in data array");
        }
        
        // Fallback: delete by token if id didn't work
        if (!$deleted && !empty($this->token)) {
            $tokenEsc = $this->conn->real_escape_string($this->token);
            $sql = "DELETE FROM `session` WHERE `token` = '$tokenEsc'";
            $result = $this->conn->query($sql);
            if ($result && $this->conn->affected_rows > 0) {
                $deleted = true;
                error_log("UserSession::removeSession - Deleted by token");
            } else {
                error_log("UserSession::removeSession - Delete by token failed. affected_rows=" . $this->conn->affected_rows . ", error=" . $this->conn->error);
            }
        }
        
        // Clear the PHP session token as well
        if ($deleted) {
            Session::delete('session_token');
        }
        
        return $deleted;
    }
}
