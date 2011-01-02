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
		
		// $ItemInfo = new
		//foreach ($itemsIndex as $item) {
			
		//}
		
		$this->view->data = $itemsIndex;
	}
}
