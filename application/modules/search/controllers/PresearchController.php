<?php

class Search_PresearchController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
	}
	
	public function queryAction()
	{
		$options = array();
    	$options['remote_ip'] = $_SERVER['REMOTE_ADDR'];
    	$options['search_keywords'] = $this->getRequest()->data;
		$SearchForm = new Nashmaster_SearchForm($options);

		// retrieve data from search form
		$this->view->regions = $SearchForm->getRegions();
		$this->view->services = $SearchForm->getServices();

		// we need to have a locale to display services and cities in appropriate language
		// but right now the cities are displayed in Russian in case Ukrainian name is not available
		// and services are displayed in the current locale language selected by user
		$locale = $this->view->getLocale();

		// write search statistics
		$StatKeywords = new Search_Model_StatKeywords();
		$StatKeywords->add($options['search_keywords'], $locale, implode(',', array_keys($this->view->services)), implode(',', array_keys($this->view->regions)));
		
		// if no any services were recognized then output the region and suggested strings in case of typos
		if (empty($this->view->services)) {
			$suggest = $SearchForm->getSuggest($locale);
			if (!empty($suggest)) {
				$this->view->suggest = $suggest;
				return;
			}
			
			// this can be used in case no any service were recognized for suggestion
			// we have have only region - so we can ask people to correct their request
			$this->view->noservice = true;
			return;
		}

		// process synonyms for search results highlight
		$synonymsByServices = $SearchForm->getMatchSynonyms();
		$SynonymServices = new Joss_Crawler_Db_SynonymsServices();
		$Synonyms = new Joss_Crawler_Db_Synonyms();
		
		$match = $options['search_keywords'];
		foreach ($synonymsByServices as $serviceId => $serviceMatch) {
			
			// assign direct matches from keywords
			$match .= ',' . implode(',', $serviceMatch);
			
			/*
			 * TODO: we need to improve and reorganize our services structure
			 *       to have better results on inexplicid match
			 *
			// assign inexplicid matches from service synonyms
			$synonymIdsRowset = $SynonymServices->getSynonymsByServiceId($serviceId);
			$synonymIds = array();
			foreach ($synonymIdsRowset as $val) {
				$synonymIds[] = $val->synonym_id;
			}
			
			$synonymsRowset = $Synonyms->getItemsByIds($synonymIds);
			$synTitle = array();
			foreach ($synonymsRowset as $syn) {
				$synTitle[] = $syn->title;
			}
			
			$data['match'] .= ',' . implode(',', $synTitle);
			*/
		}
		
		$this->view->match = $match;
	}
}