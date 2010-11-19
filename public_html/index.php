<?php

// lets measre the time of script execution
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

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

if (APPLICATION_ENV == 'development') {
	/*
	 * Put errors on ON for debugging this file
	 */
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
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

/*
* You should avoid putting too many lines before the cache section.
* For example, for optimal performances, "require_once" or
* "Zend_Loader::loadClass" should be after the cache section.
*/
$frontendOptions = array(
   'lifetime' => 7200,
   'debug_header' => true, // for debugging
   'regexps' => array(
       // cache the whole page for maximum perfomance as we don't have static content right now here
       '^/$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

       '^/uk/$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

       '^/uk$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

       '^/ru/$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

       '^/ru$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),
    )
);

$backendOptions = array(
    'cache_dir' => realpath(APPLICATION_CACHE),
	'hashed_directory_level' => 1,
	'file_name_prefix'	=> 'zend_cache_page'
);

// getting a Zend_Cache_Frontend_Page object
require_once 'Zend/Cache.php';
$cache = Zend_Cache::factory('Page',
                             'File',
                             $frontendOptions,
                             $backendOptions);
$res = $cache->start();
// if the cache is hit, the result is sent to the browser and the
// script stop here

/*
 * Plugin loader cache
 */
$classFileIncCache = APPLICATION_PATH . '/../data/cache/pluginLoaderCache.php';
if (file_exists($classFileIncCache)) {
    include_once $classFileIncCache;
}
require_once 'Zend/Loader/PluginLoader.php';
Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);

// TODO: test the benefits and drawbacks of chaching
//       Zend_Application object to increase perfomance according
//       the technics described here: http://habrahabr.ru/blogs/zend_framework/64971/
$configFile = APPLICATION_PATH . DIRECTORY_SEPARATOR .  'configs' . DIRECTORY_SEPARATOR . 'application.ini';
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    $configFile
);

//Start
$application->bootstrap();
$application->run();

// calculate total execution time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime) * 1000;
echo "\n<!-- " . $totaltime . " ms -->";