<?php

class Search_ResultsController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
	}
	
	function getAction()
	{
		$request = $this->getRequest();
		$serviceId = $request->service;
		$regionId = $request->region;

		$searchIndex = new Search_Model_Index();

		$itemsIndex = $searchIndex->getData($serviceId, $regionId);
		$this->view->data = $itemsIndex;
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
