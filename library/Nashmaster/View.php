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
    
   /**
    * output form for users feedback
    */
    public function feedbackForm()
    {
    	return new Users_Form_Feedback();
    }
    
    public function renderRegions()
    {
    	if (empty($this->searchForm)) {
    		return '';
    	}
    	
    	$regions = $this->searchForm->getRegions();
    	
    	$regionsHTML = '';
    	foreach ($regions as $reg) {
    		$name = ($this->getLocale() == 'uk') ? $reg['name_uk'] : $reg['name'];
    		$regionsHTML .= ((empty($regionsHTML)? '' : ',' ) . '<span id="city-' . $reg['id'] . '">' . $name . '</span>');
    	}
    	
    	return $regionsHTML;

    }

	/**
	 * json_encode() replacement that handles cyrillyc characters correctly
	 *
	 * Thanks to: http://www.php.net/manual/en/function.json-encode.php#78719
	 *
	 * @param mixed $a
	 * @return string json code
	 */
	public function php2js($a=false)
	{
		  if (is_null($a)) return 'null';
		  if ($a === false) return 'false';
		  if ($a === true) return 'true';
		  if (is_scalar($a))
		  {
		    if (is_float($a))
		    {
		      // Always use "." for floats.
		      $a = str_replace(",", ".", strval($a));
		    }
		
		    // All scalars are converted to strings to avoid indeterminism.
		    // PHP's "1" and 1 are equal for all PHP operators, but
		    // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
		    // we should get the same result in the JS frontend (string).
		    // Character replacements for JSON.
		    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
		    array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
		    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		  }
		  $isList = true;
		  for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		  {
		    if (key($a) !== $i)
		    {
		      $isList = false;
		      break;
		    }
		  }
		  $result = array();
		  if ($isList)
		  {
		    foreach ($a as $v) $result[] = $this->php2js($v);
		    return '[ ' . join(', ', $result) . ' ]';
		  }
		  else
		  {
		    foreach ($a as $k => $v) $result[] = $this->php2js($k).': '.$this->php2js($v);
		    return '{ ' . join(', ', $result) . ' }';
		  }
	}
	
}