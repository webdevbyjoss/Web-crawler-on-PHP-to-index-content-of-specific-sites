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
        $resource = 'mvc:' . ($request->module ? $request->module : 'default');
        
        if (!$acl->isAllowed($userInfo['roles'], $resource)) {

        	$request
        		->setParam('resource', $resource)
        		->setParam('role', $userInfo['roles'])
        		->setModuleName('default')
	            ->setControllerName('error') // using the errorController seems appropriate
	            ->setActionName('denied')
	            ->setDispatched(false);
	            ;
	        return;
        }
        
        // Prepage search form
        $options = array();
    	$options['remote_ip'] = $_SERVER['REMOTE_ADDR'];
    	
        $SearchForm = new Nashmaster_SearchForm($options);
        $this->view->searchForm = $SearchForm;
	}
}