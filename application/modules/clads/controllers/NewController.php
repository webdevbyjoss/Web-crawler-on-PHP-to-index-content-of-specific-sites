<?php

class Clads_NewController extends Nashmaster_Controller_Action
{
	public function indexAction()
	{
		/* Initialize action controller here */
    	$Regions = new Searchdata_Model_Regions();
		// Ukraine has ID = 1
		$CountryCodeUkraine = 1;
		$this->view->regions = $Regions->getItems($CountryCodeUkraine);
		
		    	
    	// retrieve services information
		$Services = new Searchdata_Model_Services();
		$this->view->services = $Services->getAllItems();
	}
	
	public function addAction()
	{
		$params = $this->getRequest()->getParams();
		
		// get user information from session
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		
		$itemInfo = array(
			'user_id' => $userInfo['id'],
			'description' => $params['description'],
			'name' => $params['name'],
			'phone' => $params['phone'],
		);
		
		$regions = array_merge($params['near'], $params['large']);
		$regions[] = $params['city'];
		
		$Items = new Clads_Model_Items();
		$Items->createItem($itemInfo, $params['services'], $regions);
	}
	
	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		$Items = new Clads_Model_Items();
		$Items->deleteItem($id);
		
		$this->_helper->redirector('index', 'index', 'default'); // back to homepage
	}
}