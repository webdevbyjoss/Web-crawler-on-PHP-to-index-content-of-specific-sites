<?php
/**
 * Database table gateway to manage items regions
 *
 * @name		Joss_Crawler_Db_ItemRegions
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_ItemRegions extends Zend_Db_Table_Abstract
{
	/**
	 * Different types of redions
	 */
	const TYPE_CITY = 0;
	const TYPE_DISTRICT = 1;
	const TYPE_COUNTRY = 2;
	
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_item_regions';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';

	/**
	 * Selects all regions for current item grouped by region type
	 *
	 * @param int $id
	 * @return array
	 */
	public function getDataById($id)
	{
		$select = $this->select();
		$select->where('item_id = ?', $id);
		
		$allItems = $this->fetchAll($select);
		
		$data = array();
		foreach ($allItems as $v) {
			 $data[$v['item_type']][] = $v;
		}

		return $data;
	}
	
	/**
	 * Will remove all regions relations with provided 'id'
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
	 * Crates the relation between item and city
	 *
	 * @param int $itemId
	 * @param int $cityId
	 * @return null
	 */
	public function addCity($itemId, $regionId, $tags = '')
	{
		$this->addRelation($itemId, $regionId, self::TYPE_CITY, $tags);
	}

	/**
	 * Crates the relation between item and region
	 *
	 * @param int $itemId
	 * @param int $cityId
	 * @return null
	 */
	public function addRegion($itemId, $regionId, $tags = '')
	{
		$this->addRelation($itemId, $regionId, self::TYPE_DISTRICT, $tags);
	}
	
	/**
	 * Crates the relation between item and country
	 *
	 * @param int $itemId
	 * @param int $cityId
	 * @return null
	 */
	public function addCountry($itemId, $regionId, $tags = '')
	{
		$this->addRelation($itemId, $regionId, self::TYPE_COUNTRY, $tags);
	}

	/**
	 * Crates the relation between item and region
	 *
	 * @param int $itemId
	 * @param int $cityId
	 * @param int $type
	 * @return int relation identifier
	 */
	public function addRelation($itemId, $regionId, $type, $tags = '')
	{
		$data = array(
			  'item_id'		=> $itemId
			, 'item_type'	=> $type
			, 'region_id'	=> $regionId
			, 'description' => $tags
		);
		
		return $this->insert($data);
	}

}