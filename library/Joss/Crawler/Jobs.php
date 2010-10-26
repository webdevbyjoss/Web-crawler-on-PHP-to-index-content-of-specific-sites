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
class Joss_Crawler_Jobs
{
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
	public function __construct($adapters)
	{
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
		// 1. get next job from database
		$DbJobs = new Joss_Crawler_Db_Jobs();
		$job = $DbJobs->getJobForProcessing();

		if (null == $job) {
			/**
			 * TODO: write appropriate message to the output and to the log
			 */
			return false;
		}
		
		// 2. recognize the adapter
		// TODO: adapter recognision can be done in more elegance maner
		foreach ($this->_adapters as $adapterClass) {
			$Adapter = new $adapterClass();
			if ($Adapter->matchDataLink($job['url'])) {
				break;
			}
		}

		// 3. extract content
		$job['raw_body'] = base64_decode($job['raw_body']);
		$Adapter->loadPage($job['url'], $job['raw_body']);
		
		// 4. grap the URLs with interesting  data and create new jobs for that pages
		$links = $Adapter->getDataLinks();
		foreach ($links as $key => $link) {
			$DbJobs->createJob($link['url']);
		}
		
		$Items = new Joss_Crawler_Db_Items();
		// 5. grap the data from the page
		$data = $Adapter->getData();
		if (null !== $data) {
			foreach ($data as $advert) {
				$Items->add($advert);
			}
		}
		
		// FIXME: this is temporrary code that helps to run crawling without actual processing of content
		$DbJobs->finishJob($job['crawl_jobs_id']);
		return true;
	}

}