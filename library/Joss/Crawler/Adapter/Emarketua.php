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
	protected $_encoding = 'CP1251';
	
	// general category page "Строительство, Ремонт Украина"
	protected $_initialUrl = 'http://emarket.ua/construction';
	
	protected $_categoryLinks = array (
		  // sub-category pages with pagenation
		  '@/construction/appartments-repair/[0-9]+\?.*@',
		  // sub-category pages
		  // Дизайн интерьеров Украина
		  '@http://emarket.ua/construction/interior-design@',
		  // Ландшафтный дизайн и проектирование Украина
		  '@http://emarket.ua/construction/landscape-design@',
		  // Лестницы: продажа и установка Украина
		  '@http://emarket.ua/construction/steps@',
		  // Лифты, эскалаторы: продажа и установка Украина
		  '@http://emarket.ua/construction/elevators-escalators@',
		  // Ворота, защитные роллеты Украина
		  '@http://emarket.ua/construction/gates@',
		  // Балконы: обшивка и утепление Украина
		  '@http://emarket.ua/construction/balconies@',
		  // Окна: продажа и установка Украина
		  '@http://emarket.ua/construction/window-glasses@',
		  // Ограждение, заборы Украина
		  '@http://emarket.ua/construction/fences@',
		  // Отопление, обогрев: продажа и установка Украина
		  '@http://emarket.ua/construction/heating@',
		  // Стеклянные конструкции, стекло, зеркала Украина
		  '@http://emarket.ua/construction/glass_constructions_glass@',
		  // Охранные системы и видеонаблюдение Украина
		  '@http://emarket.ua/construction/security@',
		  // Потолки: продажа и установка Украина
		  '@http://emarket.ua/construction/ceiling@',
		  // Пол, паркет, ламинат: продажа и укладка Украина
		  '@http://emarket.ua/construction/floor@',
		  // Проектные и архитектурные работы Украина
		  '@http://emarket.ua/construction/planning-works@',
		  // Ремонт квартир, домов и офисов Украина
		  '@http://emarket.ua/construction/appartments-repair@',
		  // Сантехника: продажа и установка Украина
		  '@http://emarket.ua/construction/sanitation@',
		  // Сауны и бассейны Украина
		  '@http://emarket.ua/construction/saunas-pools@',
		  // Вентиляция и кондиционирование Украина
		  '@http://emarket.ua/construction/air-conditioning@',
		  // Кирпич, плиты: продажа и укладка Украина
		  '@http://emarket.ua/construction/wall-materials@',
		  // Строительные и монтажные работы Украина
		  '@http://emarket.ua/construction/constructional-works@',
		  // Электрика, проводка: монтаж и продажа Украина
		  '@http://emarket.ua/construction/electricity@',
		  // Другие строительные товары и услуги Украина
		  '@http://emarket.ua/construction/other@',
	);

	protected $_dataLinks = array (
		  '@/construction/.*_[0-9]+\.html$@',
		  '@http://emarket.kiev.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.kh.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.dn.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket-ua.od.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.zp.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.crimea.ua/construction/.*_[0-9]+\.html@',
		  '@http://emarket.dp.ua/construction/.*_[0-9]+\.html@',
	);
	
	protected function extractItems()
	{
		// we will use a special class to parse the page data
		require_once realpath(dirname(__FILE__) . '/../../../') . '/simple_html_dom.php';
		
		$html = file_get_html($this->_lastPageContent);
		
		
		$info = array();
		// set adapter ID
		$info['adapter'] = __CLASS__;
		
		// recognize item ID
		preg_match('@/construction/.*_([0-9]+)\.html$@', $this->_currentUrl, $matches);
		$info['id'] = $matches[1];
		
		// Find all article blocks
		$info['info']['title'] = $html->find('div#content_container_detail h1.title', 0)->plaintext;
		$info['info']['description'] = $html->find('div#block5 table tr td span', 0)->plaintext;
		$info['info']['contact_name'] = $html->find('div.lot table table tr td b', 1)->plaintext;
		$info['info']['phone'] = $this->_normalizePhoneNumber($html->find('div.lot table table table tr td b i', 0)->plaintext);
		
		$info['regions'] = $this->getRegions();
		$info['services'] = $this->getServices();
		
		return array($info);
	}
	
	public function getRegions()
	{
		// recognize regions
		$regionsMapping = array(
			0 => 'http://emarket.kiev.ua',
			1 => 'http://emarket.kh.ua',
			2 => 'http://emarket.dn.ua',
			3 => 'http://emarket-ua.od.ua',
			4 => 'http://emarket.zp.ua',
			5 => 'http://emarket.crimea.ua',
			6 => 'http://emarket.dp.ua',
		);
		
		foreach ($regionsMapping as $id => $pattern) {
			if (false !== strpos($this->_currentUrl, $pattern)) {
				$regionId = $id;
				break;
			}
		}
		
		$regionTags = explode(', ', $html->find('span.region_ad', 0)->plaintext);
		
		$regions[] = array('id' => $regionId, 'tags' => $regionTags);

		return $regions;
	}
	
	public function getServices()
	{
		$serviceId = 2;
		$serviceTags = $html->find('tr#multitag td', 1)->plaintext;
		
		$services[] = array('id' => $serviceId, 'tags' => $serviceTags);
		
		return $services;
	}

}