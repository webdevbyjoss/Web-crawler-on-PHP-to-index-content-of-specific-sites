<?php

class CatalogController extends Nashmaster_Controller_Action
{
	public function indexAction()
	{
		$request = $this->getRequest();
		
		echo '<pre>';
		var_dump($request->getParams());
		echo '</pre>';
	}
	
	public function servicesAction()
	{
		$request = $this->getRequest();
		
		echo '<pre>';
		var_dump($request->getParams());
		echo '</pre>';
	}
}