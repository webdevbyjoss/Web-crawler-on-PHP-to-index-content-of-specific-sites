<?php
/**
 * Web Crawler site abstract adapter that will parse graped content
 *
 * @name		Joss_Crawler_Adapter_Abstract
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
abstract class Joss_Crawler_Adapter_Abstract implements Joss_Crawler_Adapter_Interface
{
	/**
	 * Default encoding that we need to convert all pages to
	 */
	const DEFAULT_ENCODING = 'UTF-8';
	
	/**
	 * Used for "matchDataLink" method to match data pages only
	 */
	const MATCH_DATA_ONLY = true;
	
	/**
	 * Initial URL to start crawling from
	 * regulary its a homepage or category page
	 *
	 * @var string
	 */
	protected $_initialUrl = '';

	/**
	 * Page encoding to use while dealing with text
	 *
	 * @var stirng
	 */
	protected $_encoding = 'UTF-8';

	/**
	 * Link patterns that match category pages
	 *
	 * @var array
	 */
	protected $_categoryLinks = null;
	
	/**
	 * Link patterns that match pages with data
	 *
	 * this will allow us to understand wether we need to try and search
	 * data on page or parse only links without any data extraction
	 *
	 * NOTE! In case you have a category page and data page under
	 *       the same URL then palce it under _dataLinks list
	 *
	 * @var array
	 */
	protected $_dataLinks = null;
	
	/**
	 * All pages links patterns
	 *
	 * Reg exp patterns to match data URL
	 * each element of the array should be a string that holds a correct regular expression
	 *
	 * regular expression will be matched using the following construct
	 * if (preg_match($currentPattern, $link)) return true; else return false;
	 *
	 * this array will be merged in the constructor from all the data URLs
	 * we have in $_categoryLinks and $_dataLinks
	 *
	 * @var array
	 */
	protected $_dataLinksPatterns = null;
	
	/**
	 * The url with the currently loaded content
	 *
	 * @var string
	 */
	protected $_currentUrl = null;

	/**
	 * The content of the last loaded page
	 *
	 * @var string
	 */
	protected $_lastPageContent = '';
	
	/**
	 * This variable will hold the data of the currently loaded page after
	 * parse_url($url);
	 */
	protected $_urlData = null;
	
	/**
	 * We need to build the list of all links we have for this site
	 * that will be used for crawling
	 */
	public function __construct()
	{
		$this->_dataLinksPatterns = array_merge($this->_categoryLinks, $this->_dataLinks);
	}

	/**
	 * Returns true if this link is recognized as link to page on site
	 * that holds data that we need to parse
	 *
	 * This method will allow us to recognize that this particular
	 * adapter is aplicable to process content from the provided URL
	 *
	 * @param string $link the URL to check
	 * @return boolean true if provided link matches the pattern
	 */
	public function matchDataLink($link, $onlyData = false)
	{
		if ($onlyData) {
			$dataLinksPatterns = $this->_dataLinks;
		} else {
			$dataLinksPatterns = $this->_dataLinksPatterns;
		}
		
		foreach ($dataLinksPatterns as $currentPattern) {
			if (preg_match($currentPattern, $link)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Will return all links on the loaded page
	 *
	 * @see Joss/Crawler/Adapter/Joss_Crawler_Adapter_Interface::getUrls()
	 * @return array the array of the links on the page
	 */
	public function getUrls()
	{
		return $this->_getUrls($this->_lastPageContent);
	}
	
	/**
	 * Returns initial URL of the adapter to start crawling from
	 * @return string website URL
	 */
	public function getInitialUrl()
	{
		return $this->_initialUrl;
	}
	
	/**
	 * Returns the list of URLs holding the data we need to parse
	 * @return string the list of links with data
	 */
	public function getDataLinks()
	{
		$links = $this->getUrls();
		$dataLinks = array();
		
		foreach ($links as $index => $link) {
			if ($this->matchDataLink($link['url'])) {
				// we need to make all relative URL to be absolute using the domain of current page
				$link['url'] = $this->_normalizeUrl($link['url']);
				$dataLinks[$link['url']] = $link;
			}
		}
		
		return $dataLinks;
	}

	/**
	 * Returns the list of data
	 *
	 * generally on this level of abstraction we will check if specified
	 * URL contains data and in case it does we will extract that data from the page
	 *
	 * @see Joss_Crawler_Adapter_Interface::getData()
	 * @return array the list of data
	 */
	public function getData()
	{
		if (!$this->matchDataLink($this->_currentUrl, self::MATCH_DATA_ONLY)) {
			return null;
		}
		
		return $this->extractItems();
	}
	
	/**
	 * Loads content and stores it into "lastPageContent" field
	 *
	 * @param string $url
	 * @param string $headers
	 * @param string $body
	 * @return null
	 */
	public function loadPage($url, $body)
	{
		// convert all encodings to UTF-8 as a standatd encoding for our database
		if ($this->_encoding !== self::DEFAULT_ENCODING) {
			// we have data in some other format, lets convert everything to UTF-8
			// TODO: we need to look at encoding autodetection here to avoid situation when
			// encoding has been chaned on site and content was encoded incorrectly
			$body = iconv($this->_encoding, self::DEFAULT_ENCODING . '//TRANSLIT//IGNORE', $body);
		}
		
		$this->_lastPageContent = $body;
		$this->_currentUrl = $url;
		
		// parse page meta data that will be used in advance for relative to absolute links transformation
		// and sitebase calculaton for current page
		$this->_urlData = parse_url($this->_currentUrl);
	}
	
	/**
	 * Recognizes URL accoridng to the pattern and returns the list of available ones
	 *
	 * @param text $htmlCode
	 * @return array the array of the links
	 */
	protected function _getUrls($htmlCode)
	{
		// Get all links from the page using the following regular expression
		// TODO: this chould be improved in the future
		$links_regex = '/<a.*href=[\"|\']([^javascript:|\'|\"].*)[\"|\'].*>(.*)<\/a>/Ui';
		
		preg_match_all($links_regex, $htmlCode, $out, PREG_PATTERN_ORDER);
		
		$lenght = count($out[0]);
		$links = array();
		for ($i = 0; $i < $lenght; $i++) {
			$links[] = array(
				'url' => $out[1][$i],
				'content' => $out[2][$i],
			);
		}
		
		return $links;
	}

	/**
	 * Checks for link to be relative
	 * and appends the domain from the currently processed page
	 *
	 * @param string $url
	 * @return string normilized URL
	 */
	protected function _normalizeUrl($url)
	{
		if (false === strpos($url, 'http://')) {
			return $this->_relativeToAbsoluteUrl($url);
		}
		
		return $url;
	}
	
	/**
	 * This function normalizes phone number to the standart international format
	 * +112223333333
	 * where
	 * 11      - area code
	 * 222     - phone network code
	 * 3333333 - personal phone number
	 *
	 * @param string $number phone number
	 * @param string $areaCode default area code
	 * @return normalized phone number according to international phone numbers format
	 */
	protected function _normalizePhoneNumber($number, $defaultAreaCode = '38')
	{
		// strip all non-numeric characters
		$number = preg_replace('/[^0-9]/', '', $number);

		// parse phone number
		$phoneNumber = substr($number, -7);
		$networkCode = substr($number, -10, 3);
		$areaCode = substr($number, -12, 3);

		// process area code in case the area code incorrect or it omited
		if (strlen($areaCode) < strlen($defaultAreaCode) ) {
			$areaCode = $defaultAreaCode;
		}

		return '+' . $areaCode . $networkCode . $phoneNumber;
	}

	/**
	 * Converts relative URl to absolute using the current page URL
	 *
	 * @param string $url relative URL
	 * @return string absolute URL
	 */
	protected function _relativeToAbsoluteUrl($url)
	{
		return $this->_urlData['scheme'] . '://' . $this->_urlData['host'] . $url;
	}
}