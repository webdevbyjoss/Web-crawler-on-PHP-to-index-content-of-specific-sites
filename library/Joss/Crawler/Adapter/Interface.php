<?php
/**
 * We will use this interface to check if crawler adapter
 * possibly in the future created by the third-party developer  
 * meats all the requirements
 * 
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
interface Joss_Crawler_Adapter_Interface
{
	/**
	 * Retur the list of sutable links on this page
	 */
	public function getUrls();
	
	/**
	 * 	Returns parsed data
	 */
	public function getData();
}