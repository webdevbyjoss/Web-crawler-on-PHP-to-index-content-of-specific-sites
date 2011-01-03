<?php

class IndexController extends Zend_Controller_Action
{
 
    public function init()
    {
        /* Initialize action controller here */
    }
 
    public function indexAction()
    {
    	$options = array();
    	$options['remote_ip'] = $_SERVER['REMOTE_ADDR'];
    	
        $SearchForm = new Nashmaster_SearchForm($options);
        $this->view->searchForm = $SearchForm;
    }
}