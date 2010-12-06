<?php

class Searchdata_Model_Cities extends Zend_Db_Table_Abstract
{
    protected $_name = 'city';
    
    public function getItems($regionId = null)
    {
    	if (null !== $regionId) {
    		return $this->fetchAll(array('region_id = ' . (int) $regionId));
    	}
        
    	return $this->fetchAll();
    }
    
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