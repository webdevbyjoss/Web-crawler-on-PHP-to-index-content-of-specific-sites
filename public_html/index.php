<?php

/*
 * Put errors on ON for debugging this file
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
 * Define the application environment
 */
define('APPLICATION_ENV', getenv('APPLICATION_ENV'));

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




/*
* You should avoid putting too many lines before the cache section.
* For example, for optimal performances, "require_once" or
* "Zend_Loader::loadClass" should be after the cache section.
*/
 
$frontendOptions = array(
   'lifetime' => 7200,
   'debug_header' => false, // for debugging
   'regexps' => array(
       // cache the whole IndexController
       '^/$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

        '^/searchdata/index/services$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

        '^/searchdata/index/regiones$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

       // TODO: investigate the problem with pages not being cached

        '^/searchdata/list/regiones/countryid/$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

        '^/searchdata/list/cities/regionid/$' => array('cache' => true,
       'make_id_with_get_variables' => true,
       'cache_with_get_variables' => true,
       'make_id_with_cookie_variables' => true,
       'cache_with_cookie_variables' => true),

    )
);

$backendOptions = array(
    'cache_dir' => realpath(APPLICATION_PATH . '/../temp')
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


// Create application, bootstrap, and run
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . DIRECTORY_SEPARATOR .  'configs' . DIRECTORY_SEPARATOR . 'application.ini'
);

//Start
$application->bootstrap();
$application->run();