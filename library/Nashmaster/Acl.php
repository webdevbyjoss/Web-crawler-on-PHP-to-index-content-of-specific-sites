<?php

class Nashmaster_Acl extends Zend_Acl
{
	const ROLE_GUEST	= 'guest';
	const ROLE_USER		= 'user';
	const ROLE_ADMIN	= 'admin';
	const ROLE_CRON		= 'cron';
	
	public function __construct()
	{
		// set the list of roles
		$this->addRole(new Zend_Acl_Role(self::ROLE_GUEST));
		$this->addRole(new Zend_Acl_Role(self::ROLE_USER), self::ROLE_GUEST);
		$this->addRole(new Zend_Acl_Role(self::ROLE_ADMIN));
		$this->addRole(new Zend_Acl_Role(self::ROLE_CRON));
		
		// set the list of resources
		$this->add(new Zend_Acl_Resource('mvc:default'));
		$this->add(new Zend_Acl_Resource('mvc:crawler'));
		$this->add(new Zend_Acl_Resource('mvc:search'));
		
		// setup permissions
		$this->allow(self::ROLE_GUEST, 'mvc:default');
		$this->allow(self::ROLE_GUEST, 'mvc:search');

		$this->allow(self::ROLE_ADMIN, 'mvc:crawler');
		$this->allow(self::ROLE_ADMIN, 'mvc:default');
		$this->allow(self::ROLE_CRON, 'mvc:crawler');
	}
}