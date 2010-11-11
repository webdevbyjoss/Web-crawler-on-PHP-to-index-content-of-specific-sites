<?php

class Nashmaster_Controller_Action extends Zend_Controller_Action
{
	public function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
		$acl = new Nashmaster_Acl();
        
		if ($auth->hasIdentity()) {
            $userInfo = $auth->getStorage()->read();
        } else {
        	$userInfo['roles'] = Nashmaster_Acl::ROLE_GUEST;
        }
        
        // Lets check the permissions
        $request = $this->getRequest();
        $resource = 'mvc:' . $request->module;
        
        if (!$acl->isAllowed($userInfo['roles'], $resource)) {
        	throw new Exception('Access denied. ' . $resource . '::' . $userInfo['roles']);
        }
        
	}
}