<?
include('./include/config.inc.php');
ob_start('ob_gzhandler');

$user = User::getInstance();

$styles = Styles::getStyles();

if($user->isValid()) { 
	$style = new Style($user->style);
} else {
	$style = new Style('0');
}

if(isset($_POST['submit_register'])) {
	if($_POST['password'] == $_POST['passwordconf']) {
		if(strlen($_POST['password']) > 3 && strlen($_POST['password']) < 13) {
			if(!Users::exists($_POST['email'])) {
				if(checkEmail($_POST['email'])) {

					$user->name = $_POST['email'];
					

					if(!$user->isValid()) {
						
						$user->style = DEFAULT_STYLE;
						$user->timezone = DEFAULT_TIMEZONE;
						$user->s_disableads = 0;
				
						$user->newUser();
						
						if($user->isValid()) {
							$user->fetch();
							$user->password = $_POST['password'];
							$user->UpdatePassword();
							//header('Location: ' . HOMEPAGE_URL);
							die();
						} else {
							die('Could not create new user :(');
						}
					} else {
						$user->Update(false);
						$user->password = $_POST['password'];
						$user->UpdatePassword();

						header('Location: ' . HOMEPAGE_URL);
						die();

					}

				} else {
					$errmsg = "Invalid Email Address";
				}
			} else {
				$errmsg = "Username (email) already in use!";
			}
		} else {
			$errmsg = "Invalid Password (must be 3-12 Characters length)";
		}

	} else {
		$errmsg = "Password & Password Confirmation do not match.";
	}
	

}

/*TEMP CSS SETTER*/
if(isset($_GET['cssfile'])) {
	$cssfile = $_GET['cssfile'];
} else {
	$cssfile = SITE_BASE . '/' . STYLE_DIR . '/' . $style->cssname;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns='http://www.w3.org/1999/xhtml'><head>
<title>On-My.TV: Register Account - TV Calendar, Listings, Episode Guide. What's on your tv?</title>
<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, tv guide, on my tv" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<link rel="stylesheet" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/css/mmccaatt.css,<?=$cssfile ?>" type="text/css" title="default" media="all" />
<link rel="shortcut icon" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/favicon.ico"  /> </head>

<body><h1>Register Account</h1>

	<div class="explain"><span class="openeps"><strong>"Registering" an account with the calendar allows your settings to be stored permanently </strong></span></div>
	<div class="explain"><span class="openeps"><strong>Your email address is **only** used for ID &amp; P/W Reset</strong></span></div>
<div class="explain"><span class="openeps new"><strong>If you do not register an account to your email address, your settings will be deleted after 4 weeks.</strong></span></div>
<div class="explain"><span class="openeps"><strong>Please Note : Once logged in, you should not need to log in again unless you clear your cookies.</strong></span></div>
<div class="showselectbody">
<div class="regish" >
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" >
	<? if($user->name == null && $user->password == null || $errmsg != '') {  echo $errmsg . ""; ?>

		<label for="email" class="regis">Email Address : </label>
		  <div class="inputs"><input name="email" id="email" type="text" value="<?=$_POST['email']?>" maxlength="200" /></div> 
 
		<label for="password" class="regis">Password : </label>
		  <div class="inputs"><input name="password" id="password" type="password" value="" /></div> 
 
		<label for="passwordconf" class="regis">Confirm Password : </label>
		  <div class="inputs"><input name="passwordconf" id="passwordconf" type="password" value="" /></div>

		<div class="regminitext"><input name="submit_register" id="submit_register" type="submit" class="regbutton" value="Register" /></div>

		</form>
	<?
	} else {
		echo "You already have an account logged in under the username '" . $user->name . "'. To create a new user account, you will need to <strong>logout</strong>";
	}
	?>
</div>
</div>
</body>
</html>