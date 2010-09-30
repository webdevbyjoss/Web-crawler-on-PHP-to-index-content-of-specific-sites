<?php
/**
 * Distributed web crawler API controller
 * returns job for clients and receives the results
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Crawler_ApiController extends Zend_Controller_Action
{
	/**
	 * we 100% that actions from this controller will be
	 * called from CLI so we disabling layout and auto output
	 *
	 * @see Zend_Controller_Action::init()
	 */
	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}

	/**
	 * Receive the API key hash (id) from the client and salt value (data),
	 * search for appropriate client account in our database. If client
	 * data is OK then we need to return some assigned job for this client.
	 */
	public function getAction()
	{
		// get API key and get client information from database
		$request = $this->getRequest();
		$apiKeyHash = $request->id;
		$salt = $request->data;

		if (empty($apiKeyHash) || empty($salt)) {
			$responce['message'] = 'Please provide API key and salt value';
			echo json_encode($responce);
			return;
		}
		
		$Clients = new Crawler_Model_Clients();
		$clientData = $Clients->getByHash($apiKeyHash, $salt);
		
		if (null === $clientData) {
			$responce['message'] = 'Client not found or API key is incorrect';
			echo json_encode($responce);
			return;
		}
		
		// register job for this client
		$JobsManager = new Joss_Crawler_Db_Jobs();
		$jobData = $JobsManager->assignJob($clientData['crawl_clients_id'], $clientData['api_key']);
		
		if (null === $jobData) {
			$responce['message'] = 'No any jobs available for now';
			echo json_encode($responce);
			return;
		}
		
		// return job information to client
		$responce['data'] = base64_encode($jobData['url']);
		$responce['id'] = $jobData['id'];
		
		echo json_encode($responce);
	}
	
	/**
	 * Receive data from the client, check if job session ID provided by client is correct
	 * then we need to update job record with the data received from client
	 */
	public function postAction()
	{
		$request = $this->getRequest();

		/*
		$content = base64_decode($request->data);
		$headersEndPosition = strpos($content, "\r\n\r\n") + 4;
      	$headers = substr($content, 0, $headersEndPosition);
      	unset($content);
      	*/

      	$JobsManager = new Joss_Crawler_Db_Jobs();
      	$res = $JobsManager->setJobResults($request->id, $request->data);
      	
      	if (empty($res)) {
      		echo 'Incorrect API key';
      		return;
      	}
      	
      	echo 'GOOD, GOOD CLIENT! WILL GIVE YOU A DONUT!';
	}
	
}