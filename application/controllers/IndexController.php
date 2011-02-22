<?php

class IndexController extends Nashmaster_Controller_Action
{
 
    public function init()
    {
        /* Initialize action controller here */
    }
 
    public function indexAction()
    {
		$Services = new Searchdata_Model_Services();
		$this->view->services = $Services->getAllItems();
		
		$Regions = new Searchdata_Model_Regions();
		// Ukraine has ID = 1
		$CountryCodeUkraine = 1;
		$this->view->regions = $Regions->getItems($CountryCodeUkraine);
    }

}