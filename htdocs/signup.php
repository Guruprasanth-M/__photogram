<?php

include 'libs/load.php';

if(Session::isAuthenticated()){
    header("Location: " . get_config('base_path'));
    die();
}

Session::renderPage();
