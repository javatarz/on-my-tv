<?

require_once('./include/config.inc.php');

if(isset($_GET['xml']) || isset($_GET['rss'])) {
    include('feeds.php');
    die();
}

$s = start_time();

if(isset($_GET['uid'])) {
	$user = new User($_GET['uid']);

} else {
	$user = User::getInstance();
}

$styles = Styles::getStyles();
//Handle settings changes in a seperate file so they can be changed from any page
include('handle_settings_changes.php');

$rss_available = True;
$xml_available = True;
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

/*TEMP CSS SETTER*/
if(isset($_GET['cssfile'])) {
	$extra_cssfile = htmlentities($_GET['cssfile']);
} else {
	if($style->cssname == '') {
		$style = new Style('0');
	}
	$cssfile = SITE_BASE . '/' . STYLE_DIR . '/' . $style->cssname;
}

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
	TVEpisodes::getFilteredEpisodesByShows($osh,True);
	//$shw = $all_shows[INPUT_SHOW_ID];
	$shw = new TVShow(INPUT_SHOW_ID);
	
} else {
	TVEpisodes::getFilteredEpisodesByShows($shows);
}

$today = new DateTime();
$todayutc = new DateTime();
$today->setTimeZone($in_tz);
$week = $today->format('W');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns='http://www.w3.org/1999/xhtml'><head>
<? 
if(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'week') {
	echo "<title>On-My.TV: Weekly - Week " . $display_week . " " . $display_year . " Show Summary and TV Guide. What's on your tv?</title>";
} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'rolling') {
	echo "<title>On-My.TV: " . $rstring  . " Show Summary and TV Guide. What's on your tv?</title>";
} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'day') {

	echo "<title>On-My.TV: Daily - " . $display_day . ' ' . $display_month . ' ' . $display_year  . " Show Summary and TV Guide. What's on your tv?</title>";
} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'show') {
	echo "<title>On-My.TV: Upcoming Episodes - " . $shw->name . " Show Summary and TV Guide. What's on your tv?</title>";
} else {
	echo "<title>On-My.TV: Upcoming Episodes Show Summary and TV Guide. What's on your tv?</title>";
}
?>

<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, tv guide, on my tv" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<link rel="stylesheet" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/css/mmccaatt.css,<?=$cssfile ?>" type="text/css" title="default" media="all" />


<link rel="shortcut icon" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/favicon.ico"  /> 
<?
if($rss_available) {
	echo '<link href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?rss" rel="alternate" type="application/rss+xml" title="' . $channel['title'] . '" />';
}
?>


<script type="text/javascript" src="http://<?=COOKIE_DOMAIN . SITE_BASE?>/js/jquery-1.3.2.min.js,common.jq.comp.js"></script>
<script type="text/javascript" src="http://<?=COOKIE_DOMAIN . SITE_BASE?>/js/nextairing.jq.comp.js"></script>
<script type="text/javascript">
	<!--
		if (top.location!= self.location) {
			top.location = self.location.href
		}
	//-->
</script>
</head>

<body>  
<div id="pop" style="display: none; width: 400px;"> </div>
<?

include('user_handler.php');
include('register_handler.php');

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

include("start_bar.php");

$footer_hidden = true;
include('settings.php');

if(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'week') {
    $display_text = 'Week ' . $display_week . '/' . $display_year . ' Overview';



} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'rolling') {
    $display_text = $rstring;
    $nav_links = False;
} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'day') {
    $display_text = $display_day . ' ' . $display_month . ' ' . $display_year;
    $nav_links = True;
} elseif(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'show') {
    $display_text = 'Episode Dates for ' . $shw->name . ' (Newest First)';
    $nav_links = False;
    if(strlen($shw->custom_summary) > 0) {
        $custom_summary = stripslashes(nl2br($shw->custom_summary));
    }
} else {
    $display_text = 'Next Episode Dates';
}



$nair = new Template('next_airing');

$nair->assign('display_text',$display_text);

$nav_links = (defined('DISPLAY_TYPE') && (DISPLAY_TYPE == 'week' || DISPLAY_TYPE == 'day'));

$nair->assign('nav_links',$nav_links,Template::BOOL);

if($nav_links) {
    $prev = clone $d;
	$prev->modify('-1 ' . DISPLAY_TYPE);
    
	$next = clone $d;
	$next->modify('+1 ' . DISPLAY_TYPE);
    
    $nair->assign('previous_link',$prev->format('j.n.Y') . '.' . COOKIE_DOMAIN);
    $nair->assign('next_link',$next->format('j.n.Y') . '.' . COOKIE_DOMAIN);
    
    $nair->assign('previous_link_text',$prev->format('jS'));
    $nair->assign('next_link_text',$next->format('jS'));
    
}




$nair->assign('has_episodes',count(TVEpisodes::$episodes) > 0,Template::BOOL);
$nair->assign('sitebase',SITE_BASE);
$nair->assign('is_not_daily',(DISPLAY_TYPE != 'week' && DISPLAY_TYPE != 'rolling' && DISPLAY_TYPE != 'show'),Template::BOOL);
$cpl = $nair->startLoop('EPLOOP');

	
$cur_day = null;
$old_day = null;

$l = 0;

$this_season = TVEpisodes::$episodes[0]->season;

foreach(TVEpisodes::$episodes as $ep) {
    
    $shw = $all_shows[$ep->show_id];
    $ep->show_name = $shw->name;

    $ep->tv_rage_id = $shw->tvragerid;
    $ep->tv_dot_com_id = $shw->tvdotcomrid;

    $epdate = new DateTime($ep->ep_tzfix_date);
    $epdateutc = new DateTime($ep->date);
    
    $epdate = mod_zone_local($shw->timezone,$in_tz,$epdate,$mod_timezones);
    
    

    if(defined('INPUT_DAY') && $epdate->format('j') != INPUT_DAY) {
        	
        continue;
    }

    if(defined('INPUT_WEEK') && $epdate->format('W') != INPUT_WEEK) {
     
        continue;
    } else {
        $cur_day = $epdate->format('d-m-Y');
    }
    
    if(defined('INPUT_SHOW_ID')) {
        $cur_season = $ep->season;
    }

    $do_split = ($cur_day != $old_day && (DISPLAY_TYPE == 'week' || DISPLAY_TYPE == 'rolling')) || (defined('INPUT_SHOW_ID') && ($cur_season != $old_season));
    
    
    $dhead = (!defined('INPUT_SHOW_ID'))? $epdate->format('D') : 'Season ' . $cur_season;
    $cpl->assign('istoday',($today->format('d-m-Y') == $cur_day || $this_season == $cur_season),Template::BOOL);
    $cpl->assign('day_header',$dhead);
    
        
    $cpl->assign('not_top_split',($l != 0 && $do_split),Template::BOOL);
    
    $cpl->assign('dodaysplit', $do_split,Template::BOOL); 
    
    $cpl->assign('dospacer',$l == 0 || $do_split,Template::BOOL); 
    $cpl->assign('is_top',$l == 0 || $do_split,Template::BOOL);
    
    $old_day = $cur_day;
    $old_season = $cur_season;

    $cpl->assign('show_id',$shw->id);
    $cpl->assign('show_name',$shw->name);
    $cpl->assign('show_name_clean',clean_name($shw->name));
    $cpl->assign('main_url',COOKIE_DOMAIN . SITE_BASE);
    
    $cpl->assign('show_epnum',defined('INPUT_SHOW_ID'),Template::BOOL);
    $en = $ep->name;
    if(strlen($en) > 42) {
        $en = substr($en,0,40) . "...";
    
        
    }
    

    $cpl->assign('has_countryflag',file_exists(ABSOLUTE_SITE_BASE . IMAGES_FLAG_DIR . "/" . strtolower($shw->country) . ".png"),Template::BOOL);
    $cpl->assign('flagimage_url',"http://" . COOKIE_DOMAIN . SITE_BASE . IMAGES_FLAG_DIR . "/" . strtolower($shw->country) . ".png");
    $cpl->assign('has_classification',$shw->classification != '',Template::BOOL);
    
    if(is_array($shw->genres) && count($shw->genres) > 0) {
        $ep->genres = $shw->genres;
        $cpl->assign('has_genres',true,Template::BOOL);
        $cpl->assign('genres',implode(', ',$shw->genres));
    } else {
        $cpl->assign('has_genres',false,Template::BOOL);
    }

    $cpl->assign('classification',$shw->classification);
    $cpl->assign('country',$shw->country);

    $rating = number_format($ep->calcRating(),2);
    $cpl->assign('valid_user',$user->isValid(),Template::BOOL);
    $cpl->assign('rating',($rating == 0)? 'unrated' : $rating);
    $cpl->assign('has_votes',$ep->votes > 0,Template::BOOL);
    $cpl->assign('votecount',$ep->votes);
    $cpl->assign('has_network',!empty($shw->network),Template::BOOL);

    $cpl->assign('status',$shw->status);
    $cpl->assign('has_status',!empty($shw->status),Template::BOOL);


    $cpl->assign('network',$shw->network);
    //$en = htmlentities($en);
    $cpl->assign('is_showpage',!defined('INPUT_SHOW_ID'),Template::BOOL);


    $cpl->assign('has_tvrage_url',!empty($ep->tvrage_url),Template::BOOL);
    $cpl->assign('tvrage_url',$ep->tvrage_url);
    
    $cpl->assign('order',"r_" . $ep->id);
    $cpl->assign('episode_id',$ep->id);
    $cpl->assign('episode_name_short',$en);
    
    $cpl->assign('episode_name',$ep->name);
    
    $cpl->assign('hasairtime',true,Template::BOOL);
    $cpl->assign('airtime',($user->s_24hour)? $epdate->format('H:i') : $epdate->format('g:ia'));
    $cpl->assign('airdate',$epdate->format('jS M Y'));
    $tut = TimeUntil($epdateutc->format('U'),$todayutc->format('U'),1);
    if($tut === false) {
        
        $tut = TimeAgo($epdateutc->format('U'),$todayutc->format('U'),1) . " ago";
    } else {
        $tut = "in " . $tut;
    }
    $cpl->assign('timeuntil',$tut);
    $cpl->assign('haslength',($shw->length != 0),Template::BOOL);
        if($shw->length != 0) {
            $enddate = clone $epdate;
            $enddate->modify('+ ' . ($shw->length / 60) . ' minutes');
            $cpl->assign('endairtime','- ' . (($user->s_24hour)? $enddate->format('H:i') : $enddate->format('g:ia')));
            $ep->length = $shw->length;
            $ep->enddate = $enddate->format('Y-m-d H:i:s');
        
        } else {
            $cpl->assign('endairtime','');
        }
        if($shw->network != '') {
            $ep->network = $shw->network;
            $cpl->assign('network',' on ' . htmlentities($shw->network));
        } else {
            $cpl->assign('network','');
        }
    


    $cpl->assign('ep_season',$ep->season);
    $cpl->assign('ep_episode',$ep->episode);
    $cpl->assign('has_episode_summary',($ep->episode_summary != ''),Template::BOOL);
    $cpl->assign('episode_summary',strip_tags($ep->episode_summary));
    $l++;
    $cpl->nextLoop();
    
    
}
$nair->endLoop($cpl);
echo $nair->output();

 include("./footer.php");
 ?>

</body>
</html>
<?
//echo "<!-- " . print_r(DBConn::$cachegroups,true) . " -->";
/*

if(defined('DISPLAY_TYPE') && DISPLAY_TYPE == 'day') {
	$prev = clone $d;
	$prev->modify('-1 day');

	$next = clone $d;
	$next->modify('+1 day');

?>

<div style="width: 1000px; margin: 0px auto 10px auto; padding: 0px; background-color: #333333; display: block; height: 20px; border-top: 1px solid white;"><div class="prev-month-thin"><a href="http://<?=$prev->format('j.n.Y') . '.' . COOKIE_DOMAIN?>">&lt;&lt; <strong><?=$prev->format('jS M')?></strong></a></div><div class="next-month-thin" style="text-align: right;"><a href="http://<?=$next->format('j.n.Y') . '.' . COOKIE_DOMAIN?>"><strong><?=$next->format('jS M')?></strong> &gt;&gt;</a></div></div>
<?
}
?>



<div style="margin: 10px auto; "><a href="<?=HOMEPAGE_URL?>" style="color: #FEA143; margin: 0 auto 80px auto;"><strong>Back to the TV Calendar</strong></a></div>
*/
?>