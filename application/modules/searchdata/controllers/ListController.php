<?php

class Searchdata_ListController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
	}
	
    public function regionesAction()
    {
        $Regiones = new Searchdata_Model_Regions();
        $this->view->regiones = $Regiones->getItems($this->_getParam('countryid'));
    }
    
    public function citiesAction()
    {
        $Cities = new Searchdata_Model_Cities();
        $this->view->cities = $Cities->getItems($this->_getParam('regionid'));
    }
    
	public function idcitiesAction()
    {
        $Cities = new Searchdata_Model_Cities();
        $this->view->cities = $Cities->getItems($this->_getParam('regionid'));
    }
    
    public function relatedcitiesAction()
    {
    	$Cities = new Searchdata_Model_Cities();
		$CitiesDistance = new Searchdata_Model_CitiesDistances();
    	
		$cityId = $this->_getParam('cityid');
		
		$this->view->cityInfo = $Cities->getCityById($cityId);
    	$this->view->nearCities = $CitiesDistance->getNearCities($cityId, 60, 999);
    	$this->view->largeCities = $CitiesDistance->getLargeCities($cityId);
    }
    
}