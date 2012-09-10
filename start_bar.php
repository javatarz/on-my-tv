<?
if(!isset($_GET['os'])) {
	$os = '/os';
}
if(!isset($_GET['ol'])) {
	$ol = '/ol';
}

if(!isset($_GET['or'])) {
	$or = '/or';
}

if(!isset($_GET['ox'])) {
	$ox = '/ox';
}

$tt = ($user->s_24hour)? $today->format('H:i') : $today->format('g:ia');
?>
<div id="options">
<?
/*
if($user->name == 'maz@lawlr.us') {
?>
<div class="o0"><br /><a id="linkslink" href="<?= clearURLSet(CURRENT_URL) . $ox ?>">[Navigate]</a></div>
<?
}*/
?>
<div class="o1"><strong><acronym title="New shows added to CAT since your Last Filter Update">New shows added to CAT</acronym>:</strong> <?=(isset($nshows))? $nshows : '0'?><br /><strong><acronym title="Last Filter Update">LFU</acronym>:</strong> <?= $last_update->format('H:i jS M \'y') ?></div>
<div class="o2"><strong>New (filtered) episodes airing today:</strong> <?=$todays_shows?><br /><strong><a href="<?=SHOW_SELECT_URL?>"><?= $sel_sh_count . '/' . $sh_count ?> - Add Shows to Your Filter</a></strong><?= ($user->name == null && $user->password == null)? '' : ' | <a href="' . clearURLSet(CURRENT_URL) . '/download-ics/' . $user->uid . '">Download iCal File</a>'?></div>
<div class="o1"><strong><acronym title="Your current timezone setting">Timezone</acronym>:</strong> <?= $selected_timezone . ' (' . $tt . ')' ?><br /><span class="usr"><strong><acronym title="Your logged in Username">USR</acronym>:</strong> <?echo ($user->name == null && $user->password == null)? '<a href="' . clearURLSet(CURRENT_URL) . $ol . '" id="loginlink" >Login</a> / <a href="' . clearURLSet(CURRENT_URL) . $or . '" id="registerlink" >Register</a>' : '<strong>' . preg_replace('%@.*%iS','',$user->name) . '</strong>'?> <a id="settingslink" href="<?= clearURLSet(CURRENT_URL) . $os ?>">[Settings]</a><?echo ($user->name == null && $user->password == null)? '' : ' <a href="' . HOMEPAGE_URL . SITE_BASE  . '/logout" id="logoutlink" >[Logout]</a>'?></span></div> 
</div> 
