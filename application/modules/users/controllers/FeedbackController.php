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
		$form = new Users_Form_Feedback();
		if ($form->isValid($this->getRequest()->getPost())) {
			$this->view->success = true;
			//store feedback into database
			$dbAdapter = $this->getFrontController()
	            ->getParam('bootstrap')
	            ->getResource('multidb')
	            ->getDb("front_db");
	        $dbAdapter->insert('feedback', array(
	        	"email" => $form->getElement('email')->getValue(),
	        	"subject" => $form->getElement('subject')->getValue(),
	        	"message" => $form->getElement('feedback_message')->getValue()
	        ));
		}
		else {
			$this->view->success = false;
			$this->view->errors = $form->getMessages();
		}
		$this->getResponse()->setHeader("Content-Type", "application/json");
	}
}


?>