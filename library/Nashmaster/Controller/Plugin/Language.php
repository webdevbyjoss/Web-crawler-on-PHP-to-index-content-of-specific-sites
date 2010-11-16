<?php

class Nashmaster_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract
{
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		 $lang = $request->getParam('lang', null);
		 
		 $translate = Zend_Registry::get('Zend_Translate');
		 $locale = Zend_Registry::get('Zend_Locale');
		 
		 // Change language if available
		 if ($translate->isAvailable($lang)) {
		 	$locale->setLocale($lang);
		 	$translate->setLocale($locale);
		 } else {
		 	// Otherwise get default language
		 	$locale = $translate->getLocale();
		 	if ($locale instanceof Zend_Locale) {
		 		$lang = $locale->getLanguage();
		 	} else {
		 		$lang = $locale;
		 	}
		 	
		 	// TODO: looks ugly, but I've found this recomentation on ZF mailing list
		 	// by Matthew Weier O'Phinney
		 	// see: http://zend-framework-community.634137.n4.nabble.com/Redirect-from-a-custom-route-best-option-td670370.html
		 	/*
		 	$response = $this->getResponse();
			$response->setRedirect("/$lang/");
			$response->sendResponse();
			exit;
			*/
		 }
		 
		 // Set language to global param so that our language route can
		 // fetch it nicely.
		 $front = Zend_Controller_Front::getInstance();
		 $router = $front->getRouter();
		 $router->setGlobalParam('lang', $lang);
	}
}