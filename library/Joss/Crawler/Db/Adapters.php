<?php
/**
 * Database table gateway to manage crawler adapters
 *
 * @name		Joss_Crawler_Db_Adapter
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_Adapters extends Zend_Db_Table_Abstract
{
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_adapter';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Extracts adapter ID from the name
	 *
	 * @param string $name
	 * @return int datapter ID
	 */
	public function getIdByName($name)
	{
		// $sql = 'SELECT id FROM ' . $this->_name . ' WHERE ';
		$select = $this->select()->where('adapter = ?', $name);
		$adapter = $this->fetchRow($select);
		
		return $adapter->id;
	}
}