<?
include('./include/config.inc.php');
$return_url = '<a style="font-weight: bold;" href="' . HOMEPAGE_URL . '">Return to the Calendar</a>';
error_reporting(E_ERROR);
require("class.phpmailer.php");
ob_start('ob_gzhandler');

$user = User::getInstance();

$styles = Styles::getStyles();

if($user->isValid()) { 
	$style = new Style($user->style);
} else {
	$style = new Style('0');
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
<title>On-My.TV: Forgotten Password Reset - TV Calendar, Listings, Episode Guide. What's on your tv?</title>
<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, tv guide, on my tv" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<link rel="stylesheet" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/css/mmccaatt.css,<?=$cssfile ?>" type="text/css" title="default" media="all" />
<link rel="shortcut icon" href="http://<?=COOKIE_DOMAIN . SITE_BASE?>/favicon.ico"  /> </head>
 
</head>
<?

if(isset($_POST['submit_resetpassword'])) { 
	if($_POST['password'] == $_POST['passwordconf']) {
		if(strlen($_POST['password']) > 3 && strlen($_POST['password']) < 13) {
			$user->uid = $_POST['uid'];
			$user->fetch();
			if($user->isValid() && md5($user->uid . $user->name) == $_POST['uidsh']) {
				$user->password = $_POST['password'];
			
				$user->UpdatePassword();
				$errmsg = "Password changed successfully! Please relogin.<br />" . $return_url;
				$user->logout();
			} else {
				$errmsg = "Sorry, you're not authorized to change the password for this account.<br />" . $return_url;
			}
		}
	}
} elseif(isset($_POST['submit_resetemail'])) {
	$fuser = Users::returnUserWithEmail($_POST['email']);
	if($fuser->isValid()) {
		$_hash = md5($fuser->uid . $fuser->name);
		$_name = urlencode($fuser->name);
		$mail = new PHPMailer();
		$mail->Host = "mailserver";
		$mail->SMTPSecure = 'ssl';
		$mail->SMTPAuth = true;
		$mail->Username = 'nopasswordtest';
		$mail->Password = '';
		$mail->Mailer = "smtp";
		$mail->AddAddress($fuser->name,'');
		$mail->Subject = "[On-My.TV] Password Reset Request";
		$mail->IsSmtp(true);
		$mail->From = "do_not_reply@on-my.tv";
		$mail->Sender = "do_not_reply@on-my.tv";
		$mail->FromName = "[On-My.TV] Password Reset Request";
		$mail->isHTML(true);
		$mail->Body = <<<OEF
		<strong>To reset your password, please follow the link below:</strong><br />
		<a href="http://on-my.tv/forgotten-password/${_hash}/{$_name}">Password Reset</a><br /><br />
		Cheers,<br />
		<strong> The on-my.tv Team.</strong>
OEF;
	
		if(!$mail->Send()) {
			$errmsg = 'Unable to send password reset request email at this time. Please try again later.<br />' . $return_url;
		
		} else {
			$errmsg = 'Mail Sent! Please be patient.<br />' . $return_url;
		}
	} else {
		$errmsg = 'Email given was not on record.<br />' . $return_url;
	}


} elseif(isset($_GET['uidsh']) && isset($_GET['name'])) {
	$fuser = Users::returnUserWithEmail($_GET['name']);
	if($fuser->isValid()) {
		if(md5($fuser->uid . $fuser->name) == $_GET['uidsh']) {


		
?>
<body><h1>Reset your Password</h1>

	
<div class="explain"><span class="openeps new"><strong>You may now reset the password for account '<?=$_GET['name']?>':</strong></span></div>
<div class="showselectbody">
<div class="regish" >
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" >
	<? if($errmsg != '') {  echo $errmsg . " <br />"; } else { ?>
		<input name="uid" id="uid" type="hidden" value="<?=$fuser->uid?>" />
		<input name="uidsh" id="uidsh" type="hidden" value="<?=$_GET['uidsh']?>" />
		<label for="password" class="regis">New Password : </label>
		  <div class="inputs"><input name="password" id="password" type="password" value="" maxlength="12" /></div> 
		<label for="passwordconf" class="regis">Confirm Password : </label>
		  <div class="inputs"><input name="passwordconf" id="passwordconf" type="password" value="" maxlength="12" /></div> 

		<div class="regminitext"><input name="submit_resetpassword" id="submit_resetpassword" type="submit" class="regbutton" value="Set Password" /></div>

		</form>
	<?
	}
	?>
</div>
</div>
</body>
</html>
<?
		} else {
			$errmsg = "Sorry, you're not authorized to change the password for this account.<br />" . $return_url;
		}
	
	} else {
		$errmsg = "Sorry, user invalid!<br />" . $return_url;
	}
	
}

if(!isset($_GET['uidsh']) || $errmsg != '') {
//Email form here

	
?>


<body><h1>Request Password Reset Email</h1>

	
<div class="explain"><span class="openeps new"><strong>Filling out this form will NOT reset your password - you will be emailed a unique link which you may use to reset your password.</strong></span></div>
<div class="showselectbody">
<div class="regish" >
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" >
	<? if($errmsg != '') {  echo $errmsg . " <br />"; } else { ?>

		<label for="email" class="regis">Email Address : </label>
		  <div class="inputs"><input name="email" id="email" type="text" value="<?=$user->email?>" maxlength="200" /></div> 

		<div class="regminitext"><input name="submit_resetemail" id="submit_resetemail" type="submit" class="regbutton" value="Request Email" /></div>

		</form>
	<?
	}
	?>
</div>
</div>
</body>
</html>
<?
}
?>
