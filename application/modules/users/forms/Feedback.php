<?php

class Users_Form_Feedback extends Zend_Form 
{
	public function init()
	{
		$this->setAction("/users/feedback/post/");
		$this->setMethod("POST");
		
		$this->addElement('select', 'subject', array(
			"multiOptions" => array(
				"0" => '---',
				"1" => "technical-support",
				"2" => "subject-support"
			),
			"label" => "Subject",
			'style' => "width: 185px",
			'validators' => array(
				new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER + Zend_Validate_NotEmpty::ZERO)
    		)
		));
		
		$this->addElement('text', 'email', array(
			'filters' => array('StringTrim', 'StringToLower'),
			'validators' => array('EmailAddress'),
			'required' => true,
			'label' => 'E-mail',
			'size' => 33 	
		));

		$this->addElement('textarea', 'feedback_message', array(
			'required' => true,
			'label' => 'Message',
			'rows' => 5,
			'cols' => 30
		));
		
		$this->addElement('submit', 'feedback_submit', array(
			'label' => "Submit"
		));
		
		$this->setDecorators(array(
//			'formElements',
			array('viewScript', array('viewScript' => 'feedbackform.phtml')) 
		));
	}
}


?>