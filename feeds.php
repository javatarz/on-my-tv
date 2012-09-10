<?

require_once('./include/config.inc.php');


$s = start_time();

if(isset($_GET['uid'])) {
	$user = new User($_GET['uid'],True);
} else {
	$user = User::getInstance();
}

$styles = Styles::getStyles();
//Handle settings changes in a seperate file so they can be changed from any page
include('handle_settings_changes.php');


/* Start cat user handling */
$filtered_shows = array();
if($user->isValid()) {

	$selected_timezone = $user->timezone;
	if(defined('INPUT_SHOW_ID')) {
		$osh[INPUT_SHOW_ID] = new TVShow(INPUT_SHOW_ID);
		
	}

	$shows = TVShows::getUserFilter($user->id);
	
	$filtered_shows = $shows;
	$sel_sh_count = count($shows);

	$shc = TVShows::getShows();
	$sh_count = count($shc);

	if(count($shows) == 0) {
		$shows = $shc;
		$sel_sh_count = $sh_count;
	}
	
	$nshows = TVShows::getNewShows($user->last_filter_update);

	$style = new Style($user->style);

	$watched = TVEpisodes::getWatched($user->id);


} else {
	$style = new Style(DEFAULT_STYLE);
	$selected_timezone = DEFAULT_TIMEZONE;
	if(defined('INPUT_SHOW_ID')) {
		$osh[INPUT_SHOW_ID] = new TVShow(INPUT_SHOW_ID);
		
	}

	$shows = TVShows::getShows();

	$sh_count = count($shows);
	$sel_sh_count = 0;
	$watched = array();

}

$all_shows = TVShows::getShows();

foreach($all_shows as $sh) {
	$_op[$sh->id] = $sh;
}

$all_shows = $_op;

if(!is_array($watched)) { $watched = array(); }


/* End cat user handling */



	


	if(empty($selected_timezone)) {
        $selected_timezone = DEFAULT_TIMEZONE;
    }


	$d = new DateTime();
	$in_tz = new DateTimeZone($selected_timezone);
	$d->setTimeZone($in_tz);
	$d_start = clone $d;

	if(defined('INPUT_ROLLING') && defined('INPUT_ROLLING_MOD')) {
		define("DISPLAY_TYPE",'rolling');
		

		$d_start = clone $d;
		$d_end = clone $d;
		
		if(INPUT_ROLLING_MOD == '+') {
			$d_end->modify(INPUT_ROLLING_MOD . " " . INPUT_ROLLING);
			$d_end->setTime(23,59,59);
			
			
			//$d_start->modify('-1 day');
			$d_start->setTime(00,00,00);
			
		} else {
			$d_start->modify(INPUT_ROLLING_MOD . " " . INPUT_ROLLING);
			$d_start->setTime(00,00,00);

		}
        $deftz = new DateTimeZone(DEFAULT_TIMEZONE);
        $d_start->setTimeZone($deftz);
        $d_end->setTimeZone($deftz);
        
		$rstring = ucwords(INPUT_ROLLING_MOD_STRING . " " . INPUT_ROLLING);

	} elseif(defined('INPUT_DAY') && defined('INPUT_MONTH') && defined('INPUT_YEAR')) {
		define("DISPLAY_TYPE",'day');

		$d->setDate(INPUT_YEAR,INPUT_MONTH,INPUT_DAY);

		$d_start = clone $d;
		$d_end = clone $d;

		$d_start->setDate(INPUT_YEAR,INPUT_MONTH,INPUT_DAY-1);
		$d_start->setTime(00,00,00);

		$d_start->setTimeZone($in_tz);
		$d_end->setTime(23,59,59);
		$d_end->setDate(INPUT_YEAR,INPUT_MONTH,INPUT_DAY+1);
		$display_year = $d->format('Y');
		$display_month = $d->format('M');
		$display_day = $d->format('jS');
		
	} elseif(defined('INPUT_WEEK') && defined('INPUT_YEAR')) {
		define("DISPLAY_TYPE",'week');
		
		$d_start->setISODate(INPUT_YEAR,INPUT_WEEK);
		$d_start->setTime(00,00,00);
		$d_start->setTimeZone($in_tz);
		$d_end = clone $d_start;
		$d_end->setTime(23,59,59);

		$display_year = $d_start->format('Y');
		$display_week = $d_start->format('W');

		if(!defined('INPUT_LIMIT')) {
			$d_end->modify('+1 week');
		} else {
			$d_end->modify('+' . INPUT_LIMIT);
		}
		
		

	} elseif(defined('INPUT_SHOW_ID')) {
		define("DISPLAY_TYPE",'show');
		$d_start = clone $d;
        $d_start->modify('-10 years');
        $d_end = clone $d;
		$d_end->modify('+10 years');
        
	} else {
		define("DISPLAY_TYPE",'upcoming');
		$d_end = clone $d;
		$d_end->modify('+2 weeks');
	}


	//$d_start->setTime(00,00,00);
	


TVEpisodes::setFilterTimezone($selected_timezone);
TVEpisodes::setFilterPeriod($d_start,$d_end);

TVEpisodes::setFilterSelectSummaries(true);
if(defined('INPUT_SHOW_ID')) {
	TVEpisodes::getFilteredEpisodesByShows($osh);
	//$shw = $all_shows[INPUT_SHOW_ID];
	$shw = new TVShow(INPUT_SHOW_ID);
	
} else {
	TVEpisodes::getFilteredEpisodesByShows($shows);
}


$today = new DateTime();
$todayutc = new DateTime();
$today->setTimeZone($in_tz);
$week = $today->format('W');


$t_start = clone $today;
$t_end = clone $today;

$t_start->setTime(00,00,00);
$t_end->setTime(23,59,59);

TVEpisodes::setFilterPeriod($t_start,$t_end);
TVEpisodes::setFilterSelectSummaries(false);
if(count($filtered_shows) > 0) {
	$todays_shows = TVEpisodes::getShowFilteredEpisodeCount($user->id);
} else {
	$todays_shows = TVEpisodes::getAllShowsFilteredEpisodeCount();
}

$last_update = new DateTime($user->last_filter_update);
$last_update->setTimeZone($in_tz);
$xml_eplist = Array();

foreach(TVEpisodes::$episodes as $ep) {
    $shw = $all_shows[$ep->show_id];
    $ep->show_name = $shw->name;
    $ep->show_tv_rage_id = $shw->tvragerid;
    $ep->show_tv_dot_com_id = $shw->tvdotcomrid;
    
 
        $epdate = new DateTime($ep->ep_tzfix_date);
        $epdateutc = new DateTime($ep->date);
        $ep->ep_date_utc = $ep->ep_tzfix_date;
        $epdate = mod_zone_local($shw->timezone,$in_tz,$epdate,$mod_timezones);
            
        if(defined('INPUT_DAY') && $epdate->format('j') != INPUT_DAY) {
            
            continue;
        }

        if(defined('INPUT_WEEK') && $epdate->format('W') != INPUT_WEEK) {
            
            continue;
        }
        
        $xml_eplist[] = $ep;
    
}
    
if(isset($_GET['xml'])) {
	/*if(!$user->isValid() || empty($user->name)) {
		die("You must be signed in using a valid account (WITH USERNAME) to access XML data. This means if you're grabbing it automatically for whatever reason, send the cookie your browser will have stored on this page along with your request.<br />" . $return_url);
	}*/
	require_once 'XML/Serializer.php'; 
	$serializer_options = array ( 
	   'addDecl' => TRUE, 
	   'encoding' => 'UTF-8', 
	   'indent' => '	', 
	   'typeHints' => FALSE,
	   'rootName' => 'tvdata', 
	   'defaultTagName' => 'entry', 
	   'cdata' => TRUE
	); 
	
	$Serializer = &new XML_Serializer($serializer_options); 

  
    
	$status = $Serializer->serialize(array('episodes' => $xml_eplist)); 
	if (PEAR::isError($status)) { 
	   die($status->getMessage()); 
	}  else {
		header('Content-type: text/xml'); 
		
		echo $Serializer->getSerializedData(); 
		die();
	}
} else if(isset($_GET['rss'])) {

    $channel = array();
	if(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'week') {
		$channel['title'] = "On-My.TV Weekly TV Show Guide";
		$channel['description'] = "Upcoming TV Episode air dates by week based on your selected shows filter.";
	} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'rolling') {
		$channel['title'] = "On-My.TV " . $rstring . " Rolling TV Show Guide";
		$channel['description'] = "Rolling TV Episode air dates for the " . $rstring . " based on your selected shows filter.";
	} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'day') {
		$channel['title'] = "On-My.TV Daily TV Show Guide (" . $display_day . ' ' . $display_month . ' ' . $display_year  . ")";
		if(!defined('INPUT_TODAY')) {
			$channel['description'] = "Upcoming TV Episode air dates for " . $display_day . ' ' . $display_month . ' ' . $display_year  . " - this is unlikely to change. Why not use the Rolling Week RSS instead?";
		} else {
			$channel['description'] = "Daily TV Episode air dates";
		}
	} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'show') {
		$channel['title'] = "On-My.TV " . $shw->name . " Upcoming Episodes Guide";
		$channel['description'] = "Upcoming TV Episode air dates for " . $shw->name . " based on your selected shows filter.";
	} else {
		$channel['title'] = "On-My.TV Upcoming Episodes Guide";
		$channel['description'] = "Upcoming TV Episode air dates based on your selected shows filter.";
	}
    
    
	require_once 'XML/Serializer.php'; 
	$serializer_options = array ( 
	   'addDecl' => TRUE, 
	   'indent' => '	', 
	   'typeHints' => FALSE,
	   'mode' => 'simplexml',
	   'attributesArray' => '_attributes',
	   'rootName' => 'rss', 
	   'rootAttributes' =>  array('version' => '2.0', 'xmlns:atom' => 'http://www.w3.org/2005/Atom'),
	   'defaultTagName' => 'item', 
	   'cdata' => false
	); 
	
	$Serializer = &new XML_Serializer($serializer_options); 

	$ustr = str_ireplace($_SERVER['QUERY_STRING'],"",$_SERVER['REQUEST_URI']);
	$channel['link'] = "http://" . $_SERVER['HTTP_HOST'] . $ustr . urlencode($_SERVER['QUERY_STRING']);
	$channel['atom:link'] = array('_attributes' => array('href' => $channel['link'] . '', 'rel' => 'self', 'type' => 'application/rss+xml'));
	$channel['pubDate'] = $today->format('r');

	foreach($xml_eplist as $kx => $xml_entry) {
		$r_date = new DateTime($xml_entry->ep_tzfix_date);
		
		if(($r_date >= $today) || (defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'day')) {
			
			$rss_item = array();
			$tt = ($user->s_24hour)? $epdate->format('H:i D jS') : $epdate->format('g:ia D jS');
			$rss_item['title'] = $xml_entry->show_name . " " . $xml_entry->season . "x" . zerofill($xml_entry->episode,2) . " (" . $tt . ")";

			switch($_GET['feed_id']) {
				default:
				case '1':
					$rss_item['link'] = $xml_entry->summary_url;
				break;

				case '2':
					$rss_item['link'] = $xml_entry->summary_url;
				break;
			}

			$rss_item['description'] = $xml_entry->name . " - " . str_ireplace("<br />","",$xml_entry->episode_summary);
			$rss_item['guid'] = "http://" . $r_date->format('j.n.Y') . "." . COOKIE_DOMAIN . SITE_BASE . "/#r_" . $xml_entry->id;
			
			
			$channel[] = $rss_item;
		}

		
	}

	$status = $Serializer->serialize(array('channel' => $channel)); 
	if (PEAR::isError($status)) { 
	   die($status->getMessage()); 
	}  else {
		returnOnlineUserCount();
		header('Content-type: application/rss+xml'); 
		
		echo $Serializer->getSerializedData(); 
		die();
	}
}
?>
