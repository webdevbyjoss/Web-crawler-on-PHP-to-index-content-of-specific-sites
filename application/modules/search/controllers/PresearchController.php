<?php

class Search_PresearchController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function queryAction()
	{
		$options = array();
    	$options['remote_ip'] = $_SERVER['REMOTE_ADDR'];
    	$options['search_keywords'] = $this->getRequest()->data;
		$SearchForm = new Nashmaster_SearchForm($options);
		
		$regions = $SearchForm->getRegions();
		$services = $SearchForm->getServices();
		
		// process data and transform it into json
		$locale = $this->view->getLocale();
		
		$data = array();
		if (is_array($regions)) {
			foreach ($regions as $key => $val) {
				$data['regions'][$val['id']] = !empty($val['name_uk']) ? $val['name_uk'] : $val['name'];
			}
		}
		
		if (is_array($services)) {
			foreach ($services as $key => $val) {
				$data['services'][$val['id']] = ($locale == 'uk') ? $val['name_uk'] : $val['name'];
			}
		}
		
		echo $this->php2js($data);
	}

	/**
	 * json_encode() replacement that handles cyrillyc characters correctly
	 *
	 * Thanks to: http://www.php.net/manual/en/function.json-encode.php#78719
	 *
	 * @param mixed $a
	 * @return string json code
	 */
	private function php2js($a=false)
	{
		  if (is_null($a)) return 'null';
		  if ($a === false) return 'false';
		  if ($a === true) return 'true';
		  if (is_scalar($a))
		  {
		    if (is_float($a))
		    {
		      // Always use "." for floats.
		      $a = str_replace(",", ".", strval($a));
		    }
		
		    // All scalars are converted to strings to avoid indeterminism.
		    // PHP's "1" and 1 are equal for all PHP operators, but
		    // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
		    // we should get the same result in the JS frontend (string).
		    // Character replacements for JSON.
		    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
		    array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
		    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		  }
		  $isList = true;
		  for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		  {
		    if (key($a) !== $i)
		    {
		      $isList = false;
		      break;
		    }
		  }
		  $result = array();
		  if ($isList)
		  {
		    foreach ($a as $v) $result[] = $this->php2js($v);
		    return '[ ' . join(', ', $result) . ' ]';
		  }
		  else
		  {
		    foreach ($a as $k => $v) $result[] = $this->php2js($k).': '.$this->php2js($v);
		    return '{ ' . join(', ', $result) . ' }';
		  }
	}
}

