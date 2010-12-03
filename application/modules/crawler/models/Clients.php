<?php
/**
 * Table Data Gateway to web crawler client
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Crawler_Model_Clients extends Zend_Db_Table_Abstract
{
	protected $_name = 'crawl_clients';
	
	/**
	 * Return client record from database
	 *
	 * TODO: it seams like this aproach of extracting clients can be a huge
	 *       perfomance bottleneck in sace we will have a lot of clients
	 *       even if it provides the maximum level of security
	 *       so possibly in the future we will revice this to make it faster
	 *
	 * @param string $hash md5() hash from client api key + salt
	 * @return array client data
	 */
	public function getByHash($hash, $salt)
	{
		$sql = 'SELECT crawl_clients_id, api_key, description
		FROM '.$this->_name.'
		WHERE CAST(MD5(concat(api_key, "' . intval($salt) . '")) AS CHAR) = "' . $hash . '"';

		return $this->getAdapter()->fetchRow($sql);
	}
}