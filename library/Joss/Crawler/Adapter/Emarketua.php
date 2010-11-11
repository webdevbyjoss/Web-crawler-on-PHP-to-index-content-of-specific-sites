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
	const UNIQUE_ADAPTER_HASH = 'Emarketua';
	
	protected $_encoding = 'CP1251';
	
	protected $_phoneAreaCode = '380';
	
	protected $_initialUrls = null;
	
	protected $_categoryLinks = array (
		  // sub-category pages with pagenation
		  '@/construction/appartments-repair/[0-9]+\?.*@',
		  // sub-category pages
	);
	
	protected $_dataLinks = array (
		  '@/construction/.*_[0-9]+\.html$@',
	);

	/**
	 * The list of domains used on this portal
	 *
	 * @var array
	 */
	private $_domains = array(
			'emarket.kiev.ua',		// Киев и Киевская область
			'emarket.kh.ua',		// Харьков и Харьковская область
			'emarket.dn.ua',		// Донецк и Донецкая область
			'emarket-ua.od.ua',		// Одесса и Одесская область
			'emarket.zp.ua',		// Запорожье и Запорожская область
			'emarket.crimea.ua',	// Симферополь и Крым
			'emarket.dp.ua',		// Днепропетровская область
			'emarket.vn.ua',		// Винница и Винницкая область
			'emarket.lutsk.ua',		// Луцк и Волынская область
			'emarket.zt.ua',		// Житомир и Житомирская область
			'emarket.uz.ua',		// Ужгород и Закарпатская область
			'emarket.if.ua',		// Ивано-Франковск и Ивано-Франковская область
			'emarket.kr.ua',		// Кировоград и Кировоградская область
			'emarket.lg.ua',		// Луганск и Луганская область
			'emarket.lviv.ua',		// Львов и Львовская область
			'emarket.mk.ua',		// Николаев и Николаевская область
			'emarket.pl.ua',		// Полтава и Полтавская область
			'emarket.rv.ua',		// Ровно и Ровенская область
			'emarket.sumy.ua',		// Сумы и Сумская область
			'emarket.te.ua',		// Тернополь и Тернопольская область
			'emarket.ks.ua',		// Херсон и Херсонская область
			'emarket.km.ua',		// Хмельницкий и Хмельницкая область
			'emarket.ck.ua',		// Черкассы и Черкасская область
			'emarket.cn.ua',		// Чернигов и Черниговская область
			'emarket.cv.ua',		// Черновцы и Черновицкая область
			'emarket-ua.ru',		// Москва и Росия
			'emarket.ua', 			// Все регионы Украины
	);

	private $_htmlParser = null;
	
	/**
	 * Lets build the list of URLs
	 */
	protected function init()
	{
		$categoryPatterns = array(
			'interior-design', 		  		// Дизайн интерьеров
			'landscape-design',       		// Ландшафтный дизайн и проектирование
			'steps',						// Лестницы: продажа и установка
			'elevators-escalators',			// Лифты, эскалаторы: продажа и установка
			'gates',						// Ворота, защитные роллеты
			'balconies',					// Балконы: обшивка и утепление
			'window-glasses',				// Окна: продажа и установка
			'fences',						// Ограждение, заборы
			'heating',						// Отопление, обогрев: продажа и установка
			'glass_constructions_glass',	// Стеклянные конструкции, стекло, зеркала
			'security',						// Охранные системы и видеонаблюдение
			'ceiling',						// Потолки: продажа и установка
			'floor',						// Пол, паркет, ламинат: продажа и укладка
			'planning-works',				// Проектные и архитектурные работы
			'appartments-repair',			// Ремонт квартир, домов и офисов
			'sanitation',					// Сантехника: продажа и установка
			'saunas-pools',					// Сауны и бассейны
			'air-conditioning',				// Вентиляция и кондиционирование
			'wall-materials',				// Кирпич, плиты: продажа и укладка
			'constructional-works',			// Строительные и монтажные работы
			'electricity',					// Электрика, проводка: монтаж и продажа
			'other',						// Другие строительные товары и услуги
		);
		
		// we have categories for each region
		foreach ($categoryPatterns as $pattern) {
			$this->_categoryLinks[] = '@http://(' . str_replace('.', '\.', implode('|', $this->_domains)) . ')/construction/' . $pattern . '@';
		}
		
		// each region has its own sub domail URL
		$this->_dataLinks[] = '@http://(' . str_replace('.', '\.', implode('|', $this->_domains)) . ')/construction/.*_[0-9]+\.html@';
		
		// we need initial URL for each category
		foreach ($this->_domains as $domain) {
			$this->_initialUrls[] = 'http://' . $domain . '/construction';
		}
		
	}
	
	public function extractItems()
	{
		// we will use a special class to parse the page data
		// FIXME: possibly after refactoring we can use this class entirely in Abstract class
		// FIXME: we need to move this initialization code to "loadPage" method as this data chould be
		//        regenerated on every new content load
		require_once realpath(dirname(__FILE__) . '/../../../') . '/simple_html_dom.php';
		
		$this->_htmlParser  = str_get_html($this->_lastPageContent);
		$html = $this->_htmlParser;
		
		$info = array();
		// set adapter ID
		$info['adapter'] = self::UNIQUE_ADAPTER_HASH;
		
		// recognize item ID
		preg_match('@/construction/.*_([0-9]+)\.html$@', $this->_currentUrl, $matches);
		$info['id'] = $matches[1];
		$info['url'] = $this->_currentUrl;
		
		// Find all article blocks
		$info['info']['title'] = $html->find('div#content_container_detail h1.title', 0)->plaintext;
		$info['info']['description'] = $html->find('div#block5 table tr td span', 0)->plaintext;
		$name = $html->find('div.lot table table tr td b', 1)->plaintext;
		
		// People specify their phones in the "Contacts" test field
		// some times they specify address along side with the phone number
		// we need take care about that and filter address information
		$phonesField = $html->find('div.lot table table table tr td b i', 0)->plaintext;
		
		// some times people input their phones as "0673089495 | 543299"
		if (strpos($phonesField, '|')) {
			$phoneNumbers = explode('|', $phonesField);
		// some times people input their phones as "0673089495 / 543299"
		} elseif (strpos($phonesField, '/')) {
			$phoneNumbers = explode('/', $phonesField);
		// some times people input their phones as "0673089495 , 543299"
		} elseif (strpos($phonesField, ',')) {
			$phoneNumbers = explode(',', $phonesField);
		// some times people input their phones as "543299 моб. 0673089495"
		} elseif (strpos($phonesField, 'моб.')) {
			$phoneNumbers = explode('моб.', $phonesField);
		// some times people input their phones as "543299 Моб. 0673089495"
		} elseif (strpos($phonesField, 'Моб.')) {
			$phoneNumbers = explode('Моб.', $phonesField);
		// some times people input their phones as "543299 +380673089495"
		} elseif (strpos($phonesField, ' +')) {
			$phoneNumbers = explode(' +', $phonesField);
		// some times people input their phones as "0673089495; 543299"
		} elseif (strpos($phonesField, ';')) {
			$phoneNumbers = explode(';', $phonesField);
		// stupid way to input your phone "54-32-99 067-308-94-95"
		} elseif (preg_match('/\s([0-9][0-9]-[0-9][0-9]-[0-9][0-9])\s/', ' ' . $phonesField . ' ', $matchNumbers)) {
			$phoneNumbers = explode($matchNumbers[1], $phonesField);
			$phoneNumbers[] = $matchNumbers[1];
		// WTF!!! "067 308 94 95 543299"
		} elseif (preg_match('/\s([0-9][0-9][0-9][0-9][0-9][0-9])\s/', ' ' . $phonesField . ' ', $matchNumbers)) {
			$phoneNumbers = explode($matchNumbers[1], $phonesField);
			$phoneNumbers[] = $matchNumbers[1];
		// AAAAA!!! "0382-65-84-74 0673944829 0976123184" TODO: we need to process all the matches and not only first
		} elseif (preg_match('/\s([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])\s/', ' ' . $phonesField . ' ', $matchNumbers)) {
			$phoneNumbers = explode($matchNumbers[1], $phonesField);
			$phoneNumbers[] = $matchNumbers[1];
		} else {
			$phoneNumbers[] = trim($phonesField);
		}

		// also try to recognize phone number from the text body
		// match the number in form of (099) 529-57-33
		if (preg_match('/(\([0-9][0-9][0-9]\) [0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])/', $info['info']['description'], $matchNumbers)) {
			$phoneNumbers[] = $matchNumbers[1];
		}
		
		foreach($phoneNumbers as $number) {
			$number = $this->_normalizePhoneNumber($number);
			if (null !== $number) {
				$info['info']['contacts'][] = array(
					'contact_name' => $name,
					'phone' => $number,
				);
			}
		}

		// FIXME: this is for debug only to determine the situation when phone number wasn't recognized
		if (empty($info['info']['contacts'])) {
			
			$Synonyms = new Joss_Crawler_Db_SynonymsErrors();
			$data = array(
				  'title' => $this->_currentUrl
				, 'lang_id' => $phonesField
			);
			$Synonyms->insert($data);

		}
		
		$info['regions'] = $this->getRegions();
		$info['services'] = $this->getServices($info['info']);

		return array($info);
	}
	
	public function getRegions()
	{
		$html = $this->_htmlParser;
		
		// recognize regions
		$regionsMapping = array(
			'emarket.kiev.ua'	=> '10184,10165',	// Киев и Киевская область
			'emarket.kh.ua'		=> '10532,10504',	// Харьков и Харьковская область
			'emarket.dn.ua'		=> '10029,10002',	// Донецк и Донецкая область
			'emarket-ua.od.ua'	=> '10398,10373',	// Одесса и Одесская область
			'emarket.zp.ua'		=> '10119,10111',	// Запорожье и Запорожская область
			'emarket.crimea.ua'	=> '10252,10227',	// Симферополь и Крым
			'emarket.dp.ua'		=> '9977,9964',	// Днепропетровская область
			'emarket.vn.ua'		=> '9916,9909',	// Винница и Винницкая область
			'emarket.lutsk.ua'	=> '9955,9943',	// Луцк и Волынская область
			'emarket.zt.ua'		=> '10076,10061',	// Житомир и Житомирская область
			'emarket.uz.ua'		=> '10108,10094',	// Ужгород и Закарпатская область
			'emarket.if.ua'		=> '10151,10133',	// Ивано-Франковск и Ивано-Франковская область
			'emarket.kr.ua'		=> '10214,10201',	// Кировоград и Кировоградская область
			'emarket.lg.ua'		=> '10299,10259',	// Луганск и Луганская область
			'emarket.lviv.ua'	=> '10337,10318',	// Львов и Львовская область
			'emarket.mk.ua'		=> '10367,10354',	// Николаев и Николаевская область
			'emarket.pl.ua'		=> '10430,10407',	// Полтава и Полтавская область
			'emarket.rv.ua'		=> '10452,10437',	// Ровно и Ровенская область
			'emarket.sumy.ua'	=> '10475,10455',	// Сумы и Сумская область
			'emarket.te.ua'		=> '10501,10480',	// Тернополь и Тернопольская область
			'emarket.ks.ua'		=> '10556,10535',	// Херсон и Херсонская область
			'emarket.km.ua'		=> '10579,10559',	// Хмельницкий и Хмельницкая область
			'emarket.ck.ua'		=> '10603,10583',	// Черкассы и Черкасская область
			'emarket.cn.ua'		=> '10631,10607',	// Чернигов и Черниговская область
			'emarket.cv.ua'		=> '10647,10633',	// Черновцы и Черновицкая область
			'emarket-ua.ru'		=> '4400,,3159',	// Москва и Росия
			'emarket.ua'		=> ',,9908',	// Все регионы Украины
		);
		
		foreach ($regionsMapping as $pattern => $ids) {
			if (false !== strpos($this->_currentUrl, $pattern)) {
				$regionIds = explode(',', $ids);
				break;
			}
		}

		$regions['tags'] = explode(', ', $html->find('span.region_ad', 0)->plaintext);
		
		if (!empty($regionIds[0])) {
			$regions['cities'] = array($regionIds[0]);
		}

		if (!empty($regionIds[1])) {
			$regions['regions'] = array($regionIds[1]);
		}
		
		if (!empty($regionIds[2])) {
			$regions['countries'] = array($regionIds[2]);
		}

		return $regions;
	}
	
	public function getServices($webInformation)
	{
		$Synonyms = new Joss_Crawler_Db_Synonyms();
		
		$servicesMap = $Synonyms->getTaxonomyRelations();
		$servicesDescriptionMap = $Synonyms->getFullTextRelations();
		
		$html = $this->_htmlParser;
		$servicesText = $html->find('tr#multitag td', 1)->plaintext;
		$serviceTags = explode('|', $servicesText);
		
		$unknown = array();
		$services = array();
		$serviceKeywords = array();
		foreach ($serviceTags as $key => $tag) {
			
			$tag = trim($tag, " .,");
			
			if (!empty($servicesMap[$tag])) {
				
				if (false !== strpos($servicesMap[$tag], ',')) {
					$ids = explode(',', $servicesMap[$tag]);
				} else {
					$ids = array($servicesMap[$tag]);
				}
				
				foreach ($ids as $id) {
					$services[] = array(
						'id' => $id,
						'tags' => $tag
					);
					
					// fill up the keywords array, that will be used to prepare
					// the list of tags that are stored in database
					if (empty($serviceKeywords[$id])) {
						$serviceKeywords[$id] = $tag;
					} else {
						$serviceKeywords[$id] .= (', ' . $tag);
					}
				}

			} else {
				
				if (!empty($tag)) {
					$unknown[$tag] = $this->_currentUrl;
				}
				
			}
		}
		
		// FIXME: we need to collec unknown items during development to populate our wocabulary
		if (!empty($unknown)) {
			
			$Synonyms = new Joss_Crawler_Db_SynonymsErrors();
			
			foreach ($unknown as $term => $url) {
				$info = array(
					  'title' => $url
					, 'lang_id' => $term
				);
				$Synonyms->insert($info);
			}
			
		}
		
		// if service can't be recognized by exact tags match then we can try to recognize this by
		// matching common keywords in the description
		$quaziNumber = array();
		
		// run thrue wocabulary
		foreach ($servicesDescriptionMap as $keyword => $id) {
			
			// in my testing wocabulary I had an empty keywords, so lets filter them
			if (empty($keyword) || empty($id)) {
				continue;
			}
			
			// we need to use UTF-8 case insensetive words match
			// FIXME: this regexp can be improved
			if (preg_match('/[^\w]' . $keyword . '[^\w]/ui', ' ' . $webInformation['description'] . ' ')) {
				
				if (!empty($quaziNumber[$keyword])) {
					$quaziNumber[$keyword]++;
				} else {
					$quaziNumber[$keyword] = 1;
				}
			}
			
			// OK! Lets make title more relevant than description
			// as potentially there is a lot more junk in description
			// FIXME: we 100% need to create a better regexp for this
			if (preg_match('/[^\w]' . $keyword . '[^\w]/ui', ' ' . $webInformation['title'] . ' ')) {
				
				if (!empty($quaziNumber[$keyword])) {
					$quaziNumber[$keyword]++;
					$quaziNumber[$keyword]++;
				} else {
					$quaziNumber[$keyword] = 2;
				}
			}
		}
		
		// Ok, we have the amounts of matched keywords with the appropriate IDs
		// lets get the most valuable of them
		// TODO: we take everything as for now but in the future
		//       when we will collect a lot of data we will filter accidental matches
		//       by calculating the integral power of collection and filter those
		//       tags that donate less than X% to that value, where X - trashhold coefficient
		if (!empty($quaziNumber)) {
			
			// lets get rid of the trash and accidental matches
			// 1. calculate the power for each service
			$totalPowerPerService = array();
			foreach ($quaziNumber as $keyword => $amount) {
				if (false !== strpos($servicesDescriptionMap[$keyword], ',')) {
					$ids = explode(',', $servicesDescriptionMap[$keyword]);
				} else {
					$ids = array($servicesDescriptionMap[$keyword]);
				}
				
				foreach ($ids as $id) {
					if (empty($totalPowerPerService[$id])) {
						$totalPowerPerService[$id] = $amount;
					} else {
						$totalPowerPerService[$id] += $amount;
					}
					
					if (empty($serviceKeywords[$id])) {
						$serviceKeywords[$id] = $keyword;
					} else {
						$serviceKeywords[$id] .= (', ' . $keyword);
					}
					
				}

			}
			
			// 1.1 Lets add some scores for already recognized services
			// taxonomically recognized services are more relevant and
			// will be scored with the x3 factor
			foreach ($services as $s) {
				if (empty($totalPowerPerService[$s['id']])) {
					$totalPowerPerService[$s['id']] = 3;
				} else {
					$totalPowerPerService[$s['id']] += 3;
				}
			}

			// 2. lets calculate the total power
			$totalPowerNumber = 0;
			foreach ($totalPowerPerService as $pow) {
				$totalPowerNumber += $pow;
			}
			
			// 3. calculate trashhold amount
			// for now we croping the items that donate less than 26% to the total power
			$trashHold = $totalPowerNumber / 100 * 26;
			
			// 4. filter possibly axidental matches
			foreach($totalPowerPerService as $index => $pow) {
				if ($pow < $trashHold) {
					unset($totalPowerPerService[$index]);
				}
			}
			
			foreach ($totalPowerPerService as $id => $val) {
				$services[] = array(
					'id' => $id,
					'tags' => $serviceKeywords[$id]
				);
			}

		}
		
		// during development lets investigate strange URLs
		// FIXME: bad practice, don't output anything directly
		if (empty($services)) {
			$Synonyms = new Joss_Crawler_Db_SynonymsErrors();
			$info = array(
				  'title' => $this->_currentUrl
				, 'lang_id' => 'NO SERVICES'
			);
			$Synonyms->insert($info);
		}

		return $services;
	}

}