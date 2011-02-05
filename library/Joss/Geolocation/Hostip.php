<?php

class Joss_Geolocation_Hostip
{
	const API_URL = 'http://api.hostip.info/';
	
	/**
	 * Calls to free online IP-to-City service and
	 * return the city/region data like
	 *
	 * array(6) {
	 *	  ["ip"]=>
	 *	  string(12) "80.243.144.3"
	 *	  ["country"]=>
	 *	  string(7) "UKRAINE"
	 *	  ["country_code"]=>
	 *	  string(2) "UA"
	 *	  ["city_name"]=>
	 *	  string(9) "Ternopil'"
	 *	  ["lng"]=>
	 *	  string(7) "25.5833"
	 *	  ["lat"]=>
	 *	  string(5) "49.55"
	 *	}
	 *
	 * @param string $ip
	 * @param array
	 */
	static public function getCityByIp($ip)
	{
          // build URL
          $requestUrl = self::API_URL . '?ip=' . $ip;

          // get API responce
          $client = new Zend_Http_Client($requestUrl, array(
            'maxredirects' => 0,
          	'timeout'	   => 1
          ));

          $responseObject = $client->request();

          if ($responseObject->getStatus() != 200) {
          	  return null;
          }

          $response = $responseObject->getBody();

          // analyze responce
          $xml = new SimpleXMLElement($response);

          // coordinates are available as lng,lat
          $coordinates = current($xml->xpath('//gml:coordinates'));

          if (empty($coordinates)) {
          	return null;
          }

       	  // WARNING: only for PHP 5.3
          // $coordsString = $coordinates->__toString();
          // FOR PHP before version 5.3 use:
          $coordsString = sprintf("%s", $coordinates);

          $coords = explode(',', $coordsString);
          $geoData['lng'] = $coords[0];
          $geoData['lat'] = $coords[1];

          // extract additional data
          $geoData['ip'] = $ip;
          // NOTE: doesn't works before PHP 5.3
          // $geoData['country'] = current($xml->xpath('//countryName'))->__toString();
          // $geoData['country_code'] = current($xml->xpath('//countryAbbrev'))->__toString();
          // $geoData['city_name'] = current($xml->xpath('//Hostip/gml:name'))->__toString();

          return $geoData;
	}

}