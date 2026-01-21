<?php
include_once 'includes/Session.class.php';
include_once 'includes/User.class.php';
include_once 'includes/Database.class.php';
include_once 'includes/UserSession.class.php';

global $__site_config;
$__site_config_path = dirname(is_link($_SERVER['DOCUMENT_ROOT']) ? readlink($_SERVER['DOCUMENT_ROOT']) : 
            $_SERVER['DOCUMENT_ROOT']).'/photogramconfig.json';
if (!file_exists($__site_config_path)) {
    die("Configuration file not found: $__site_config_path");
}
$__site_config = file_get_contents($__site_config_path);

if (session_status() !== PHP_SESSION_ACTIVE) {
    Session::start();
}
function load_template($name)
{
    include $_SERVER['DOCUMENT_ROOT'] . get_config('base_path'). "_templates/$name.php";
}

function get_config($key, $default=null)
{
    global $__site_config;
    $array = json_decode($__site_config, true);
    if (isset($array[$key])) {
        return $array[$key];
    } else {
        return $default;
    }
}


