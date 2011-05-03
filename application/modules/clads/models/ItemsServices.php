<?php
/**
 * Table gataway to classified ads
 */
class Clads_Model_ItemsServices extends Zend_Db_Table_Abstract
{
	protected $_name = 'items_services';
	
	/**
	 * Selects all services for current item
	 *
	 * @param int $id
	 * @return array
	 */
	public function getDataById($id)
	{
		$select = $this->select();
		$select->where('item_id = ?', $id);
		return $this->fetchAll($select);
	}
}