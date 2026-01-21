<?php
include 'libs/load.php';

$user = "123455";

if (isset($_GET['logout'])) {
    if (Session::isset("session_token")) {
        $token = Session::get("session_token");
        try {
            $Session = new UserSession($token);
            if ($Session->removeSession()) {
                echo "<h3> Previous Session removed from db </h3>";
            } else {
                echo "<h3>Previous Session not removed from db (removeSession returned false)</h3>";
            }
        } catch (Exception $e) {
            // Session might not exist in DB - try direct delete by token
            $conn = Database::getConnection();
            $tokenEsc = $conn->real_escape_string($token);
            $sql = "DELETE FROM `session` WHERE `token` = '$tokenEsc'";
            if ($conn->query($sql) && $conn->affected_rows > 0) {
                echo "<h3>Session removed from db (direct delete)</h3>";
            } else {
                echo "<h3>Session not found in db or already deleted</h3>";
            }
        }
    }
    Session::destroy();
    die("Session destroyed, <a href='new.php'>Login Again</a>");
}

/*
1. Check if session_token in PHP session is available
2. If yes, construct UserSession and see if its successful.
3. Check if the session is valid one
4. If valid, print "Session validated"
5. Else, print "Invalid Session" and ask user to login.
*/

if (Session::isset("session_token")) {
    if (UserSession::authorize(Session::get("session_token"))) {
        echo "<h1>Session Login, WELCOME $user </h1>";
    } else {
        Session::destroy();
        die("<h1>Invalid Session, <a href='new.php'>Login Again</a></h1>");
    }
} else {
    $pass = "123";
    if (!$pass) die("<h1>Password  is Empty</h1>");
    if (UserSession::authenticate($user, $pass)) {
        echo "<h1>New LOGIN Success,  WELCOME $user</h1>";
    } else echo "<h1>New Login Failed! $user</h1>";
}

echo <<<EOL
<br><br><a href="new.php?logout">Logout</a>
EOL;
