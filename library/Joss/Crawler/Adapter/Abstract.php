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
	 * Initial URL to start crawling from
	 * regulary its a homepage or category page
	 * 
	 * @var string
	 */
	protected $startingUrl = '';
	
	/**
	 * The content of the last loaded page
	 * 
	 * @var string
	 */
	protected $lastPageContent = '';
	
	/**
	 * Page encoding to use while dealing with text
	 * 
	 * @var stirng
	 */
	protected $encoding = 'UTF-8';
	
	/**
	 * Data links patterns
	 * 
	 * Reg exp patterns to match data URL
	 * each element of the array should be a string that holds a correct regular expression
	 * 
	 * regular expression will be matched using the following construct
	 * if (preg_match($currentPattern, $link)) return true; else return false;
	 * 
	 * @var array
	 */
	protected $dataLinksPatterns = null;
	
	/**
	 * Lets load thestarting URL page here
	 * 
	 */
	public function __construct()
	{
		$this->_loadPage($this->startingUrl);
	}

	/**
	 * Will return all links on the loaded page
	 * 
	 * @see Joss/Crawler/Adapter/Joss_Crawler_Adapter_Interface::getUrls()
	 * @return arraythe array of the links on the page
	 */
	public function getUrls()
	{
		return $this->_getUrls($this->lastPageContent);
	}

	/**
	 * Returns the list of URLs holding the data we need to parse 
	 */
	public function getDataLinks()
	{
		$links = $this->getUrls();
		$dataLinks = array();
		
		foreach ($links as $index => $link) {
			if ($this->_matchDataLink($link['url'])) {
				$dataLinks[$link['url']] = $link;
			}
		}
		
		return $dataLinks;
	}
	
	/**
	 * Loads content from the provided URL amd stores it into "lastPageContent" field 
	 * 
	 * @uses  Zend_Http_Client 
	 * @param string $url
	 * @return null
	 */
	protected function _loadPage($url)
	{
		$config = array(
			'useragent' => "IE 7 – Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)"
		);
		
		$client = new Zend_Http_Client($url, $config);
		$response = $client->request();
		
		// deal with gzip-content
		$headers = $response->getHeaders();
		if ($headers['Content-encoding'] == 'gzip') {
			$textHtml = $response->decodeGzip($response->getRawBody());
		} else {
			$textHtml = $response->getBody();
		}
		
		// convert all encodings to UTF-8 as a standatd encoding for our database
		if ($this->encoding !== "UTF-8") {
			// we have data in some other format, lets convert everything to UTF-8
			$textHtml = iconv($this->encoding, "UTF-8", $textHtml);
		}
		
		$this->lastPageContent = $textHtml;
	}

	/**
	 * Recognizes URL accoridng to the pattern and returns the list of available ones 
	 * 
	 * @param text $htmlCode
	 * @return array the array of the links
	 */
	protected function _getUrls($htmlCode)
	{
		// Get all links from the page
		$links_regex = '/<a.*href=[\"|\']([^javascript:|\'|\"].*)[\"|\'].*>(.*)<\/a>/Ui';
		
		preg_match_all($links_regex, $htmlCode, $out, PREG_PATTERN_ORDER);
		
		$lenght = count($out[0]);
		$links = array();
		for ($i = 0; $i < $lenght; $i++) {
			$links[] = array(
		//		'link' => $out[0][$i],
				'url' => $out[1][$i],
				'content' => $out[2][$i]
			);
		}
		
		return $links;
	}
	
	/**
	 * Returns true if this link is recognized as link to page on site 
	 * that holds data that we need to parse
	 * 
	 * @param string $link the URL to check
	 */
	protected function _matchDataLink($link)
	{

		foreach ($this->dataLinksPatterns as $currentPattern) {
			if (preg_match($currentPattern, $link)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * This function checks for link to be relative 
	 * and appends the domain from the currently processed page
	 * 
	 * @param string $url
	 */
	protected function _relativeToAbsoluteUrl($url)
	{
		
	}
	
	protected function _normalizeUrl($url)
	{
		if (false === strpos($url, 'http://')) {
			
		}

	}
	
	
}