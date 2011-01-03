<?php
/**
 * Table Data Gateway to web crawler synonyms recognisions errors
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_SynonymsErrors extends Zend_Db_Table_Abstract
{
	const STATUS_UNPROCESSED = 0;
	
	const STATUS_PROCESSED = 1;
	
	const STATUS_NEED_REVIEW = 2;
	
	protected $_name = 'crawl_data_synonyms_errors';
	
	/**
	 * Logs error into database
	 *
	 * @param strign $data
	 * @param string $tags
	 */
	public function log($data, $tags)
	{
		$data = array(
		  	'title' => $data,
			'lang_id' => $tags,
		);
		
		try {
			$this->insert($data);
		} catch (Zend_Db_Exception $e) {
			// echo "DATABASE TABLE UNIQUE INTEX ERROR " . __FILE__;
		}

	}
	
	/**
	 * Get the list of item for each status
	 *
	 * @return array
	 */
	public function getAmounts()
	{
		$sql = 'SELECT processed, COUNT(processed) FROM ' . $this->_name . ' GROUP BY processed';
		return $this->getAdapter()->fetchPairs($sql);
	}
	
	/**
	 * Returns the pagination adapter
	 * according to the required filter
	 *
	 * @param int $status
	 * @return Zend_Paginator_Adapter_DbSelect
	 */
	public function getPagerByStatus($status)
	{
		$select = $this->select();
		$select->where('processed = ?', $status);
		$select->order('title');
		
		return new Zend_Paginator_Adapter_DbSelect($select);
	}
	
	/**
	 * Set status of problem
	 *
	 * @param int $id
	 * @param int $status
	 * @param string $usetNotes user notes that explains last status change
	 * @return null
	 */
	public function setStatus($id, $status, $usetNotes = null)
	{
		$data = array(
			'processed' => $status
		);
		
		if (null !== $usetNotes) {
			$data['admin_notes'] =  $usetNotes;
		}
		
		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		$this->update($data, $where);
		
		$info = $this->find($id)->current();
		$where = $this->getAdapter()->quoteInto('title = ?', $info['title']);
		$this->update($data, $where);
	}
	
	/**
	 * Return the problem details by ID
	 *
	 * @param int $id
	 * @return Zend_Db_Table_Row
	 */
	public function getDetailsById($id)
	{
		return $this->find($id)->current();
	}
}