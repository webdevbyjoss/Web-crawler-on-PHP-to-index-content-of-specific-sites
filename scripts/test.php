<?php

// we will use a special class to parse the page data
require_once realpath(dirname(__FILE__) . '/../library/') . '/simple_html_dom.php';

$currentUrl = 'http://emarket.kiev.ua/construction/kompleksnyiy-remont_5055763.html';

$pageContent = iconv('CP1251', 'UTF-8', file_get_contents($currentUrl));

$html = str_get_html($pageContent);
//$html = file_get_html($currentUrl);

function normalizePhoneNumber($number, $defaultAreaCode = '38')
{
	// strip all non-numeric characters
	$number = preg_replace('/[^0-9]/', '', $number);

	// parse phone number and process area code
	$phoneNumber = substr($number, -7);
	$networkCode = substr($number, -10, 3);
	$areaCode = substr($number, -12, 3);

	if (strlen($areaCode) < strlen($defaultAreaCode) ) {
		$areaCode = $defaultAreaCode;
	}
	
	return '+' . $areaCode . $networkCode . $phoneNumber;
}

// Find all article blocks
$info = array();

// set adapter ID
$info['adapter'] = 111;

// recognize item ID
preg_match('@/construction/.*_([0-9]+)\.html$@', $currentUrl, $matches);
$info['id'] = $matches[1];


$info['info']['title'] = $html->find('div#content_container_detail h1.title', 0)->plaintext;
$info['info']['description'] = $html->find('div#block5 table tr td span', 0)->plaintext;
$info['info']['contact_name'] = $html->find('div.lot table table tr td b', 1)->plaintext;
$info['info']['phone'] = $html->find('div.lot table table table tr td b i', 0)->plaintext;

$info['info']['phone'] = normalizePhoneNumber($info['info']['phone']);

// recognize regions
$regionId = 1;
$regionTags = $html->find('span.region_ad', 0)->plaintext;

$info['regions'][] = array('id' => $regionId, 'tags' => $regionTags);

// recognize services
$serviceId = 2;
$serviceTags = $html->find('tr#multitag td', 1)->plaintext;

$info['services'][] = array('id' => $serviceId, 'tags' => $serviceTags);

var_dump(array($info));