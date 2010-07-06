<?php

/*
 * Put errors on ON for debugging this file
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
 * Define the application environment
 */
define('APPLICATION_ENV', 'development');

/*
 * Defines the directory separator for windows or unix env
 */
define('DS', DIRECTORY_SEPARATOR);

/*
 * Define the absolute/relative paths to the library path, the app library path,
 * app path and the database configuration path
 */
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application') );
define('APPLICATION_LIBRARY_PATH', realpath(APPLICATION_PATH . '/../library'));

$paths = array(
	APPLICATION_LIBRARY_PATH
);

/*
 * Set the include paths to point to the new defined paths
 */
set_include_path(implode(PATH_SEPARATOR, $paths));

// Create application, bootstrap, and run
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . DS .  'configs' . DS . 'application.ini'
);

//Start
$application->bootstrap();
$application->run();
