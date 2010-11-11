<?php
/**
 * This controller will process all the jobs
 */
class Crawler_TestController extends Zend_Controller_Action
{
	public function init()
	{
		// we 100% that actions from this controller will be
		// called from CLI so we disabling layout and auto output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function indexAction()
	{
		// $currentUrl = 'http://emarket.te.ua/construction/remont-kvartir_3741711.html';
		// $currentUrl = 'http://emarket.te.ua/construction/remont-kvartir_2071097.html';
		// $currentUrl = 'http://emarket.te.ua/construction/dveri-vhidni-mizhkimnatni_5247451.html'; // no service tags
		// $currentUrl = 'http://emarket.ks.ua/construction/metallokonstruktsii-i-metalloizdeliya-kovka_3952853.html';
		// $currentUrl = 'http://emarket.ks.ua/construction/metalloizdeliya_4137577.html';
		$currentUrl = 'http://emarket.te.ua/construction/sayding_4246013.html';

		$pageContent = file_get_contents($currentUrl);
		// $pageContent = iconv('CP1251', 'UTF-8', $pageContent);
		
		$Adapter = new Joss_Crawler_Adapter_Emarketua();
		$Adapter->loadPage($currentUrl, $pageContent);
		
		$data = $Adapter->getData();
		
		var_dump($data);
	}
	
	public function synAction()
	{
		$Synonyms = new Joss_Crawler_Db_Synonyms();
		
		$servicesMap = $Synonyms->getTaxonomyRelations();
		$servicesDescriptionMap = $Synonyms->getFullTextRelations();
		
		var_dump($servicesMap);
		var_dump($servicesDescriptionMap);
	}
	
	public function loadAction()
	{
		$Synonyms = new Joss_Crawler_Db_Synonyms();
		
		$servicesMap = array (
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
		);
		
		foreach ($servicesMap as $key => $value) {
			
			$data = array(
				'tags_type' => Joss_Crawler_Db_Synonyms::TYPE_TAXONOMY,
				'title' => $key,
				'lang_id' => 'ru',
			);
			
			$synonymId = $Synonyms->insert($data);
			
			if (false !== strpos($value, ',')) {
				$services = explode(',', $value);
			} else {
				$services = array($value);
			}
			
			$SynonymsServices = new Joss_Crawler_Db_SynonymsServices();
			foreach ($services as $service) {
				$data = array(
					'synonym_id' => $synonymId,
					'service_id' => $service
				);
				$SynonymsServices->insert($data);
			}
		}
		
		$servicesDescriptionMapUA = array (
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
		);
		
		
		foreach ($servicesDescriptionMapUA as $key => $value) {
			
			$data = array(
				'tags_type' => Joss_Crawler_Db_Synonyms::TYPE_FULLTEXT,
				'title' => $key,
				'lang_id' => 'uk',
			);
			
			$synonymId = $Synonyms->insert($data);
			
			if (false !== strpos($value, ',')) {
				$services = explode(',', $value);
			} else {
				$services = array($value);
			}
			
			$SynonymsServices = new Joss_Crawler_Db_SynonymsServices();
			foreach ($services as $service) {
				$data = array(
					'synonym_id' => $synonymId,
					'service_id' => $service
				);
				$SynonymsServices->insert($data);
			}
		}
		
		$servicesDescriptionMapRU = array(
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
		);
		
		
		foreach ($servicesDescriptionMapRU as $key => $value) {
			
			$data = array(
				'tags_type' => Joss_Crawler_Db_Synonyms::TYPE_FULLTEXT,
				'title' => $key,
				'lang_id' => 'uk',
			);
			
			$synonymId = $Synonyms->insert($data);
			
			if (false !== strpos($value, ',')) {
				$services = explode(',', $value);
			} else {
				$services = array($value);
			}
			
			$SynonymsServices = new Joss_Crawler_Db_SynonymsServices();
			foreach ($services as $service) {
				$data = array(
					'synonym_id' => $synonymId,
					'service_id' => $service
				);
				$SynonymsServices->insert($data);
			}
		}

	}
}