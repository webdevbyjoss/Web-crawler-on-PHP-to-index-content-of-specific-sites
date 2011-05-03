<?php

class Clads_UpdateController extends Nashmaster_Controller_Action
{
	public function indexAction()
	{
		
	}
	
	public function infoAction()
	{
		$params = $this->getRequest()->getParams();
		
		list ($name, $id) = explode('-', $params['elem']);
		
		$data = array(
			$name => $params['text']
		);
		
		$Items = new Clads_Model_Items();
		$Items->updateInfo($data, $id);
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
}