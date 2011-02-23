<?php
/**
 * Cities
 *
 * is_region_center - indicates if current city is an administrative center of regions
 * name - the UTF-8 name of the city in russian
 * name_uk - the UTF-8 name of the city in ukrainian
 * uniq_name - unique name in russian, in case we have couple cities with the same name we will set the unique name here that will include the administrative regions
 * uniq_name_uk - unique name in ukrainian
 * seo_name - the russian varuan of SEO-friendly URL
 * seo_name_uk - the ukrainian varuan of SEO-friendly URL
 */
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

		$select = $this->select();
		
    	if (!is_array($regionId)) {
    		$select->where('region_id = ?', (int) $regionId);
    	} else {
    		$select->where('region_id IN (' . implode(',', $regionId) . ')');
    	}

    	$select->order(array('is_region_center DESC'));
    	
    	return $this->fetchAll($select);
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

    /**
     * Returns city by its uniques SEO name
     *
     * @param string $seoName
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getCityBySeoName($seoName)
    {
    	$select = $this->select();
    	
    	$select->where('seo_name = ?', $seoName);
    	$select->orWhere('seo_name_uk = ?', $seoName);
    	
    	$data = $this->fetchAll($select);

    	if (0 == count($data)) {
    		return null;
    	}
    	
    	return $data->current();
    }
    
}