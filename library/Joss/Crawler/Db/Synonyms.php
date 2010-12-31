<?php
/**
 * Database table gateway to manage services synonyms crawled from the web and stored in database
 *
 * @name		Joss_Crawler_Db_Synonyms
 * @version		0.0.1
 * @package		joss-crawler
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Joss_Crawler_Db_Synonyms extends Zend_Db_Table_Abstract
{
	const TYPE_TAXONOMY = 0;
	const TYPE_FULLTEXT = 1;
	
	/**
	 * The name of the database table
	 *
	 * FIXME: I'm not sure wether this must be on model level or library level
	 *        its just I don't want this library to be depended on any specific application
	 *
	 * @var string
	 */
	protected $_name = 'crawl_data_synonyms';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'id';
	
	/**
	 * Returns the data and relations for synonyms
	 *
	 * @param int type $type the type of synonym
	 * @param string $lang language code, null for all languages
	 * @return array
	 */
	public function getSynonymsRelations($type = null, $lang = null)
	{
		$synonyms = $this->getData($type, $lang);
		
		foreach ($synonyms as $syn) {
			$ids[] = $syn['id'];
		}

		$SynonymServices = new Joss_Crawler_Db_SynonymsServices();
		$relations = $SynonymServices->getRelationsByIds($ids);

		// FIXME: this should be done in more accurate maner to eliminate processor and memory load
		$synonymRelations = array();
		$synonymRelations = array();
		foreach ($synonyms as $syn) {
			
			$currentRelations = array();
			foreach ($relations as $rel) {
				
				if ($syn['id'] == $rel['synonym_id']) {
					$currentRelations[] = $rel['service_id'];
				}
			}
			
			// this is in case we have a simmilar synonyms but with the different relations
			if (!empty($synonymRelations[$syn['title']])) {
				$synonymRelations[$syn['title']] = $synonymRelations[$syn['title']] . ',' . implode(',', $currentRelations);
			} else {
				$synonymRelations[$syn['title']] = implode(',', $currentRelations);
			}
		}
		
		return $synonymRelations;
	}
	
	/**
	 * Returns the list of taxonomy relations
	 *
	 * @param string $lang language code, null for all languages
	 * @return array
	 */
	public function getTaxonomyRelations($lang = null)
	{
		return $this->getSynonymsRelations(self::TYPE_TAXONOMY, $lang);
	}
	
	/**
	 * Returns the list of full text searh keywords relations
	 *
	 * @param string $lang language code, null for all languages
	 * @return array
	 */
	public function getFullTextRelations($lang = null)
	{
		return $this->getSynonymsRelations(self::TYPE_FULLTEXT, $lang);
	}
	
	/**
	 * Get the synonyms according to the provided filters
	 *
	 * @param int $type class constands
	 * @param string $lang language code, null for all languages
	 * @return Zend_Db_Table_Row
	 */
	public function getData($type = null, $lang = null)
	{
		$select = $this->select();
		if (null !== $type) {
			$select->where('tags_type = ?', $type);
		}
		
		if (null !== $lang) {
			$select->where('lang_id = ?', $lang);
		}
		
		return $this->fetchAll($select);
	}
	
	
	public function getElementByText($text, $type = null, $lang = null)
	{
		$select = $this->select();
		
		$select->where('title = ?', $text);
		
		if (null !== $type) {
			$select->where('tags_type = ?', $type);
		}
		
		if (null !== $lang) {
			$select->where('lang_id = ?', $lang);
		}

		$item = $this->fetchRow($select);
		
		if (empty($item)) {
			$item = $this->fetchNew();
			$item->title = $text;
			$item->tags_type = $type;
			$item->lang_id = $lang;
			$item->save();
		}
		
		return $item;
	}
	
	/**
	 * Searches for the synonyms that looks like a simmilar
	 *
	 * output will contain the array with IDs and relevancy points
	 *
	 *
	 *
	 * @param string $tag
	 * @return array ids with the relevancy points
	 */
	public function searchServicesByTag($tag)
	{
		$select = $this->select();
		$select->where('title LIKE "%' . $tag . '%"');
		
		$synonymsList = $this->fetchAll($select);
		
		if (0 == count($synonymsList)) {
			return NULL;
		}
		
		foreach ($synonymsList as $syn) {
			$ids[] = $syn->id;
		}
		
		$SynonymServices = new Joss_Crawler_Db_SynonymsServices();
		$relations = $SynonymServices->getRelationsByIds($ids);
		
		$returnIds = array();
		foreach ($relations as $rel) {

			// TODO: we can also try to add extra measurement points here
			//       by analyzing the type of synonym (taxonomy / full text search)
			if (empty($returnIds[$rel->service_id]['rate'])) {
				$returnIds[$rel->service_id]['rate'] = 1;
			} else {
				$returnIds[$rel->service_id]['rate'] += 1;
			}

		}
		
		// get services data
		// $returnIds[$rel->service_id]['name'] = $title[$rel->service_id];
		// $returnIds[$rel->service_id]['name_uk'] = $title[$rel->service_id];
		$Services = new Searchdata_Model_Services();
		$returnFullIds = array();
		foreach($returnIds as $id => $rate) {
			$service = $Services->getById($id);
			$returnFullIds[$id]['rate'] = $rate;
			$returnFullIds[$id]['name'] = $service->name;
			$returnFullIds[$id]['name_uk'] = $service->name_uk;
		}
		
		return $returnFullIds;
	}

}