<?php

class Search_BuildController extends Zend_Controller_Action
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
		$searchIndex = new Search_Model_Index();
		$ItemServices = new Clads_Model_ItemsServices();
		$ItemRegions = new Clads_Model_ItemsRegions();
		
		$Items = new Clads_Model_Items();
		$itemsRowset = $Items->getItems();
		
		foreach ($itemsRowset as $item) {
			
			// get serives
			$itemsServicesRowset = $ItemServices->getDataById($item->id);
			// get regions
			$itemsRegionsRowset = $ItemRegions->getDataById($item->id);
			
			foreach ($itemsServicesRowset as $service) {
				foreach ($itemsRegionsRowset as $region) {
					
					try {

						$searchIndex->add(
							$item->id,
							$service->service_id,
							$region->region_id,
							$item->name,
							$item->phone,
							$item->description
						);

					} catch (Zend_Db_Exception $e) {
						
						$searchIndex->updateIndex(
							$item->id,
							$service->service_id,
							$region->region_id,
							$item->name,
							$item->phone,
							$item->description
						);
						
					}
					
				}
			}
			
		}
	}
	
}