<?
require_once('./include/config.inc.php');

if(!isset($user)) {
	$user = User::getInstance();
}


if(!$footer_hidden) {
	//include('handle_settings_changes.php');
	
	$set = new Template('calendar_fullpage_settings');
	$set->assign('sitebase',SITE_BASE);
	$set->assign('stylename',STYLE_DIR . '/' . $style->cssname);
	$set->assign('request_uri',SITE_BASE . '/index.php');

} else {
	$set = new Template('settings_box');
	$set->assign('request_uri', $_SERVER['REQUEST_URI']);

}

$tzl = $set->startLoop('TZLOOP');


foreach($timezones as $n => $t) {
	$tzl->assign('tzname',$n);
	$tzl->assign('tzid',$t);
	if($user->isValid()) {
		$tzl->assign('iscurtz',$t == $user->timezone,Template::BOOL);
	} else {
		$tzl->assign('iscurtz',$t == DEFAULT_TIMEZONE,Template::BOOL);
	}
	$tzl->nextLoop();
}

$set->endLoop($tzl);

$stl = $set->startLoop('STYLOOP');

foreach($styles as $sx) {
	
	$stl->assign('styname',$sx->name);
	$stl->assign('styid',$sx->id);
	if($user->isValid()) {
		$stl->assign('iscursty',$sx->id == $user->style,Template::BOOL);
	} else {
		$stl->assign('iscursty',$sx->cssname == DEFAULT_STYLE_NAME,Template::BOOL);
	}
	$stl->nextLoop();

}
$set->endLoop($stl);

$set->assign('hidden',(isset($_GET['os']))? false : true,Template::BOOL);
$set->assign('closettings', clearURLSet(CURRENT_URL));
$set->assign('s_numberss_true',($user->s_daily_numbers == true),Template::BOOL);
$set->assign('s_numberss_false',($user->s_daily_numbers == false),Template::BOOL);

$set->assign('s_sundayfirsts_true',($user->s_sunday_first == true),Template::BOOL);
$set->assign('s_sundayfirsts_false',($user->s_sunday_first == false),Template::BOOL);

$set->assign('s_airtimess_true',($user->s_daily_airtimes == true),Template::BOOL);
$set->assign('s_airtimess_false',($user->s_daily_airtimes == false),Template::BOOL);

$set->assign('s_networkss_true',($user->s_daily_networks == true),Template::BOOL);
$set->assign('s_networkss_false',($user->s_daily_networks == false),Template::BOOL);

$set->assign('s_epnamess_true',($user->s_daily_epnames == true),Template::BOOL);
$set->assign('s_epnamess_false',($user->s_daily_epnames == false),Template::BOOL);

$set->assign('s_popupss_true',($user->s_popups == true),Template::BOOL);
$set->assign('s_popupss_false',($user->s_popups == false),Template::BOOL);

$set->assign('s_wunwatchedd_true',($user->s_wunwatched == true),Template::BOOL);
$set->assign('s_wunwatchedd_false',($user->s_wunwatched == false),Template::BOOL);


$set->assign('s_24hourr_true',($user->s_24hour == true),Template::BOOL);
$set->assign('s_24hourr_false',($user->s_24hour == false),Template::BOOL);

$set->assign('s_sortbynamee_true',($user->s_sortbyname == true),Template::BOOL);
$set->assign('s_sortbynamee_false',($user->s_sortbyname == false),Template::BOOL);



echo $set->output();
?>