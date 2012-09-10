<?
include('./include/config.inc.php');
$s = start_time();
ob_start('ob_gzhandler');

$user = User::getInstance();

$styles = Styles::getStyles();

//Handle settings changes in a seperate file so they can be changed from any page
include('handle_settings_changes.php');


$filtered_shows = array();
/* Start cat user handling */
if($user->isValid()) {

	$selected_timezone = $user->timezone;
	$shows = TVShows::getUserFilter($user->id);
	$filtered_shows = $shows;
	$sel_sh_count = count($shows);

	

	if(count($shows) == 0) {
		$shc = TVShows::getShows();
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


	$shows = TVShows::getShows();
	$sh_count = count($shows);
	$sel_sh_count = 0;
	$watched = array();

}


/*TEMP CSS SETTER*/
if(isset($_GET['cssfile'])) {
	$cssfile = $_GET['cssfile'];
} else {
	$cssfile = SITE_BASE . "/" . STYLE_DIR . '/' . $style->cssname;
}

if(!isset($_GET['m']) || !isset($_GET['y'])) {
	$d = new DateTime();
	$d->setTime(00,00,00);
	$in_tz = new DateTimeZone($selected_timezone);


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
	$d->setDate($_GET['y'],$_GET['m'],1);
	$d->setTime(00,00,00);
	$in_tz = new DateTimeZone($selected_timezone);


	$calendar = new Calendar($_GET['y'],$in_tz,$user->s_sunday_first);
	$cur_month = $calendar->addMonth($d->format('n'));


	$d_start = clone $d;
	$d_end = clone $d;

	
	$d_start->setDate($d_start->format('Y'),$d_start->format('n'),-1);
	$d_start->setTime(00,00,00);



	$d_end->setDate($d_end->format('Y'),$d_end->format('n'),$d_end->format('t')+1);
	$d_end->setTime(23,59,59);


}


$today = new DateTime();
$today->setTimeZone($in_tz);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-style-type" content="text/css" />
	<meta http-equiv="content-script-type" content="text/javascript" />
	<link rel="stylesheet" href="<?=SITE_BASE?>/css/mmccaatt.css,<?=$cssfile ?>" type="text/css" title="default" media="all" />
	<link rel="shortcut icon" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/favicon.ico" /> 
	
	
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


	<meta name="Robots" content="indexall,followall" />
<title>On-My.TV: Frequently Asked Questions - TV Calendar, Listings, Episode Guide. What's on your tv?</title> 
<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, tv guide, on my tv, FAQ, Frequently Asked Questions" />
</head>
<body> 
<?

include('user_handler.php');

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
?>
<h3 class="faq">Frequently Asked Questions</h3>
 
 <br /> 
 <br /> 
 <br />  
				<table class="faq" >
				<tr>
				<td class="faqanswers">
				<strong>How to use this FAQ</strong>
				<br /><div class="openeps"> 
				<div class="b1"></div>  
				This F.A.Q. is meant as a guideline to questions that we recieve e-mails about. It is most likely NOT a problem solving page. Please visit our IRC channel on <strong><a href="irc://irc.efnet.net">irc.efnet.net</a></strong> channel <strong>#tvcat</strong>, we may be able to help you out. <br />
				</div></td>
				</tr>
				</table> 

				<table class="faq" >
				<tr>
				<td class="faqanswers"><strong>Why should i register an account?</strong>
				  <div class="openeps"> 
				<div class="b1"></div>
				While you can use all of the features (except from the .ics generator) without registering on the site, registering assures that your settings are stored permanently within the calendar database.<br /><br />As an anonymous user your settings are stored in the server database, but if your user ID receives no activity for a period of 4 or more weeks, your settings are deleted to keep the database clean. Registering also allows you to login to the calendar from multiple locations. <br /><br />Once logged in, even as an anonymous user, you should never have to login again <strong>UNLESS</strong> you clean your browsers' cookie cache, or logout. The details you provide when you register, an email address and a password, are used for nothing else but logins and password recovery. We will <strong>NEVER</strong> share your email address with a third party.<br /><br />If you would like to register with the calendar, please head to the <a href="/register_account"> register page</a>. If you already have a filter or settings set with an anonymous user before registering, your settings should be transferred to your registered user as long as the anonymous user cookie exists at registration time.<br /><br /></div></td>
				</tr>
				</table>
				<div>
					<a href="/" class="gobackday">Back to the Calendar</a>
				</div>  

				<table class="faq" >
				<tr>
				<td class="faqanswers"><strong>Where does the info come from? </strong>
				  <div class="openeps"> 
				<div class="b1"></div>
				Since 2009 the site has been using <a href="http://www.tvrage.com">TVRage.com</a>'s XML Feeds as its' main data source. Where show information is missing or incorrect, <a href="http://epguides.com">Epguides</a> data is used to fill in the gaps. Data is collected once a day.
				</div></td>
				</tr>
				</table>
				<div>
					<a href="/" class="gobackday">Back to the Calendar</a>
				</div> 
				 
				<table class="faq" >
				<tr>
				<td class="faqanswers"><strong>Why are some shows not shown, or have incorrect information? </strong>
				  <div class="openeps"> 
				<div class="b1"></div>
				Due to the sheer amount of data which is used to build the calendar, it is really not a manual job. We do have nearly every major / minor weekly series tracked and we do run updates every day, but if TVRage have show info missing, then so do we. If TVRage have a show listing but it is not visible on the calendar, Please email using the contact link at the bottom of the page asking for the show to be added. When requesting a show be added, it would be very helpful to link to the TVRage page for a show (or give its' show ID, if you know how to find it), its' page on Epguides and TV.com. 
				</div></td>
				</tr>
				</table>
				<div>
					<a href="/" class="gobackday">Back to the Calendar</a>
				</div> 
				
				<table class="faq" >
				<tr>
				<td class="faqanswers"><strong>What do the colours mean?</strong>
				  <div class="openeps"> 
				<div class="b1"></div>
				We have recently introduced a system that highlights the FIRST EPSIODE of that season (eg 1x01, 3x01, 17x01). This is so you can more easily see when your favorite shows start. The first episodes are shown in ORANGE.  <br /><br />

We have a new feature where you can mark of which shows you have watched.  When you mark them, the shows will go GREEN.<br /><br />

All standard shows are coloured WHITE.				</div></td>
				</tr>
				</table>
				<div>
					<a href="/" class="gobackday">Back to the Calendar</a>
				</div>

				<table class="faq" >
				<tr>
				<td class="faqanswers"><strong>How can I generate an ICS for google calendar?</strong>
				  <div class="openeps"> 
				<div class="b1"></div>
				Currently, the link you see on the top bar when logged in to 'Download iCal File', forces your browser to download the iCal to file directly rather than showing the contents of the file in your browser. This is the desired behaviour if you want to import it manually into a calendar program which may not have an import-from-url feature. Unfortunately, the way the file is forced to download (by telling your browser the file is an attachment) is not compatible with google calendar; for some reason, it is unable to read the data (this used to work, strangely).<br /><br />If you copy the URL given to you from the 'Download iCal File' link and change '<i>download-ics</i>' to '<i>generate-ics</i>', you should be able to use the new link within google calendar and most calendar programs with an import-from-url feature. By telling the server to generate the ics rather than download it, the file is sent without an attachment header and can be read correctly by google.<br /><br />

			</div></td>
				</tr>
				</table>
				<div>
					<a href="/" class="gobackday">Back to the Calendar</a>
				</div>


				<table class="faq" >
				<tr>
				<td class="faqanswers"><strong>How can I use the RSS / XML Feeds?</strong>
				  <div class="openeps"> 
				<div class="b1"></div>
				XML / RSS outputs are available via links at the bottom of any of the daily, weekly or monthly episode grids (not for the main calendar view by month). You do not need to be signed into the site, but if your RSS Reader / Browser sends the cookie used to log you in to the site along with the request for the RSS or XML feeds, they will be filtered by your filter settings.<br /><br />The XML output is a custom XML format representing the data objects used to store the TV Show / Episode data internally, intended for use by scripts, and cannot be read by RSS readers. You must use the RSS feed for this as it is specifically formatted.<br /><br />Whether you intend to use either of the feeds with a script or simply in your own RSS reader, please set your automatic refresh to 30+ minutes - the data does not change often and anything less is a waste of both your and my bandwidth.<br />I <strong>DO</strong> intend to expose more stored data via the XML feed in the future.  <br /><br />

			</div></td>
				</tr>
				</table>
				<div>
					<a href="/" class="gobackday">Back to the Calendar</a>
				</div>

<div> 
<br />



</div>
<? include("./footer.php"); ?>
</body>
</html>
