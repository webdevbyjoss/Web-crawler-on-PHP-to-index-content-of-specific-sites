<?php

class Searchdata_Model_Cities extends Zend_Db_Table_Abstract
{
    protected $_name = 'city';
    
    public function getItems($regionId)
    {
        return $this->fetchAll(array('region_id = ' . (int) $regionId));
    }
}