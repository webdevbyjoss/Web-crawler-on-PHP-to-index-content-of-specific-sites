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
		$options['search_keywords'] = 'вікна';
		$options['remote_ip'] = '127.0.0.1';
		
		$SearchForm = new Nashmaster_SearchForm($options);
		var_dump($SearchForm);
	}

	public function dataAction()
	{
		$url = 'http://emarket.ua/construction/elevators-escalators';
		
		$job = new Joss_Crawler_Db_Jobs();
		$data = $job->getLastJobByUrl($url);
		
		// load page content
		$rawBody = base64_decode($data['raw_body']);
		
		$emarketUa = new Joss_Crawler_Adapter_Emarketua();
		$emarketUa->loadPage($url, $rawBody);
		
		$links = $emarketUa->getDataLinks();
		var_dump($links);
		
		
	}
	
}