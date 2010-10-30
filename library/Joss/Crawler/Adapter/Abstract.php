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
	 * Default area code will be used in case the area code in omited in phone numbers
	 */
	const DEFAULT_PHONE_AREA_CODE = '380'; // because i use this class for Ukrainian sites mainly :P
	
	/**
	 * Used for "matchDataLink" method to match data pages only
	 */
	const MATCH_DATA_ONLY = true;
	
	/**
	 * Initial URL to start crawling from
	 * regulary its a homepage or category page
	 *
	 * @var array
	 */
	protected $_initialUrls = null;

	/**
	 * Page encoding to use while dealing with text
	 *
	 * @var stirng
	 */
	protected $_encoding = 'UTF-8';

	/**
	 * Default area code will be used in case the area code in omited in phone numbers
	 * this can be overriden in child adapters
	 */
	protected $_phoneAreaCode = '380';
	
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
	 * Holds a list of valid international phone numbers area codes
	 * that are used for area code validation during phone number normalization
	 * FIXME: move this data and phone-realated functionality to the separate class
	 */
	private $_phoneAreaCodes = array(
	
		// Zone 1 – North American Numbering Plan Area
		'1', // United States of America & Canada, U.S. Virgin Islands, Northern Mariana Islands, Guam
			 // American Samoa, Puerto Rico,
//		'1340', //
//		'1670', //
//		'1671', //
//		'1684', //
//		'1939', //
//		'1787', //
//		'1264', // Anguilla
//		'1268', // Antigua and Barbuda
//		'1242', // Bahamas
//		'1246', // Barbados
//		'1441', // Bermuda
//		'1284', // British Virgin Islands
//		'1345', // Cayman Islands
//		'1767', // Dominica
//		'1809', // Dominican Republic
//		'1829', // Dominican Republic
//		'1849', // Dominican Republic
//		'1473', // Grenada
//		'1876', // Jamaica
//		'1664', // Montserrat
//		'1869', // Saint Kitts and Nevis
//		'1758', // Saint Lucia
//		'1784', // Saint Vincent and the Grenadines
//		'1868', // Trinidad and Tobago
//		'1649', // Turks and Caicos Islands
//		'1721', // Sint Maarten
		
		// Zone 2 – Mostly Africa
		'20', // Egypt
		'212', // Morocco
		'213', // Algeria
		'216', // Tunisia
		'218', // Libya
		'220', // Gambia
		'221', // Senegal
		'222', // Mauritania
		'223', // Mali
		'224', // Guinea Guinea
		'225', // Côte d'Ivoire
		'226', // Burkina Faso
		'227', // Niger
		'228', // Togo
		'229', // Benin
		'230', // Mauritius
		'231', // Liberia
		'232', // Sierra Leone
		'233', // Ghana
		'234', // Nigeria
		'235', // Chad
		'236', // Central African Republic
		'237', // Cameroon
		'238', // Cape Verde
		'239', // São Tomé and Príncipe
		'240', // Equatorial Guinea
		'241', // Gabon
		'242', // Republic of the Congo
		'243', // Democratic Republic of the Congo
		'244', // Angola
		'245', // Guinea-Bissau
		'246', // Diego Garcia
		'247', // Ascension Island
		'248', // Seychelles
		'249', // Sudan
		'250', // Rwanda
		'251', // Ethiopia
		'252', // Somalia
		'253', // Djibouti
		'254', // Kenya
		'255', // Tanzania
		'256 ', // Uganda
		'257', // Burundi
		'258', // Mozambique
		'260', // Zambia
		'261', // Madagascar
		'262', // Réunion; also Mayotte
		'263', // Zimbabwe
		'264', // Namibia
		'265', // Malawi
		'266', // Lesotho
		'267', // Botswana
		'268', // Swaziland
		'269', // Comoros
		'27', // South Africa
		'290', // Saint Helena, Tristan da Cunha
		'291', // Eritrea
		'297', // Aruba
		'298', // Faroe Islands
		'299', // Greenland
		
		// Zones 3/4 – Europe
		'30', // Greece
		'31', // Netherlands
		'32', // Belgium
		'33', // France
		'34', // Spain
		'350', // Gibraltar
		'351', // Portugal
		'352', // Luxembourg
		'353', // Ireland
		'354', // Iceland
		'355', // Albania
		'356', // Malta
		'357', // Cyprus
		'358', // Finland
		'359', // Bulgaria
		'36', // Hungary
		'370', // Lithuania
		'371', // Latvia
		'372', // Estonia
		'373', // Moldova
		'374', // Armenia
		'375', // Belarus
		'376', // Andorra
		'377', // Monaco
		'378', // San Marino
		'379', // assigned to Vatican City but uses +39 with Italy.
		'380', // Ukraine
		'381', // Serbia
		'382', // Montenegro
		'385', // Croatia
		'386', // Slovenia
		'387', // Bosnia and Herzegovina
		'388', // shared code for groups of nations
		'389', // Republic of Macedonia
		'39', // Vatican City Italy and Vatican City
		'40', // Romania
		'41', // Switzerland
		'420', // Czech Republic
		'421', // Slovakia
		'423', // Liechtenstein
		'43', // Austria
		'44', // United Kingdom, including  Isle of Man and the Channel Islands.
		'45', // Denmark
		'46', // Sweden
		'47', // Norway
		'48', // Poland
		'49', // Germany
		
		// Zone 5 – Mostly Latin America
		'500', // Falkland Islands
		'501', // Belize
		'502', // Guatemala
		'503', // El Salvador
		'504', // Honduras
		'505', // Nicaragua
		'506', // Costa Rica
		'507', // Panama
		'508', // Saint-Pierre and Miquelon
		'509', // Haiti
		'51', // Peru
		'52', // Mexico
		'53', // Cuba
		'54', // Argentina
		'55', // Brazil
		'56', // Chile
		'57', // Colombia
		'58', // Venezuela
		'590', // Guadeloupe, Saint Barthélemy, Saint Martin
		'591', // Bolivia
		'592', // Guyana
		'593', // Ecuador
		'594', // French Guiana
		'595', // Paraguay
		'596', // Martinique
		'597', // Suriname
		'598', // Uruguay
		'599', // Netherlands Antilles
		
		// Zone 6 – Southeast Asia and Oceania
		'60', // Malaysia
		'61', // Australia including external territories of Christmas Island and Cocos Islands
		'62', // Indonesia
		'63', // Philippines
		'64', // New Zealand
		'65', // Singapore
		'66', // Thailand
		'670', // East Timor
		'671', // formerly Guam
		'672', // Australian external territories other than Christmas, Cocos Islands, such as Australian Antarctic Territory, Norfolk Island
		'673', // Brunei
		'674', // Nauru
		'675', // Papua New Guinea
		'676', // Tonga
		'677', // Solomon Islands
		'678', // Vanuatu
		'679', // Fiji
		'680', // Palau
		'681', // Wallis and Futuna
		'682', // Cook Islands
		'683', // Niue Island
		'684', // formerly American Samoa
		'685', // Samoa
		'686', // Kiribati
		'687', // New Caledonia
		'688', // Tuvalu
		'689', // French Polynesia
		'690', // Tokelau
		'691', // Federated States of Micronesia
		'692', // Marshall Islands
		
		// Zone 7 – Eurasia (former Soviet Union)
		'7', // Russia (managed by Russia; some codes assigned to territories outside Russia).
//		'76', // Kazakhstan
//		'77', // Kazakhstan
//		'7840', // Abkhazia
//		'7940', // Abkhazia
		
		// Zone 8 – East Asia and Special Services
		'800', // International Freephone (UIFN)
		'81', // Japan
		'82', // South Korea
		'84', // Vietnam
		'850', // North Korea
		'852', // Hong Kong
		'853', // Macau
		'855', // Cambodia
		'856', // Laos
		'857', // ANAC satellite service
		'858', // ANAC satellite service
		'86', // Mainland China
		'870', // Inmarsat "SNAC" service
		'878', // Universal Personal Telecommunications services
		'880', // Bangladesh
		'881', // Global Mobile Satellite System
		'882', // International Networks
		'883', // International Networks
		'886', // Taiwan
		'888', // Telecommunications for Disaster Relief by OCHA
		
		// Zone 9 – Central, South and Western Asia
		'90', // Turkey
//		'90392', // Turkish Republic of Northern Cyprus
		'91', // India
		'92', // Pakistan
		'93', // Afghanistan
		'94', // Sri Lanka
		'95', // Burma
		'960', // Maldives
		'961', // Lebanon
		'962', // Jordan
		'963', // Syria
		'964', // Iraq
		'965', // Kuwait
		'966', // Saudi Arabia
		'967', // Yemen
		'968', // Oman
		'971', // United Arab Emirates
		'972', // Israel
		'973', // Bahrain
		'974', // Qatar
		'975', // Bhutan
		'976', // Mongolia
		'977', // Nepal
		'979', // International Premium Rate Service - originally assigned to Abu Dhabi, now covered under 971
		'98', // Iran
		'991', // International Telecommunications Public Correspondence Service trial (ITPCS)
		'992', // Tajikistan
		'993', // Turkmenistan
		'994', // Azerbaijan
		'995', // Georgia
		'99544', // Abkhazia
		'996', // Kyrgyzstan
		'998', // Uzbekistan
	);

	/**
	 * We need to build the list of all links we have for this site
	 * that will be used for crawling
	 */
	public function __construct()
	{
		// call initialization function
		$this->init();
		
		$this->_dataLinksPatterns = array_merge($this->_categoryLinks, $this->_dataLinks);
	}
	
	/**
	 * Place the initialization code here
	 */
	protected function init() {}

	/**
	 * Returns true if this link is recognized as link to page on site
	 * that holds data that we need to parse
	 *
	 * This method will allow us to recognize that this particular
	 * adapter is aplicable to process content from the provided URL
	 *
	 * @param string $link the URL to check
	 * @param bool search only within data links
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
	 *
	 * @return string website URL
	 */
	public function getInitialUrl()
	{
		return $this->_initialUrls;
	}
	
	/**
	 * Returns the list of URLs holding the data we need to parse
	 *
	 * @return string the list of links with data
	 */
	public function getDataLinks()
	{
		// we need this check to grap the URLs only from the category pages
		// and avoid extracting links from the item details page
		// possibly this can be changed in the future
		if ($this->matchDataLink($this->_currentUrl, self::MATCH_DATA_ONLY)) {
			return null;
		}
		
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
		// we will not even try to extract data from the page
		// if we 100% sure that this page is only a category page
		// and not the item details page
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
	 * @see http://en.wikipedia.org/wiki/List_of_country_calling_codes
	 *      for complete list of country codes
	 *
	 * @param string $number phone number
	 * @param string $areaCode default area code
	 * @return normalized phone number according to international phone numbers format
	 */
	protected function _normalizePhoneNumber($number, $defaultAreaCode = null)
	{
		// strip all non-numeric characters
		$number = preg_replace('/[^0-9]/', '', $number);
		
		// in case the phone number starts from the "0" or "00" we can strip that zeros
		// and then replace it to international area sign "+"
		if ('00' === substr($number, 0, 2)) {
			$number = substr($number, 2);
		}
		
//		if ('0' === substr($number, 0, 1)) {
//			$number = substr($number, 1);
//		}
		
		$number = '1636313567';
		
		// parse phone number
		$phoneNumber = substr($number, -7);
		// $networkCode = substr($number, -9, 2);
		// $areaCode = substr($number, -12, 4);

		var_dump($phoneNumber);
		var_dump($networkCode);
		var_dump($areaCode);
		
		// lets recognize the area code
		if (preg_match('/^(' . implode('|', $this->_phoneAreaCodes) . ')/', $number)) {

			foreach ($this->_phoneAreaCodes as $code) {
				if (preg_match('/^(' . $code . ')/', $areaCode)) {
					$areaCode = $code;
					break;
				}
			}
			
			$networkCode =
			
		} else {
			// in case the area code incorrect or it is ommited
			if (null == $defaultAreaCode) {
				$areaCode = $this->_phoneAreaCode;
			} else {
				$areaCode = $defaultAreaCode;
			}
		}

		var_dump($areaCode);
		
		// add international area sign "+" here to have a phone number in international format
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