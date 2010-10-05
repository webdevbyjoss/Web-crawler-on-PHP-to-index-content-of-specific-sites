<?php
/**
 * Triget crawling jobs by starting appropriate
 * jobs quelle with initial URLs from each crawling adapter
 *
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Jobs {
	
	/**
	 * The list of supported creawler adapters
	 *
	 * @var array
	 */
	protected $_adapters = null;
	
	/**
	 * Assign the list of supported crawler adapters
	 *
	 * @param array $adapters
	 */
	public function __construct($adapters) {
		$this->_adapters = $adapters;
	}
	
	/**
	 * Check the quelle
	 *
	 * TODO: Check if prewious quele was finished and only then start new quelle
	 */
	public function startQuelle()
	{
		$DbJobs = new Joss_Crawler_Db_Jobs ();
		
		if (! $DbJobs->isFinished ()) {
			
			/**
			 * TODO: write appropriate message to the output and to the log
			 */
			return false;
		}
		
		foreach ( $this->_adapters as $adapterClass ) {
			$Adapter = new $adapterClass();
			$DbJobs->createJob($Adapter->getInitialUrl());
		}
	
	}
	
	public function processNextJob()
	{
		// 1. get job
		$DbJobs = new Joss_Crawler_Db_Jobs();
		$job = $DbJobs->getJobForProcessing();

		// 2. recognize the adapter
		foreach ($this->_adapters as $adapterClass) {

 			// Call static method when class name defined dinamically via variable (for versions before PHP 5.3.0)
			$res = call_user_func(  array(&$adapterClass, 'matchDataLink') , $job['url'] );
			// Call static method when class name defined dinamically via variable (for versions after PHP 5.3.0)
			// $res = $adapterClass::matchDataLink($job['url']));
			if (true == $res) {
				break;
			}
		}
		
		// 3. extract content
		$Adapter = new $adapterClass ();
		
		// $links = $Adapter->getDataLinks();

		foreach ($links as $key => $link) {
			// echo $key . "| " . $link['url'] . "| " . $link['content'] . "\n";
			echo $link['url'] . "\n";
		}
		
		// 4. grap the interesting URLs
		
		
		// 5. grap the data from the page
		
		
	}

}