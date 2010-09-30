#!/usr/bin/php
<?php

// should be removed starting from PHP version >= 5.3.0
defined('__DIR__') || define('__DIR__', dirname(__FILE__));

// initialize the application path, library and autoloading
defined('APPLICATION_PATH') ||
	define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));

$paths = explode(PATH_SEPARATOR, get_include_path());
$paths[] = realpath(__DIR__.'/../library');
set_include_path(implode(PATH_SEPARATOR, $paths));
unset($paths);

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace(array('Custom_', 'Joss_', 'Nashmaster_'));

// define application options and read params from CLI
$getopt = new Zend_Console_Getopt(array(
    'action|a=s' => 'action to perform in format of "module/controller/action"',
    'env|e-s'    => 'defines application environment (defaults to "production")',
    'help|h'     => 'displays usage information',
));

try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();
    return false;
}

// show help message in case it was requested or params were incorrect (module, controller and action)
if ($getopt->getOption('h') || !$getopt->getOption('a')) {
    echo $getopt->getUsageMessage();
    return true;
}

// initialize values based on presence or absence of CLI options
$env = $getopt->getOption('e');
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (null === $env) ? 'production' : $env);

// initialize Zend_Application
$application = new Zend_Application (
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

// bootstrap and retrive the frontController resource
$front = $application->getBootstrap()
					 ->bootstrap('frontController')
					 ->getResource('frontController');

// magic starts from this line!
//
// we will use Zend_Controller_Request_Simple and some kind of custom code
// to emulate missed in Zend Framework ecosystem
// "Zend_Controller_Request_Cli" that can be found as proposal here:
// http://framework.zend.com/wiki/display/ZFPROP/Zend_Controller_Request_Cli
//
// I like the idea to define request params separated by slash "/"
// for ex. "module/controller/action/param1/param2/param3/.."
//
// NOTE: according to the current implementation we can't omit
// 		 any of module/controller/action, only params can be omited
//
// TODO: allow to omit "module", "action" params
//      and set them to "default" and "index" accordantly
//
// so lets split the params we've received from the CLI
// and pass them to the reqquest object
//
// FIXME: I think this functionality should be moved to the routing
//
$params = array_reverse(explode('/', $getopt->getOption('a')));
$module = array_pop($params);
$controller = array_pop($params);
$action = array_pop($params);
$request = new Zend_Controller_Request_Simple ($action, $controller, $module);

// set front controller options to make everything operational from CLI
$front->setRequest($request)
	  ->setResponse(new Zend_Controller_Response_Cli())
	  ->setRouter(new Custom_Controller_Router_Cli())
	  ->throwExceptions(true);

// lets bootstrap our application and enjoy!
$application->bootstrap()
			->run();