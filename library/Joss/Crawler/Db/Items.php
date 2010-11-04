<?php
/**
 * Database table gateway to manage items crawled from the web and stored in database
 *
 * @name		Joss_Crawler_Db_Item
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_Items extends Zend_Db_Table_Abstract
{
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_item';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Creates or updates advertisement record in database
	 *
	 * it accepts the array that has the following structure
	 *
	 * 'id'		=> '123123'
	 *
	 * 'adapter' => 'Adaptername'
	 *
	 * 'url'	=> 'http://www.google.com'
	 *
	 * 'services'	=> array(
	 * 					0 => array('id' => '123', 'tags' => array('tag1', 'tag2', 'tag3', ...))
	 * 					1 => array('id' => '234', 'tags' => array('tag1', 'tag2', 'tag3', ...))
	 * 					...
	 * 			       )
	 *
	 * 'regions'	=> array(
	 * 					'tags' => array('tag1', 'tag2', 'tag3', ...)
	 * 					'cities' => array('123', '124', ...)
	 * 					'regions' => array('123', '124', ...)
	 * 					'countries' => array('123', '124', ...)
	 * 				 )
	 *
	 * 'info'		=> array(
	 * 					'title'	=> '',
	 * 					'description'	=> '',
	 * 					'contacts'	=> array(
	 * 									0 => array('contact_name' => 'John', 'phone' => '+88888888888888'),
	 * 									1 => array('contact_name' => 'John', 'phone' => '+88888888888888'),
	 * 									...
	 * 								)
	 * 				   )
	 * TODO: this method SHOULD be refactored ASAP as it has horrable design
	 *
	 * @param array $advert
	 * @return integer - the ads ID
	 */
	public function add($advert)
	{
		$Adapters = new Joss_Crawler_Db_Adapters();
		$adapterId = $Adapters->getIdByName($advert['adapter']);

		// extract existing or create new item
		$isCreated = false;
		$Item = $this->getItemByAdapterId($advert['id'], $adapterId);

		if (empty($Item)) {
			$Item = $this->fetchNew();
			$isCreated = true;
		}
		
		$Item->title = $advert['info']['title'];
		$Item->description = $advert['info']['description'];
		$Item->url = $advert['url'];
		$Item->adapter_id = $adapterId;
		$Item->adapter_specific_id = $advert['id'];
		$primaryKey = $Item->save();
		
		// process item contacts
		// update contacts information for each phone number
		// or mark old phone numbers as outdated
		// TODO: this peace of code should be refactored in the future
		$Contacts = new Joss_Crawler_Db_ItemContacts();
		$ItemContacts = $Contacts->getContactsByItemId($primaryKey);
		
		if (!empty($ItemContacts)) {

			$phoneNumbers = array();
			foreach ($ItemContacts as $contact) {
				$phoneNumbers[] = $contact->phone;
			}
			
			$nonOutdated = array();
			foreach ($advert['info']['contacts'] as $contact) {
				
				if (!in_array($contact['phone'], $phoneNumbers)) {
					$Contacts->createContact($primaryKey, $contact['contact_name'], $contact['phone']);
				} else {
					$nonOutdated[] = $phoneNumbers;
				}
	
			}
			
			foreach ($ItemContacts as $contact) {
				if (!in_array($contact->phone, $nonOutdated)) {
					$contact->outdated = Joss_Crawler_Db_ItemContacts::STATUS_OUTDATED;
					$contact->save();
				}
			}
				
		} else {
			
			foreach ($advert['info']['contacts'] as $contact) {
				$Contacts->createContact($primaryKey, $contact['contact_name'], $contact['phone']);
			}
			
		}
		
		
		// get regions from database to compare with updated values
		$ItemRegions = new Joss_Crawler_Db_ItemRegions();
		$RegionsData = $ItemRegions->getDataById($primaryKey);
		
		if (!empty($RegionsData)) {
			
			// lets build the list of cities that sould not be touched and ones that should be removed
			$existentCities = array(); // we will store "item_id" here
			$removeCities = array();   // we will store "id" here
			 
			// if no any cities then we can remove all cities
			if (empty($advert['regions']['cities'])) {
				
				foreach ($RegionsData[Joss_Crawler_Db_ItemRegions::TYPE_CITY] as $city) {
					$removeCities[] = $city['id'];
				}
				
			} else {
				
				foreach ($RegionsData[Joss_Crawler_Db_ItemRegions::TYPE_CITY] as $city) {
					if (!in_array($city['region_id'], $advert['regions']['cities'])) {
						$removeCities[] = $city['id'];
					} else {
						$existentCities[] = $city['region_id'];
					}
				}
	
			}
	
	
			// lets build the list of regions that sould not be touched and ones that should be removed
			$existentRegions = array(); // we will store "item_id" here
			$removeRegions = array();   // we will store "id" here
			
			if (empty($advert['regions']['regions'])) {
	
				foreach ($RegionsData[Joss_Crawler_Db_ItemRegions::TYPE_DISTRICT] as $district) {
					$removeRegions[] = $district['id'];
				}
				
			} else {
	
				foreach ($RegionsData[Joss_Crawler_Db_ItemRegions::TYPE_DISTRICT] as $district) {
					if (!in_array($district['region_id'], $advert['regions']['regions'])) {
						$removeRegions[] = $district['id'];
					} else {
						$existentRegions[] = $district['region_id'];
					}
				}
	
			}
		
			
			// lets build the list of countries that sould not be touched and ones that should be removed
			$existentCountries = array(); // we will store "item_id" here
			$removeCountries = array();   // we will store "id" here
	
			// no any countries so we chould remove all countries
			if (empty($advert['regions']['countries'])) {
				
				foreach ($RegionsData[Joss_Crawler_Db_ItemRegions::TYPE_COUNTRY] as $country) {
					$removeCountries[] = $country['id'];
				}
				
			} else {
	
				foreach ($RegionsData[Joss_Crawler_Db_ItemRegions::TYPE_COUNTRY] as $country) {
					if (!in_array($country['region_id'], $advert['regions']['countries'])) {
						$removeCountries[] = $country['id'];
					} else {
						$existentCountries[] = $country['region_id'];
					}
				}
			}
			
			$removeItems = array_merge($removeCountries, $removeRegions, $removeCities);
			$ItemRegions->removeAll($removeItems);
			
		} else {
			$existentCountries = array();
		}
		
		// lets add new cities/regions/countries
		if (!empty($advert['regions']['cities'])) {
			foreach ($advert['regions']['cities'] as $city) {
				if (!in_array($city, $existentCountries)) {
					$ItemRegions->addCity($primaryKey, $city, $advert['regions']['tags']);
				}
			}
		}

		if (!empty($advert['regions']['regions'])) {
			foreach ($advert['regions']['regions'] as $district) {
				if (!in_array($district, $existentCountries)) {
					$ItemRegions->addRegion($primaryKey, $district, $advert['regions']['tags']);
				}
			}
		}

		if (!empty($advert['regions']['countries'])) {
			foreach ($advert['regions']['countries'] as $country) {
				if (!in_array($country, $existentCountries)) {
					$ItemRegions->addCountry($primaryKey, $country, $advert['regions']['tags']);
				}
			}
		}
		
		// process services
		$ItemServices = new Joss_Crawler_Db_ItemServices();
		$ServicesData = $ItemServices->getDataById($primaryKey);
		
		if (empty($ServicesData)) {
			
			foreach ($advert['services'] as $service) {
				$ItemServices->addService($primaryKey, $service['id'], $service['tags']);
			}
			
		} else {
			
			$availableServices = array();
			foreach ($ServicesData as $service) {
				$availableServices[] = $service['service_id'];
			}
			
			$newServices = array();
			foreach ($advert['services'] as $service) {
				$newServices[] = $service['id'];
				$newServicesTags[$service['id']] = $service['tags'];
			}
			
			$removeServices = array();
			foreach ($availableServices as $service) {
				if (!in_array($service, $newServices)) {
					$removeServices[] =  $service;
				}
			}
			
			$ItemServices->removeAll($removeServices);
			
			foreach ($newServices as $service) {
				if (!in_array($service, $availableServices)) {
					$ItemServices->addService($primaryKey, $service, $newServicesTags[$service]);
				}
			}

		}
		
		// congratulations! we are finished!
		return true;
	}
	
	protected function getItemByAdapterId($id, $adapterId)
	{
		$select = $this->select();
		$select->where('adapter_specific_id = ?', $id)
			   ->where('adapter_id = ?', $adapterId);
		
		return $this->fetchRow($select);
	}

}