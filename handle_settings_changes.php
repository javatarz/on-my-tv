<?
require_once('./include/config.inc.php');

/* Start handling sub-cat settings */

if(isset($_POST['settings'])) {
	if(isset($_POST['s_numbers'])) {
		if($_POST['s_numbers'] == 1 || $_POST['s_numbers'] == 0) {
			$user->s_daily_numbers = $_POST['s_numbers'];
		}
	}

	if(isset($_POST['s_sundayfirst'])) {
		if($_POST['s_sundayfirst'] == 1 || $_POST['s_sundayfirst'] == 0) {
			$user->s_sunday_first = $_POST['s_sundayfirst'];
		}
	}

	if(isset($_POST['s_airtimes'])) {
		if($_POST['s_airtimes'] == 1 || $_POST['s_airtimes'] == 0) {
			$user->s_daily_airtimes = $_POST['s_airtimes'];
		}
	}

	if(isset($_POST['s_networks'])) {
		if($_POST['s_networks'] == 1 || $_POST['s_networks'] == 0) {
			$user->s_daily_networks = $_POST['s_networks'];
		}
	}

	if(isset($_POST['s_epnames'])) {
		if($_POST['s_epnames'] == 1 || $_POST['s_epnames'] == 0) {
			$user->s_daily_epnames = $_POST['s_epnames'];
		}
	}

	if(isset($_POST['s_popups'])) {
		if($_POST['s_popups'] == 1 || $_POST['s_popups'] == 0) {
			$user->s_popups = $_POST['s_popups'];
		}
	}

	if(isset($_POST['s_wunwatched'])) {
		if($_POST['s_wunwatched'] == 1 || $_POST['s_wunwatched'] == 0) {
			$user->s_wunwatched = $_POST['s_wunwatched'];
		}
	}

	if(isset($_POST['s_sortbyname'])) {
		if($_POST['s_sortbyname'] == 1 || $_POST['s_sortbyname'] == 0) {
			$user->s_sortbyname = $_POST['s_sortbyname'];
		}
	}
    
    if(isset($_POST['s_24hour'])) {
		if($_POST['s_24hour'] == 1 || $_POST['s_24hour'] == 0) {
			$user->s_24hour = $_POST['s_24hour'];
		}
	}


	

	if(!$user->isValid()) {
		if(!isset($_POST['style'])) {
			$user->style = DEFAULT_STYLE;
		} else {
			$s_check = new Style($_POST['style']);
			if($s_check->isValid()) {
				$user->style = $_POST['style'];
			} else {
				$user->style = DEFAULT_STYLE;
			}
		}

		if(in_array($_POST['timezone'],$timezones)) {
			$user->timezone = $_POST['timezone'];
		} else {
			$user->timezone = DEFAULT_TIMEZONE;
		}

		$user->newUser();
		
		if($user->isValid()) {
			$user->fetch();
			//header('Location: ' . SITE_BASE . '/reforward');
			header('Location: ' . $_SERVER['REQUEST_URI']);
			die();
		} else {
			die('Could not create new user :(');
		}
	} else {

		if(!isset($_POST['style'])) {
			$user->style = DEFAULT_STYLE;
		} else {
			$s_check = new Style($_POST['style']);
			if($s_check->isValid()) {
				$user->style = $_POST['style'];
			} else {
				$user->style = DEFAULT_STYLE;
			}
		}
		
		if(in_array($_POST['timezone'],$timezones)) {
			$user->timezone = $_POST['timezone'];
		} else {
			$user->timezone = DEFAULT_TIMEZONE;
		}
		TVEpisodes::killWatched();
		$user->Update(false);

		header('Location: ' . $_SERVER['REQUEST_URI']);
		die();

	}
}
/* End handling sub-cat settings */
