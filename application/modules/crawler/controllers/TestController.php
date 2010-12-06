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
	
	public function searchAction()
	{
		$options = array(
			'keywords' => 'укладка паркету 333, тернопіль іва фі=- тернополь -  фівафіва %:?№%;""'
		);
		
		$SearchForm = new Nashmaster_SearchForm($options);
		$data = $SearchForm->setKeywords($options['keywords']);
	}
	
	public function translateAction()
	{
		$ruUrl = 'http://ru.wikipedia.org/wiki/';
		$ukUrl = 'http://uk.wikipedia.org/wiki/';
		
		$Client = new Zend_Http_Client();
		
		// 1. Get cities from the database
		$Cities = new Searchdata_Model_Cities();
		$citiesRowset = $Cities->getItems();
		echo "\nReceived the list of cities: " . count($citiesRowset);
		
		foreach ($citiesRowset as $city) {
			echo "\nRetreiving information for.. " . $city->name;
			$title = '';
			$Client->setUri($ruUrl . urlencode($city->name));
			$response = $Client->request();
			$htmlData = $response->getBody();
			$pattern = '@<li class="interwiki-uk"><a href="http://uk\.wikipedia\.org/wiki/(.*)" title="(.*)">Українська</a></li>@u';
			preg_match($pattern, $htmlData, $matchData);
			if (!empty($matchData[1])) {

				$title = urldecode($matchData[1]);
				
				if (false !== strpos($title, '_(')) {
					
					$title = preg_replace('/_\(.*\)/u', '', $title);
					
					$title =
				}
				
				if (!empty($title)) {
					$city->name_uk = $title;
					$city->save();
				}

			}
			
			// SELECT * FROM city WHERE name_uk LIKE '%_(%';
			

		}
		
	}
}