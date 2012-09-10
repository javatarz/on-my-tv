<?
require_once("functions.inc.php");

class Style
{
	var $id;
	var $name;
	var $cssname;
	var $thumbname;

	var $adjstop;
	var $adjsbottom;


	private $qry;

	private $valid;

	function __construct($_id = null)
	{
		
		if($_id != null && empty($this->id)) {
			$this->id = $_id;
			$this->fetch();
		}
	}

	

	/*function __set($n, $v) {
		if((isset($this->$n) && $v != '') || !isset($this->$n)) {
			$this->$n = $v;
		}
	}*/

	function fetch() {
		$cache_options = array("key" => "qry_fetchstyleoptions_" . md5($this->id),"timeout" => 0);
		$this->qry = DBConn::getInstance(__CLASS__)->prepare("SELECT sty.sty_name as name, sty.sty_cssname as cssname, sty.sty_thumbname as thumbname, sty.sty_adjstop as adjstop, sty.sty_adjsbot as adjsbottom FROM " . TBL_STYLES . " sty WHERE sty.sty_id = :styleid LIMIT 1");

		$this->qry->bindValue(":styleid",$this->id);
		$this->qry->execute($cache_options);
		
		$this->qry->fetchIntoCached(&$this);
	
		$this->valid = 1;

	}

	function isValid() {
		return ($this->valid > 0)? true : false;
	}
}
?>