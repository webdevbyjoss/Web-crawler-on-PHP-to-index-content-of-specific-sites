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
	    // 1 degree equals 0.017453292519943 radius
	    $degreeRadius = deg2rad(1);
	 
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
		
	}

}