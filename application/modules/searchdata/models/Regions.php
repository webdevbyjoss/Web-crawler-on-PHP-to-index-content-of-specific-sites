<?php

class Searchdata_Model_Regions extends Zend_Db_Table_Abstract
{
    protected $_name = 'region';
    
    public function getItems($countryId = 1)
    {
        return $this->fetchAll(array('country_id = ' . (int) $countryId));
    }
}