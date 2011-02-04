<?php

class Search_ResultsController extends Zend_Controller_Action
{
	const RESULTS_PER_PAGE = 10;
	
	public function init()
	{
		$this->_helper->layout->disableLayout();
	}
	
	function getAction()
	{
		$request = $this->getRequest();
		
		// params can be passed as single param (123) or
		// as a coma separated list of IDs (232,256,52,56)
		if (false !== strpos($request->service, ',')) {
			$serviceIds = explode(',', $request->service);
		} else {
			$serviceIds = array($request->service);
		}

		// same for regions
		if (false !== strpos($request->region, ',')) {
			$regionIds = explode(',', $request->region);
		} else {
			$regionIds = array($request->region);
		}

		$searchIndex = new Search_Model_Index();
		$pagination = new Zend_Paginator($searchIndex->getDataPagenation($serviceIds, $regionIds));
		$pagination->setCurrentPageNumber($request->page);
		$pagination->setDefaultItemCountPerPage(self::RESULTS_PER_PAGE);
		$this->view->data = $pagination;
		
		// get data for matched words highlight
		$Synonyms = new Joss_Crawler_Db_Synonyms();
		$SynonymServices = new Joss_Crawler_Db_SynonymsServices();
	}
	
	function statAction()
	{
		// disable auto render
		$this->_helper->viewRenderer->setNoRender(true);

		// get information from request
		$request = $this->getRequest();
		
		$StatSearchClicks = new Search_Model_StatSearchClicks();
		$StatSearchClicks->add(urldecode($request->data));
	}
	
}