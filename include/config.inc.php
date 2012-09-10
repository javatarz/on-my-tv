<?
error_reporting(E_NONE);

//cat Config
    define("DB_DSN", "mysql");
    define("DB_SERVER", "localhost");
    define("DB_USER", "calendar");
    define("DB_PASS", "password");
    define("DB_NAME", "calendar");
    require_once('dbconn.class.php');

define('ERROR_EMAIL','debug.email@domain.com');

define("TBL_SHOWS","shows");
define("TBL_SEARCH_SHOWS","shows_search");
define("TBL_EPISODES","episodes");
define("TBL_USERS","users");
define("TBL_USERSELECTIONS","user_selections");
define("TBL_WATCHEDEPS","user_watched");
define("TBL_VOTED","user_votes");
define("TBL_STYLES","styles");

define("EPGUIDES_QUERY_URL","http://www.epguides.com");
define("TVRAGE_SEARCH_QUERY_URL","http://www.tvrage.com/feeds/search.php?show=");
define("TVRAGE_QUERY_URL","http://www.tvrage.com/quickinfo.php?show=");
define("TVRAGE_DATA_QUERY_URL","http://www.tvrage.com/feeds/showinfo.php?sid=");


define("DATA_LAST_UPDATED","/var/www/calendar/last_update.dat");
define("JSON_PASSWORD","password-used-for-json-requests");

define("IMAGES_FLAG_DIR","/imgs/flags/png");

define("MEMCACHE_HOST","127.0.0.1");
define("MEMCACHE_PORT",11211);
//Define the time before sessions are timed out in the database
define("SESSION_TIMEOUT",120);

//This string is used to name cookies
define("SITE_ID","101");
define("COOKIE_DOMAIN","cookie-domain.com"); //e.g. on-my.tv

define("MEMCACHE_PREFIX","udr_");

define("CURRENT_USERS_FILE","/var/www/calendar/current_users.dat");
define("ONLINEUSERS_HISTORY_FILE","/var/www/calendar/online_users_history.dat");
define("ONLINEUSERS_HISTORY_INTERVAL",300); //Interval between user count collections
define("ONLINEUSERS_HISTORY_HISTORY",604800); //Total number of seconds to collect
define("ONLINEUSERS_GRAPH_OUTPUT","/var/www/calendar/online_users_history.png"); 
define("ONLINEUSERS_GRAPH_DAILY_OUTPUT","/var/www/calendar/online_users_daily_history.png");
define("ONLINEUSERS_GRAPH_WEEKLY_OUTPUT","/var/www/calendar/online_users_weekly_history.png");

//Defines the relative location of the site (eg. www.pogdesign.co.uk/cat - SITE_BASE = '/cat' (NO TRAILING SLASH))

define("SITE_BASE","");

define("STYLE_DIR",'css');

//Defines the absolute location of the site (eg. /var/www/www.pogdesign.co.uk/htdocs/
define("ABSOLUTE_SITE_BASE","/var/www/calendar");

define("TEMPLATE_DIR","/var/www/calendar/templates");

define("DEFAULT_STYLE",5);

define("DEFAULT_STYLE_NAME",'catblue.css');


/* Dont ever delete this bit, $ip is used later in the script */
$bad_ips = array();
if(array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER) && $_SERVER["HTTP_X_FORWARDED_FOR"] != null && $_SERVER["HTTP_X_FORWARDED_FOR"] != $_SERVER["REMOTE_ADDR"]) {
	$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} elseif(array_key_exists('REMOTE_ADDR',$_SERVER)) {
	$ip = $_SERVER["REMOTE_ADDR"];
} else {
	$ip = '';
}

if(stripos($ip,'::ffff:') === 0) {
	$ip = substr($ip,7);
}

if(array_key_exists($ip,$bad_ips)) {
	
	echo "<h1>Your IP, <strong>" . $ip . "</strong> has been manually blacklisted from the calendar. Please contact <strong><a href='mailto:contact_email@domain.com'>contact_email@domain.com</a></strong>, stating your IP to request that your blacklist is removed.</h1><br />";
	echo "<h3>The reason given for your blacklisting is:</h3>";
	echo $bad_ips[$ip];
	die();

}


define("DPARSE_CANCELLED",false);

$mod_timezones = array();

$mod_timezones['GMT-5'] = array
    (
        'US/Alaska'     =>      '+4 hours',
        'US/Pacific'    =>      '+3 hours',
        'US/Mountain'   =>      '+2 hours',
        
    );

// REMEMBER TO UPDATE DATABASE ENUM OF TIMEZONES WHEN YOU UPDATE THIS LIST
$timezones['US - Hawaii'] = 'US/Hawaii';
$timezones['US - Alaska'] = 'US/Alaska';
$timezones['US - Pacific'] = 'US/Pacific';
$timezones['US - Mountain'] = 'US/Mountain';
$timezones['US - Arizona'] = 'America/Phoenix';
$timezones['US - Central'] = 'US/Central';
$timezones['US - Eastern'] = 'US/Eastern';
$timezones['CA - Atlantic'] = 'Canada/Atlantic';
$timezones['SA - Brazil East'] = 'Brazil/East';
$timezones['SA - Chile'] = 'Chile/Continental';

$timezones['GMT'] = 'GMT';
$timezones['EU - London'] = 'Europe/London';

$timezones['EU - Central'] = 'CET';
$timezones['EU - Stockholm'] = 'Europe/Stockholm';
$timezones['EU - Helsinki'] = 'Europe/Helsinki';
$timezones['ASIA - Israel'] = 'Asia/Jerusalem';
$timezones['ASIA - Dubai'] = 'Asia/Dubai';
$timezones['ASIA - Hong Kong'] = 'Asia/Hong_Kong';
$timezones['ASIA - Bangkok'] = 'Asia/Bangkok';
$timezones['AU - Western'] = 'Australia/West';
$timezones['AU - North'] = 'Australia/North'; 
$timezones['AU - South'] = 'Australia/South'; 
$timezones['AU - Eastern'] = 'Australia/NSW';
$timezones['AU - Queensland'] = 'Australia/Queensland';

$timezones['NZ - Auckland'] = 'Pacific/Auckland';

define("DEFAULT_INPUT_TIMEZONE",'US/Eastern');
define("DEFAULT_TIMEZONE",'GMT');
// REMEMBER TO UPDATE DATABASE ENUM OF TIMEZONES WHEN YOU UPDATE THIS LIST


define("SHOW_SELECT_URL","http://" . COOKIE_DOMAIN.SITE_BASE . "/show-select");
define("HOMEPAGE_URL","http://" . COOKIE_DOMAIN.SITE_BASE . "");
define("THIS_WEEKS_URL","http://next.seven.days." . COOKIE_DOMAIN.SITE_BASE . "/");
define("NEXT_WEEKS_URL","http://next.week." . COOKIE_DOMAIN.SITE_BASE . "/");

define("TODAYS_URL","http://today." . COOKIE_DOMAIN.SITE_BASE . "/");

define("TOMORROWS_URL","http://tomorrow." . COOKIE_DOMAIN.SITE_BASE . "/");

define("CURRENT_URL","http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
ini_set("date.timezone",DEFAULT_TIMEZONE);

require_once(ABSOLUTE_SITE_BASE . '/include/functions.inc.php');
require_once(ABSOLUTE_SITE_BASE . '/include/log_current.inc.php');

function __autoload($class_name) {

	if(!file_exists(ABSOLUTE_SITE_BASE . '/include/' . $class_name . '.class.php')) {
		$class_name = strtolower($class_name);
	}
	
	if(file_exists(ABSOLUTE_SITE_BASE . '/include/' . $class_name . '.class.php')) {
		require_once(ABSOLUTE_SITE_BASE . '/include/' . $class_name . '.class.php');
	} else {
	
		if(!file_exists(ABSOLUTE_SITE_BASE . '/include/' . $class_name . '.util.php')) {
			$class_name = strtolower($class_name);
		}
		if(file_exists(ABSOLUTE_SITE_BASE . '/include/' . $class_name . '.util.php')) {
			require_once(ABSOLUTE_SITE_BASE . '/include/' . $class_name . '.util.php');
		}

	}
}

function start_time() {
    $stimer = explode( ' ', microtime() );
    $stimer = $stimer[1] + $stimer[0];
    return $stimer;
}

function end_time($stimer) {
    $etimer = explode( ' ', microtime() );
    $etimer = $etimer[1] + $etimer[0];

    return ($etimer-$stimer);
}

function log_exception_handler($e) {
    echo "Error occurred and has been logged. Sorry for the inconvenience :(";
    
    try {
        $user = User::getInstance();
    } catch(Exception $r) {
    }
    

    $now = new DateTime();
    
    $hash = md5(date('r'));
    
    $subject = '[ ' . $now->format('H:i:s d/m/y') . ' // ' . COOKIE_DOMAIN . ' ] Exception: ' . $e->getCode();
    
   
    $h = array(
        'From: debug_out@' . COOKIE_DOMAIN,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative;boundary="' . $hash . '";',
    );
    
    $vars = array(
        'u_id' => $user->id,
        'u_name' => $user->name,
        'e_file' => $e->getFile(),
        'e_line' => $e->getLine(),
        'e_code' => $e->getCode(),
        'e_msg' => $e->getMessage(),
        'e_trace' => $e->getTraceAsString(),
        'i_post' => print_r($_POST,true),
        'i_get' => print_r($_GET,true),
        'i_server' => print_r($_SERVER,true),
        'i_cookie' => print_r($_COOKIE,true),
        'boundary' => $hash,
    );
    
    
    $mtp = new Template('error_mail');
    $mtp->assign_assoc($vars);
    @mail(ERROR_EMAIL,$subject,$mtp->output(),implode("\n",$h));
}

set_exception_handler('log_exception_handler');


?>
