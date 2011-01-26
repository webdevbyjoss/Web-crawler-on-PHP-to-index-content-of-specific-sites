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
		$this->_helper->viewRenderer->setNoRender(true);

		//send feedback message to ofuz over Zend_Http_Client
		$ofuzUri = "http://todo.nash-master.com/ofuz_helper.php";
		$client = new Zend_Http_Client($ofuzUri);
		$req = $this->getRequest();
		$category = $req->getParam('category');
		if (is_array($category))
			$category = $category[0];
		switch ($category) {
			case "1": $category = "щось не працює або працює взагалі неправильно"; break;
			case "2": $category = "не вдалося нічого знайти чи результати пошуку низької якості"; break;
			case "3": $category = "маю пораду щодо вдосконалення чи покращення роботи ресурсу"; break;
			case "4": $category = "пропоную співпрацю з моїм сайтом або компанією"; break;
			case "5": $category = "хочу бути вашим представником у своєму місті"; break;
		}

		$client->setParameterPost(array(
			"category" => $category,
			"message" => $req->getParam("message"),
			"email" => $req->getParam("email"),
			"telephone" => $req->getParam("telephone")
		));
		$ofuz_response = $client->request("POST")->getBody();
		if ($ofuz_response == "SUCCESS") {
			echo Zend_Registry::get("Zend_Translate")->_('Thanks for your feedback'); 
		}
		else 
			echo $ofuz_response;
	}
}


?>