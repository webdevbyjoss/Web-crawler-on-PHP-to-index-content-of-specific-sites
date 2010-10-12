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
	
	/**
	 * Creates new job in database table
	 *
	 *
	 *
	 * @param string $url
	 * @return null
	 */
	public function createJob($url)
	{
		// create new job only if there are no unprocessed jobs available in database
		if ($this->_isJob($url)) {
			return null;
		}
		
		$data = array(
			'url' => $url,
			'updated' => new Zend_Db_Expr('NOW()'),
			'created' => new Zend_Db_Expr('NOW()'),
		);

		$this->insert($data);
	}
	
	/**
	 * Assigns some available job from database to the provided client ID
	 * and generates session hash based on client apiKey to make sure the result
	 * received from the client for this job is 100% authentic
	 *
	 * @param int $clientId
	 * @param string $apiKey
	 * @return array
	 */
	public function assignJob($clientId, $apiKey)
	{
		$sql = 'SELECT crawl_jobs_id, url
		FROM ' . $this->_name . '
		WHERE status = ' . self::LINK_ADDED . '
		ORDER BY crawl_jobs_id DESC
		LIMIT 1';
		
		$job = $this->getAdapter()->fetchRow($sql);
		
		if (empty($job)) {
			return null;
		}
		
		$data = array(
			'client_id' => $clientId,
			'session_hash' => md5($apiKey . $job['crawl_jobs_id']),
			'status' => self::LINK_IN_PROCESS,
		);
		
		$where = $this->getAdapter()->quoteInto('crawl_jobs_id = ?', $job['crawl_jobs_id']);
		$this->update($data, $where);
		
		// TODO: revise this to use Row Gateway pattern and return table row object
		$res['url'] = $job['url'];
		$res['id'] = $job['crawl_jobs_id'];
		
		return $res;
	}
	
	/**
	 * Check if provided session hash is correct
	 * and updated jobs result data
	 *
	 * @param string $sessionHash
	 * @param string $headers
	 * @param string $content
	 * @return boolean
	 */
	public function setJobResults($sessionHash, $content)
	{
		$sql = 'SELECT crawl_jobs_id
		FROM ' . $this->_name . '
		WHERE session_hash = "' . $sessionHash . '"';
		
		$jobId = $this->getAdapter()->fetchOne($sql);
		
		if (empty($jobId)) {
			return null;
		}
		
		$data = array(
			'status' => self::LINK_FINISHED,
			'raw_body' => $content
		);
		
		$where = $this->getAdapter()->quoteInto('crawl_jobs_id = ?', $jobId);
		$this->update($data, $where);
		return true;
	}
	
	/**
	 * Retrieves next job from the database available for processing
	 */
	public function getJobForProcessing()
	{
		// get next available job from the database
		$sql = 'SELECT crawl_jobs_id, url, raw_body
		FROM ' . $this->_name . '
		WHERE status = ' . self::LINK_FINISHED . '
		ORDER BY crawl_jobs_id DESC
		LIMIT 1';
		
		$job = $this->getAdapter()->fetchRow($sql);
		
		// check if there were no any jobs available
		if (empty($job)) {
			return null;
		}
		
		// mark it as in process to prevent double processing of the same job
		$data = array(
			'status' => self::CONTENT_IN_PROCESS
		);
		
		$where = $this->getAdapter()->quoteInto('crawl_jobs_id = ?', $job['crawl_jobs_id']);
		$this->update($data, $where);
		
		return $job;
	}

	/**
	 * Temporrary function to finish job manually
	 *
	 * @param int $jobId
	 */
	public function finishJob($jobId)
	{
		$data = array(
			'status' => self::CONTENT_FINISHED
		);
		
		$where = $this->getAdapter()->quoteInto('crawl_jobs_id = ?', $jobId);
		$this->update($data, $where);
	}
	
	/**
	 * Checks if there are any unprocessed jobs in database from under the specified URL
	 *
	 * @param string $url
	 * @return boolean
	 */
	protected function _isJob($url)
	{
		// get next available job from the database
		$sql = 'SELECT crawl_jobs_id
		FROM ' . $this->_name . ' USE INDEX (url)
		WHERE  url = "' . addslashes($url) . '" LIMIT 1'; // AND status <> ' . self::CONTENT_FINISHED . ' LIMIT 1';

		$job = $this->getAdapter()->fetchRow($sql);
		
		// check if there were no any jobs available
		if (empty($job)) {
			return false;
		}
		
		return true;
	}

}