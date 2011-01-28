<?php
/**
 *
 * @author Andriy
 * Model class for manipulate feedback
 *
 */
class Users_Model_Feedback
{
	/**
	 *
	 * @var array $_modes
	 * array of modes in which feedback message have been stored
	 * possible values at that time is 'bugtracker' and 'dbase'
	 */
	private $_modes;
	 
	public function __construct($modes=array())
	{
		$modes = (array)$modes;
		if (empty($modes)) {
			$this->setModes(array('bugtracker'));
		}
	}
	
	public function setModes($modes)
	{
		$this->_modes = (array)$modes;
	}
	
	public function save($params=array())
	{
		if (!$params) {
			return false;
		}
		
		$bugtrackerAdapter = new Model_OfuzAdapter();
	}
}