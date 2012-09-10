<?
require_once('./include/config.inc.php');
 //error_reporting(E_ERROR);
$s = start_time();
$user = User::getInstance();

$styles = Styles::getStyles();
//Handle settings changes in a seperate file so they can be changed from any page
include('handle_settings_changes.php');


/* Start cat user handling */
if($user->isValid()) {

	$selected_timezone = $user->timezone;
	$shows = TVShows::getUserFilter($user->id);
	$filtered_shows = $shows;
	$sel_sh_count = count($shows);



	if(count($shows) == 0) {
		$shc = TVShows::getShows(true);
		$sh_count = count($shc);
		$shows = $shc;
		$sel_sh_count = $sh_count;
	} else {
		$sh_count = TVShows::getShowsCount();
	}
	
	$nshows = TVShows::getNewShows($user->last_filter_update);

	$style = new Style($user->style);

	$watched = TVEpisodes::getWatched($user->id);


} else {
	$style = new Style(DEFAULT_STYLE);

	$selected_timezone = DEFAULT_TIMEZONE;

	/* $r = geoip_record_by_name($ip);

	 if($r != false) {
		$x = new DateTime();
		$long = $r['longitude'];

		foreach($timezones as $k => $t) {
			$t = new DateTimeZone($t);
			$in_offset = round($long / 15,0);
			$out_offset = $t->getOffset($x) / 3600;

			if($in_offset == $out_offset) {
				$selected_timezone = $timezones[$k];
			}

		}
	} */


	$shows = TVShows::getShows(true);
	$sh_count = count($shows);
	$sel_sh_count = 0;
	$watched = array();

}



if(!defined('INPUT_MONTH') || !defined('INPUT_YEAR')) {
	$d = new DateTime();
	$d->setTime(00,00,00);
	try {
		$in_tz = new DateTimeZone($selected_timezone);
	} catch(Exception $e) {
		$in_tz = new DateTimeZone(DEFAULT_TIMEZONE);
	}

	$calendar = new Calendar($d->format('Y'),$in_tz,$user->s_sunday_first);
	$cur_month = $calendar->addMonth($d->format('n'));

	$d_start = clone $d;
	$d_end = clone $d;

	
	$d_start->setDate($d_start->format('Y'),$d_start->format('n'),-1);
	$d_start->setTime(00,00,00);


	
	$d_end->setDate($d_end->format('Y'),$d_end->format('n'),$d_end->format('t')+1);
	$d_end->setTime(23,59,59);

} else {

	$d = new DateTime();
	$d->setDate(INPUT_YEAR,INPUT_MONTH,1);
	$d->setTime(00,00,00);
	try {
		$in_tz = new DateTimeZone($selected_timezone);
	} catch(Exception $e) {
		$in_tz = new DateTimeZone(DEFAULT_TIMEZONE);
	}


	$calendar = new Calendar(INPUT_YEAR,$in_tz,$user->s_sunday_first);
	$cur_month = $calendar->addMonth($d->format('n'));


	$d_start = clone $d;
	$d_end = clone $d;

	
	$d_start->setDate($d_start->format('Y'),$d_start->format('n'),-1);
	$d_start->setTime(00,00,00);



	$d_end->setDate($d_end->format('Y'),$d_end->format('n'),$d_end->format('t')+1);
	$d_end->setTime(23,59,59);


}


if(!is_array($watched)) { $watched = array(); }


/* End cat user handling */





/*TEMP CSS SETTER*/

if(isset($_GET['cssfile'])) {
	$extra_cssfile = htmlentities($_GET['cssfile']);
} else {
	if($style->cssname == '') {
		$style = new Style('0');
	}
	$cssfile = SITE_BASE . '/' . STYLE_DIR . '/' . $style->cssname;
}

/*
if($style->cssname == '') {
    $style = new Style('0');
}
$cssfile = SITE_BASE . '/' . STYLE_DIR . '/' . $style->cssname;*/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns='http://www.w3.org/1999/xhtml'><head>
<title>On-My.TV: Monthly - <?=$d->format('F')?> TV Calendar, Listings, Episode Guide. What's on your tv?</title>
<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, tv guide, on my tv" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="description" content="TV Episode Guide: Air Dates, Episode Summaries, 7-Day Grid, XML &amp; RSS Feeds. What's on your tv?" /> 
<link rel="stylesheet" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/css/mmccaatt.css,<?=$cssfile?>" type="text/css" title="default" media="all" />
<?
if(isset($extra_cssfile)) {
    echo '<link rel="stylesheet" href="' . $extra_cssfile . '" type="text/css" title="default" media="all" />';
}
?>
<link rel="shortcut icon" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/favicon.ico"  /> 
<script type="text/javascript" src="http://<?=COOKIE_DOMAIN . SITE_BASE?>/js/jquery-1.3.2.min.js,common.jq.comp.js"></script>

<?


	$js_includes = Array();
	
	if($user->s_wunwatched == true) {
		$js_includes[] = 'showwatched.jq.comp.js';
	}

	if($user->s_popups == true) {
        $js_includes[] = 'calpopups.jq.comp.js';  
	}
    
    

if(count($js_includes) > 0) {
    $jsr = implode(',',$js_includes);
?>
    <script type="text/javascript" src="http://<?=COOKIE_DOMAIN . SITE_BASE . '/js/' . $jsr?>"></script>
<?
}
?>


<script type="text/javascript">
	<!--
		if (top.location!= self.location) {
			top.location = self.location.href
		}
	//-->
</script>
</head>

<body>  
<div id="pop" style="display: none; z-index: 999;">&nbsp;</div>
<div id="click_overlay" style="display: none; z-index: 998;">&nbsp;</div>

<?

include('user_handler.php');
include('register_handler.php');





TVEpisodes::setFilterTimezone($selected_timezone);
TVEpisodes::setFilterPeriod($d_start,$d_end);
TVEpisodes::setFilterSelectSummaries(false);

$today = new DateTime();
$today->setTimeZone($in_tz);

TVEpisodes::getFilteredEpisodesByShows($shows);

foreach($shows as $sh) {
	

	
	$eps = TVEpisodes::returnFilteredEpisodesForShow($sh->id);
	

	
	
	foreach($eps as $ep) {
        
        $epdate = new DateTime($ep->ep_tzfix_date);
        
        $epdate = mod_zone_local($sh->timezone,$in_tz,$epdate,$mod_timezones);
        
        if($epdate->format('n') == $cur_month->month) {
            
            $ep_day = $cur_month->getDay($epdate->format('j'));
            if(!$user->s_sortbyname) {
                $order = "r_" . $ep->id;
                $order2 = "r_" . $epdate->format('Hi') . '_' . $sh->name . "_" . $sh->id . '_' . $ep->id;
            } else {
                $order = "r_" . $ep->id;
                $order2 = $sh->name . '_' . $ep->id;
            }
            $cpl = new Template('day_content');
            
            $cpl->assign('show_name',$sh->name);
            $cpl->assign('show_link',"http://" . $epdate->format('j.n.Y') . "." . COOKIE_DOMAIN . SITE_BASE . '/'  . '#' . $order);
            
            
            $cpl->assign('ep_season',$ep->season);
            $cpl->assign('ep_episode',$ep->episode);
            $cpl->assign('ep_airtime',($user->s_24hour)? $epdate->format('H:i') : $epdate->format('g:ia'));
            
            
            $ntplink = ($user->s_premium && !empty($ep->hash))? ' <a href="/ntp.php?hash=' . $ep->hash . '" title="' . htmlentities($ep->subject) . '">[NZB]</a>' : '';
            $cpl->assign('ntplink',$ntplink);
            
            $enddate = clone $epdate;
            
            if($sh->length != 0 ) {
                $enddate->modify('+ ' . ($sh->length / 60) . ' minutes');
            } else {
                $enddate->modify('+ 30 minutes');
            }
            
        

            
            $cpl->assign('airingnow',($epdate->format('U') <= ($today->format('U') + $today->format('Z')) && $enddate->format('U') >= ($today->format('U') + $today->format('Z')) ),Template::BOOL);
            
            $cpl->assign('ep_network',$sh->network);
            
            if($user->s_popups == true) {
                $json_request = $ep->id;
            
                //$jsop = serialize($json_request);
                $jsop = $json_request;
                
                $cpl->assign('json_request',$jsop);
            } else {
                $cpl->assign('json_request',$order);
            }
            
            $cpl->assign('wunwatched',!$user->s_wunwatched,Template::BOOL);

            $ws = $sh->id . '-' . $ep->season . '-' . $ep->episode;
            $is_watched = in_array($ws,$watched);

            $cpl->assign('ep_name',$ep->name);

            $cpl->assign('iswatched',$is_watched,Template::BOOL);
            
            if(!$is_watched) {
                $cpl->assign('watchlink',SITE_BASE . '/watched/' . $sh->id . '-' . $ep->season . '-' . $ep->episode . '/' . $d->format('n') . '-' . $d->format('Y'));
            } else {
                $cpl->assign('watchlink',SITE_BASE . '/unwatched/' . $sh->id . '-' . $ep->season . '-' . $ep->episode . '/' . $d->format('n') . '-' . $d->format('Y'));
            }
            
            $cpl->assign('dnetsplit', $user->s_daily_networks && ($user->s_daily_numbers || $user->s_daily_airtimes), Template::BOOL);
            $cpl->assign('dnumsplit', $user->s_daily_numbers && ($user->s_daily_airtimes), Template::BOOL);
            

            $cpl->assign('daily_numbers_on',$user->s_daily_numbers,Template::BOOL);
            $cpl->assign('daily_airtimes_on',$user->s_daily_airtimes,Template::BOOL);
            $cpl->assign('daily_networks_on',$user->s_daily_networks,Template::BOOL);
            $cpl->assign('daily_epnames_on',$user->s_daily_epnames,Template::BOOL);

            $cpl->assign('firstep',($ep->episode == 1),Template::BOOL);
            
            $ep_day->addContent($cpl->output(),$order2);
            
        }
		
		
	}

}


$def_tz = new DateTimeZone(DEFAULT_TIMEZONE);

$t_start = new DateTime();

$t_end = new DateTime();
$t_start->setTimeZone($in_tz);
$t_end->setTimeZone($in_tz);
$t_start->setTime(00,00,00);
$t_end->setTime(23,59,59);


$t_start->setTimeZone($def_tz);
$t_end->setTimeZone($def_tz);


TVEpisodes::setFilterPeriod($t_start,$t_end);

TVEpisodes::setFilterSelectSummaries(false);
if(count($filtered_shows) > 0) {
	$todays_shows = TVEpisodes::getShowFilteredEpisodeCount($user->id);
} else {
	$todays_shows = TVEpisodes::getAllShowsFilteredEpisodeCount();
}

$last_update = new DateTime($user->last_filter_update);
//$last_update->setTimeZone($in_tz);

include("start_bar.php");

//Assign this to hide footer by default for javascripts... not set when the footer is called directly
$footer_hidden = true;
include('settings.php');

//include('quotes.php');
$c = new Template('calendar');
if($user->isValid()) {
	$c->assign('disableads',!$user->s_disableads,Template::BOOL);
} else {
	$c->assign('disableads',true,Template::BOOL);
}

$c->assign('adjstop','');
$c->assign('adjsbottom','');


echo $calendar->generate($c);


include ("footer.php");


?>  
 
</body>
</html>
<?
DBConn::closeInstances();
?>
