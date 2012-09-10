<?
include('./include/config.inc.php');
$s = start_time();

$user = User::getInstance();

$styles = Styles::getStyles();

if($user->isValid()) { 
	$style = new Style($user->style);
} else {
	$style = new Style(DEFAULT_STYLE);
}

/*TEMP CSS SETTER*/
if(isset($_GET['cssfile'])) {
	$cssfile = $_GET['cssfile'];
} else {
	$cssfile = SITE_BASE . "/" .  STYLE_DIR . '/' . $style->cssname;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns='http://www.w3.org/1999/xhtml'><head>
<title>On-My.TV: Filter Properties. What's on your tv?</title>
<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, tv guide, on my tv" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<link rel="stylesheet" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/css/mmccaatt.css,<?=$cssfile ?>" type="text/css" title="default" media="all" />
<link rel="shortcut icon" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/favicon.ico" /> 
<link rel="stylesheet" href="<?=SITE_BASE?>/css/mmccaatt.css,<?=$cssfile ?>" type="text/css" title="default" media="all" />
<script type="text/javascript" src="http://<?=COOKIE_DOMAIN . SITE_BASE?>/js/jquery-1.3.2.min.js,common.jq.comp.js"></script>
<script type="text/javascript" src="http://<?=COOKIE_DOMAIN . SITE_BASE?>/js/showselect.jq.comp.js"></script>



</head>

<body><h1>TV Show Filters</h1>
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">

<div style="text-align:center"><input class="searchbutton" type="button" id="sall" value="Select All" /><input class="searchbutton" type="button" id="snone" value="Select None" /></div>

<div class="explain"><span class="openeps"><strong><span class="new">Yellow</span> show names denote shows added to the CAT since you last updated your filter </strong></span></div>
<div class="explain"><span class="openeps"><strong><span class="prem">Green</span> show names denote shows airing their premier season at present or 2nd series hasn't started yet </strong></span></div>
<div class="explain"><span class="openeps"><strong>Please Note : Names are no longer preceeded with "The" - e.g. The Office -> Office, The</strong></span></div>
<div class="showselectbody">
<?

$sel = new Template('showselect_show');

if(isset($_POST['filter'])) {


	if(!$user->isValid()) {
		if(!isset($_POST['style'])) {
			$user->style = DEFAULT_STYLE;
		}

		if(in_array($_POST['timezone'],$timezones)) {
			$user->timezone = $_POST['timezone'];
		} else {
			$user->timezone = DEFAULT_TIMEZONE;
		}

		$user->newUser();

		if($user->isValid()) {
			$user->fetch();
		} else {
			die('Could not create new user :(');
		}
	} else {

		$user->Update(true);

	}
	
	
	if(is_array($_POST['selected_shows'])) {

		foreach($_POST['selected_shows'] as $s) {
			TVShows::addFilter($s);
		}

		TVShows::storeFilter($user->id);

	} else {
		TVShows::clearFilter();
		TVShows::storeFilter($user->id);
	
	}

	header('Location: ' . HOMEPAGE_URL . '');
}

if($user->isValid()) {
	$shows_selected = TVShows::getUserFilter($user->id);
} else {
	$shows_selected = array();
}

$shows = TVShows::getShows(true);

$leps = TVEpisodes::getLatestEpisodes($shows);


$last_char = '';

for($i = 0; $i < count($shows); $i++) {
	//echo "<!-- " . print_r($shows[$i],true) . " -->";
	if(stripos($shows[$i]->name,'The') === 0) {
		$shows[$i]->origname = trim($shows[$i]->name);
		$shows[$i]->name = trim(str_ireplace('The ','',$shows[$i]->name) . ', The');
	} else {
		$shows[$i]->origname = trim($shows[$i]->name);
	}
	
}



$first_char = '';

$sh_ab = $sel->startLoop('SHOWALPH');
$i = 4;
foreach($shows as $sh) {
	$first_char = substr($sh->name,0,1);

	if(strtolower($last_char) != strtolower($first_char)) {
		if(is_numeric($first_char) && !is_numeric($last_char)) {
			$sh_ab->assign('endchanged',($last_char != ''),Template::BOOL);
			$sh_ab->assign('ischanged',true,Template::BOOL);
			
			$sh_ab->assign('showchar','#');
			$i = 0;
			
		} elseif(!is_numeric($first_char)) {
			$sh_ab->assign('endchanged',($last_char != ''),Template::BOOL);
			$sh_ab->assign('ischanged',true,Template::BOOL);
	
			$sh_ab->assign('showchar',strtoupper($first_char));
			$i = 0;
		
		} else {
			$sh_ab->assign('ischanged',false,Template::BOOL);
			$sh_ab->assign('endchanged',false,Template::BOOL);
			$i++;
		}
	} else {
		$sh_ab->assign('ischanged',false,Template::BOOL);
		$sh_ab->assign('endchanged',false,Template::BOOL);
		$i++;
	}

	
	$sh_ab->assign('show_name_clean',clean_name($sh->origname));
	$sh_ab->assign('main_url',COOKIE_DOMAIN . SITE_BASE);


	$sh_ab->assign('checkid',$sh->id);

	$last_char = $first_char;

	$latest_ep = TVEpisodes::returnLatestEpisodeForShow($sh->id);
	
	if($latest_ep !== false && is_a($latest_ep,'TVEpisode')) {
		if($latest_ep->season == 1) {
			$sh_premiering = true;
		} else {
			$sh_premiering = false;
		}

	} else {
		$sh_premiering = true;
	}

	$added_date = new DateTime($sh->added_date);
	$updated_date = new DateTime($user->last_filter_update);

	if($added_date->format('U') >= $updated_date->format('U')) {
		$sh_new = true;
	} else {
		$sh_new = false;
	}

	$sh_selected = false; 

	foreach($shows_selected as $ss) {
		if($ss->id == $sh->id) {
			$sh_selected = true;
			break;
		}
	}
	
	$sh_ab->assign('newclass',$sh_new,Template::BOOL);
	
	$sh_ab->assign('premieringclass',!$sh_new && $sh_premiering,Template::BOOL);
	

	$sh_ab->assign('ischecked',$sh_selected,Template::BOOL);
	$sh_ab->assign('show_id',$sh->id);
	$sh_ab->assign('showname',$sh->name);
	
		 

	$sh_ab->nextLoop();
}

$sel->endLoop($sh_ab);



echo $sel->output();


include ("footer.php");

?>
