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
    
}