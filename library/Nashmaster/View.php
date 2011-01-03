<?php

class Nashmaster_View extends Zend_View
{
	private $_auth = null;
	private $_translate = null;
	private $_locale = null;
	private $_request = null;
	
	public function __construct($options)
	{
		$this->_auth = Zend_Auth::getInstance();
		parent::__construct($options);
	}
	
	public function getLocale()
	{
		return $this->_locale->toString();
	}
	
	public function setTranslation($translation)
	{
		$this->_translate = $translation;
		$this->_locale = Zend_Registry::get('Zend_Locale');
	}
	
	public function profileLink()
    {
        if ($this->_auth->hasIdentity()) {
            $userData = $this->_auth->getStorage()->read();
            return 'Welcome, <a href="/profile/' . $userData['username'] . '">' . $userData['real_name'] .  '</a> <a href="/login/logout/">Logout</a>';
        }
        
        /*return '<a href="/login">Login</a>';*/
    }
    
    public function isAdmin()
    {
    	$userData = $this->_auth->getStorage()->read();
    	if ($userData['roles'] == 'admin') {
    		return true;
    	}
    	
    	return false;
    }
    
    public function getLanguages()
    {
    	$langs = $this->_translate->getList();

        $locale = $this->_translate->getLocale();
    	
    	$languagesTitles = array();
	    foreach($langs as $language => $content) {
	    	$languagesTitles[$language] = Zend_Locale::getTranslation($language, 'language', $language);
	    }

    	return $languagesTitles;
    }
    
    public function getRequest()
    {
    	if (empty($this->_request)) {
    		$this->_request = Zend_Controller_Front::getInstance()->getRequest();
    	}
    	
    	return $this->_request;
    }
    
    public function getActionName()
    {
    	return $this->getRequest()->getActionName();
    }
    
    public function getControllerName()
    {
    	return $this->getRequest()->getControllerName();
    }
    
    public function getModuleName()
    {
    	return $this->getRequest()->getModuleName();
    }
    
    public function getImagesUrl()
    {
    	return '/images/'; // we will move this to CDN in the future
    }
    
    public function T($messageid = null)
    {
    	/**
    	 * Process the arguments
    	 */
        $options = func_get_args();
        array_shift($options);
 
        $count  = count($options);
        $locale = null;
        if ($count > 0) {
            if (Zend_Locale::isLocale($options[($count - 1)], null, false) !== false) {
                $locale = array_pop($options);
            }
        }
 
        if ((count($options) === 1) and (is_array($options[0]) === true)) {
            $options = $options[0];
        }
    	
    	/**
         * Proxify the call to Zend_Translate_Adapter
         */
        $message = $this->_translate->translate($messageid, $locale);
        
        /**
         * If no any options provided then just return message
         */
        if ($count === 0) {
            return $message;
        }
 
        /**
         * Apply options in case we have them
         */
        return vsprintf($message, $options);
    }

    /**
     * Outputs language options with the ability to select desired one
     */
    public function languageSelector()
    {
    	$langs = $this->getLanguages();
    	
    	$output = '';
    	foreach ($langs as $key => $lang) {
    		
    		if ($this->getLocale() == $key) {
    			$output .= ' <span class="active-language">' . $lang . '</span> ';
    		} else {
    			$output .= ' <a href="' . $this->url(
					array(
						'lang' => $key,
						'controller' => $this->getControllerName(),
						'action' => $this->getActionName(),
						'module' => $this->getModuleName(),
					)
					,'default')
				. '">' . $lang . '</a> ';
    		}

    	}

    	return $output;
    }
    
    public function renderRegions()
    {
    	if (empty($this->searchForm)) {
    		return '';
    	}
    	
    	$regions = $this->searchForm->getRegions();
    	
    	$regionsHTML = '';
    	foreach ($regions as $reg) {
    		// TODO: we should add locale check here
    		$regionsHTML .= ((empty($regionsHTML)? '' : ',' ) . '<span id="city-' . $reg['id'] . '">' . $reg['name_uk'] . '</span>');
    	}
    	
    	return $regionsHTML;
    }

}