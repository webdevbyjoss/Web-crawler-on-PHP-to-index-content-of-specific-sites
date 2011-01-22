<?php
/**
 * Database table gateway to manage synonyms relations to services
 *
 * @name		Joss_Crawler_Db_SynonymsServices
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_SynonymsServices extends Zend_Db_Table_Abstract
{
	const ENTITY_TABLE = 'crawl_data_synonyms';
	
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_data_synonyms_services';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Returns the list of relations for provided synonym IDs
	 *
	 * @param array $id
	 */
	public function getRelationsByIds($Ids)
	{
		if (is_array($Ids)) {
			$Ids = implode(',', $Ids);
		}
		
		$select = $this->select();
		
		// FIXME: this is not very secure, so possibly we will need to fix this
		$select->where('synonym_id IN (' . $Ids .')');

		return $this->fetchAll($select);
	}
	
	/**
	 * Extracts the list of synonyms by provided service Id
	 *
	 * @param int $Id
	 * @return Zend_Db_Table_Rowset
	 */
	public function getSynonymsByServiceId($Id)
	{
		$select = $this->select();
		$select->join(array('s' => self::ENTITY_TABLE), 'synonym_id = s.id', array('title'));
		$select->where('service_id = ?', $Id);
		
		var_dump($select->__toString());
		die();
		
		return $this->fetchAll($select);
	}

}