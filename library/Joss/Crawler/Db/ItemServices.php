<?php
/**
 * Database table gateway to manage items services
 *
 * @name		Joss_Crawler_Db_ItemServices
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_ItemServices extends Zend_Db_Table_Abstract
{
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_item_services';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';

	/**
	 * Selects all services for current item grouped by region type
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
	
	/**
	 * Will remove all services relations with provided 'id'
	 *
	 * @param array $ids
	 * @return bool
	 */
	public function removeAll($ids)
	{
		$where = $this->getAdapter()->quoteInto('id IN (?)', implode(',', $ids));
		$this->delete($where);
	}
	
	/**
	 * Create item to service relation
	 *
	 * @param int $itemId
	 * @param int $serviceId
	 * @param string $tags
	 * @return int relation identifier
	 */
	public function addService($itemId, $serviceId, $tags)
	{
		$data = array(
			  'item_id'		=> $itemId
			, 'service_id'	=> $serviceId
			, 'description' => $tags
		);
		
		return $this->insert($data);
	}
}