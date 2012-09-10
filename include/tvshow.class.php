<?
require_once("functions.inc.php");

class TVShow
{
	var $id;
	var $name;
	var $stringid;
	var $tvragerid;
	var $tvdotcomrid;
	var $tvrageid = '';
	var $metadata = '';
	var $_tmetadata = array();
	var $added_date;
	var $updated_date;
	var $airtime;
	var $timezone;
	var $length;
	var $origname;
	var $status;
	var $network;
	var $country;
	var $custom_summary;
	var $added_count;
	var $deleted_count;
	var $iscancelled;
	var $matchpercent = 0;

	var $una = null;

	private $valid;

	var $errorcode;



	
	function __construct($_id = null)
	{
		if($_id != null) {
			
			$this->id = $_id;
			$this->fetch();
		} elseif($this->id != null) {
			
			//$this->fetchTVShow();
		}
		
	}

	function __wakeup()
	{
	}

	function __sleep()
	{
		unset($this->dbObject);
	
		$vars = (array)$this;
		foreach ($vars as $key => $val)
		{
			if (is_null($val))
			{
				unset($vars[$key]);
			}
		}   
		return array_keys($vars);
	}


	  public function &__get($name){
		$this->_tmetadata = @json_decode($this->metadata,True);
		
		if($name == 'metadata') {
			return $this->_tmetadata;
		} elseif (isset($this->_tmetadata[$name])){
			return $this->_tmetadata[$name];
		} else {
			return $this->$name;
		}
	  }


	function fetch() {
		$cache_options = array("key" => "qry_fetch_tvshow_options" . md5($this->id),"timeout" => 0);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT sh_id as id, sh_name as name, sh_stringid as stringid, sh_metadata as metadata, sh_tvrage_rid as tvrage_rid, sh_tvdotcom_rid as tvdotcom_rid, sh_added_date as added_date, sh_updated_date as updated_date, sh_airtime as airtime, sh_timezone as timezone, sh_length as length, sh_status as status, sh_network as network, sh_country as country, sh_custom_summary as custom_summary, sh_added_count as added_count, sh_deleted_count as deleted_count, sh_iscancelled as iscancelled FROM " . TBL_SHOWS . " WHERE sh_id = :showid LIMIT 1");


		$_qry->bindValue(":showid",$this->id);
		$_qry->execute($cache_options);
		
		$_qry->fetchIntoCached(&$this);
	
		$this->valid = $_qry->foundRows();
		//$this->metadata = @unserialize($this->metadata);

	}

	function isValid() {
		return ($this->valid > 0)? true : false;
	}

	function newTVShow() {
		$_qry = DBConn::getInstance(__CLASS__)->prepare("INSERT INTO " . TBL_SHOWS . " SET sh_name = :name, sh_metadata = :metadata, sh_stringid = :stringid, sh_tvrage_rid = :tvragerid, sh_tvdotcom_rid = :tvdotcomrid, sh_added_date = NOW(), sh_updated_date = NOW(), sh_airtime = :airtime, sh_length = :length, sh_status = :status, sh_network = :network, sh_country = :country, sh_custom_summary = :customsummary, sh_added_count = :addedcount, sh_deleted_count = :deletedcount, sh_iscancelled = :iscancelled");

		$_qry->bindValue(":name",$this->name);
		$_qry->bindValue(":stringid",$this->stringid);
		$_qry->bindValue(":metadata",$this->metadata);
		$_qry->bindValue(":tvragerid",$this->tvragerid);
		$_qry->bindValue(":tvdotcomrid",$this->tvdotcomrid);
		$_qry->bindValue(":summaryurl",$this->summary_url);
		$_qry->bindValue(":airtime",$this->airtime);
		$_qry->bindValue(":length",$this->length);
		$_qry->bindValue(":status",$this->status);
		$_qry->bindValue(":network",$this->network);
		$_qry->bindValue(":country",$this->country);
		$_qry->bindValue(":customsummary",$this->custom_summary);
		$_qry->bindValue(":addedcount",$this->added_count);
		$_qry->bindValue(":deletedcount",$this->deleted_count);
		$_qry->bindValue(":iscancelled",$this->iscancelled);
		//$_qry->bindValue(":showid",$this->id);


		$_qry->execute();
		
		if($_qry->rowCount() > 0) {
			return true;
		} else {
			return false;
		}


	}

	function updateTVShow() {
		DBConn::clearCacheItem("qry_fetch_tvshow_options" . md5($this->id));
		$_qry = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_SHOWS . " SET sh_name = :name, sh_stringid = :stringid, sh_metadata = :metadata, sh_tvrage_rid = :tvragerid, sh_tvdotcom_rid = :tvdotcomrid, sh_added_date = :addeddate, sh_updated_date = NOW(), sh_airtime = :airtime, sh_length = :length, sh_status = :status, sh_network = :network, sh_country = :country, sh_custom_summary = :customsummary, sh_added_count = :addedcount, sh_deleted_count = :deletedcount, sh_iscancelled = :iscancelled WHERE sh_id = :showid");
		
		$_qry->bindValue(":name",$this->name);
		$_qry->bindValue(":stringid",$this->stringid);
		$_qry->bindValue(":metadata",$this->metadata);
		$_qry->bindValue(":tvragerid",$this->tvragerid);
		$_qry->bindValue(":tvdotcomrid",$this->tvdotcomrid);
		$_qry->bindValue(":summaryurl",$this->summary_url);
		$_qry->bindValue(":addeddate",$this->added_date);
		$_qry->bindValue(":episodeguide_url",$this->episodeguide_url);
		$_qry->bindValue(":airtime",$this->airtime);
		$_qry->bindValue(":length",$this->length);
		$_qry->bindValue(":status",$this->status);
		$_qry->bindValue(":network",$this->network);
		$_qry->bindValue(":country",$this->country);
		$_qry->bindValue(":customsummary",$this->custom_summary);
		$_qry->bindValue(":addedcount",$this->added_count);
		$_qry->bindValue(":deletedcount",$this->deleted_count);
		$_qry->bindValue(":iscancelled",$this->iscancelled);
		$_qry->bindValue(":showid",$this->id);

		
		$_qry->execute();
		
		if($_qry->rowCount() > 0) {
			return true;
		} else {
			return false;
		}

	}



}

?>