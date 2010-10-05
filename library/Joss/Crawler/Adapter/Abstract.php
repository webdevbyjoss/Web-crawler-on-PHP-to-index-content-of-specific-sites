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
	 * Initial URL to start crawling from
	 * regulary its a homepage or category page
	 *
	 * @var string
	 */
	protected static $_initialUrl = '';

	/**
	 * Page encoding to use while dealing with text
	 *
	 * @var stirng
	 */
	protected static $_encoding = 'UTF-8';
	
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
	protected static $_dataLinksPatterns = null;

	/**
	 * The url with the currently loaded content
	 *
	 * @var string
	 */
	private $_currentUrl = null;
	
	/**
	 * The content of the last loaded page
	 *
	 * @var string
	 */
	private $_lastPageContent = '';
	
	/**
	 * This variable will hold the data of the currently loaded page after
	 * parse_url($url);
	 */
	private $_urlData = null;
	
	/**
	 * Lets load thestarting URL page here
	 *
	 */
	public function __construct()
	{
		$this->_loadPage(self::$_initialUrl);
	}

	/**
	 * Returns true if this link is recognized as link to page on site
	 * that holds data that we need to parse
	 *
	 * This method will allow us to recognize that this particular
	 * adapter is aplicable to process content from the provided URL
	 *
	 * @access public static
	 * @param string $link the URL to check
	 * @return boolean true if provided link matches the pattern
	 */
	public static function matchDataLink($link)
	{
		foreach (self::$_dataLinksPatterns as $currentPattern) {
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
		return self::$_initialUrl;
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
			if (self::matchDataLink($link['url'])) {
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
		if (self::$_encoding !== self::DEFAULT_ENCODING) {
			// we have data in some other format, lets convert everything to UTF-8
			$textHtml = iconv(self::$_encoding, self::DEFAULT_ENCODING, $textHtml);
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