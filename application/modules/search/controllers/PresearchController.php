<?php

class Search_PresearchController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function updateAction()
	{
		
	}

}