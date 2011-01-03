<?php

class Searchdata_Model_Countries extends Zend_Db_Table_Abstract
{
    protected $_name = 'country';
    
    public function getItems()
    {
        return $this->fetchAll(array('display = 1'));
    }
    
}