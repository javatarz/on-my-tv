<?


function returnOnlineUserCount($ip) {
	$f = CURRENT_USERS_FILE;
	$timeout = 120;
	DBConn::getInstance();

	$uarray = DBConn::$memcached->get('online_users');

	if(!is_array($uarray)) {
		$uarray = array();
	}

	foreach($uarray as $key => $value) {
		if(time() >= $value) {
			unset($uarray[$key]);
		}

	}

	if(!array_key_exists($ip,$uarray)) {
		$d = time() + $timeout;
		$uarray[$ip] = $d;
		//$uarray[$ip]['url'] = $_SERVER['REQUEST_URI'];
	} else {
		$d = time() + $timeout;
		$uarray[$ip] = $d;
		//$uarray[$ip]['url'] = $_SERVER['REQUEST_URI'];
	}

	DBConn::$memcached->set('online_users',$uarray,MEMCACHE_COMPRESSED,0);
	return count($uarray);
}

?>