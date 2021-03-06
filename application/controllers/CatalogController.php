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
		$city = $this->getCityInfo($citySeoName);
		$this->view->city = $city['city'];
		/*
		if (!empty($city['city_near'])) {
			$this->view->city_near = $city['city_near'];
		}
		$this->view->city_large = $city['city_large'];
		*/
		// retrieve services information
		$Services = new Searchdata_Model_Services();
		$this->view->services = $Services->getAllItems();
		
		$Categories = new Searchdata_Model_Categories();
		$this->view->categories = $Categories->getAllItems();
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
		
		// retrieve service and check for availability
		$serviceSeoName = $request->getParam('service');
		$Services = new Searchdata_Model_Services();
		$requestedService = $Services->getBySeoName($serviceSeoName);
		if (empty($requestedService)) {
			throw new Zend_Controller_Action_Exception('Specified service "' . $serviceSeoName . '" not found', 404);
		}
		
		// retrieve city information
		$citySeoName = $request->getParam('city');
		$city = $this->getCityInfo($citySeoName);
		$this->view->city = $city['city'];
		if (!empty($city['city_near'])) {
			$this->view->city_near = $city['city_near'];
		}
		$this->view->city_large = $city['city_large'];
		$this->view->service = $requestedService;
		
		// prepare search data
		$serviceIds = array($this->view->service->service_id);
		$regionIds = array($this->view->city->city_id);
		$page = (((int) $request->page) > 1) ? (int) $request->page : 1;
		
		$alsoSearch = $request->getParam('also_search');
		if (!empty($alsoSearch)) {
			$extraCities = explode(',', $alsoSearch);
			$this->view->extraCities = $extraCities;
			$regionIds = array_merge($regionIds, $extraCities);
		}

		// process search
		$searchIndex = new Search_Model_Index();
		$pagination = new Zend_Paginator($searchIndex->getDataPagenation($serviceIds, $regionIds));
		$pagination->setCurrentPageNumber($page);
		$pagination->setDefaultItemCountPerPage(self::RESULTS_PER_PAGE);
		
		$this->view->data = $pagination;
	}

	
	private function getCityInfo($cityTitle)
	{
		$Cities = new Searchdata_Model_Cities();
		$CitiesDistance = new Searchdata_Model_CitiesDistances();
		$city = $Cities->getCityBySeoName($cityTitle);
		$return['city'] = $city;
		if ($city->is_region_center == 0) {
			$return['city_near'] = $CitiesDistance->getNearCities($city->city_id);
		}
		$return['city_large'] = $CitiesDistance->getLargeCities($city->city_id);
		return $return;
	}

}
