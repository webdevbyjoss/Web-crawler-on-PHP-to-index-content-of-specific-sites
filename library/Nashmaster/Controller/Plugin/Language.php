<?php

class Nashmaster_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract
{
	public function routeStartup(Zend_Controller_Request_Abstract $request)
	{
		$translate = Zend_Registry::get('Zend_Translate');
		$locale = Zend_Registry::get('Zend_Locale');
		
		// we should take locale from the URL, in case it is presented there and then
		// WARNING: this is very tricky and possibly there is some kind of better solution somewhere
		if (!($request instanceof  Zend_Controller_Request_Http)) {
			// QUICKFIX: we receiving error message while running from CLI
			// PHP Fatal error:  Call to undefined method Zend_Controller_Request_Simple::getRequestUri()
			return;
		}
		
		$uri = $request->getRequestUri();
		$match = null;
		if (false !== mb_ereg('^/([a-z]{2})', $uri, $match)) {
			$lang = $match[1];
			if ($translate->isAvailable($lang)) {
				$locale->setLocale($lang);
			 	$translate->setLocale($locale);
			}
		} else {
		 	// Otherwise get default language
		 	$locale = $translate->getLocale();
		 	if ($locale instanceof Zend_Locale) {
		 		$lang = $locale->getLanguage();
		 	} else {
		 		$lang = $locale;
		 	}
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
		 
		// Set language to global param so that our language route can
		// fetch it nicely.
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$router->setGlobalParam('lang', $lang);
		
		// Instantiate default module route
		$routeDefault = new Zend_Controller_Router_Route_Module (
	        array(),
	        $front->getDispatcher(),
	        $front->getRequest()
	    );

	    // Create route with language id (lang)
		$routeLang = new Zend_Controller_Router_Route(
			':lang',
			null, // array('lang' => $locale->getLanguage()),
			array('lang' => '[a-z]{2}')
		);
	    // Chain it with language route
	    $routeLangDefault = $routeLang->chain($routeDefault);

	    // Add both language route chained with default route and
	    // plain language route
	    $router->addRoute('default_nolang', $routeDefault);
	    $router->addRoute('default', $routeLangDefault);
	    $router->addRoute('lang', $routeLang);
	    
	    
	    	    
	   	// SEO purposes
	    // application URL to be indexed by search engine

	    // nash-master.com/ru/город/днепропетровск
	    // nash-master.com/ua/місто/заліщики
	    $cityRoute = new Zend_Controller_Router_Route(
	    	'@url-city/:city',
			array(
				'module'       => 'default',
				'controller' => 'catalog',
				'action'     => 'index'
			)
	    );
		$router->addRoute('city_lang', $routeLang->chain($cityRoute));
	    
	    
	    
	    // nash-master.com/ru/город/днепропетровск/евроремонт/
	    // nash-master.com/ua/місто/заліщики/пластикові-вікна/
	    $cityServiceRoute = new Zend_Controller_Router_Route(
	    	'@url-city/:city/:service/*',
			array(
				'module'       => 'default',
				'controller' => 'catalog',
				'action'     => 'cityservice'
			)
	    );
		$router->addRoute('city_service_lang', $routeLang->chain($cityServiceRoute));

		
		
		// nash-master.com/ru/вид-услуг/евроремонт/
	    // nash-master.com/ua/вид-послуг/пластикові-вікна/
		$serviceRoute = new Zend_Controller_Router_Route(
             '@url-type-of-service/:service',
             array(
             	 'module' => 'default',
                 'controller' => 'catalog',
                 'action'     => 'service'
          	 )
      	);
      	$router->addRoute('services_lang', $routeLang->chain($serviceRoute));
      	
      	
      	
      	// add footer routes
      	// http://nash-master.com/ua/terms-and-conditions
      	// http://nash-master.com/ua/help
      	$serviceRoute = new Zend_Controller_Router_Route(
             'terms-and-conditions',
             array(
				'controller' => 'terms-and-conditions',
				'action' => 'index',
				'module' => 'default',
          	 )
      	);
      	$router->addRoute('terms', $routeLang->chain($serviceRoute));

      	$serviceRoute = new Zend_Controller_Router_Route(
             'help',
             array(
				'controller' => 'help',
				'action' => 'index',
				'module' => 'default',
          	 )
      	);
      	$router->addRoute('help', $routeLang->chain($serviceRoute));
      	
      	/* TEST
		$info = $cityRoute->assemble(array('city' => 'Заліщики', 'service' => 'Євроремонт'));
		var_dump($info);
		die();
		*/
      	
	}
}