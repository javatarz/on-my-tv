<?
if(!class_exists('User')) { Header('Location: ' . CURRENT_URL); }
$user = User::getInstance();

$uha = new Template('register_box');

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
							header('Location: ' . HOMEPAGE_URL);
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
                    $uha->assign('hidden',false,Template::BOOL);
				}
			} else {
				$errmsg = "Username (email) already in use!";
                $uha->assign('hidden',false,Template::BOOL);
			}
		} else {
			$errmsg = "Invalid Password (must be 3-12 Characters length)";
            $uha->assign('hidden',false,Template::BOOL);
		}

	} else {
		$errmsg = "Password & Password Confirmation do not match.";
        $uha->assign('hidden',false,Template::BOOL);
	}
	

} else {
    $uha->assign('hidden',(isset($_GET['or']))? false : true,Template::BOOL);
}
$uha->assign('email',$_POST['email']);
$uha->assign('closeregister',clearURLSet(CURRENT_URL));
$uha->assign('errormessage',$errmsg);
$uha->assign('request_uri', $_SERVER['REQUEST_URI']);
if ($user->name == null && $user->password == null || isset($_POST['submit_register'])) {
    echo $uha->output();
}
?>