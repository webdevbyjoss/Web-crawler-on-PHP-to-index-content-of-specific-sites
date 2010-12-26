<?php
/**
 * This controller will process all the jobs
 */
class Crawler_TestController extends Zend_Controller_Action
{
	public function init()
	{
		// we 100% that actions from this controller will be
		// called from CLI so we disabling layout and auto output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function indexAction()
	{
		// $currentUrl = 'http://emarket.te.ua/construction/remont-kvartir_3741711.html';
		// $currentUrl = 'http://emarket.te.ua/construction/remont-kvartir_2071097.html';
		// $currentUrl = 'http://emarket.te.ua/construction/dveri-vhidni-mizhkimnatni_5247451.html'; // no service tags
		// $currentUrl = 'http://emarket.ks.ua/construction/metallokonstruktsii-i-metalloizdeliya-kovka_3952853.html';
		// $currentUrl = 'http://emarket.ks.ua/construction/metalloizdeliya_4137577.html';
		$currentUrl = 'http://emarket.te.ua/construction/sayding_4246013.html';

		$pageContent = file_get_contents($currentUrl);
		// $pageContent = iconv('CP1251', 'UTF-8', $pageContent);
		
		$Adapter = new Joss_Crawler_Adapter_Emarketua();
		$Adapter->loadPage($currentUrl, $pageContent);
		
		$data = $Adapter->getData();
		
		var_dump($data);
	}
	
	public function synAction()
	{
		$Synonyms = new Joss_Crawler_Db_Synonyms();
		
		$servicesMap = $Synonyms->getTaxonomyRelations();
		$servicesDescriptionMap = $Synonyms->getFullTextRelations();
		
		var_dump($servicesMap);
		var_dump($servicesDescriptionMap);
	}
	
	public function geoAction()
	{
		//$data = Joss_Geolocation_Hostip::getCityByIp('1.6.9.0'); // 80.243.144.3
		//http://maps.googleapis.com/maps/api/geocode/xml?latlng=49.55,25.5833&sensor=false
		$geoAPI = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=';
		
		$client = new Zend_Http_Client();
		
		// init table gateways
		$Cities = new Searchdata_Model_Cities();
		$Regiones = new Searchdata_Model_Regions();
		$Countries = new Searchdata_Model_Countries();
		
		$regionTitle = array();
		$countryTitle = array();
		
		// $citiesRowset = $Cities->getItems();
		$citiesRowset = $Cities->getItems(array(10607,10633));
		$i = 0;
		foreach ($citiesRowset as $city) {
			
			if (!empty($city->latitude)) {
				continue;
			}
			
			// get country
			if (empty($countryTitle[$city->country_id])) {
				$c = $Countries->find($city->country_id)->current();
				$country = ($c->name_uk == 'Україна') ? 'Україна' :  $c->name;
				$countryTitle[$city->country_id] = $country;
			} else {
				$country = $countryTitle[$city->country_id];
			}
			
			// get region
			if (empty($regionTitle[$city->region_id])) {
				$r = $Regiones->find($city->region_id)->current();
				$region = $r->name_uk ? $r->name_uk . ' область' : $r->name;
				$regionTitle[$city->region_id] = $region;
			} else {
				$region = $regionTitle[$city->region_id];
			}
			
			$cityTitle = $city->name_uk ? $city->name_uk : $city->name;
			
			// prepare address string
			$address = $cityTitle . ', ' . $region . ', ' . $country;

			// API call
			$client->setUri($geoAPI . urlencode($address));
			try {
				$responce = $client->request();
				
			} catch (Zend_Http_Client_Adapter_Exception $e) {
				echo "\nERROR:" . $address;
				continue;
			}
			
			$data = json_decode($responce->getBody());
			
			// process data
			// var_dump($data);
			if (!in_array($data->status, array('OK', 'ZERO_RESULTS'))) {
				// we have a problem with something
				die($data->status);
			}
			
			// get coordinates
			$cityGdata = array();
			foreach ($data->results as $location) {
				if (in_array('locality', $location->types) && in_array('political', $location->types)) {
					$cityGdata[] = $location->geometry;
				}
			}

			// lets ignore other matches for now
			$matchCity = current($cityGdata);
			
			if (empty($matchCity)) {
				continue;
			}
			
			// save data into database
			$city->latitude = $matchCity->location->lat;
			$city->longitude = $matchCity->location->lng;
			$city->bound_southwest_latitude = $matchCity->bounds->southwest->lat;
			$city->bound_southwest_longitude = $matchCity->bounds->southwest->lng;
			$city->bound_northeast_latitude = $matchCity->bounds->northeast->lat;
			$city->bound_northeast_longitude = $matchCity->bounds->northeast->lng;
			$city->save();
			
			// random timeout for google API
			sleep(rand(0,3));
		}

	}
	
	public function searchAction()
	{
		$options = array(
			'keywords' => 'укладка паркету 333, тернопіль іва фі=- тернополь -  фівафіва %:?№%;""'
		);
		
		$SearchForm = new Nashmaster_SearchForm($options);
		$SearchForm->setKeywords($options['keywords']);
		
		$data = $SearchForm->getAdapter();
	}
	
	public function translateAction()
	{
		$ruUrl = 'http://ru.wikipedia.org/wiki/';
		$ukUrl = 'http://uk.wikipedia.org/wiki/';
		
		$Client = new Zend_Http_Client();
		$Client->setConfig(
			array(
				'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
				'timeout' => '30',
				'keepalive' => true,
				'encodecookies' => true,
			)
		);
		
		// 1. Get cities from the database
		$Cities = new Searchdata_Model_Cities();
		$citiesRowset = $Cities->getItems(array(9909,9943,9964,10002,10061,10094,10111,10133,10165,10201,10227,10259,10318,10354,10373,10407,10437,10455,10480,10504,10535,10559,10583,10607,10633));
		echo "\nReceived the list of cities: " . count($citiesRowset);
		
		foreach ($citiesRowset as $city) {

			if (!empty($city->name_uk)) {
				continue;
			}
			
			$title = '';
			$Client->setUri($ruUrl . urlencode($city->name));
			
			try {
				
				$response = $Client->request();
				
			} catch (Zend_Http_Client_Adapter_Exception $e) {

				// we need a pause during 1 second between calls to avoid web-server overload
				sleep(1);
				continue;
			}
			
			$htmlData = $response->getBody();
			$pattern = '@<li class="interwiki-uk"><a href="http://uk\.wikipedia\.org/wiki/(.*)" title="(.*)">Українська</a></li>@u';
			preg_match($pattern, $htmlData, $matchData);
			
			if (empty($matchData[1])) {
				continue;
			}

			$title = urldecode($matchData[1]);
			if (false !== strpos($title, '_(')) {
				$title = preg_replace('/_\(.*\)/u', '', $title);
			}
			
			if (false !== strpos($title, '_')) {
				continue;
			}

			$city->name_uk = $title;
			$city->save();
		}

	}
	
	public function resetidsAction()
	{
		$Cities = new Searchdata_Model_Cities();
		$Regiones = new Searchdata_Model_Regions();
		$Countries = new Searchdata_Model_Countries();
		
		$Cities_old = new Searchdata_Model_Citiesold();
		$Regiones_old = new Searchdata_Model_Regionsold();
		$Countries_old = new Searchdata_Model_Countriesold();
		
		// trancate tables
		$db = $Cities->getAdapter();
		$db->query("TRUNCATE TABLE country");
		$db->query("TRUNCATE TABLE region");
		$db->query("TRUNCATE TABLE city");
		
		// recursively fill tables with new data
		$data = array(
			'name' => 'Украина',
			'name_uk' => 'Україна',
			'old_id' => '9908'
		);

		$countryId = $Countries->insert($data);
		
		$resions = $Regiones_old->getItems(9908);
		foreach ($resions as $reg) {
			
			$data = array(
				'country_id' => $countryId,
				'name' => $reg->name,
				'name_uk' => $reg->name_uk,
				'old_id' => $reg->region_id,
			);
			$resionId = $Regiones->insert($data);
			
			// process cities of this region
			$cities = $Cities_old->getItems($reg->region_id);
			
			foreach ($cities as $city) {
				
				$data = array(
					'country_id' => $countryId,
					'region_id' => $resionId,
					'is_region_center' => $city->is_region_center,
					'name' => $city->name,
					'name_uk' => $city->name_uk,
				  	'longitude' => $city->longitude,
					'latitude' => $city->latitude,
					'bound_southwest_longitude' => $city->bound_southwest_longitude,
					'bound_southwest_latitude' => $city->bound_southwest_latitude,
					'bound_northeast_longitude' => $city->bound_northeast_longitude,
					'bound_northeast_latitude' => $city->bound_northeast_latitude,
					'old_id' => $city->city_id
				);
				
				$Cities->insert($data);
			}
			
		}
		
	}

}


class Searchdata_Model_Citiesold extends Zend_Db_Table_Abstract {
	protected $_name = 'city_old';
    public function getItems($regionId = null) {
    	if (null === $regionId) {
    		return $this->fetchAll();
    	}
    	
    	if (!is_array($regionId)) {
    		return $this->fetchAll(array('region_id = ' . (int) $regionId));
    	}
    	
    	return $this->fetchAll(array('region_id IN (' . implode(',', $regionId) . ')'));
    }
}
class Searchdata_Model_Regionsold extends Zend_Db_Table_Abstract {
	protected $_name = 'region_old';
    public function getItems($countryId) {
        return $this->fetchAll(array('country_id = ' . (int) $countryId));
    }
}
class Searchdata_Model_Countriesold extends Zend_Db_Table_Abstract {
	protected $_name = 'country_old';
}