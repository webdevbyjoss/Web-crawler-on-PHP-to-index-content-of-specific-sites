<?php

class Mobile_ApiController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
	}
	
	public function searchAction()
	{
		$params = $this->getRequest()->getParams();
		
		$Index = new Search_Model_Index();
		$this->view->results = $Index->geoSearch($params['cat_id'], $params['lat'], $params['lng']);
	}
	
	public function debugAction()
	{
		
	}
	
}