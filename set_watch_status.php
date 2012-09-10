<?
include('./include/config.inc.php');
ob_start('ob_gzhandler');


$user = User::getInstance();


if(isset($_GET['w']) && isset($_GET['sh']) && isset($_GET['s']) && isset($_GET['e']) && is_numeric($_GET['sh']) && is_numeric($_GET['s']) && is_numeric($_GET['e'])) {

	if(!$user->isValid()) {
		$user->style = DEFAULT_STYLE;
		$user->timezone = DEFAULT_TIMEZONE;


		$user->newUser();

		if(!$user->isValid()) {
			die('Could not create new user :(');
		} else {
			$user->fetch();
		}
	}


	if($_GET['w'] == 1) {
		$v = TVEpisodes::setWatched($_GET['sh'],$_GET['s'],$_GET['e'],$user->id);
	} else {
		$v = TVEpisodes::setUnWatched($_GET['sh'],$_GET['s'],$_GET['e'],$user->id);
	}
	
	if(isset($_GET['r'])) {
		if(isset($_REQUEST['x'])) {
			if($v && $_GET['w'] == 1) {
                if(isset($_REQUEST['urid'])) {
                    echo htmlentities($_REQUEST['urid']);
                } else {
                    echo '&lt;';
                }
			} else {
                if(isset($_REQUEST['urid'])) {
                    echo htmlentities($_REQUEST['urid']);
                } else {
                    echo '&gt;';
                }
			}
		} else {
			header('Location: ' . SITE_BASE . '/' . $_GET['r']);
		}
	} else {
		header('Location: ' . SITE_BASE . '/reforward');
	}
}
