<?php

class Search_Model_StatKeywords extends Zend_Db_Table_Abstract
{
	protected $_name = 'stat_keywords';
	
	public function add($keywords, $locale = null, $services = null, $regions = null)
	{
		$data = array(
			'created' => new Zend_Db_Expr('NOW()'),
			'keywords' => $keywords,
			'locale' => $locale,
			'services' => $services,
			'regions' => $regions,
		);
		
		$this->insert($data);
	}
}