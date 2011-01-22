<?php

set_include_path(realpath(dirname(__FILE__) . '/../library/'));

// we will use a special class to parse the page data
require_once 'Nashmaster/SearchForm.php';
require_once 'Zend/Session/Namespace.php';

$options = array(
	'keywords' => 'укладка паркету 333, тернопіль іва фі=- -  фівафіва %:?№%;""'
);

$SearchForm = new Nashmaster_SearchForm($options);

$keywords = $SearchForm->prepareKeywords($options['keywords']);

var_dump($keywords);
