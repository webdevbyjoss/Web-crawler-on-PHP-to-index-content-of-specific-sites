<?php
/**
 * Database table gateway to manage items crawled from the web and stored in database
 *
 * @name		Joss_Crawler_Db_Item
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_Items extends Zend_Db_Table_Abstract
{
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_jobs';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Creates or updates advertisement record in database
	 *
	 * it accepts the array that has the following structure
	 *
	 * 'uid'		=> 4f3f53fn3f5fk3   - md5('domain.com' + id)
	 *
	 * 'services'	=> array(
	 * 					0 => array('id' => '123', 'tags' => array('tag1', 'tag2', 'tag3', ...))
	 * 					1 => array('id' => '234', 'tags' => array('tag1', 'tag2', 'tag3', ...))
	 * 					...
	 * 			       )
	 *
	 * 'regions'	=> array(
	 * 					0 => array('id' => '123', 'tags' => array('tag1', 'tag2', 'tag3', ...))
	 * 					1 => array('id' => '234', 'tags' => array('tag1', 'tag2', 'tag3', ...))
	 * 					...
	 * 				   )
	 *
	 * 'info'		=> array(
	 * 					'contact-name'	=> '',
	 * 					'phone-number'	=> '',
	 * 					'description'	=> ''
	 * 				   )
	 *
	 * @param array $advert
	 * @return integer - the ads ID
	 */
	public function add($advert)
	{
		
	}
	
	protected function isExists($hash)
	{
		$this->
	}

}