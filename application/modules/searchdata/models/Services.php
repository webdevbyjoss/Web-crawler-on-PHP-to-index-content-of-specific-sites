<?php

class Searchdata_Model_Services extends Zend_Db_Table_Abstract
{
    protected $_name = 'services';
    
    public function getItems($id = null)
    {
        if (null == $id) {
            return $this->fetchAll(array('ISNULL(parent_id)'));
        }
        
        return $this->fetchAll(array('parent_id = ' . (int) $id ));
    }
    
    public function getById($id)
    {
    	return $this->find($id)->current();
    }
    
    
    public function getAllItems()
    {
    	return $this->fetchAll();
    }
}