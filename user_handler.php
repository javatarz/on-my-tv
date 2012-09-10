<?
if(!class_exists('User')) { Header('Location: ' . CURRENT_URL); }
$user = User::getInstance();

if(isset($_REQUEST['sub_logout'])) {
	$user->logout();
	Header('Location: /');

} elseif(isset($_POST['sub_login'])) {
	
	if($user->login($_POST['username'],$_POST['password'])) {
		
		Header('Location: http://' . $_SERVER['HTTP_HOST'] . '/' . clearURLSet($_SERVER['REQUEST_URI']));
	} else {
		$uha = new Template('login_box');

		$errormessage = "U / P Invalid";
		$uha->assign('hidden',false,Template::BOOL);
		$uha->assign('closelogin',clearURLSet(CURRENT_URL));
		$uha->assign('errormessage',$errormessage);
		$uha->assign('request_uri', $_SERVER['REQUEST_URI']);
		echo $uha->output();
	}
} elseif(!$user->isValid()) {
	$uha = new Template('login_box');
	$uha->assign('hidden',(isset($_GET['ol']))? false : true,Template::BOOL);
	$uha->assign('closelogin',clearURLSet(CURRENT_URL));
	$uha->assign('errormessage','');
	$uha->assign('request_uri', $_SERVER['REQUEST_URI']);
	echo $uha->output();
} else {
	$uha = new Template('login_box');
	$uha->assign('hidden',(isset($_GET['ol']))? false : true,Template::BOOL);
	$uha->assign('closelogin',clearURLSet(CURRENT_URL));
	$uha->assign('errormessage','You\'re currently logged in as an anonymous user. Please register if you would like to save your current settings permanently.');
	$uha->assign('request_uri', $_SERVER['REQUEST_URI']);
	echo $uha->output();
}
/*
if($user->name == 'maz@lawlr.us') {
    $uln = new Template('link_box');
    $uln->assign('closelogin',clearURLSet(CURRENT_URL));
    
    $uln->assign('this_week',THIS_WEEKS_URL);
    $uln->assign('today',TODAYS_URL);
    $uln->assign('tomorrow',TOMORROWS_URL);
    $uln->assign('this_month',HOMEPAGE_URL);

    echo $uln->output();
}*/
?>