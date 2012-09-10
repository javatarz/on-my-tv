<?
require_once('./include/config.inc.php');

$user = User::getInstance();
if($user->isValid()) {
	$user->logout();
	Header('Location: ' . HOMEPAGE_URL);
}
?>