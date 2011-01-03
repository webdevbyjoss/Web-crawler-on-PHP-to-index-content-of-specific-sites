<?php
/**
 * Starter
 *
 * - bootstrapa and run application
 * - measure perfomance
 * - controlls full-page caching
 *
 * @name		Nashmaster_Starter
 * @version		1.0
 * @package		nashmaster
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Nashmaster_Starter
{
	/**
	 * Application path that will be used to initialize Zend_Application
	 *
	 * @var string
	 */
	protected $_appPath = '';
	
	/**
	 * Application environment string
	 *
	 * @var string
	 */
	protected $_appEnv = '';

	/**
	 * This will track the script execution start
	 *
	 * @var string
	 */
	protected $_startTime = null;
	
	/**
	 * The instance of Zend_Auth
	 * that will be used to check whether
	 * current user is authenticated
	 *
	 * @var Zend_Auth
	 */
	protected $_auth = null;
	
	/**
	 * Init
	 *
	 * @param string $appPath
	 * @param string $appEnv
	 */
	public function __construct($appPath, $appEnv)
	{
		// lets measure start microtime
		$this->_startTime = $this->_getTime();
		
		$this->_appPath = $appPath;
		$this->_appEnv = $appEnv;
		
		require_once 'Zend/Auth.php';
		$this->_auth = Zend_Auth::getInstance();
	}
	
	/**
	 * Bootstraps and runs the application
	 *
	 * @return null
	 */
	public function run()
	{
		/*
		 * Plugin loader cache
		 */
		$classFileIncCache = $this->_appPath . '/../data/cache/pluginLoaderCache.php';
		if (file_exists($classFileIncCache)) {
		    include_once $classFileIncCache;
		}
		require_once 'Zend/Loader/PluginLoader.php';
		Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
		
		// TODO: test the benefits and drawbacks of chaching
		//       Zend_Application object to increase perfomance according
		//       the technics described here: http://habrahabr.ru/blogs/zend_framework/64971/
		$configFile = $this->_appPath . DIRECTORY_SEPARATOR .  'configs' . DIRECTORY_SEPARATOR . 'application.ini';
		require_once 'Zend/Application.php';
		$application = new Zend_Application(
		    $this->_appEnv,
		    $configFile
		);
		
		// Bootstrap and run application
		$application->bootstrap();
		$application->run();
	}

	/**
	 * Processes the full page cahe
	 *
	 * @param string $appCacheDir
	 * @param bool $debug
	 * @return null
	 */
	public function pageCache($appCacheDir, $debug = false)
	{
		// late return right now in case user is loged into the system
		// TODO: we should create a mechanism to have a list of cached pages even for
		// 		 loged in users, possibly we should use the abilities of
		// 		 "Page" front end options available
		if ($this->_auth->hasIdentity()) {
			return null;
		}
		
		$frontendOptions = array(
		   'lifetime' => 432000,
		   'debug_header' => $debug, // for debugging
			// lets set global default options
		   'default_options' => array(
				'cache' => false // by default do not cache any pages
			),
		   'regexps' => array(
		       // cache each matched page for maximum perfomance
		       // as we have only static content right now there
		       '^/$'		=> array('cache' => true),
		       '^/uk/$'		=> array('cache' => true),
		       '^/uk$'		=> array('cache' => true),
		       '^/ru/$'		=> array('cache' => true),
		       '^/ru$'		=> array('cache' => true),
		    )
		);
		
		$backendOptions = array(
		    'cache_dir' => realpath($appCacheDir),
			'hashed_directory_level' => 1,
			'file_name_prefix'	=> 'zend_cache_page'
		);
		
		// getting a Zend_Cache_Frontend_Page object
		require_once 'Zend/Cache.php';
		$cache = Zend_Cache::factory('Page',
		                             'File',
		                             $frontendOptions,
		                             $backendOptions);
		
		// lets cahce by  request URI
		$cacheId = 'full_page_cache_' . preg_replace("/[^a-zA-Z0-9_]/", "", $_SERVER['REQUEST_URI']);
		$res = $cache->start($cacheId);
		// if the cache is hit, the result is sent to the browser and the
		// script stop here
	}

	/**
	 * Returns total execution time from the very begining
	 * so please try to initialize this class instance
	 * as easly in your application as you can
	 *
	 * @param bool $inSeconds
	 * @return string execution time
	 */
	public function getExecutionTime($inMSeconds = false)
	{
		$endtime = $this->_getTime();
		
		if (true === $inMSeconds) {
			return intval(($endtime - $this->_startTime) * 1000); // 1 milisec = 1000 microsec
		}

		return $endtime - $this->_startTime;
	}
	
	/**
	 * This will track the page execution time
	 * TODO: report this value to build a statistics
	 *
	 * @return null
	 */
	public function trackTime()
	{
		//disable total time calculation if response in JSON format
		//such  behavior for prevent parseError in browser 
		foreach (Zend_Controller_Front::getInstance()->getResponse()->getHeaders() as $header) {
			if ($header["name"] == "Content-Type" && $header["value"] == "application/json")
				return;
		}
		$totaltime = $this->getExecutionTime(true);
		// echo "\n<!-- " . $totaltime . " ms -->";
	}
	
	/**
	 * Returns microtime
	 *
	 * @access protected
	 * @return string microtime
	 */
	protected function _getTime()
	{
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		return  $mtime[1] + $mtime[0];
	}
}