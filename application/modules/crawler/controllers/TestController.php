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
		$SearchForm->setKeywords($options['keywords']);
		
		$data = $SearchForm->getAdapter();
	}
	
	public function translateAction()
	{
		$ruUrl = 'http://ru.wikipedia.org/wiki/';
		$ukUrl = 'http://uk.wikipedia.org/wiki/';
		
		$Client = new Zend_Http_Client();
		$Client->setConfig(
			array(
				'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
				'timeout' => '30',
				'keepalive' => true,
				'encodecookies' => true,
			)
		);
		
		// 1. Get cities from the database
		$Cities = new Searchdata_Model_Cities();
		$citiesRowset = $Cities->getItems(array(9909,9943,9964,10002,10061,10094,10111,10133,10165,10201,10227,10259,10318,10354,10373,10407,10437,10455,10480,10504,10535,10559,10583,10607,10633));
		echo "\nReceived the list of cities: " . count($citiesRowset);
		
		foreach ($citiesRowset as $city) {

			if (!empty($city->name_uk)) {
				continue;
			}
			
			$title = '';
			$Client->setUri($ruUrl . urlencode($city->name));
			
			try {
				
				$response = $Client->request();
				
			} catch (Zend_Http_Client_Adapter_Exception $e) {

				// we need a pause during 1 second between calls to avoid web-server overload
				sleep(1);
				continue;
			}
			
			$htmlData = $response->getBody();
			$pattern = '@<li class="interwiki-uk"><a href="http://uk\.wikipedia\.org/wiki/(.*)" title="(.*)">Українська</a></li>@u';
			preg_match($pattern, $htmlData, $matchData);
			
			if (empty($matchData[1])) {
				continue;
			}

			$title = urldecode($matchData[1]);
			if (false !== strpos($title, '_(')) {
				$title = preg_replace('/_\(.*\)/u', '', $title);
			}
			
			if (false !== strpos($title, '_')) {
				continue;
			}

			$city->name_uk = $title;
			$city->save();
		}

	}

}