<?php

/**
 * Set timezone
 */
date_default_timezone_set('Europe/Helsinki');

/*
 * Define the application environment
 */
$env = getenv('APPLICATION_ENV');
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (empty($env) ? 'production' : $env));

if ('development' == APPLICATION_ENV) {
	/*
	 * Put errors ON for debugging this file
	 */
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	define('DEBUG_ENABLE', true);
}

/*
 * Define the absolute/relative paths to the library path, the app library path,
 * app path and the database configuration path
 */
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application') );
define('APPLICATION_CACHE', realpath(APPLICATION_PATH . '/../tmp'));

define('APPLICATION_LIBRARY_PATH', realpath(APPLICATION_PATH . '/../library'));

/*
 * Set the include paths to point to the new defined paths
 */
// $paths = explode(PATH_SEPARATOR, get_include_path());
$paths[] = APPLICATION_LIBRARY_PATH;
set_include_path(implode(PATH_SEPARATOR, $paths));


// lets measure the time of script execution
// and run the application initialization
require_once 'Nashmaster/Starter.php';
$Starter = new Nashmaster_Starter(APPLICATION_PATH, APPLICATION_ENV);

// You should avoid putting too many lines before the cache section.
DEBUG_ENABLE ? false : $Starter->pageCache(APPLICATION_CACHE, DEBUG_ENABLE);
// if the cache is hit, the result is sent to the browser and the
// script stop here

$Starter->run();

// calculate total execution time
// $totaltime = $Starter->getExecutionTime(true);
// echo "\n" . $totaltime . " ms";