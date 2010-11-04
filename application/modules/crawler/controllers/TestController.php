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
		$currentUrl = 'http://emarket.vn.ua/construction/venetsianskie-shtukaturki_5201053.html';

		$pageContent = file_get_contents($currentUrl);
		// $pageContent = iconv('CP1251', 'UTF-8', $pageContent);
		
		$Adapter = new Joss_Crawler_Adapter_Emarketua();
		$Adapter->loadPage($currentUrl, $pageContent);
		
		$data = $Adapter->getData();
		
		var_dump($data);
	}
}