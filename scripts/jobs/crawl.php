<?php
/**
 * Client for distributed web crawling
 *
 * My ain was to build a completely independent script that will have no any dependencies
 * and will be ready to operate in any environmnt with any PHP configuration settings
 * and any PHP version that are available right now in any OS
 *
 * Main functional advantage is to use direct sockets connections in order to
 * emulate user's browser, so we will not depend on any configuration restrictions like
 * for ex. "allow_url_fopen"
 *
 * Also we implementing some kind of security check to make sure
 * that job was processed only by certified crawler and the crawl results are authentic
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
 * @copyright	2010
 * @license		GPL
 */

// USER CONFIGURATION

define ('API_KEY', 'test'); // API key allows to identify the client for distributed crawling




// CONFIGURATION FINISHED
// WARNING!!! You dont need to edit the code under this line
// atleast you realy understand what are you doing as that can
// brake the correct work of distributed web crawler client
// script is quite laconic and dosn't depends on any external libraries, configs

// server configuration
define ('JOB_SERVER_DOMAIN', 'joseph-dev.nash-master.com');
define ('JOB_GET_URL', '/crawler/api/get');
define ('JOB_POST_URL', '/crawler/api/post');
define ('USE_SSL', false);

// client configuration
define ('USER_AGENT', 'PHP Distributed Web Crawler/1.0');
define ('USER_AGENT_CLIENT', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
define ('CALLS_PER_RUN', 40);

// lets measre the time of script execution
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

for ($i = 0; $i < CALLS_PER_RUN; $i++) {

	// prepare information
	// we randomly generating some salt to avoid stealing our mp5() hash by network sniffer
	$salt = rand(1000, 9999);
	$key = 'id=' . md5(API_KEY . $salt) . '&data=' . $salt;
	
	// receive job from server
	$result = post_data($key, JOB_SERVER_DOMAIN, JOB_GET_URL, USER_AGENT);
	$job = json_decode($result, true);
	
	if (empty($job)) {
		echo "\nERROR: $result\n";
		die();
	}
	
	if (!empty($job['message'])) {
		echo "\nERROR: " . $job['message'] . "\n";
		die();
	}
	
	// process received job
	$url = base64_decode($job['data']);
	$content = get_url_content($url, USER_AGENT_CLIENT);
	
	// post job results to job server
	$content = base64_encode($content);
	$data = 'id=' . md5(API_KEY . $job['id']);
	$data .= '&data=' . urlencode($content);
	
	$result = post_data($data, JOB_SERVER_DOMAIN, JOB_POST_URL, USER_AGENT);
	
	echo "OK: " . $result . "\n";
	// we need a pause during 1 second between calls to avoid web-server overload
	sleep(0.5);
}

// calculate total execution time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

echo "\nTotal time: " . $totaltime . " seconds\n";
exit;

/**
 * This function will POST data to the specified server URL
 *
 * @param $data 		string data should be urlencoded
 * @param $host			string
 * @param $script_path	string
 * @param $use_ssl		boolean
 * @return string
 */
function post_data($data, $host, $script_path, $user_agent = 'PHP Distributed Web Crawler/1.0', $use_ssl = false)
{
	if ($use_ssl) {
		$sock = fsockopen ("ssl://" . $host, 443, $errno, $errstr, 30);
	} else {
		$sock = fsockopen ($host, 80, $errno, $errstr, 30);
	}
	
	if (!$sock) {
	    throw new Exception($errstr, $errno);
	}
	
	fwrite($sock, "POST " . $script_path . " HTTP/1.0\r\n");
	fwrite($sock, "Host: " . $host . "\r\n");
	fwrite($sock, "User-Agent:" . $user_agent . "\r\n");
	fwrite($sock, "Content-type: application/x-www-form-urlencoded\r\n");
	fwrite($sock, "Content-length: " . strlen($data) . "\r\n");
	fwrite($sock, "Accept: */*\r\n");
	fwrite($sock, "\r\n");
	fwrite($sock, $data . "\r\n");
	fwrite($sock, "\r\n");
	fwrite($sock, "\r\n");
	
	$headers = '';
	while ($str = trim(fgets($sock, 4096))) {
	        $headers .= "$str\n";
	}
	
	// TODO: process some error headers
	
	$body = "";
	while (!feof($sock)) {
	       $body .= fgets($sock, 4096);
	}
	
	return $body;
}

/**
 * Returns the content from the specified URL
 *
 * @param $url 		string the url address
 * @return string
 */
function get_url_content($url, $user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)')
{
	// get the host name and url path
	$url_data = parse_url($url);

	if (isset($url_data['path'])) {
		$path = $url_data['path'];
	} else {
		$path = '/';
	}
	
	if (isset($url_data['query'])) {
		$path .= '?' . $url_data['query'];
	}
	
	// connect to the remote server

	// DOTO: add the fucntionality to respect "robots.txt" rules,
	// see: http://www.the-art-of-web.com/php/parse-links/
	//      http://www.the-art-of-web.com/php/parse-robots/

	if (!empty($url_data['scheme']) && 'https' == $url_data['scheme']) {
		$sock = fsockopen ("ssl://" . $url_data['host'], 443, $errno, $errstr, 30);
	} else {
		
		if (isset($url_data['port'])) {
    		$port = $url_data['port'];
		} else {
			// most sites use port 80
			$port = '80';
		}
		
		$sock = fsockopen($url_data['host'], $port, $errno, $errstr, 30);
	}
	
	if (!$sock) {
	    throw new Exception($errstr, $errno);
	}
	
	// send the necessary headers to get page content
	fwrite($sock, "GET " . $path . " HTTP/1.0\r\n");
	fwrite($sock, "Host: " . $url_data['host'] . "\r\n");
	fwrite($sock, "User-Agent: " . $user_agent . "\r\n");
	fwrite($sock, "Connection: Close\r\n\r\n");
	
	// retrieve the response from the remote server
	$content = '';
    while ($part = fread($sock, 4096)) {
        $content .= $part;
    }

    fclose($sock);
    
    return $content;
}
