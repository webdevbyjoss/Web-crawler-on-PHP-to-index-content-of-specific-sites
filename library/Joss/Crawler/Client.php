<?php
/**
 * Crawler clien that will go thrue specified site and grap the content using the appropriate adapter
 * 
 * @name		Joss_Crawler_Client
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Client
{
	/**
	 * Web crawler adapter that will parse content for appropriate site
	 * 
	 * @var Joss_Crawler_Adapter_Interface
	 */
	protected $_adapter = null;

	/**
	 * Stores the list of links with the processing statuses 
	 * 
	 * @var Joss_Crawler_Links
	 */
	protected $_urls = null;

	/**
	 * Will set the adapter to use for the current site
	 * 
	 * @param  Joss_Crawler_Adapter_Interface $adapters the array that will map the domain with the adapter
	 */
	public function __construct(Joss_Crawler_Adapter_Interface $adapter)
	{
		$this->_adapter = $adapter;
	}
	
	/**
	 * This will allow to set the adapter in runtime
	 * 
	 * @param Joss_Crawler_Adapter_Interface $adapter
	 */
	public function setAdapter(Joss_Crawler_Adapter_Interface $adapter)
	{
		$this->_adapter = $adapter;
	}

	/**
	 * We will start crawling this site
	 */
	public function process()
	{
		echo 'Done!';
	}
	
}