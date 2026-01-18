<?php
include_once 'includes/Session.class.php';
include_once 'includes/Database.class.php';
include_once 'includes/User.class.php';

global $__site_config;
$config_path = dirname(__DIR__) . '/../../photogramconfig.json';
if (file_exists($config_path)) {
    $__site_config = file_get_contents($config_path);
} else {
    die("Configuration file not found: $config_path");
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    Session::start();
}
function load_template($name)
{
    include '_templates/' . $name . '.php';
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


