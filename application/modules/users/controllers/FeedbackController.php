<?php
/**
 * Controller for users feedbacks
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Andrew Gonchar <gonandriy@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Users_FeedbackController extends Zend_Controller_Action
{
	public function postAction()
	{
		$this->_helper->layout->disableLayout();

		/*
		//send feedback message to ofuz over Zend_Http_Client
		$ofuzUri = "http://todo.nash-master.com/ofuz_helper.php";
		
		$client = new Zend_Http_Client($ofuzUri);
		$req = $this->getRequest();
		
		$category = $req->getParam('category');
		if (is_array($category)) {
			$category = $category[0];
		}
		
		$client->setParameterPost(array(
			"category" => $category,
			"message" => $req->getParam("message"),
			"email" => $req->getParam("email"),
			"telephone" => $req->getParam("telephone")
		));
		$ofuz_response = $client->request("POST")->getBody();
		*/
		
		$params = $this->getRequest()->getParams();

		switch ($params['category']) {
			case "1": $category = "errors on site"; break;
			case "2": $category = "search fail"; break;
			case "3": $category = "too slow"; break;
			case "4": $category = "make better"; break;
			case "5": $category = "partnership"; break;
			case "6": $category = "affiliate"; break;
		}
		
		mail(ADMIN_EMAIL, '[NASH-MASTER] Support message: ' . $category,
		'From: ' . $params['email'] . "\n\n" . $params['message']);
	}
}