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

	public function indexAction()
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