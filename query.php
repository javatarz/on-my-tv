<?
require_once('./include/config.inc.php');
ob_start('ob_gzhandler');
$user = User::getInstance();

if($user->isValid()) {
	$selected_timezone = $user->timezone;
} else {
	$selected_timezone = DEFAULT_TIMEZONE;
}

if(isset($_REQUEST['q'])) {
	
	$shi = new Template('episode_popup');
	$shi->assign('main_url',COOKIE_DOMAIN . SITE_BASE);

	$request_object = substr($_REQUEST['q'],2);

    $in_tz = new DateTimeZone($selected_timezone);

	TVEpisodes::setFilterTimezone($selected_timezone);
	TVEpisodes::setFilterSelectSummaries(false);

	$ep = TVEpisodes::getEpisodeByID($request_object);
	
	//$ep->fetch();

	$sh = new TVShow($ep->show_id);
	//$sh->fetch();

	$epdate = new DateTime($ep->ep_tzfix_date);
	
    $epdate = mod_zone_local($sh->timezone,$in_tz,$epdate,$mod_timezones);
    
	if(strlen($ep->episode_summary) == 0) {$ep->episode_summary = "No Summary Available..."; }
	if(is_float($ep->calcRating())) {
		$whval = floor($ep->calcRating());
		$pval = floor(($ep->calcRating() - $whval) / 0.5);
		if($pval == 0) {
			$hval = 0;
		} else {
			$hval = floor((1 - ($ep->calcRating() - $whval)) / 0.5);
		}

		$rval = floor(5 - $whval - ($pval * 0.5) - ($hval * 0.5));
	} else {
		$whval = $ep->calcRating();
		$pval = 0;
		$hval = 0;
		$rval = 5 - $whval;
	}


	$shi->assign('rating',$whval,Template::REPEAT);
	$shi->assign('rating_parts',$pval,Template::REPEAT);
	$shi->assign('rating_off_parts',$hval,Template::REPEAT);
	$shi->assign('rating_off',$rval,Template::REPEAT);

	$has_airnetwork = $ep->ep_tzfix_date != null && $sh->network != '';
	$shi->assign('has_airtnetwork',$has_airnetwork,Template::BOOL);

    $shi->assign('show_link',"http://" . $epdate->format('j.n.Y') . "." . COOKIE_DOMAIN . SITE_BASE . '/'  . '#r_' . $ep->id);
	$shi->assign('has_countryflag',file_exists(ABSOLUTE_SITE_BASE . IMAGES_FLAG_DIR . "/" . strtolower($sh->country) . ".png") && $has_airnetwork,Template::BOOL);
	$shi->assign('flagimage_url',"http://" . COOKIE_DOMAIN . SITE_BASE . IMAGES_FLAG_DIR . "/" . strtolower($sh->country) . ".png");
	$shi->assign('country',$sh->country);
	$shi->assign('episode_summary',summary_format_ajax($ep->episode_summary));
	$shi->assign('showname',$sh->name);
	$shi->assign('season',$ep->season);
	$shi->assign('episode',$ep->episode);
	$shi->assign('episode_title',$ep->name);
	
	$shi->assign('ep_airtime',($user->s_24hour)? $epdate->format('H:i') : $epdate->format('g:ia'));
	$shi->assign('ep_network',$sh->network);
	echo $shi->output();
	
}


?>