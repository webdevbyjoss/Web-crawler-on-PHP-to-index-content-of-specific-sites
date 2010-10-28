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
	 * Returns parsed data
	 *
	 * use the following variables in order to process content
	 *
	 * $this->_currentUrl		// The url with the currently loaded content
	 * $this->_lastPageContent	// The content of the last loaded page
	 * $this->_urlData			// This variable will hold the data of the currently loaded page after parse_url($url)
	 *
	 * @return array
	 */
	public function extractItems();
}