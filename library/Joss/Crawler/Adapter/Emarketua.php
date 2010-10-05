<?php
/**
 * Web crawler adapter for "emarket.ua" domain
 *
 * @name		Joss_Crawler_Adapter_Emarketua
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Adapter_Emarketua extends Joss_Crawler_Adapter_Abstract
{
	protected static $_initialUrl = 'http://emarket.ua/construction/appartments-repair';
	
	protected static $_encoding = 'CP1251';
	
	protected static $_dataLinksPatterns = array (
		  //'@http://emarket.ua/construction/appartments-repair.*@',

		  // data pages
		  '@/construction/.*_[0-9]+\.html$@',
		  '@http://emarket.kiev.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.kh.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.dn.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket-ua.od.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.zp.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.crimea.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.dp.ua/construction/.*_[0-9]+\.html@',

		  // category pages with pagenation
		  '@/construction/appartments-repair/[0-9]+\?.*@',
	);

	public function getData()
	{
		// this will be filled later
	}

}