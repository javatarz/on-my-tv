<?

class User {

	var $id;
	var $uid;
	var $name;
	var $password;
	var $style;
	var $last_login;
	var $timezone;
	var $ip_address;
	var $last_filter_update;

	var $s_daily_numbers;
	var $s_sunday_first;
	var $s_daily_airtimes;
	var $s_daily_networks;
	var $s_daily_epnames;
	var $s_popups;
	var $s_wunwatched;
	var $s_disableads;
	var $s_sortbyname;
    var $s_24hour;
    var $s_premium;
    
	var $valid;

	private $qry;

	private $unique_cnt;

	private static $instance;


	function __construct($_uid = null,$_skipcookie = false) {
		
		if(isset($_COOKIE[SITE_ID . '_UID']) && $_skipcookie == false) {
			$this->uid = $_COOKIE[SITE_ID . '_UID'];
            DBConn::$uid = $this->uid;
			$this->fetch();

			if($this->isValid()) {
				$this->UpdateTime();
			} else {
				$this->s_daily_numbers = 1;
				$this->s_sunday_first = 0;
				$this->s_daily_airtimes = 0;
				$this->s_daily_networks = 0;
				$this->s_daily_epnames = 0;
				$this->s_popups = 1;
				$this->s_wunwatched = 1;
				$this->s_disableads = 0;
				$this->s_sortbyname = 0;
                $this->s_24hour = 1;
                $this->s_premium = 0;
                $this->style = DEFAULT_STYLE;
                $this->timezone = DEFAULT_TIMEZONE;
			}

		} elseif($_uid != null) {
			$this->uid = $_uid;
            DBConn::$uid = $this->uid;
			$this->fetch();

			if($this->isValid()) {
				$this->UpdateTime();
			} else {
				$this->s_daily_numbers = 1;
				$this->s_sunday_first = 0;
				$this->s_daily_airtimes = 0;
				$this->s_daily_networks = 0;
				$this->s_daily_epnames = 0;
				$this->s_popups = 1;
				$this->s_wunwatched = 1;
				$this->s_disableads = 0;
				$this->s_sortbyname = 0;
                $this->s_24hour = 1;
                $this->s_premium = 0;
                $this->style = DEFAULT_STYLE;
                $this->timezone = DEFAULT_TIMEZONE;
			}

		} else {
			$this->s_daily_numbers = 1;
			$this->s_sunday_first = 0;
			$this->s_daily_airtimes = 0;
			$this->s_daily_networks = 0;
			$this->s_daily_epnames = 0;
			$this->s_popups = 1;
			$this->s_wunwatched = 1;
			$this->s_disableads = 0;
			$this->s_sortbyname = 0;
			$this->s_24hour = 1;
            $this->s_premium = 0;
            $this->style = DEFAULT_STYLE;
            $this->timezone = DEFAULT_TIMEZONE;
            
			$this->valid = 0;
		}

		

		$this->unique_cnt = 0;

	}

	public static function getInstance() {
		if(!isset(User::$instance)) {
			User::$instance = new User();
		}

		return User::$instance;

	}

	function unique_id() {
		$this->uid = str_shuffle(md5($_SERVER['REMOTE_ADDR'] . time() . md5(SITE_ID)));
		$this->qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_USERS . " WHERE usr_id = :userid LIMIT 1");

		$this->qry->bindValue(":userid",$this->uid);
		$this->qry->execute();

		$_cnt = $this->qry->fetchColumn();
		
		if($_cnt > 0 && $this->unique_cnt < 10) {
			$this->unique_cnt++;
			return $this->unique_id();
		} elseif($_cnt > 0) {
			return false;
		} else {
			return true;
		}
	}

	function login($_user,$_pass) {
		$cache_options = array("type" => 'userselect',"key" => "qry_can_login_" . md5($_user . "_" . $_pass),"timeout" => 3600);
		$this->qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_USERS . " WHERE usr_name = :user AND usr_password = MD5(:pass) LIMIT 1");

		$this->qry->bindValue(":user",$_user);
		$this->qry->bindValue(":pass",$_pass);
		$this->qry->execute($cache_options);
		
		if($this->qry->fetchColumnCached() > 0) {
			$this->qry->closeCursor();
			$cache_options = array("type" => 'userselect',"key" => "qry_login_uid" . md5($_user . "_" . $_pass),"timeout" => 3600);
			$this->qry = DBConn::getInstance(__CLASS__)->prepare("SELECT usr_uid as uid FROM " . TBL_USERS . " WHERE usr_name = :user AND usr_password = MD5(:pass) LIMIT 1");


			$this->qry->bindValue(":user",$_user);
			$this->qry->bindValue(":pass",$_pass);
			$this->qry->execute($cache_options);
			$this->uid = $this->qry->fetchColumnCached();
			$this->qry->closeCursor();
			setCookie(SITE_ID . '_UID',$this->uid,time()+((3600*8760)*15),'/','.' . COOKIE_DOMAIN);
			return true;
		} else {
			return false;
		}

	}

	function logout() {
		setCookie(SITE_ID . '_UID',$this->uid,time()-3600,'/','.' . COOKIE_DOMAIN);
	}

	function newUser() {
		if($this->unique_id()) {
			$this->qry = DBConn::getInstance(__CLASS__)->prepare("INSERT INTO " . TBL_USERS . " (usr_uid,usr_name,usr_style,s_daily_numbers,s_sunday_first,s_daily_airtimes,s_daily_networks, s_daily_epnames, s_popups,s_wunwatched,s_disableads,s_sortbyname,s_24hour,usr_last_login,usr_timezone,usr_ip_address,usr_last_filter_update) VALUES (:userid,:name,:style,:s_numbers,:s_sundayfirst,:s_airtimes,:s_networks,:s_epnames,:s_popups,:s_wunwatched,:s_disableads,:s_sortbyname,:s_24hour,NOW(),:timezone,:ipaddress,NOW())");

			$this->qry->bindValue(":userid",$this->uid);
			$this->qry->bindValue(":name",$this->name);
			$this->qry->bindValue(":style",$this->style);
			$this->qry->bindValue(":s_numbers",$this->s_daily_numbers);
			$this->qry->bindValue(":s_sundayfirst",$this->s_sunday_first);
			$this->qry->bindValue(":s_airtimes",$this->s_daily_airtimes);
			$this->qry->bindValue(":s_networks",$this->s_daily_networks);
			$this->qry->bindValue(":s_epnames",$this->s_daily_epnames);
			$this->qry->bindValue(":s_popups",$this->s_popups);
			$this->qry->bindValue(":s_wunwatched",$this->s_wunwatched);
			$this->qry->bindValue(":s_disableads",$this->s_disableads);
			$this->qry->bindValue(":s_sortbyname",$this->s_sortbyname);
            $this->qry->bindValue(":s_24hour",$this->s_24hour);
			$this->qry->bindValue(":timezone",$this->timezone);
			$this->qry->bindValue(":ipaddress",$_SERVER['REMOTE_ADDR']);
			$this->qry->execute();
		
			$this->valid = $this->qry->rowCount();
            $this->id = DBConn::getInstance()->lastInsertId();
            
			DBConn::clearCacheGroup('userselect');
			setCookie(SITE_ID . '_UID',$this->uid,time()+((3600*8760)*15),'/','.' . COOKIE_DOMAIN);
		}


	}

	function fetch() {
		$cache_options = array("type" => 'userselect',"key" => "qry_fetch_user_data_" . md5($this->uid),"timeout" => 3600);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT usr.usr_id as id, usr.usr_name as name, usr.usr_password as password, usr.usr_style as style, usr.s_daily_numbers as s_daily_numbers, usr.s_sunday_first as s_sunday_first, usr.s_daily_airtimes as s_daily_airtimes, usr.s_daily_networks as s_daily_networks, usr.s_daily_epnames as s_daily_epnames, usr.s_popups as s_popups, usr.s_wunwatched as s_wunwatched, usr.s_disableads as s_disableads, usr.s_sortbyname as s_sortbyname, usr.s_24hour as s_24hour, usr.s_premium as s_premium, usr.usr_last_login as last_login, usr.usr_timezone as timezone, usr.usr_ip_address as ip_address, usr.usr_last_filter_update as last_filter_update FROM " . TBL_USERS . " usr WHERE usr_uid = :userid LIMIT 1");

		

		$_qry->bindValue(":userid",$this->uid);
		$_qry->execute($cache_options);
		
		$_qry->fetchIntoCached(&$this);
		
        if(!is_null($this->id)) {
            $this->valid = 1;
        }
        
		$_qry->closeCursor();

	}

	function isValid() {
		return ($this->valid > 0 && !is_null($this->id))? true : false;
	}

	function UpdatePassword() {
		DBConn::clearCacheGroup('userselect');
		$_qry = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_USERS . " SET usr_password = md5(:password) WHERE usr_id = :userid");	
	
		$_qry->bindValue(":userid",$this->id);
		$_qry->bindValue(":password",$this->password);
		$_qry->execute();

		return $_qry->rowCount() > 0;

	}

	function UpdateTime() {
      
		if(strtotime($this->last_login) < (time() - 600)) {
			DBConn::clearCacheGroup('userselect');
      
			$_qry = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_USERS . " SET usr_last_login = NOW() WHERE usr_id = :userid");	
			$_qry->bindValue(":userid",$this->id);
			$_qry->execute();
			return $_qry->rowCount() > 0;
		} else {
			return 0;
		}
	}

	function Update($_do_filter_update = false) {
		
		if($_do_filter_update) {
			$_qry = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_USERS . " SET usr_name = :name, usr_style = :style, s_daily_numbers = :s_numbers, s_sunday_first = :s_sundayfirst, s_daily_airtimes = :s_airtimes, s_daily_networks = :s_networks, s_daily_epnames = :s_epnames, s_popups = :s_popups, s_wunwatched = :s_wunwatched, s_disableads = :s_disableads, s_sortbyname = :s_sortbyname, s_24hour = :s_24hour, usr_last_login = NOW(), usr_timezone = :timezone, usr_ip_address = :ipaddress, usr_last_filter_update = NOW() WHERE usr_id = :userid");	
		} else {
			$_qry = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_USERS . " SET usr_name = :name, usr_style = :style, s_daily_numbers = :s_numbers, s_sunday_first = :s_sundayfirst, s_daily_airtimes = :s_airtimes, s_daily_networks = :s_networks, s_daily_epnames = :s_epnames, s_popups = :s_popups, s_wunwatched = :s_wunwatched, s_disableads = :s_disableads, s_sortbyname = :s_sortbyname, s_24hour = :s_24hour, usr_last_login = NOW(), usr_timezone = :timezone, usr_ip_address = :ipaddress WHERE usr_id = :userid");	
		}	

		$_qry->bindValue(":userid",$this->id);
		$_qry->bindValue(":name",$this->name);
		$_qry->bindValue(":style",$this->style);
		$_qry->bindValue(":s_numbers",$this->s_daily_numbers);
		$_qry->bindValue(":s_sundayfirst",$this->s_sunday_first);
		$_qry->bindValue(":s_airtimes",$this->s_daily_airtimes);
		$_qry->bindValue(":s_networks",$this->s_daily_networks);
		$_qry->bindValue(":s_epnames",$this->s_daily_epnames);
		$_qry->bindValue(":s_popups",$this->s_popups);
		$_qry->bindValue(":s_wunwatched",$this->s_wunwatched);
		$_qry->bindValue(":s_disableads",$this->s_disableads);
		$_qry->bindValue(":s_sortbyname",$this->s_sortbyname);
        $_qry->bindValue(":s_24hour",$this->s_24hour);
		$_qry->bindValue(":timezone",$this->timezone);
		$_qry->bindValue(":ipaddress",$this->ip_address);
		$_qry->execute();
		DBConn::clearCacheGroup('userselect');
		return $_qry->rowCount() > 0;
		
	}



}



?>