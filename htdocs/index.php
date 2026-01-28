<?php

include 'libs/load.php';

if (isset($_GET['logout'])) {
    if (Session::isset("session_token")) {
        try {
            $Session = new UserSession(Session::get("session_token"));
            if ($Session->removeSession()) {
                echo "<h3> Previous session was successfully removed from database. </h3>";
            } else {
                echo "<h3> Previous session could not be removed from database. </h3>";
            }
        } catch (Exception $e) {
            // Already invalid or expired
            error_log("Logout error: " . $e->getMessage());
        }
    }
    Session::destroy();
    header("Location: " . get_config('base_path'));
    die();
} else {
    Session::renderPage();
}

