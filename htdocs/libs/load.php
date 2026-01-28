<?php

include_once 'includes/Session.class.php';
include_once 'includes/User.class.php';
include_once 'includes/Database.class.php';
include_once 'includes/UserSession.class.php';
include_once 'includes/WebAPI.class.php';

global $__site_config;

$wapi = new WebAPI();
$wapi->initiateSession();

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

function load_template($name)
{
    Session::loadTemplate($name);
}

function validate_credentials($username, $password)
{
    if ($username == "sibi@selfmade.ninja" and $password == "password") {
        return true;
    } else {
        return false;
    }
}
