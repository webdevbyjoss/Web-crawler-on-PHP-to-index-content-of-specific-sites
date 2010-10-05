<?php
/**
 * This controller will process all the jobs
 */
class Crawler_JobsController extends Zend_Controller_Action
{
	public function init()
	{
		// we 100% that actions from this controller will be
		// called from CLI so we disabling layout and auto output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function indexAction()
	{
		// define available adapters
		$adapters = array(
			'Joss_Crawler_Adapter_Emarketua'
		);
		
		$Client = new Joss_Crawler_Jobs($adapters);
		$res = $Client->startQuelle();
		
		/*
		$Client = new Joss_Crawler_Adapter_Emarketua();
		$links = $Client->getDataLinks();
		
		
		foreach ($links as $key => $link) {
			// echo $key . "| " . $link['url'] . "| " . $link['content'] . "\n";
			echo $link['url'] . "\n";
		}
		*/
	}
	
	/**
	 * This action will process information from
	 */
	public function processAction()
	{
		$adapters = array (
			'Joss_Crawler_Adapter_Emarketua'
		);
		
		$Jobs = new Joss_Crawler_Jobs($adapters);
		
		$Jobs->processNextJob();
	}
	
}