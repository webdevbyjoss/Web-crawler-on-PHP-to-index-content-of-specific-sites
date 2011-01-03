<?php

class Joss_Geolocation
{
	const EARTH_RADIUS_AVG = 6368.6; // Eastern Europe
	
	/**
	 * Function to calculate distance between two coordinates
	 *
	 * thanks to: http://andries.systray.be/blog/2007/07/12/calculating-a-distance-between-2-points-in-php/
	 *
	 * @param float $latitudeFrom
	 * @param float $longitudeFrom
	 * @param float $latituteTo
	 * @param float $longitudeTo
	 * @return float ditance in kilometers
	 */
	public function getDistance($latitudeFrom, $longitudeFrom, $latituteTo, $longitudeTo)
	{
	    // to make everything faster lets asume that 1 degree equals ~0.017453292519943 radius
	    $degreeRadius = 0.017453292519943; // $degreeRadius = deg2rad(1);
	 
	    // convert longitude and latitude values
	    // to radians before calculation
	    $latitudeFrom  *= $degreeRadius;
	    $longitudeFrom *= $degreeRadius;
	    $latituteTo    *= $degreeRadius;
	    $longitudeTo   *= $degreeRadius;
	 
	    // apply the Great Circle Distance Formula
	    $d = sin($latitudeFrom) * sin($latituteTo) + cos($latitudeFrom)
	       * cos($latituteTo) * cos($longitudeFrom - $longitudeTo);
	 
	    return (self::EARTH_RADIUS_AVG * acos($d));
	}

	/**
	 * Returns city ID by IP address
	 *
	 * @param string $ip
	 * @return int the ID of the city
	 */
	public function getCityByIp($ip)
	{
		if (empty($ip)) {
			throw new Exception('IP address is not defined');
		}
		
		// 1. try to get data from cahce
		// or get data from local database
		$GeoDb = new Joss_Geolocation_Db();
		$data = $GeoDb->getRegionByIp($ip);
		
		if (count($data) > 0) {
			$currentCity = $data->current();
			$return[] = array(
				'id' => $currentCity->city_id,
				'name' => $currentCity->name,
				'name_uk' => $currentCity->name_uk
			);
			return $return;
		}
		
		// 2. try to get data from online service
		// convet retrieved data to local database IDs
		// and store data into local database
		// and store data into local cache
		$data = Joss_Geolocation_Hostip::getCityByIp($ip);
		
		$Cities = new Searchdata_Model_Cities();
		$currentCity = $Cities->getCityByCoords($data['lng'], $data['lat']);

		$GeoDb->addCity($ip, $currentCity);
		
		$return[] = array(
			'id' => $currentCity->city_id,
			'name' => $currentCity->name,
			'name_uk' => $currentCity->name_uk
		);
		
		return $return;
	}

}