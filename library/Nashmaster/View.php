<?php

class Nashmaster_View extends Zend_View
{
	private $_auth = null;
	
	public function __construct($options)
	{
		$this->_auth = Zend_Auth::getInstance();
		
		parent::__construct($options);
	}
	
	public function profileLink()
    {
        if ($this->_auth->hasIdentity()) {
            $userData = $this->_auth->getStorage()->read();
            return 'Welcome, <a href="/profile/' . $userData['username'] . '">' . $userData['real_name'] .  '</a> <a href="/login/logout/">Logout</a>';
        }
        
        return '<a href="/login">Login</a>';
    }
    
    public function isAdmin()
    {
    	$userData = $this->_auth->getStorage()->read();
    	if ($userData['roles'] == 'admin') {
    		return true;
    	}
    	
    	return false;
    }
}