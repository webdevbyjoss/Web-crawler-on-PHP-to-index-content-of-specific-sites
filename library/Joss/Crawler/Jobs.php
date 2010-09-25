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
		$DbJobs = new Joss_Crawler_Db_Jobs();
		
		if (!$DbJobs->isFinished()) {
			
			/**
			 * TODO: write appropriate message to the output and to the log
			 */
			return false;
		}
		
		return true;
	}
	
}