<?php
/**
 * This controller will process all the jobs
 */
class Crawler_TestController extends Zend_Controller_Action
{
	public function init()
	{
		// we 100% that actions from this controller will be
		// called from CLI so we disabling layout and auto output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function searchAction()
	{
		$options = array(
			'keywords' => 'укладка паркету 333, тернопіль іва фі=- тернополь -  фівафіва %:?№%;""'
		);
		
		$SearchForm = new Nashmaster_SearchForm($options);
		$SearchForm->setKeywords($options['keywords']);
		
		$data = $SearchForm->getAdapter()->getIterator();
		var_dump($data);
	}

}