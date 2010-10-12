<?php
/**
 * This controller will process all the jobs
 */
class Crawler_JobsController extends Zend_Controller_Action
{
	const JOBS_PER_TIME = 100;
	
	// define available adapters
	protected $_adapters = array(
		'Joss_Crawler_Adapter_Emarketua'
	);
	
	public function init()
	{
		// we 100% that actions from this controller will be
		// called from CLI so we disabling layout and auto output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function indexAction()
	{
		$Client = new Joss_Crawler_Jobs($this->_adapters);
		$res = $Client->startQuelle();
		if (false === $res) {
			echo "\nThere are still some jobs in processing\n";
			return;
		}
		
		echo "\nQuelle started!\n";
	}
	
	/**
	 * This action will process information from
	 */
	public function processAction()
	{
		$Jobs = new Joss_Crawler_Jobs($this->_adapters);
		
		for ($i = 0; $i < self::JOBS_PER_TIME; $i++) {
			
			$res = $Jobs->processNextJob();
			
			if (false === $res) {
				echo "\nAll jobs are completed\n";
				return;
			}
		}
		
		echo "\nJob(s) processed!\n";
	}

}