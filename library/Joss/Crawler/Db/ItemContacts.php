<?php
/**
 * Database table gateway to manage crawler item contacts
 *
 * @name		Joss_Crawler_Db_ItemContacts
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_ItemContacts extends Zend_Db_Table_Abstract
{
	/**
	 * Contacts statuses
	 */
	const STATUS_ACTUAL = 0;
	const STATUS_OUTDATED = 1;
	
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_item_contacts';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Used to create a contact
	 *
	 * @param int $itemId
	 * @param string $contactName
	 * @param string $phonenumber
	 * @return bool
	 */
	public function createContact($itemId, $contactName, $phonenumber)
	{
		$Contact = $this->fetchNew();
		$Contact->item_id  = $itemId;
		$Contact->name = $contactName;
		$Contact->phone = $phonenumber;
		$Contact->save();
	}

	/**
	 * Returns the list of contacts related to the specified ID
	 *
	 * @param int $id
	 * @return array
	 */
	public function getContactsByItemId($id)
	{
		$select = $this->select();
		$select->where('item_id = ?', $id);
		return $this->fetchAll($select);
	}
}