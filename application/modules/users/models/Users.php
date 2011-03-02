<?php

class Users_Model_Users extends Zend_Db_Table_Abstract
{
	/**
	 * The name of the database table
	 *
	 * @var string
	 */
	protected $_name = 'users';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Retrieves user information for provided username
	 *
	 * @param string $username
	 * @return Zend_Db_Table_Row user information
	 */
	public function getUserInfo($username)
	{
		$select = $this->select();
		$select->where('username = ?', $username);
		
		return $this->fetchRow($select);
	}
	
	public function subscribeEmail($email)
	{
		$select = $this->select();
		$select->where('email = ?', $email);
		
		$user = $this->fetchRow($select);
		
		if (empty($user)) {
			$user = $this->fetchNew();
			$user->email = $email;
			$user->save();
		}
	}
}