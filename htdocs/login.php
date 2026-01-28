<?php

include 'libs/load.php';
// echo "Hello world";
if(Session::isAuthenticated()){
    header("Location: " . get_config('base_path'));
    die();
}

Session::renderPage();
