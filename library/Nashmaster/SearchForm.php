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
	 * Lets init start values or/and get saved values from session
	 *
	 * @param array $options
	 * @param Zend_Session_Namespace $session
	 * @return void
	 */
	public function __construct($options, Zend_Session_Namespace $session = null)
	{
		if (null !== $session) {
			$this->setAdapter($session);
		} else {
			$this->setAdapter(new Zend_Session_Namespace(__CLASS__));
		}
		
		$this->initValues($options);
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
	 * - region (specified)		  - regions that are specified in "select regions" form
	 * - services (specified)	  - services that are specified in "select services" form
	 * - base_region (autimatically recognized) - automatically recognized value
	 *
	 * NOTE: appropriate data will be ovverriden with the values of the higher priority
	 *
	 * base_region < region < inline_regions
	 * services < inline_services
	 * last_search_keyword < search_keyword
	 *
	 * @param array $options should include values received from online form
	 * @return void
	 */
	public function initValues($options)
	{
		// 1. Init search keywords values and
		// recalculate inline data
		if (!empty($options['search_keywords'])) {
			$this->setKeywords($options['search_keywords']);
		}
		
		// 2. Init base region value
		// and try to recognize visitor's location
		if (empty($this->_session->base_region)) {
			$this->_session->base_region = $this->detectLocationByIp($options['remote_ip']);
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
		if ($this->_session->last_search_keywords == $keywords) {
			return null;
		}
		
		$keywordList = $this->prepareKeywords($keywords);
		
		$regionsMatch = $this->getRegionsByKeywords($keywordList);
		$this->_session->inline_regions = $regionsMatch['ids'];

		// we can eliminate overhead here
		// by excluding keywords that are already recognized as regions
		$keywordList = array_diff($keywordList, $regionsMatch['hit_keywords']);

		$servicesMatch = $this->getServicesByKeywords($keywordList);
		$this->_session->inline_services = $servicesMatch['ids'];
		
		// save value for future use
		$this->_session->last_search_keywords = $keywords;
	}

	/**
	 * Strip punctuation marks and all words that are shorter that 3 characters
	 *
	 * TODO: we 100% should apply the speaming algorythm here
	 *       for example some simple variation of Porter's steaming
	 *       to receive move accurate search results
	 *
	 * @param string $keywords
	 * @return string array of words in keywords string
	 */
	public function prepareKeywords($keywords)
	{
		$keywords = mb_ereg_replace('[^\w]', ' ', $keywords);
		
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
	 * 'ids' => '123,345,567,896'
	 * 'hit_keywords' => array('keyword1', 'keyword2', 'keyword3')
	 *
	 * @param array $keywords
	 * @return mixed the list of matched keywords or false
	 */
	public function getRegionsByKeywords($keywords)
	{
		// TODO: possibly we should implement this hard dependency
		//       via dependance injection
		$Cities = new Searchdata_Model_Cities();
		
		$ids = array();
		$hit_keywords = array();
		foreach ($keywords as $keyword) {
			
			// TODO: we should add the posibility to recognize not only cities
			//       but also the regions and areas in the future
			$matchList = $Cities->getCitiesByTag($keyword);
			if (null === $matchList) {
				continue;
			}
		
			foreach($matchList as $city) {
				$ids[$city->city_id] = $city->city_id;
				$hit_keywords[] = $keyword;
			}
			
		}
		
		$res = array();
		$res['hit_keywords'] = $hit_keywords;
		$res['ids'] = implode(',', $ids);
		return $res;
	}

	public function getServicesByKeywords($keywords)
	{
		// TODO: possibly we should implement this hard dependency
		//       via dependancy injection
		$Services = new Joss_Crawler_Db_Synonyms();
		
		$ids = array();
		$hit_keywords = array();
		
		// the service recognizion algorithm will be very simple for now
		// 1. get all matches for each single keyword
		// 2. then count the amount of matches of the same type of service
		// 3. filter the incorrect matches using simple noise filtering algoright
		// 4. PROFIT!!
		
		$servicesPowerList = array();
		foreach ($keywords as $keyword) {
			
			// TODO: we can use Full Text search feature of MyISAM engine and MATCH() function
			//       to calculate the relevancy to make our search more acurrate
			//       and add typo protection
			$matchServices = $Services->searchServicesByTag($keyword);
			
			if (null === $matchServices) {
				continue;
			}

			foreach($matchServices as $serviceId => $serviceRate) {
				if (empty($servicesPowerList[$serviceId])) {
					$servicesPowerList[$serviceId] = $serviceRate;
				} else {
					$servicesPowerList[$serviceId] += $serviceRate;
				}
			}
			
			$hit_keywords[] = $keyword;
		}

		$servicesPower = 0;
		foreach($servicesPowerList as $serviceId => $serviceRate) {
			$servicesPower += $serviceRate;
		}
		
		// calculate trashhold amount
		// for now we croping the items that donate less than 20% to the total power
		$trashHold = $servicesPower / 100 * 20;
		
		$resultServices = array();
		foreach($servicesPowerList as $serviceId => $serviceRate) {
			if ($serviceRate >= $trashHold) {
				$resultServices[] = $serviceId;
			}
		}
		
		$res = array();
		$res['hit_keywords'] = $hit_keywords;
		$res['ids'] = implode(',', $resultServices);
		return $res;
	}
	
	/**
	 * Detects visitor location using the IP-to-City database.
	 */
	public function detectLocationByIp($ip)
	{
		$IpToCity = new Joss_Geolocation();
		
	}

}