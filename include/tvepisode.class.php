<?
require_once("functions.inc.php");

class TVEpisode
{
	public $id;
	public $show_id;
	public $season;
	public $episode;
	public $date;
	public $name;
	public $summary_url;
	public $tvrage_url;
	public $screen_cap;
	public $episode_summary;
	public $ratetotal;
	public $votes;

	public $timezone;

	//Utilized when show is retrieved with a fixed timezone
	private $ep_tzfix_date;

	private static $_qry = array();

	private $valid;

	//Use this to speed up queries by not retrieving summaries for certain uses
	private $filtersummaries;


	static $fetchfields = array
	(
		'no_summary' => 
			'sh_id as show_id, ep_id as id, ep_tvrage_id as ep_tvrage_id, ep_season as season, ep_date as date, ep_title as name, ep_number as episode, ep_ratetotal as ratetotal, ep_votes as votes', 
		'summary' =>  
			'sh_id as show_id, ep_id as id, ep_tvrage_id as ep_tvrage_id, ep_season as season, ep_date as date, ep_title as name, ep_number as episode, ep_summary_url as summary_url, ep_tvrage_url as tvrage_url,  ep_screen_cap as screen_cap, ep_summary as episode_summary, ep_ratetotal as ratetotal, ep_votes as votes'
	);

	private $errorcode;

	function setup_queries() {
		
		if(!array_key_exists('fetch_summaries',self::$_qry) || !is_a(self::$_qry['fetch_summaries'],'DBStat')) self::$_qry['fetch_summaries'] = DBConn::getInstance(__CLASS__)->prepare("SELECT " . self::$fetchfields['summary'] . " FROM " . TBL_EPISODES . " WHERE ep_id = :episodeid LIMIT 1");
		
		if(!array_key_exists('fetch_nosummaries',self::$_qry) || !is_a(self::$_qry['fetch_nosummaries'],'DBStat')) self::$_qry['fetch_nosummaries'] = DBConn::getInstance(__CLASS__)->prepare("SELECT " . self::$fetchfields['no_summary'] . " FROM " . TBL_EPISODES . " WHERE ep_id = :episodeid LIMIT 1");
		
		if(!array_key_exists('save_existing',self::$_qry) || !is_a(self::$_qry['save_existing'],'DBStat')) self::$_qry['save_existing'] = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_EPISODES . " SET sh_id = :showid, ep_season = :season, ep_date = :date, ep_title = :title, ep_number = :episode, ep_summary_url = :summaryurl, ep_summary = :summary, ep_ratetotal = :ratetotal, ep_votes = :votes WHERE ep_id = :episodeid");
		
		if(!array_key_exists('save_new',self::$_qry) || !is_a(self::$_qry['save_new'],'DBStat')) self::$_qry['save_new'] = DBConn::getInstance(__CLASS__)->prepare("INSERT INTO " . TBL_EPISODES . " SET sh_id = :showid, ep_season = :season, ep_date = :date, ep_title = :title, ep_number = :episode, ep_summary_url = :summaryurl, ep_summary = :summary, ep_ratetotal = :ratetotal, ep_votes = :votes;");
		
		if(!array_key_exists('save_rating',self::$_qry) || !is_a(self::$_qry['save_rating'],'DBStat')) self::$_qry['save_rating'] = DBConn::getInstance(__CLASS__)->prepare("UPDATE " . TBL_EPISODES . " SET ep_votes = ep_votes + 1, ep_ratetotal = ep_ratetotal + :rating WHERE ep_id = :episodeid");

		if(!array_key_exists('check_vote_valid',self::$_qry) || !is_a(self::$_qry['check_vote_valid'],'DBStat')) self::$_qry['check_vote_valid'] = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_VOTED . " WHERE ep_id = :episodeid AND usr_id = :userid LIMIT 1");

		if(!array_key_exists('save_rating_record',self::$_qry) || !is_a(self::$_qry['save_rating_record'],'DBStat')) self::$_qry['save_rating_record'] = DBConn::getInstance(__CLASS__)->prepare("INSERT INTO " . TBL_VOTED . " SET ep_id = :episodeid, usr_id = :userid;");
		
	}
	
	function __construct($_id = null,$_filtersummaries = false,$_timezonename = DEFAULT_TIMEZONE)
	{

	
		$this->setup_queries();

	
		$this->filtersummaries = $_filtersummaries;
		$this->timezone = $_timezonename;

		if($_id != null) {
			$this->id = $_id;
			$this->fetch();
		}
		

		
	}

	function ep_tzfix()
	{
        
	}

	function __get($name) {
        if($name == 'ep_tzfix_date') {
            if($this->id != null) {
                $qd = new DateTime($this->date);
                if($this->timezone != '') {
                    try {
                        $qd->setTimeZone(new DateTimeZone($this->timezone));
                        return $qd->format('Y-m-d H:i:s');
                    } catch(Exception $e) {
                        echo "Invalid timezone name entered";
                        die();
                    }
                }
            }
        } else {
            return $this->$name;
        }
    }

	function __set($n, $v) {
		if((isset($this->$n) && $v != '') || !isset($this->$n)) {
			$this->$n = $v;
		}
	}


	function fetch() {
		if(!is_array(self::$_qry)) {
			$this->setup_queries();
		}

		if($this->filtersummaries) {
			$_qry = self::$_qry['fetch_nosummaries'];
		} else {
			$_qry = self::$_qry['fetch_summaries'];
		}

		$_qry->setFetchMode(PDO::FETCH_INTO,$this);

		$_qry->bindValue(":episodeid",$this->id);
		$_qry->execute();
		
		$_qry->fetch();

		$qd = new DateTime($this->date);
		$qd->setTimeZone(new DateTimeZone($this->timezone));
		$this->ep_tzfix_date = $qd->format('Y-m-d H:i:s');
		
		$this->valid = $_qry->foundRows();



	}

	function isValid() {
		return ($this->valid > 0)? true : false;
	}

	function updateRating($_vote,$_user) {

		if(empty($_user)) {
			return false;
		}

		if($_vote > 5 || $_vote < 1) {
			return false;
		}

		if(!isset($this->id)) {
			return false;
		}

		if(!is_array(self::$_qry)) {
			$this->setup_queries();
		}

		$__vqry = self::$_qry['check_vote_valid'];
		$__vqry->bindValue(":episodeid",$this->id);
		$__vqry->bindValue(":userid",$_user);
		$__vqry->execute();

		if($__vqry->fetchColumn() == 1) {
			return false;
		} else {

			
			$_qry = self::$_qry['save_rating'];
			$_qry->bindValue(":episodeid",$this->id);
			
			$_qry->bindValue(":rating",$_vote);

			$_qry->execute();

			if($_qry->rowCount() == 1) {
				$this->ratetotal += $_vote;
				$this->votes += 1;
				$__rqry = self::$_qry['save_rating_record'];
				$__rqry->bindValue(":episodeid",$this->id);
				$__rqry->bindValue(":userid",$_user);
				$__rqry->execute();
				return true;
			} else {
				return false;
			}
			
		
		}
	}

	function calcRating() {
		if($this->ratetotal > 0 && $this->votes > 0) {
			return $this->ratetotal / $this->votes; 
		} else {
			return 0;
		}
	}

	function updateTVEpisode() {
		if(!is_array(self::$_qry)) {
			$this->setup_queries();
		}
		
		if(isset($this->id)) {
			$_qry = self::$_qry['save_existing'];
			$_qry->bindValue(":episodeid",$this->id);
		} else {
			$_qry = self::$_qry['save_new'];
		}

		$_qry->bindValue(":title",$this->name);
		$_qry->bindValue(":showid",$this->show_id);
		$_qry->bindValue(":summaryurl",$this->summary_url);
		$_qry->bindValue(":season",$this->season);
		$_qry->bindValue(":episode",$this->episode);
		$_qry->bindValue(":date",$this->date);
		$_qry->bindValue(":summary",$this->episode_summary);
		$_qry->bindValue(":ratetotal",(isset($this->ratetotal))? $this->ratetotal : 0);
		$_qry->bindValue(":votes",(isset($this->votes))? $this->votes : 0);
		$_qry->execute();
		
		if($_qry->rowCount() == 1 && !isset($this->id)) {
			$this->id = DBConn::getInstance(__CLASS__)->lastInsertId();
		}

		if($_qry->rowCount() > 0) {
			return true;
		} else {
			return false;
		}

	}



}

?>