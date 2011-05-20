<?php

class Search_Model_Index extends Zend_Db_Table
{
	/**
	 * Maximum results returned by API calls
	 */
	const MAX_RESULTS = 25;
	
	protected $_name = 'search_index_v2';
	
	/**
	 * Checks whether the item with the provided service ID and region ID
	 * is already presented in the index
	 *
	 * @param int $id
	 * @param int $serviceId
	 * @param int $regionId
	 * @return boolean
	 */
	public function itemExists($id, $serviceId, $regionId)
	{
		$select = $this->select();
		$select->where('item_id = ?', $id);
		$select->where('service_id = ?', $serviceId);
		$select->where('region_id = ?', $regionId);
		
		$res = $this->fetchAll($select);
		
		if (count($res) > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * add new information records to search index
	 *
	 * @param int $id
	 * @param int $serviceId
	 * @param int $regionId
	 * @param string $name
	 * @param string $phone
	 * @param string $description
	 * @return void
	 */
	public function add($id, $serviceId, $regionId, $name, $phone, $description)
	{
		$indexRecord = $this->fetchNew();
		
		$indexRecord->item_id = $id;
		$indexRecord->service_id = $serviceId;
		$indexRecord->region_id = $regionId;
		$indexRecord->name = $name;
		$indexRecord->phone = $phone;
		$indexRecord->description = $description;
		
		$indexRecord->save();
	}

	/**
	 * updates records in search index
	 *
	 * @param int $id
	 * @param int $serviceId
	 * @param int $regionId
	 * @param string $name
	 * @param string $phone
	 * @param string $description
	 * @return void
	 */
	public function updateIndex($id, $serviceId, $regionId, $name, $phone, $description)
	{
		$data = array(
			'item_id' => $id,
			'service_id' => $serviceId,
			'region_id' => $regionId,
			'name' => $name,
			'phone' => $phone,
			'description' => $description,
		);
		
		$where = array(
			'item_id = ' . $id,
			'service_id = ' . $serviceId,
			'region_id = ' . $regionId,
		);
		
		$this->update($data, $where);
	}
	
	/**
	 * update index
	 *
	 * @param int $id
	 * @param float $index
	 * @return void
	 */
	public function updateInformationIndex($id, $index)
	{
		$data = array(
			'informational_index' => $index
		);
		
		$this->update($data, 'item_id = ' . $id);
	}
	
	/**
	 * Returns pagination adapter to paginate thrue data
	 *
	 * @param array $serviceIds
	 * @param array $regionIds
	 * @return Zend_Paginator_Adapter_Interface
	 */
	public function getDataPagenation($serviceIds, $regionIds)
	{
		$select = $this->select();

		$select->where('service_id IN (' . implode(',', $serviceIds) . ')');
		$select->where('region_id IN (' . implode(',', $regionIds) . ')');
		$select->order('informational_index DESC');
		$select->group('item_id');
		
		return new Zend_Paginator_Adapter_DbSelect($select);
	}
	
	/**
	 * Retrieves search results for coordinates
	 *
	 * @param int $catId
	 * @param float $lat
	 * @param float $lng
	 * @throws Search_Model_IndexException
	 * @return Zend_Db_Table_Rowset
	 */
	public function geoSearch($catId, $lat, $lng)
	{
		// if coordinates are passed then lets try to recognize the city
		if (empty($lng) || empty($lat) ) {
			throw new Search_Model_IndexException('Coordinates are empty. Received lat:' . $lat . 'lng:' . $lng );
		}
		
		// try to recognize city
		$Cities = new Searchdata_Model_Cities();
		$currentCity = $Cities->getCityByCoords($lng, $lat);
		
		// process the situation if city wasn't recognized using city-boundaries algorythm
		if (empty($currentCity)) {
			
			// try to find the closest available city and assign report to that city
			$relatedCities = $Cities->getRelatedCities($lat, $lng, 50); // max search range is 50km
			$currentCity = current($relatedCities);
			
			if (empty($currentCity['city_id'])) {
				throw new Search_Model_IndexException('Specified city is not supported. Received lat:' . $lat . 'lng:' . $lng );
			}
			
			// report exceptional situation to administrator
			mail(ADMIN_EMAIL, '[NASH-MASTER] City-boundaries algorythm error',
			'City not found using city-boundaries algorythm. Received lat:' . $lat . 'lng:' . $lng
			. '. Assigned to "' . $currentCity['name_uk'] . '", city_id = "' . $currentCity['city_id'] . '"');
			
			$cityId = $currentCity['city_id'];
			$cityTitle = $currentCity['name_uk'];
			
		} else {
			$cityId = $currentCity->city_id;
			$cityTitle = $currentCity->name_uk;
		}
		
		$select = $this->select();
		
		$select->where('service_id IN (' . $catId . ')');
		$select->where('region_id IN (' . $cityId . ')');
		$select->order('informational_index DESC');
		$select->group('item_id');
		$select->limit(self::MAX_RESULTS);
		
		$return['city_title'] = $cityTitle;
		$return['data'] = $this->fetchAll($select);
		
		return $return;
	}
}