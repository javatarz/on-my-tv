<?
require_once("config.inc.php");

class Styles {

	public static function getStyles() {
		$cache_options = array("key" => "qry_get_styles","timeout" => 0);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT sty_id as id, sty_name as name, sty_cssname as cssname, sty_thumbname as thumbname, sty_adjstop as adjstop, sty_adjsbot as adjsbottom FROM " . TBL_STYLES . " WHERE sty_enabled = 1 ORDER BY sty_name ASC");
	
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'Style');
		$_qry->execute($cache_options);
		
		return $_qry->fetchAllCached();
		

	}

}
?>
