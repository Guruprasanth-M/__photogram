<?php

class User
{
    public static function signup($user, $pass, $email, $phone)
    {
        $conn = Database::getConnection();
        $sql = "INSERT INTO `auth` (`username`, `password`, `email`, `phone`, `active`) VALUES ('"
            . $conn->real_escape_string($user) . "', '" . $conn->real_escape_string($pass) . "', '" . $conn->real_escape_string($email) . "', '" . $conn->real_escape_string($phone) . "', '1')";

        try {
            if ($conn->query($sql) === true) {
                return true; // success
            } else {
                return $conn->error;
            }
        } catch (mysqli_sql_exception $e) {
            return $e->getMessage();
        }
    }
}
