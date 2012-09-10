<?
require_once("config.inc.php");

class TVShows {

	private static $filter;

	private static $userfilter_selectfields = "usr.sh_id as id, sh.sh_name as name, sh.sh_tvrage_rid as tvragerid, sh.sh_tvdotcom_rid as tvdotcomrid, sh.sh_metadata as metadata, sh.sh_stringid as stringid, sh.sh_added_date as added_date, sh.sh_updated_date as updated_date, sh.sh_airtime as airtime, sh.sh_timezone as timezone, sh.sh_length as length, sh.sh_status as status, sh.sh_network as network, sh.sh_country as country, sh.sh_custom_summary as custom_summary, sh.sh_added_count as added_count, sh.sh_deleted_count as deleted_count, sh.sh_iscancelled as iscancelled";

    private static $getshows_selectfields = "sh.sh_id as id, TRIM(REPLACE(LOWER(sh.sh_name),'the','')) as order_name, sh.sh_name as name, sh.sh_tvrage_rid as tvragerid, sh.sh_tvdotcom_rid as tvdotcomrid, sh.sh_metadata as metadata, sh.sh_stringid as stringid, sh.sh_added_date as added_date, sh.sh_updated_date as updated_date, sh.sh_airtime as airtime, sh.sh_timezone as timezone, sh.sh_length as length, sh.sh_status as status, sh.sh_network as network, sh.sh_country as country, sh.sh_custom_summary as custom_summary, sh.sh_added_count as added_count, sh.sh_deleted_count as deleted_count, sh.sh_iscancelled as iscancelled";
    
	public static function addFilter($_show_id) {
		if(!is_array(TVShows::$filter)) {
			TVShows::$filter = array();
		}

		TVShows::$filter[] = $_show_id;

	}

	public static function clearFilter() {
		TVShows::$filter = null;
	}

	public static function storeFilter($_user_id) {
		//Delete all rows for this user originally to re-add
		$_qry = DBConn::getInstance(__CLASS__)->prepare("DELETE FROM " . TBL_USERSELECTIONS . " WHERE usr_id = :userid");
		$_qry->bindValue(':userid',$_user_id);
        //$_qry->bindValue(':s',implode(',',TVShows::$filter);
		$_qry->execute();

		if(count(TVShows::$filter) != count(TVShows::getShows())) {
		
			$_qry = DBConn::getInstance(__CLASS__)->prepare("INSERT INTO " . TBL_USERSELECTIONS . " (usr_id,sh_id) VALUES (:userid,:shid)");
               
			$_qry->bindValue(':userid',$_user_id);

			foreach(TVShows::$filter as $f) {
				$_qry->bindValue(':shid',$f);
				$_qry->execute();
				if($_qry->rowCount() != 1) {
					echo "Failed to enter row";
					break;
				}
			}
		}
		DBConn::clearCacheItem(MEMCACHE_PREFIX . "qry_userfilter_" . $_user_id);

		TVShows::clearFilter();
	
	}

	
	public static function getUserFilter($_userid) {
		$cache_options = array("key" => "qry_userfilter_" . $_userid,"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " . TVShows::$userfilter_selectfields . " FROM " . TBL_USERSELECTIONS . " usr, " . TBL_SHOWS . " sh WHERE usr.usr_id = :userid AND usr.sh_id = sh.sh_id ORDER BY usr.sh_id ASC");

		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVShow',array());
        
		$_qry->bindValue(':userid',$_userid);

		$_qry->execute($cache_options);

		
		return $_qry->fetchAllCached();

	}

	public static function getShows($_cancelled = false) {
		$cache_options = array("type" => 'showselect',"key" => "qry_getshows","timeout" => 86400);
	
		//$docancelled = ($_cancelled)? '': ' WHERE sh_iscancelled = 0';
		$docancelled = '';
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " . TVShows::$getshows_selectfields . " FROM " . TBL_SHOWS . " sh" . $docancelled . " ORDER BY order_name ASC");	
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVShow',array());

		$_qry->execute($cache_options);
		return $_qry->fetchAllCached();

	}

	public static function getShowsCount() {
		$cache_options = array("type" => 'showselect',"key" => "qry_showcount","timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_SHOWS . "");
		
		$_qry->execute($cache_options);
		return $_qry->fetchColumnCached();

	}

	public static function getAllShows() {
		$cache_options = array("type" => 'showselect',"key" => "qry_getallshows","timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " . TVShows::$getshows_selectfields . " FROM " . TBL_SHOWS . " sh ORDER BY order_name ASC");
			
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVShow',array());

		$_qry->execute($cache_options);
		return $_qry->fetchAllCached();


	}

	public static function getNewShows($_date) {
		$cache_options = array("type" => 'showselect',"key" => "qry_new_shows_" . $_date,"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_SHOWS . " WHERE sh_added_date >= :lastupdated");
				
		$_qry->bindValue(':lastupdated',$_date);	
		$_qry->execute($cache_options);
		return $_qry->fetchColumnCached();


	}

	public static function getShowByName($_name) {
		$cache_options = array("type" => 'showselect',"key" => "qry_get_show_by_name_" . md5($_name),"timeout" => 86400);
	
        
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " . TVShows::$getshows_selectfields . ", MATCH(sr.sh_name) AGAINST ('" . un_clean_name(strtolower($_name)) . "') as matchpercent FROM " .  TBL_SEARCH_SHOWS . " sr, " .  TBL_SHOWS . " sh WHERE ((LCASE(sr.sh_name) = :name OR MATCH(sr.sh_name) AGAINST(:name2 IN NATURAL LANGUAGE MODE) > 6 OR LCASE(sr.sh_name) LIKE LCASE(:name3))) AND sr.sh_id = sh.sh_id ORDER BY matchpercent DESC LIMIT 1");
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVShow',array());

		$_ucn = un_clean_name(strtolower($_name));
		$_qry->bindValue(':name',$_ucn);	
		$_qry->bindValue(':name2',$_ucn);	
		$_qry->bindValue(':name3','%' . $_ucn . '%');	
		$_qry->execute($cache_options);
		return $_qry->fetchCached();


	}
}
?>
