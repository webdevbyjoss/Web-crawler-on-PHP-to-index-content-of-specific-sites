<?php
class Crawler_RelationsController extends Nashmaster_Controller_Action
{
	public function indexAction()
	{
		$searchIndex = new Search_Model_Index();
		$this->view->indexAmount = $searchIndex->getCount();
	}
}