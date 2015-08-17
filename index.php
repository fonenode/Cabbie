<?php

require 'config.php';

spl_autoload_register(function($class){
    $parts = explode('\\', $class);
    require 'lib/'.end($parts) . '.php';
});

// Simple routing logic
// REQUEST_URI?
$page = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] :
    (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');

$route = new Route;
$route->all($page);
