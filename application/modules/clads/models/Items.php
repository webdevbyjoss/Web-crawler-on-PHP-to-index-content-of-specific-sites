<?php
/**
 * Table gataway to classified ads
 */
class Clads_Model_Items extends Zend_Db_Table_Abstract
{
	protected $_name = 'items';
	
	/**
	 * Retrieves all items from database
	 *
	 * @return Zend_Db_Table_Rowset
	 */
	public function getItems()
	{
		return $this->fetchAll();
	}
	
	/**
	 * Returns the list of item for particular user
	 *
	 * @param int $userId
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getItemsByUser($userId)
	{
		$select = $this->select();
		// FIXME: this is not very secure but anyway can be fixed in advance
		$select->where('user_id = ' . $userId);
		return $this->fetchAll($select);
	}
	
	/**
	 * Creates new item
	 *
	 * @param array $itemInfo
	 * @param array $services
	 * @param array $regions
	 */
	public function createItem($itemInfo, $services, $regions)
	{
		$row = $this->createRow($itemInfo);
		$itemId = $row->save();
		
		$ItemsServices = new Clads_Model_ItemsServices();
		foreach ($services as $service) {
			$ItemsServices->insert(array('item_id' => $itemId, 'service_id' => $service));
		}
		
		$ItemsRegions = new Clads_Model_ItemsRegions();
		foreach ($regions as $region) {
			$ItemsRegions->insert(array('item_id' => $itemId, 'region_id' => $region));
		}

		// automatically update search index
		$searchIndex = new Search_Model_Index();
		foreach ($services as $service) {
			foreach ($regions as $region) {
				$searchIndex->add(
					$itemId,
					$service,
					$region,
					$itemInfo['name'],
					$itemInfo['phone'],
					$itemInfo['description']
				);
			}
		}
		
	}
	
	/**
	 * Removes item from database
	 *
	 * @param int $id
	 * @return null
	 */
	public function deleteItem($id)
	{
		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		$this->delete($where);
	}
	
	/**
	 * Updates item information
	 *
	 * @param array $data
	 * @param int $id
	 * @return null
	 */
	public function updateInfo($data, $id)
	{
		//  we need to doo this additional security check to be sure that current user
		// is a true owner of this item record
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		
		$where = array(
			'id = ' . intval($id),
			'user_id = ' . $userInfo['id']
		);
		$res = $this->update($data, $where);
	}
}