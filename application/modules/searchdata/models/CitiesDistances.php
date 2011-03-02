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
	public function getNearCities($cityId, $distance = 100)
	{
		$select = $this->select();
		$select->where('parent_city_id = ?', $cityId);
		$select->where('is_region_center = 0');
		$select->where('distance <= ?', $distance);
		$select->limit(11);
		
		return $this->fetchAll($select);
	}

	/**
	 * Returns the list of cities in 300km area
	 *
	 * @param int $cityId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getLargeCities($cityId, $distance = 300)
	{
		$select = $this->select();
		$select->where('parent_city_id = ?', $cityId);
		$select->where('is_region_center > 0');
		$select->where('distance <= ?', $distance);
		$select->limit(5);
		
		return $this->fetchAll($select);
	}
}