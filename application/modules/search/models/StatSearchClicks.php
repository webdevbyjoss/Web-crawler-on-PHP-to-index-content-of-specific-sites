<?php

class Search_Model_StatSearchClicks extends Zend_Db_Table_Abstract
{
	protected $_name = 'stat_search_clicks';
	
	public function add($url)
	{
		$data = array(
			'created' => new Zend_Db_Expr('NOW()'),
			'url' => $url,
		);
		
		$this->insert($data);
	}
}