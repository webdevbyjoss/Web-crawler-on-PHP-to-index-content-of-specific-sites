<?php
/**
 * This controller will process all the jobs
 */
class Crawler_JobsController extends Zend_Controller_Action
{
	const JOBS_PER_TIME = 30;
	
	public function init()
	{
		// we 100% that actions from this controller will be
		// called from CLI so we disabling layout and auto output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function indexAction()
	{
		$Client = new Joss_Crawler_Jobs();
		$res = $Client->startQuelle();
		if (false === $res) {
			echo "\nthere are still some jobs in processing\n";
			return;
		}
		
		echo "\nquelle started!\n";
	}
	
	/**
	 * This action will process information from
	 */
	public function processAction()
	{
		// lets measre the time of script execution
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;
		
		$Jobs = new Joss_Crawler_Jobs();
		
		for ($i = 0; $i < self::JOBS_PER_TIME; $i++) {
			
			$res = $Jobs->processNextJob();
			
			if (false === $res) {
				echo "\nall jobs are completed\n";
				break;
			}
		}
		
		echo "\n$i job(s) processed!\n";
		
		// calculate total execution time
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = ($endtime - $starttime);
		
		echo "total time: " . $totaltime . " seconds\n";
	}
	
	
	/**
	 * Build search form index from crawler database
	 */
	public function buildAction()
	{
		$Items = new Joss_Crawler_Db_Items();
		$searchIndex = new Search_Model_Index();
		$itemsRowset = $Items->getItems();

		$ItemServices = new Joss_Crawler_Db_ItemServices();
		$ItemRegions = new Joss_Crawler_Db_ItemRegions();
		
		foreach ($itemsRowset as $key => $item) {
			
			// get serive
			$itemsServicesRowset = $ItemServices->getDataById($item->id);
			
			// get region
			$itemsRegionsRowset = $ItemRegions->getDataById($item->id);

			if (empty($itemsRegionsRowset[0])) {
					continue;
			}
			$reg = $itemsRegionsRowset[0];
			
			// save into index in case thre is no such a unique combination
			// of (item_id,service_id,region_id)
			foreach ($reg as $myreg) {
				
				foreach ($itemsServicesRowset as $myser) {
					
					// echo "\n============\n";
					// var_dump($item->id, $myser->service_id, $myreg->region_id);
					// continue;
					
					if ($searchIndex->itemExists($item->id, $myser->service_id, $myreg->region_id)) {
						continue;
					}
					
					$searchIndex->add(
						$item->id,
						$myser->service_id,
						$myreg->region_id,
						$item->url,
						$item->title,
						$item->description
					);
				}
			}

		}
		
	}

}