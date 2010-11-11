<?php

class Crawler_EditorController extends Nashmaster_Controller_Action
{
	private $_persistence = null;
	
	public function init()
	{
		$this->_persistence = new Zend_Session_Namespace(__CLASS__);
	}
	
	/**
	 * Shows the list of items
	 */
	public function indexAction()
	{
		if (empty($this->_persistence->status)) {
			$this->_persistence->status = Joss_Crawler_Db_SynonymsErrors::STATUS_UNPROCESSED;
		}
		
		$page = $this->_getParam('page');
		
		if (empty($page)) {
			$page = empty($this->_persistence->page) ? 1 : $this->_persistence->page;
		} else {
			$this->_persistence->page = $page;
		}
		
		$this->view->status = $this->_persistence->status;
		
		$SynonymsErrors = new Joss_Crawler_Db_SynonymsErrors();
		$this->view->amounts = $SynonymsErrors->getAmounts();

		$paginator = new Zend_Paginator($SynonymsErrors->getPagerByStatus($this->_persistence->status));
		$paginator->setCurrentPageNumber($page);
		$paginator->setDefaultItemCountPerPage(15);
		$this->view->data = $paginator;
		
		// lets hilight the last accessed item
		if (!empty($this->_persistence->last_details_id)) {
			$this->view->last_details_id = $this->_persistence->last_details_id;
		}
	}
	
	/**
	 * Changes the status filter
	 */
	public function statusAction()
	{
		$params = $this->getRequest()->getParams();
		
		// save page for old status
		$this->_persistence->page_for_status[$this->_persistence->status] = $this->_persistence->page;

		$this->_persistence->status = $params['set'];
		
		// restore page value of new status or set to first page
		if (!empty($this->_persistence->page_for_status[$this->_persistence->status])) {
			$this->_persistence->page = $this->_persistence->page_for_status[$this->_persistence->status];
		} else {
			$this->_persistence->page = 1;
		}

		$this->_redirect('/crawler/editor/');
	}
	
	/**
	 * Shows the URL processing details
	 */
	public function detailsAction()
	{
		$id = $this->_getParam('id');
		
		// lets save the last accessed value into session and hilight it in the grid for conviniense
		$this->_persistence->last_details_id = $id;
		
		/**
		 * Show item details
		 */
		$SynonymsErrors = new Joss_Crawler_Db_SynonymsErrors();
		$details = $SynonymsErrors->getDetailsById($id);
		$this->view->id = $id;
		$this->view->url = $details->title;
		$this->view->problem = $details->lang_id;
		$this->view->status = $details->processed;
		if ($details->processed == Joss_Crawler_Db_SynonymsErrors::STATUS_NEED_REVIEW) {
			$this->view->notes = $details->admin_notes;
		}
		/**
		 * Recognize page content
		 */
		$jobsManager = new Joss_Crawler_Jobs();
		$job = $jobsManager->getLastJobByUrl($this->view->url);
		
		if (null !== $job) {
			$Adapter = $jobsManager->getLoadedAdapter($this->view->url, $job['raw_body']);
			$this->view->data = $Adapter->getData();

			// get additional data
			$Services = new Searchdata_Model_Services();
			$this->view->servicesList = $Services->fetchAll();
		}
		
		$DbJobs = new Joss_Crawler_Db_Jobs ();
		if ($DbJobs->isJob($this->view->url)) {
			$this->view->in_process = true;
		}
	}
	
	public function processedAction()
	{
		$id = $this->_getParam('id');
		$status = $this->_getParam('status');
		
		$SynonymsErrors = new Joss_Crawler_Db_SynonymsErrors();
		$SynonymsErrors->setStatus($id, $status);
		
		$this->_redirect('/crawler/editor');
	}
	
	public function scheduleAction()
	{
		$id = $this->_getParam('id');
		
		$SynonymsErrors = new Joss_Crawler_Db_SynonymsErrors();
		$details = $SynonymsErrors->getDetailsById($id);
		
		$DbJobs = new Joss_Crawler_Db_Jobs ();
		$DbJobs->createJob($details->title);
		
		$this->_redirect('/crawler/editor');
	}
	
	public function addSynonymAction()
	{
		$request = $this->getRequest();
		$params = $request->getParams();

		$Synonyms = new Joss_Crawler_Db_Synonyms();
		
		// this will allow us to provide a coma separated values
		if (false !== strpos($params['synonym'], ',')) {
			$tempList = explode(',', $params['synonym']);
			foreach($tempList as $tmpItem) {
				$tmpItem = trim($tmpItem);
				if (!empty($tmpItem)) {
					$synonymsList[] = $tmpItem;
				}
			}
		} else {
			$synonymsList = array($params['synonym']);
		}
		
		foreach ($synonymsList as $synItem)  {
		
			// protection from the fools that passing empty values
			if (empty($synItem)) {
				continue;
			}
			
			$synonymElement = $Synonyms->getElementByText($synItem, $params['type_id'], $params['lang_id']);
			$SynonymsServices = new Joss_Crawler_Db_SynonymsServices();
	
			$avail = $SynonymsServices->getRelationsByIds($synonymElement->id);
			$serviceIds = array();
			foreach($avail as $i) {
				$serviceIds[] = $i->service_id;
			}
	
			foreach ($params['services'] as $id => $foo) {
	
				if (!in_array($id, $serviceIds)) {
					$data = array(
						'synonym_id' => $synonymElement->id,
						'service_id' => $id,
					);
					
					$SynonymsServices->insert($data);
				}
			}
		}
		
		$this->_redirect('/crawler/editor/details/id/' . $params['id']);
	}
	
	public function updateDataAction()
	{
		$id = $this->_getParam('id');
		
		$SynonymsErrors = new Joss_Crawler_Db_SynonymsErrors();
		$details = $SynonymsErrors->getDetailsById($id);

		$jobsManager = new Joss_Crawler_Jobs();
		$job = $jobsManager->getLastJobByUrl($details->title);
		$jobsManager->processData($details->title, $job['raw_body']);
		
		$this->_redirect('/crawler/editor/details/id/' . $id);
	}
	
	
	public function developerReportAction()
	{
		$request = $this->getRequest();
		$params = $request->getParams();
		
		$SynonymsErrors = new Joss_Crawler_Db_SynonymsErrors();
		$SynonymsErrors->setStatus($params['id'], Joss_Crawler_Db_SynonymsErrors::STATUS_NEED_REVIEW, $params['user_notes']);
		
		$this->_redirect('/crawler/editor');
	}
	
}