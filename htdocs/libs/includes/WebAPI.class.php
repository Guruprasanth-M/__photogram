<?php

class WebAPI
{
    public function __construct()
    {
        // global $__site_config;
        // if (php_sapi_name() == "cli") {
        //     $__site_config_path = "/home/guruprasanth17102003/photogramconfig.json";
        //     $__site_config = file_get_contents($__site_config_path);
        // } else {
        //     $__site_config_path = dirname(is_link($_SERVER['DOCUMENT_ROOT']) ? readlink($_SERVER['DOCUMENT_ROOT']) : $_SERVER['DOCUMENT_ROOT']).'/photogramconfig.json';
        //     $__site_config = file_get_contents($__site_config_path);
        // }
        global $__site_config;
        $__site_config_path = __DIR__.'/../../../project/photogramconfig.json';
        $__site_config = file_get_contents($__site_config_path);
        Database::getConnection();
    }

    public function initiateSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Session::start();
        }
    }
}
