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
		
		
	}
	
	public function debugAction()
	{
		
	}
	
}