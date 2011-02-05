<?php
/**
 * Manages local IP-to-City database
 *
 *
 */
class Joss_Geolocation_Db extends Zend_Db_Table_Abstract
{
	/**
	 * The name of the database table
	 *
	 * @var string
	 */
	protected $_name = 'iptocity';
	
	/**
	 * Define the name of primary key column
	 *
	 * @var string
	 */
	protected $_primary = 'ip';
	
	/**
	 * Retrieve country_id, region_id, city_id by IP address
	 *
	 * @param string $ip
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getRegionByIp($ip)
	{
		return $this->find($ip);
	}

	/**
	 * Stores Ip-to-City relation into local database
	 *
	 * @param string $ip
	 * @param int $city
	 * @return void
	 */
	public function addCity($ip, $city)
	{
		if (empty($city)) {
			throw new Exception('Incorrect city Geo-IP information provided');
		}
		
		if (empty($ip)) {
			throw new Exception('Incorrect IP-address information provided');
		}
		
		$data = array(
			'ip' => $ip,
			'country_id' => $city->country_id,
			'region_id' => $city->region_id,
			'city_id' => $city->city_id,
			'name' => $city->name,
			'name_uk' => $city->name_uk,
			'lat' => $city->latitude,
			'lng' => $city->longitude,
		);
		
		$this->insert($data);
	}

}