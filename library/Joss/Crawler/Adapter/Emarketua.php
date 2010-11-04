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
		// some times people input their phones as "0673089495 , 543299"
		} elseif (strpos($phonesField, ',')) {
			$phoneNumbers = explode(',', $phonesField);
		// some times people input their phones as "0673089495; 543299"
		} elseif (strpos($phonesField, ';')) {
			$phoneNumbers = explode(';', $phonesField);
		// stupid way to input your phone "54-32-99 067-308-94-95"
		} elseif (preg_match('/\b([0-9][0-9]-[0-9][0-9]-[0-9][0-9])\b/', ' ' . $phonesField . ' ', $matchNumbers)) {
			$phoneNumbers = explode($matchNumbers[1], $phonesField);
			$phoneNumbers[] = $matchNumbers[1];
		// WTF!!! "067 308 94 95 543299"
		} elseif (preg_match('/\b([0-9][0-9][0-9][0-9][0-9][0-9])\b/', ' ' . $phonesField . ' ', $matchNumbers)) {
			$phoneNumbers = explode($matchNumbers[1], $phonesField);
			$phoneNumbers[] = $matchNumbers[1];
		} else {
			$phoneNumbers[] = trim($phonesField);
		}
		
		// FIXME: this is for debug only to determine the situation when phone number wasn't recognized
		if (empty($phoneNumbers)) {
			
			$Synonyms = new Joss_Crawler_Db_Synonyms();
			$info = array(
				  'title' => $this->_currentUrl
				, 'lang_id' => $phonesField
			);
			$Synonyms->insert($info);

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
			'emarket-ua.ru'		=> '4400',	// Москва и Росия
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
		$servicesMap = array(
			'гипсокартонные работы'	=> '14,21,31',
			'малярные работы'		=> '50',
			'плиточные работы'		=> '18,20',
			'поклейка обоев'		=> '16',
			'сантехработы'			=> '4',
			'укладка полов'			=> '51',
			'теплоизоляция' 		=> '62',
			'дизайн квартир'		=> '6',
			'дизайн домов' 			=> '6',
			'входные' 				=> '25',
			'металлопластиковые' 	=> '25',
			'грунтовки' 			=> '12',
			'шпатлёвки' 			=> '35',
			'межкомнатные' 			=> '25',
			'бронедвери' 			=> '25',
			'вагонка' 				=> '15,44',
			'лестницы на больцах'	=> '63',
			'лестницы на косоурах' 	=> '63',
			'гидроизоляция'			=> '64',
			'лестницы на консолях' 	=> '63',
			'мембранные покрытия' 	=> '61',
			'лестницы на тетивах' 	=> '63',
			'лестницы' 				=> '63',
			'дизайн кафе' 			=> '6',
			'дизайн ночных клубов' 	=> '6',
			'дизайн офисов' 		=> '6',
			'дизайн развлекательных центров' => '6',
			'дизайн ресторанов' 	=> '6',
			'влагоизоляция' 		=> '64',
			'рулонная кровля' 		=> '61',
			'универсальная гидро-пароизоляция' => '64',
			'поликарбонат' 			=> '65',
			'пароизоляция' 			=> '64',
			'мастичная кровля' 		=> '61',
			'лестницы' 				=> '63',
			'отражающая теплоизоляция' => '62',
			'стропила' 				=> '61',
			'ветроизоляция' 		=> '62',
			'водяное отопление' 	=> '59',
			'электрическое отопление' => '59',
			'газовое отопление' 	=> '59',
			'городской или селитебный ландшафт' => '7',
			'сайдинг' 				=> '3',
			'видеонаблюдение' 		=> '66',
			'сантехника' 			=> '4',
			'печное отопление' 		=> '59',
			'инфракрасное отопление' => '59',
			'напольные покрытия' 	=> '51',
			'отопление' 			=> '59',
			'сигнализации'			=> '66',
			'датчики движения' 		=> '66',
			'ламинат' 				=> '23',
			'насосное оборудование' => '4',
			'воздушное отопление' 	=> '59',
			'двери' 				=> '25',
			'окна' 					=> '25',
			'электрика' 			=> '5',
			'подоконники' 			=> '32',
			'горнопромышленный ландшафт' => '7',
			'домофоны'				=> '66',
			'монтаж металлоконструкций' => '58',
			'ирригационно-технический ландшафт' => '7',
			'сельскохозяйственный ландшафт' => '7',
			'военный ландшафт'		=> '7',
			'грузовые лифты' 		=> '67',
			'проводка' 				=> '5',
			'rehau' 				=> '25',
			'малые грузовые лифты' 	=> '67',
			'компрессора' 			=> '4',
			'сантехническое оборудование' => '4',
			'тепловое оборудование' => '59',
			'сварочное оборудование' => '58',
			'вентиляция' 			=> '60',
			'пропитки' 				=> '64',
			'гипсокартонные потолки' => '31',
			'натяжные потолки' 		=> '26',
			'подвесные потолки' 	=> '31',
			'паркет' 				=> '19',
			'каменные работы' 		=> '68',
			'пассажирские лифты' 	=> '67',
			'линейные эскалаторы' 	=> '67',
			'автомобильные подъёмники' => '67',
			'лифты для инвалидов' 	=> '67',
			'кладка кирпича' 		=> '69',
			'фундаментные работы' 	=> '42',
			'штукатурные работы' 	=> '49',
			'коттеджные лифты' 		=> '67',
			'алюминиевые потолки' 	=> '70',
			'бетон' 				=> '40',
			'паркетная доска' 		=> '27',
			'кондиционеры' 			=> '60',
			'ковролин' 				=> '22',
			'линолиум' 				=> '24',
			'плитка' 				=> '18',
			'счётчики'				=> '4',
			'счeтчики'				=> '4',
			'трансформаторы' 		=> '5',
			'грунтобетонные' 		=> '40',
			'Техноплекс' 			=> '62',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
		);
		
		// NOTE: this is for case insencetive search
		$servicesDescriptionMap = array(
			'Двері вхідні,' 			=> '25',
			'міжкімнатні' 				=> '25',
			'євроремонт' 				=> '1',
			'гіпсокартон'				=> '21',
			'шпаклювання'				=> '35',
			'монтаж санвузлів' 			=> '4',
			'електрика' 				=> '5',
			'шпалери' 					=> '16',
			'ламінат' 					=> '23',
			'декоративна штукатурка'	=> '37',
			'маляр' 					=> '50',
			'штукатур' 					=> '37',
			'плиточник' 				=> '18',
			'Малярні роботи' 			=> '50',
			'Вставляю двері' 			=> '25',
			'штукатурка' 				=> '37',
			'стяжка' 					=> '41',
			'шпатлювання' 				=> '35',
			'гіпсокартоном' 			=> '21',
			'плитка' 					=> '18',
			'облаштування плиткою' 		=> '18',
			'вагонка' 					=> '15',
			'встановлення сантехніки' 	=> '4',
			'сантехніка' 				=> '4',
			'душові кабіни' 			=> '4',
			'умивальники' 				=> '4',
			'унітази' 					=> '4',
			'біде' 						=> '4',
			'змішувачі' 				=> '4',
			'електромонтаж' 			=> '5',
			'проводки' 					=> '5',
			'проводка' 					=> '5',
			'електрообладнання' 		=> '5',
			'розетки' 					=> '5',
			'люстри' 					=> '5',
			'світильники'				=> '5',
			'піддони' 					=> '51',
			'ЗАБОРИ' 					=> '53',
			'КОЗИРКИ' 					=> '53',
			'РЕШІТКИ НА ВІКНА' 			=> '53',
			'ДВЕРІ' 					=> '25',
			'поручнів' 					=> '53',
			'поручні'					=> '53',
			'навісів'					=> '53',
			'навіс'						=> '53',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'ворота' 					=> '53',
			'перила' 					=> '53',
			'оградки' 					=> '53',
			'решетки' 					=> '53',
			'Утепление фасадов'			=> '48',
			'Утепление внешних стен'	=> '48',
			'Утепление пенопластом'		=> '48',
			'Короед' 					=> '47',
			'покраска' 					=> '17',
			'ремонт квартир' 			=> '1',
			'отопление'					=> '4',
			'сантехника' 				=> '4',
			'электрика' 				=> '5',
			'штукатурка'				=> '37',
			'малярка'					=> '50',
			'плитка'					=> '18',
			'гипсокартон'				=> '21',
			'ламинат'					=> '23',
			'тёплый пол'				=> '51',
			'пол'						=> '51',
			'дизайн'					=> '6',
			'отделочных работ'			=> '54',
			'поддоны'					=> '51',
			'евро поддоны'				=> '51',
			'обои'						=> '16',
			'балкон' 					=> '55',
			'двери' 					=> '25',
			'окно'						=> '25',
			'окна'						=> '25',
			'Сетки' 					=> '57',
			'жалюзи' 					=> '57',
			'роллеты' 					=> '57',
			'Сварочные работы' 			=> '58',
			'Металлоконструкции' 		=> '58',
			'систем отопления'			=> '59',
			'системы отопления'			=> '59',
			'водопровода'				=> '4',
			'водопровод'				=> '4',
			'вентеляции'				=> '60',
			'вентеляция'				=> '60',
			'котлы'						=> '59',
			'газовые котлы' 			=> '59',
			'бойлеры' 					=> '59',
			'конвекторы'				=> '59',
			'газовые регуляторы' 		=> '59',
			'газового оборудования' 	=> '59',
			'черепица'					=> '61',
			'Кровельные работы' 		=> '61',
			'кровля'					=> '61',
			'кровли' 					=> '61',
			'ремонт кровли' 			=> '61',
			'мягкая кровля' 			=> '61',
			'плитка тротуарная'			=> '7',
			'поликарбонатом' 			=> '65',
			'фасадную плитку'			=> '7',
			'фасадная плитка'			=> '7',
			'еврозаборы' 				=> '7',
			'козырьки' 					=> '65',
			'навесы' 					=> '65',
			'поликарбонат' 				=> '65',
			'пороги' 					=> '25',
			'карнизы' 					=> '25',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
			'' => '',
		);
		
		$html = $this->_htmlParser;
		$servicesText = $html->find('tr#multitag td', 1)->plaintext;
		$serviceTags = explode('|', $servicesText);
		
		$unknown = array();
		foreach ($serviceTags as $key => $tag) {
			
			$tag = trim($tag, " .,");
			
			if (!empty($servicesMap[$tag])) {
				
				$services[] = array(
					'id' => $servicesMap[$tag],
					'tags' => $tag
				);
				
			} else {
				
				if (!empty($tag)) {
					$unknown[$tag] = $this->_currentUrl;
				}
				
			}
		}

		// FIXME: we need to collec unknown items during development to populate our wocabulary
		if (!empty($unknown)) {
			
			$Synonyms = new Joss_Crawler_Db_Synonyms();
			
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
		if (empty($services)) {
			
			$quaziNumber = array();
			
			// run thrue wocabulary
			foreach ($servicesDescriptionMap as $keyword => $id) {
				
				// in my testing wocabulary I had an empty keywords, so lets filter them
				if (empty($keyword) || empty($id)) {
					continue;
				}
				
				// we need to use UTF-8 case insensetive words match
				// FIXME: this regexp can be improved
				if (preg_match('/[\s,\.;:]' . $keyword . '[\s,\.;:]/ui', ' ' . $webInformation['description'] . ' ')) {
					
					if (!empty($quaziNumber[$keyword])) {
						$quaziNumber[$keyword]++;
					} else {
						$quaziNumber[$keyword] = 1;
					}
				}
				
				// OK! Lets make title more relevant than description
				// as potentially there is a lot more junk in description
				// FIXME: we 100% need to create a better regexp for this
				if (preg_match('/[\s,\.;:]' . $keyword . '[\s,\.;:]/ui', ' ' . $webInformation['title'] . ' ')) {
					
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
				foreach ($quaziNumber as $keyword => $amount) {
					$services[] = array(
						'id' => $servicesDescriptionMap[$keyword],
						'tags' => $keyword
					);
				}
			}
		}
		
		// during development lets investigate strange URLs
		// FIXME: bad practice, don't output anything directly
		if (empty($services)) {
			$Synonyms = new Joss_Crawler_Db_Synonyms();
			$info = array(
				  'title' => $this->_currentUrl
				, 'lang_id' => 'NO SERVICES'
			);
			$Synonyms->insert($info);
		}
		
		return $services;
		// return $unknown;
	}

}