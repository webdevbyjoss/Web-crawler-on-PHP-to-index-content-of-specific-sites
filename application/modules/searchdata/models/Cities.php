<?php

class Searchdata_Model_Cities extends Zend_Db_Table_Abstract
{
    protected $_name = 'city';
    
    /**
     * Retrieve the list of cities in the specified region(s)
     *
     * @param mixed $regionId
     * @return Zend_Db_Table_Rowset
     */
    public function getItems($regionId = null)
    {
    	if (null === $regionId) {
    		return $this->fetchAll();
    	}
    	
    	if (!is_array($regionId)) {
    		return $this->fetchAll(array('region_id = ' . (int) $regionId));
    	}
    	
    	return $this->fetchAll(array('region_id IN (' . implode(',', $regionId) . ')'));
    }
    
    /**
     * Get the list of cities by string tag (city name)
     *
     * @param string $tag
     * @return Zend_Db_Table_Rowset
     */
    public function getCitiesByTag($tag)
    {
    	$where = array('name LIKE "' . $tag . '" OR name_uk LIKE "' . $tag . '"');
    	$data = $this->fetchAll($where);

    	if (0 == count($data)) {
    		return null;
    	}
    	
    	return $data;
    }
    
    /**
     * get city by coords
     *
     * long = 25.6
     * lat = 49.56
     *
     * return: Ternopil
     * @param float $long
     * @param float $lat
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getCityByCoords($long, $lat)
    {
    	$where = array('((bound_southwest_longitude <  ' . (float) $long . ') AND (bound_northeast_longitude > ' . (float) $long .'))
			AND ((bound_southwest_latitude < ' . (float) $lat . ') AND (bound_northeast_latitude > ' . (float) $lat . '))');

    	$data = $this->fetchAll($where);
    	
    	if (0 == count($data)) {
    		return null;
    	}
    	
    	return $data->current();
    }

}