<?php
/**
 * Vkontakte OpenID API
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Users_VkController extends Zend_Controller_Action
{
	const LOGIN_PREFIX = 'vk';
	
	private $_vkconfig = null;
	
	public function init()
	{
		$options = $this->getInvokeArg('bootstrap')->getOptions();
		$this->_vkconfig = $options['vkontakte'];
	}
	
	public function authAction()
	{
		$params = $this->getRequest()->getParams();
		$username = self::LOGIN_PREFIX . $params['uid'];
		
		// check if user is already exists in database
		$auth    = Zend_Auth::getInstance();
		$Users = new Users_Model_Users();
		$userInfo = $Users->getUserInfo($username);
		
		if ($userInfo == null) {
			// create user in database
			$userInfo = $Users->fetchNew();
			$userInfo->username = $username;
			$userInfo->password = $params['hash'];
			$userInfo->first_name = $params['first_name'];
			$userInfo->last_name = $params['last_name'];
			$userInfo->avatar = $params['photo_rec'];
			$userInfo->roles = 'user';
			$userInfo->save();
			
		} else {

			// generate auth token according to the vkontakte API
			// md5(app_id+user_id+secret_key)
			// @see: http://vk.com/developers.php?o=-1&p=Auth
			$authToken = $this->_vkconfig['app_id'] . $params['uid'] . $this->_vkconfig['secter_key'];
			
			// Get our authentication adapter and check credentials
	        $adapter = $this->getAuthAdapter($username, $authToken);
	        $result  = $auth->authenticate($adapter);
	        
	        if (!$result->isValid()) {
	            return $this->render('login-error'); // re-render the login form
	        }
		}
		
        // get user information from database as store it in auth adapter for persistance use
        $storage = $auth->getStorage();

        $info['id'] = $userInfo->id;
        $info['username'] = $userInfo->username;
        $info['real_name'] = $userInfo->first_name . ' ' . $userInfo->last_name;
        $info['roles'] = $userInfo->roles;
        $info['avatar'] = $userInfo->avatar;
        $storage->write($info);

        // We're authenticated! Redirect to the home page
        return $this->_helper->redirector('index', 'index', 'default', array('lang' => $this->getInvokeArg('bootstrap')->getResource('locale')));
        
		// login user on site
		// Zend_Debug::dump($user);
		// $this->_helper->layout->disableLayout();
		// $this->_helper->viewRenderer->setNoRender(true);
	}
	
	
	/**
	 * Returns database auth adapter
	 *
	 * @param string $login
	 * @param string $pass
	 * @return Zend_Auth_Adapter_DbTable
	 */
    public function getAuthAdapter($login, $pass)
    {
    	$db = $this->_getParam('db');
    	
        $adapter = new Zend_Auth_Adapter_DbTable(
        	$db,
            'users',
            'username',
            'password',
        	'MD5(?)'
        );
        
        $adapter->setIdentity($login)
        	->setCredential($pass);
        
        return $adapter;
    }
}