<?php
/**
 * Manage jobs in database
 *
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_Jobs extends Zend_Db_Table_Abstract
{
	/**
	 * The set of statuses that will be used to identify the quelle processing state
	 *
	 * Here is the list of quelle processing states
	 *
	 * 1. Links are added as new job records (LINK_ADDED)
	 * 2. Client extracts content for each link (LINK_IN_PROCESS)
	 * 3. Content extraction is finished (LINK_FINISHED)
	 * 4. Content processing in progress (CONTENT_IN_PROCESS)
	 * 5. Content processed, quelle finished for this item (CONTENT_FINISHED)
	 */
	const LINK_ADDED = 0;
	const LINK_IN_PROCESS = 1;
	const LINK_FINISHED = 2;
	const CONTENT_IN_PROCESS = 3;
	const CONTENT_FINISHED = 4;
	
	/**
	 * The name of the database table
	 *
	 * FIXME: move this to the model level in stead of library level
	 *
	 * @var string
	 */
	protected $_name = 'crawl_jobs';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'crawl_jobs_id';
	
	/**
	 * Check if there are still unprocessed items in quelle
	 *
	 * @return boolean
	 */
	public function isFinished()
	{
		$sql = 'SELECT COUNT(' . $this->_primary . ')
				FROM ' . $this->_name . '
				WHERE status < ' . self::CONTENT_FINISHED;
		
		$amount = $this->getAdapter()->fetchOne($sql);
		
		if (intval($amount) > 0) {
			return false;
		}
		
		return true;
	}
	
}