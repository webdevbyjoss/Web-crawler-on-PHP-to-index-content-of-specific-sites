<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype(Zend_View_Helper_Doctype::HTML5);
    }
    
    protected function _initView()
    {
    	$options = $this->getOptions();
    	$config = $options['resources']['view'];
    	
		if (isset($config)) {
			$view = new Nashmaster_View($config);
		} else {
			$view = new Nashmaster_View;
		}
		
    	if (isset($config['doctype'])) {
    		$view->doctype($config['doctype']);
    	}
    	
    	if (isset($config['language'])) {
    			$view->headMeta()->appendName('language', $config['language']);
    	}
    	
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
    		'ViewRenderer'
    	);
    	
    	$viewRenderer->setView($view);
    	return $view;
    }
    
}