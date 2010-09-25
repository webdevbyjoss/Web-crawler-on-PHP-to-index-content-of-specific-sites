#!/usr/bin/php
<?php

/**
 * Init framework
 */
$paths = explode(PATH_SEPARATOR, get_include_path());
$paths[] = realpath(dirname(__FILE__).'/../../library');
set_include_path(implode(PATH_SEPARATOR, $paths));
unset($paths);

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Joss_', 'Nashmaster_');


// define available adapters
$adapters = array(
	'Joss_Crawler_Adapter_Emarketua'
);

$Client = new Joss_Crawler_Jobs($adapters);
$res = $Client->startQuelle();

var_dump($res);

/*
$Client = new Joss_Crawler_Adapter_Emarketua();
$links = $Client->getDataLinks();


foreach ($links as $key => $link) {
	// echo $key . "| " . $link['url'] . "| " . $link['content'] . "\n";
	echo $link['url'] . "\n";
}
*/