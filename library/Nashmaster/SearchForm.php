<?php
/**
 * Search form params will be configured/stored/processed in this class
 *
 * TODO: this class should be moved to "/application/modules/searchdata/forms"
 */
class Nashmaster_SearchForm
{
	/**
	 * Holds persistant data
	 *
	 * @var Zend_Session_Namespace
	 */
	private $_session = null;
	
	/**
	 * Search form data
	 */
	private $_base_region = null;
	private $_inline_regions = null;
	private $_inline_services = null;
	private $_last_inline_services = null;
	private $_last_search_keywords = null;
	private $_regions = null;
	private $_services = null;
	
	/**
	 * Holds matched services labels
	 */
	private $_matchServices = null;
	
	/**
	 * Holds array of words simmilar to requested
	 *
	 * @var array
	 */
	private $_suggests = null;
	
	/**
	 * Lets init start values or/and get saved values from session
	 *
	 * @param array $options
	 * @param Zend_Session_Namespace $session
	 * @return void
	 */
	public function __construct($options = null, Zend_Session_Namespace $session = null)
	{
		if (null !== $session) {
			$this->setAdapter($session);
		} else {
			$this->setAdapter(new Zend_Session_Namespace(__CLASS__));
		}
		
		$this->loadDataFromSession();
		$this->initValues($options);
	}

	/**
	 * Save data into the session
	 */
	public function __destruct()
	{
		$this->_session->base_region = $this->_base_region;
		$this->_session->last_search_keywords = $this->_last_search_keywords;
		$this->_session->last_inline_services = $this->_last_inline_services;
		
		$this->_session->regions = $this->_regions;
		$this->_session->services = $this->_services;
	}
	
	/**
	 * Loads form elements from session
	 */
	public function loadDataFromSession()
	{
		$this->_base_region = $this->_session->base_region;
		$this->_last_search_keywords = $this->_session->last_search_keywords;
		$this->_last_inline_services = $this->_session->last_inline_services;
		
		$this->_regions = $this->_session->regions;
		$this->_services = $this->_session->services;
	}
	
	/**
	 * Lets initiate search form options
	 *
	 * as for now search form holds the following options:
	 * - search_keywords		- search keywords that are in search form right now
	 * - last_search_keywords	- last saved search keywords, in case search_keywords changed
	 * 							  we should re-calculate inline values
	 * - remote_ip				- IP address of the client that will be used to detect its location
	 *
	 * - inline_regions (inline)  - regions that are mentioned in search_keywords
	 * - inline_services (inline) - services that are mentioned in search_keywords
	 * - regions (specified)		  - regions that are specified in "select regions" form
	 * - services (specified)	  - services that are specified in "select services" form
	 * - base_region (autimatically recognized) - automatically recognized value
	 *
	 * NOTE: appropriate data will be ovverriden with the values of the higher priority
	 *
	 * base_region < regions < inline_regions
	 * services < inline_services
	 * last_search_keyword < search_keyword
	 *
	 * @param array $options should include values received from online form
	 * @return void
	 */
	public function initValues($options)
	{
		// 1. Init base region value
		// and try to recognize visitor's location
		if (empty($this->_base_region)) {
			$this->_base_region = $this->detectLocationByIp($options['remote_ip']);
		}

		// 2. Init search keywords values and
		// recalculate inline data
		if (!empty($options['search_keywords'])) {
			$this->setKeywords($options['search_keywords']);
		} else {
			$this->_inline_regions = null;
			$this->_inline_services = null;
		}
	}
	
	/**
	 * Sets new keywords
	 *
	 * @param string $keywords
	 */
	public function setKeywords($keywords)
	{
		// late return in case keywords hasn't been changed
		// FIXME: was not able to make this stable
		//if ($this->_last_search_keywords == $keywords && !empty($this->_last_inline_services)) {
		//	$this->_inline_services = $this->_last_inline_services;
		// 	return null;
		//}
		
		$keywordList = $this->prepareKeywords($keywords);
		$regionsMatch = $this->getRegionsByKeywords($keywordList);
		
		$this->_inline_regions = $regionsMatch['cities'];
		
		// we can eliminate overhead here
		// by excluding keywords that are already recognized as regions
		$keywordList = array_diff($keywordList, $regionsMatch['hit_keywords']);
		
		$servicesMatch = $this->getServicesByKeywords($keywordList);
		
		if (empty($servicesMatch['services'])) {
			$this->_suggests = $servicesMatch['suggest'];
		} else {
			$this->_inline_services = $servicesMatch['services'];
			$this->_last_inline_services = $servicesMatch['services'];
			$this->_matchServices = $servicesMatch['match_synonyms'];
		}
		
		// save value for future use
		$this->_last_search_keywords = $keywords;
	}
	
	/**
	 * Returns the list of matched synonyms that were recognized in getServicesByKeywords()
	 * this value is used to highlight valuable keywords in search results
	 *
	 * @return array
	 */
	public function getMatchSynonyms()
	{
		return $this->_matchServices;
	}

	/**
	 * Returns suggested possible keywords fro "Did you mean functionality
	 * with the region(s) in case it was specified
	 *
	 * @param string $locale
	 * @return array of strings
	 */
	public function getSuggest($locale = 'uk')
	{
		if (empty($this->_suggests)) {
			return NULL;
		}
		
		$citiesPhrase = '';
		if (!empty($this->_inline_regions)) {
			$cities = $this->_inline_regions;
			foreach ($cities as $city) {
				$citiesPhrase .=  ' ' . (($locale == 'uk') ? $city['name_uk'] : $city['name']);
			}
		}
		
		$fullSuggest = array();
		foreach ($this->_suggests as $title) {
			$fullSuggest[] = $title . $citiesPhrase;
		}
		
		return $fullSuggest;
	}
	
	/**
	 * Strip punctuation marks and all words that are shorter that 3 characters
	 *
	 * TODO: we 100% should apply the steamer algorythm here
	 *       for example some simple variation of Porter's steaming
	 *       to receive move accurate search results
	 *
	 * @param string $keywords
	 * @return array of strings, words in keywords string
	 */
	public function prepareKeywords($keywords)
	{
		$keywords = mb_ereg_replace('[^\w-]', ' ', $keywords);
		
		// we need this to process cities like "New York", "New-York"
		// and treat them as the same city
		$keywords = str_replace('-', ' ', $keywords);
		
		$keywords_array = explode(' ', $keywords);
		$keywords_array_new = array();
		
		foreach ($keywords_array as $word) {

			// exclude words shorter than 3 characters
			if (mb_strlen($word) < 3) {
				continue;
			}

			$keywords_array_new[] = $word;
		}
		
		return $keywords_array_new;
	}

	/**
	 * Set session adapter
	 *
	 * @param Zend_Session_Namespace $session
	 * @return void
	 */
	public function setAdapter(Zend_Session_Namespace $session)
	{
		$this->_session = $session;
	}

	/**
	 * Get session adapter
	 *
	 * @return Zend_Session_Namespace
	 */
	public function getAdapter()
	{
		return $this->_session;
	}
	
	/**
	 * Recognize whether user mentioned some region in keywords
	 *
	 * returns the following structure:
	 * 'cities' => array(
	 * 		ID => array('id' => ID, 'name' => NAME, 'name_uk' => NAME_UK)
	 * 		ID => array('id' => ID, 'name' => NAME, 'name_uk' => NAME_UK)
	 * )
	 * 'hit_keywords' => array('keyword1', 'keyword2', 'keyword3')
	 *
	 * @param array $keywords
	 * @return mixed the list of matched keywords or false
	 */
	public function getRegionsByKeywords($keywords)
	{
		// TODO: possibly we should implement this hard dependency
		//       via dependancy injection
		$Cities = new Searchdata_Model_Cities();
		
		$ids = array();
		$hit_keywords = array();
		
		// Lookup two words cities
		// lets join keywords into pairs
		// in case we have X keywords - we will have X pair
		// because we need to lookup each (i,i+1) pair where max i = X-1
		// this will help us to detect two words cities in the queries like:
		// "new york electricity specialist" => "new york"
		if (count($keywords) > 1) {

			for ($i = 0;$i < (count($keywords) - 1); $i++) {
				// TODO: we should add the posibility to recognize not only cities
				//       but also the regions and areas in the future
				// two words lookup will help us to recognize regions as well
				// NOTE: we using a trick of MySQL "LIKE" inctruction that will
				// process underscode symbol "_" as single-characted wildcard
				// so both variants will match "New York" and "New-York"
				$matchList = $Cities->getCitiesByTag($keywords[$i] . '_' . $keywords[$i + 1]);
				if (null === $matchList) {
					continue;
				}
	
				foreach($matchList as $city) {
					$ids[$city->city_id]['id'] = $city->city_id;
					$ids[$city->city_id]['name'] = $city->name;
					$ids[$city->city_id]['name_uk'] = $city->name_uk;
					$hit_keywords[] = $keywords[$i];
					$i++;
					$hit_keywords[] = $keywords[$i];
				}
			}
		
		
			// after the pair lookup was finished
			// we should exclude the keywords that are already recognized as cities
			// to avoid redundant lookups and possibly falce single-word city recognision like
			// "new york electricity specialist" => "york"
			// so in case we already found the two words city it will not process appropriate
			// single-word city
			$keywords = array_diff($keywords, $hit_keywords);
		
		}
		
		// Lookup single-word cities
		foreach ($keywords as $keyword) {
			// TODO: we should add the posibility to recognize not only cities
			//       but also the regions and areas in the future
			$matchList = $Cities->getCitiesByTag($keyword);
			if (null === $matchList) {
				continue;
			}
		
			foreach($matchList as $city) {
				$ids[$city->city_id]['id'] = $city->city_id;
				$ids[$city->city_id]['name'] = $city->name;
				$ids[$city->city_id]['name_uk'] = $city->name_uk;
				$hit_keywords[] = $keyword;
			}
		}

		$res = array();
		$res['hit_keywords'] = $hit_keywords;
		$res['cities'] = $ids;
		return $res;
	}

	public function getServicesByKeywords($keywords)
	{
		// TODO: possibly we should implement this hard dependency
		//       via dependancy injection
		$Services = new Joss_Crawler_Db_Synonyms();
		
		$ids = array();
		$hit_keywords = array();
		$servicesPowerList = array();
		$matchSynonyms = array();
		
		// we need to have a 2 words lookup first
		// to receive more relevant results for two words matches
		if (count($keywords) > 1)
		{
			// lets build a pairs of keywords
			// in case we have 3 words "word1 word2 word3"
			// we will have 2 pairs "word1 word2" and "word2 word3"
			for ($i = 0;$i < (count($keywords) - 1); $i++) {
				// NOTE: we are using a trick of MySQL "LIKE" inctruction that will
				//       process symbol "%" as wildcard
				//       so following variants will match "Plastic window", "Plastic-window", "window from Plastic" (reverse order)
				$matchServices = $Services->searchServicesByTag($keywords[$i] . '%' . $keywords[$i + 1]);
				
				if (null === $matchServices) {
					continue;
				}
				
				foreach($matchServices as $serviceId => $serviceData) {
				
					if (empty($servicesPowerList[$serviceId])) {
						
						$servicesPowerList[$serviceId] = $serviceData['rate'];
						
						$serviceTitle[$serviceId] = $serviceData['name'];
						$serviceTitleUk[$serviceId] = $serviceData['name_uk'];
	
					} else {
						$servicesPowerList[$serviceId] += $serviceData['rate'];
					}
					
					// save match synonyms data
					if (isset($matchSynonyms[$serviceId]) && is_array($matchSynonyms[$serviceId])) {
						$matchSynonyms[$serviceId] = array_merge($matchSynonyms[$serviceId], $serviceData['match']);
					} else {
						$matchSynonyms[$serviceId] = $serviceData['match'];
					}
					
				}
				
				// lets skip both words
				$hit_keywords[] = $keywords[$i];
				$i++;
				$hit_keywords[] = $keywords[$i];
			}

			// after the pair lookup was finished
			// we should exclude the keywords that are already recognized as services
			// to avoid redundant lookups and possibly falce single-word service recognision like
			// "fixing plastic windows" => "fixing windows"
			// so in case we already found the two words service it will not process appropriate
			// single-word service
			$keywords = array_diff($keywords, $hit_keywords);
			
			
			// lets build the same pairs of keywords
			// but in reverse order, so for the same example "word1 word2 word3"
			// we will have pairs like "word2 word1" and "word3 word2"
			for ($i = 0;$i < (count($keywords) - 1); $i++) {
				// NOTE: we are using a trick of MySQL "LIKE" inctruction that will
				//       process symbol "%" as wildcard
				//       so following variants will match "Plastic window", "Plastic-window", "window from Plastic" (reverse order)
				$matchServices = $Services->searchServicesByTag($keywords[$i + 1] . '%' . $keywords[$i]);
				
				if (null === $matchServices) {
					continue;
				}
				
				foreach($matchServices as $serviceId => $serviceData) {
				
					if (empty($servicesPowerList[$serviceId])) {
						
						$servicesPowerList[$serviceId] = $serviceData['rate'];
						
						$serviceTitle[$serviceId] = $serviceData['name'];
						$serviceTitleUk[$serviceId] = $serviceData['name_uk'];
	
					} else {
						$servicesPowerList[$serviceId] += $serviceData['rate'];
					}
					
					// save match synonyms data
					if (isset($matchSynonyms[$serviceId]) && is_array($matchSynonyms[$serviceId])) {
						$matchSynonyms[$serviceId] = array_merge($matchSynonyms[$serviceId], $serviceData['match']);
					} else {
						$matchSynonyms[$serviceId] = $serviceData['match'];
					}
					
				}
				
				// lets skip both words
				$hit_keywords[] = $keywords[$i];
				$i++;
				$hit_keywords[] = $keywords[$i];
			}
			
			
			// after the pair lookup was finished
			// we should exclude the keywords that are already recognized as services
			// to avoid redundant lookups and possibly falce single-word service recognision like
			// "fixing plastic windows" => "fixing windows"
			// so in case we already found the two words service it will not process appropriate
			// single-word service
			$keywords = array_diff($keywords, $hit_keywords);
		}

		// the service recognizion algorithm will be very simple for now
		// 1. get all matches for each single keyword
		// 2. then count the amount of matches of the same type of service
		// 3. filter the incorrect matches using simple noise filtering algoright
		// 4. PROFIT!!
		// TODO: In the future we shoul organize that into different strategies
		foreach ($keywords as $keyword) {
			
			// TODO: we can use Full Text search feature of MyISAM engine and MATCH() function
			//       to calculate the relevancy to make our search more acurrate
			//       and add typo protection
			$matchServices = $Services->searchServicesByTag($keyword);
			
			if (null === $matchServices) {
				continue;
			}

			foreach($matchServices as $serviceId => $serviceData) {
				
				if (empty($servicesPowerList[$serviceId])) {
					
					$servicesPowerList[$serviceId] = $serviceData['rate'];
					
					$serviceTitle[$serviceId] = $serviceData['name'];
					$serviceTitleUk[$serviceId] = $serviceData['name_uk'];

				} else {
					$servicesPowerList[$serviceId] += $serviceData['rate'];
				}

				// save match synonyms data
				if (isset($matchSynonyms[$serviceId]) && is_array($matchSynonyms[$serviceId])) {
					$matchSynonyms[$serviceId] = array_merge($matchSynonyms[$serviceId], $serviceData['match']);
				} else {
					$matchSynonyms[$serviceId] = $serviceData['match'];
				}

			}

			$hit_keywords[] = $keyword;
		}

		// in case no any services were recognized we should suggest something
		// just like "Did you mean..."
		if (empty($servicesPowerList)) {
			$res = array();
			$Synonyms = new Joss_Crawler_Db_Synonyms();
			$items = $Synonyms->getSoundsLike(implode(' ', $keywords));
			
			// FIXME: fetchCol() returns array with 1 empty element
			// we should add temporrary workaround here to skip empty values
			$items = array_filter($items);
			
			$res['suggest'] = $items;
			return $res;
		}
		
		$servicesPower = 0;
		foreach($servicesPowerList as $serviceId => $serviceInfo) {
			$servicesPower += $serviceInfo['rate'];
		}
		
		// calculate trashhold amount
		// for now we croping the items that donate less than 20% to the total power
		$trashHold = $servicesPower / 100 * 20;
		
		$resultServices = array();
		foreach($servicesPowerList as $serviceId => $serviceInfo) {
			if ($serviceInfo['rate'] >= $trashHold) {
				$resultServices[$serviceId]['id'] = $serviceId;
				$resultServices[$serviceId]['name'] = $serviceTitle[$serviceId];
				$resultServices[$serviceId]['name_uk'] = $serviceTitleUk[$serviceId];
			}
		}
		
		$res = array();
		$res['hit_keywords'] = $hit_keywords;
		$res['services'] = $resultServices;
		$res['match_synonyms'] = $matchSynonyms;
		return $res;
	}
	
	/**
	 * Detects visitor location using the IP-to-City database.
	 *
	 * city['id'] = XX
	 * city['name'] = XX
	 * city['name_uk'] = XX
	 *
	 * TODO: do something in case city was not recognized
	 *       for example try to recognize region or country
	 *       and assign visitor to the largest city in the region
	 *       or to the coutry capital city
	 */
	public function detectLocationByIp($ip)
	{
		$IpToCity = new Joss_Geolocation();
		return $IpToCity->getCityByIp($ip);
	}

	/**
	 * Get regions according to the priority data:
 	 * base_region < regions < inline_regions
	 */
	public function getRegions()
	{
		if (!empty($this->_inline_regions)) {
			return $this->_inline_regions;
		}
		
		if (!empty($this->_regions)) {
			return $this->_regions;
		}
		
		return $this->_base_region;
	}
	
	/**
	 * Get services according to the priority level:
	 * services < inline_services
	 */
	public function getServices()
	{
		if (!empty($this->_inline_services)) {
			return $this->_inline_services;
		}
		
		if (!empty($this->_services)) {
			return $this->_services;
		}
		
		return null;
	}
	
}