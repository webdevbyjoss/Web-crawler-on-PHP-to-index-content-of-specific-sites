<?php

class CatalogController extends Nashmaster_Controller_Action
{
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
		
		var_dump($this->view->service);
	}

}