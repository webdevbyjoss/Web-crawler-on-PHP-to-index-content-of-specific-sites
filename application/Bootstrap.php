<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initRoutes()
	{
		$this->bootstrap('frontController');
		$this->bootstrap('Translation');
		
		$locale = $this->getResource('Locale');
		
		// Create route with language id (lang)
		$routeLang = new Zend_Controller_Router_Route(
			':lang',
			null, // array('lang' => $locale->getLanguage()),
			array('lang' => '[a-z]{2}')
		);
		
		// Now get router from front controller
		$front  = $this->getResource('frontController');
		$router = $front->getRouter();
		
		// Instantiate default module route
		$routeDefault = new Zend_Controller_Router_Route_Module (
	        array(),
	        $front->getDispatcher(),
	        $front->getRequest()
	    );
	    
	    // Chain it with language route
	    $routeLangDefault = $routeLang->chain($routeDefault);
	    
	    // Add both language route chained with default route and
	    // plain language route
	    $router->addRoute('default_nolang', $routeDefault);
	    $router->addRoute('default', $routeLangDefault);
	    $router->addRoute('lang', $routeLang);
	    
	    // Register plugin to handle language changes
	    $front->registerPlugin(new Nashmaster_Controller_Plugin_Language());
	}
	
    protected function _initView()
    {
    	$this->bootstrap('Translation');
    	$this->bootstrap('frontController');
    	
    	$translation = $this->getResource('Translation');
    	
    	$options = $this->getOptions();
    	$config = $options['resources']['view'];
    	
		if (isset($config)) {
			$view = new Nashmaster_View($config);
		} else {
			$view = new Nashmaster_View;
		}
    	
		$view->setTranslation($translation);
		
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
    		'ViewRenderer'
    	);
    	
    	$viewRenderer->setView($view);
    	return $view;
    }
    
    protected function _initTranslation()
    {
    	$this->bootstrap('Locale');
    	$locale = $this->getResource('Locale');
    	
    	// do not cache translation in development env
		if (APPLICATION_ENV !== 'development') {
			
			$frontendOptions = array(
			   'lifetime' => 7200, // cache lifetime of 2 hours
			   'automatic_serialization' => true
			);
			
			$backendOptions = array(
			    'cache_dir' => APPLICATION_CACHE
			);
	    
			$cache = Zend_Cache::factory('Core',
										 'File',
	   									 $frontendOptions,
										 $backendOptions);

			Zend_Translate::setCache($cache);
		}
		
		$translate = new Zend_Translate(
			array(
              'adapter' => Zend_Translate::AN_XLIFF,
			  'content' => APPLICATION_PATH . '/../data/locales/',
              'locale'  => 'uk',
			  'scan'	=> Zend_Translate::LOCALE_FILENAME,
			  'useId'   => true,
			  'route'   => array('uk' => 'ru')
	        )
	    );

	    // lets change the locale to default one
	    // if there is no such localization for currently
	    // recognized locale
	    $avail = $translate->getList();
	    if (!in_array($locale->toString(), $avail)) {
	    	$locale->setLocale('uk'); // TODO: we shoul move this to configuration
	    	$translate->setLocale($locale);
	    }
	    
	    Zend_Registry::set('Zend_Translate', $translate);
	    
	    return $translate;
	}
	
	protected function _initLocale()
	{
		$locale = new Zend_Locale(); // this will automatically recognize locale from the browser
		Zend_Registry::set('Zend_Locale', $locale); // set locale application wide
		
		return $locale;
	}
}