<?php
/**
 * Store the list of links and their processing statuses
 * 
 * @name		Joss_Crawler_Adapter_Emarketua
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Links implements ArrayAccess, Iterator
{
	/**
	 * Hold the list of links here
	 */
	private $container = null;
	
	/**
	 * Save the namespace for further use 
	 * @var string
	 */
	private $namespace = null;
	
	/**
	 * Initiate container
	 * 
	 * @param string $namespace
	 */
    public function __construct($namespace)
    {
    	/*
    	 * Set the initial list
    	 * 
    	 * TODO: read the initial set of links from external data source
    	 * 		 using the apropriate "namespace"
    	 *  
    	 * @var array
    	 */
        $this->container = array();
    }
    
    /**
     * ArrayAccess implementation 
     * 
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
    	if (!empty($this->container[$offset])) {
    		return true;
    	}
    	return false;
    }
    
    public function offsetGet($offset)
    {
        if (!empty($this->container[$offset])) {
    		return $this->container[$offset];
        }
    }
    
    public function offsetSet($offset, $value)
    {
    	$this->container[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
    	unset($this->container[$offset]);
    }
    
}