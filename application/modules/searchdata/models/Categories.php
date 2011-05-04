<?php
/**
 * All services are grouped into categories
 */
class Searchdata_Model_Categories extends Zend_Db_Table_Abstract
{
    protected $_name = 'categories';
    
    /**
     * Retrieves all available categories
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getAllItems()
    {
    	return $this->fetchAll();
    }
}