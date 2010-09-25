<?php
/**
 * Web Crawler site abstract adapter that will parse graped content
 *
 * TODO: split variables to be "private" for thouse that need are internal variables
 *       and "protected" that will be used as *configuration* in child classes 
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
	protected $_currentUrl = '';
	
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
	 * Page encoding to use while dealing with text
	 * 
	 * @var stirng
	 */
	protected $_encoding = 'UTF-8';
	
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
	protected $_dataLinksPatterns = null;
	
	/**
	 * Lets load thestarting URL page here
	 * 
	 */
	public function __construct()
	{
		$this->_loadPage($this->_currentUrl);
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
	 * Returns the list of URLs holding the data we need to parse
	 * @return string the list of links with data
	 */
	public function getDataLinks()
	{
		$links = $this->getUrls();
		$dataLinks = array();
		
		foreach ($links as $index => $link) {
			if ($this->_matchDataLink($link['url'])) {
				// we need to make all relative URL to be absolute using the domain of current page
				$link['url'] = $this->_normalizeUrl($link['url']);
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
		
		// DOTO: add the fucntionality to respect "robots.txt" rules,
		// see: http://www.the-art-of-web.com/php/parse-links/
		//      http://www.the-art-of-web.com/php/parse-robots/

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
		if ($this->_encoding !== "UTF-8") {
			// we have data in some other format, lets convert everything to UTF-8
			$textHtml = iconv($this->_encoding, "UTF-8", $textHtml);
		}
		
		$this->_lastPageContent = $textHtml;
		$this->_currentUrl = $url;
		
		// parse page meta data that will be used in advance for relative to absolute links transformation 
		// get sitebase for the current page
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
		//		'link' => $out[0][$i],
				'url' => $out[1][$i],
				'content' => $out[2][$i],
			);
		}
		
		return $links;
	}

	/**
	 * Returns true if this link is recognized as link to page on site 
	 * that holds data that we need to parse
	 * 
	 * @param string $link the URL to check
	 * @return boolean true if provided link matches the pattern
	 */
	protected function _matchDataLink($link)
	{

		foreach ($this->_dataLinksPatterns as $currentPattern) {
			if (preg_match($currentPattern, $link)) {
				return true;
			}
		}
		
		return false;
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