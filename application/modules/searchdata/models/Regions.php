<?php

class Searchdata_Model_Regions extends Zend_Db_Table_Abstract
{
    protected $_name = 'region';
    
    public function getItems($countryId)
    {
        return $this->fetchAll(array('country_id = ' . (int) $countryId));
    }
}