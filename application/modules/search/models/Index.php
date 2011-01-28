<?php

class Search_Model_Index extends Zend_Db_Table_Abstract
{
	protected $_name = 'search_index_v1';
	
	/**
	 * Checks whether the item with the provided service ID and region ID
	 * is already presented in the index
	 *
	 * @param int $id
	 * @param int $serviceId
	 * @param int $regionId
	 * @return boolean
	 */
	public function itemExists($id, $serviceId, $regionId)
	{
		$select = $this->select();
		$select->where('item_id = ?', $id);
		$select->where('service_id = ?', $serviceId);
		$select->where('region_id = ?', $regionId);
		
		$res = $this->fetchAll($select);
		
		if (count($res) > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * add new information records to search index
	 *
	 * @param int $id
	 * @param int $serviceId
	 * @param int $regionId
	 * @param string $url
	 * @param string $title
	 * @param string $description
	 * @return void
	 */
	public function add($id, $serviceId, $regionId, $url, $title, $description)
	{
		$indexRecord = $this->fetchNew();
		
		$indexRecord->item_id = $id;
		$indexRecord->service_id = $serviceId;
		$indexRecord->region_id = $regionId;
		$indexRecord->url = $url;
		$indexRecord->title = $title;
		$indexRecord->description = $description;
		
		$indexRecord->save();
	}
	
	/**
	 * update index
	 *
	 * @param int $id
	 * @param float $index
	 * @return void
	 */
	public function updateIndex($id, $index)
	{
		$data = array(
			'informational_index' => $index
		);
		
		$this->update($data, 'item_id = ' . $id);
	}
	
	/**
	 * Returns pagination adapter to paginate thrue data
	 *
	 * @param array $serviceIds
	 * @param array $regionIds
	 * @return Zend_Paginator_Adapter_Interface
	 */
	public function getDataPagenation($serviceIds, $regionIds)
	{
		$select = $this->select();

		$select->where('service_id IN (' . implode(',', $serviceIds) . ')');
		$select->where('region_id IN (' . implode(',', $regionIds) . ')');
		$select->order('informational_index DESC');
		$select->group('item_id');
		
		return new Zend_Paginator_Adapter_DbSelect($select);
	}
}