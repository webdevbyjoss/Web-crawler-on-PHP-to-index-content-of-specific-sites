<?php

class CatalogController extends Nashmaster_Controller_Action
{
	/**
	 * The amount of results per page to show in search
	 */
	const RESULTS_PER_PAGE = 10;
	
	public function indexAction()
	{
		$request = $this->getRequest();
		// retrieve city information
		$citySeoName = $request->getParam('city');
		$Cities = new Searchdata_Model_Cities();
		$city = $Cities->getCityBySeoName($citySeoName);
		$this->view->city = $city;
		
		// retrieve services information
		$Services = new Searchdata_Model_Services();
		$this->view->services = $Services->getAllItems();
	}
	
	public function servicesAction()
	{
		$request = $this->getRequest();
		
		echo '<pre>';
		var_dump($request->getParams());
		echo '</pre>';
	}
	
	public function cityserviceAction()
	{
		$request = $this->getRequest();
		// retrieve city information
		$citySeoName = $request->getParam('city');
		$Cities = new Searchdata_Model_Cities();
		$city = $Cities->getCityBySeoName($citySeoName);
		$this->view->city = $city;

		$serviceSeoName = $request->getParam('service');
		$Services = new Searchdata_Model_Services();
		$this->view->service = $Services->getBySeoName($serviceSeoName);

		// prepare search data
		$serviceIds = array($this->view->service->service_id);
		$regionIds = array($this->view->city->city_id);
		$page = (((int) $request->page) > 1) ? (int) $request->page : 1;

		// process search
		$searchIndex = new Search_Model_Index();
		$pagination = new Zend_Paginator($searchIndex->getDataPagenation($serviceIds, $regionIds));
		$pagination->setCurrentPageNumber($page);
		$pagination->setDefaultItemCountPerPage(self::RESULTS_PER_PAGE);
		
		$this->view->data = $pagination;
	}

}