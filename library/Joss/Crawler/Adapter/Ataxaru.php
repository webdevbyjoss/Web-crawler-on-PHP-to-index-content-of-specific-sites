<?php
/**
 * Web crawler adapter for "ataxa.ru" domain
 *
 * @name		Joss_Crawler_Adapter_Ataxaru
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Adapter_Ataxaru extends Joss_Crawler_Adapter_Abstract
{
	const UNIQUE_ADAPTER_HASH = 'Ataxaru';
	
	protected $_initialUrls = 'http://ataxa.ru/uslugi/stroitelstvo_remont/';
}