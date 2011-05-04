<?php

class IndexController extends Nashmaster_Controller_Action
{
 
    public function init()
    {
        /* Initialize action controller here */
    	$Regions = new Searchdata_Model_Regions();
		// Ukraine has ID = 1
		$CountryCodeUkraine = 1;
		$this->view->regions = $Regions->getItems($CountryCodeUkraine);
    }
 
    public function indexAction()
    {
    	$auth = Zend_Auth::getInstance();
    	
    	if (!$auth->hasIdentity()) {
			return $this->_forward('tour');
		}
		
		$userInfo = $auth->getStorage()->read();
		
		$Items = new Clads_Model_Items();
		$itemsList = $Items->getItemsByUser($userInfo['id']);
		
		// if particular user doesn't have any items then redirect it to item creation wizard
		if (count($itemsList) < 1) {
			return $this->_helper->redirector('index', 'new', 'clads', array('lang' => $this->getInvokeArg('bootstrap')->getResource('locale')));
		}
		
		$this->view->items = $itemsList;
		/*$Services = new Searchdata_Model_Services();
		$this->view->services = $Services->getAllItems();*/
    }
    
    public function tourAction()
    {
    	
    }
    
    public function regionAction()
    {
    	$request = $this->getRequest();
    	$regionId = $request->getParam('id');
    	
    	$Cities = new Searchdata_Model_Cities();
        $this->view->cities = $Cities->getItems($regionId);
        $this->view->currentRegion = $regionId;
    }

}