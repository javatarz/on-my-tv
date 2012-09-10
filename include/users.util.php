<?

class Users {

	/*
	public static function cleanUsers() {

		if(mt_rand(1,100) == 50) {

			$_qry = DBConn::getInstance(__CLASS__)->prepare("DELETE sel, usr, wat FROM " . TBL_WATCHEDEPS . " wat, " . TBL_USERSELECTIONS . " sel, " . TBL_USERS . " usr WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) >= usr.usr_last_login AND usr.usr_name IS NULL AND usr.usr_password IS NULL AND sel.usr_id = usr.usr_id AND wat.usr_id = usr.usr_id");
			$_qry->bindValue(':userid',$_user_id);
			$_qry->execute();

			$_qry->closeCursor();
		
		}

	}*/

	public static function exists($_email) {
		$cache_options = array("type" => 'userselect',"key" => "qry_user_exists_" . md5($_email),"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_USERS . " usr WHERE usr.usr_name = :username LIMIT 1");
		$_qry->bindValue(':username',$_email);
		$_qry->execute($cache_options);

		return $_qry->fetchColumnCached();

	}

	public static function returnUserWithEmail($_email) {
		$cache_options = array("type" => 'userselect',"key" => "qry_return_user_email_" . md5($_email),"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT usr_uid as uid FROM " . TBL_USERS . " WHERE usr_name = :username LIMIT 1");
		$_qry->bindValue(':username',$_email);
		$_qry->execute($cache_options);
		
		return new User($_qry->fetchColumnCached(),true);
		

	}


}
?>