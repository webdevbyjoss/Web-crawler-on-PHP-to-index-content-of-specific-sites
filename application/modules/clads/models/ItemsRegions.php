<?php
/**
 * Table gataway to classified ads
 */
class Clads_Model_ItemsRegions extends Zend_Db_Table_Abstract
{
	protected $_name = 'items_regions';
	
	/**
	 * Selects all regions for current item
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