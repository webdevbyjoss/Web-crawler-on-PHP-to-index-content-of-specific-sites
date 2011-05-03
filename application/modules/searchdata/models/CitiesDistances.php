<?php

class Searchdata_Model_CitiesDistances extends Zend_Db_Table_Abstract
{
	protected $_name = 'city_distance';
	
	/**
	 * Returns the list of small towns in 50km area
	 *
	 * @param int $cityId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getNearCities($cityId, $distance = 60, $limit = 11)
	{
		$select = $this->select();
		$select->where('parent_city_id = ?', $cityId);
		$select->where('is_region_center = ' . Searchdata_Model_Cities::NOT_REGION_CENTER);
		$select->where('distance <= ?', $distance);
		$select->limit($limit);
		
		return $this->fetchAll($select);
	}

	/**
	 * Returns the list of cities in 300km area
	 *
	 * @param int $cityId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getLargeCities($cityId, $distance = 250, $limit = 5)
	{
		$select = $this->select();
		$select->where('parent_city_id = ?', $cityId);
		$select->where('is_region_center > ' . Searchdata_Model_Cities::NOT_REGION_CENTER);
		$select->where('distance <= ?', $distance);
		$select->limit($limit);
		
		return $this->fetchAll($select);
	}
}