<?
require_once("config.inc.php");

class TVEpisodes {


	private static $filter_period_start;

	private static $filter_period_end;

	private static $filter_timezonename;

	private static $filter_summaries;

	static $episodes;

	 static $latestepisodes;

	 static $_qry;

	public static function setFilterPeriod($_start,$_end) {
		if(is_object($_start) && is_object($_end)) {

			$_start = $_start->format('Y-m-d H:i:s');
			
			$_end = $_end->format('Y-m-d H:i:s');
		}

		TVEpisodes::$filter_period_start = $_start;
		TVEpisodes::$filter_period_end = $_end;
	}

	public static function setFilterTimezone($_tzname) {
		TVEpisodes::$filter_timezonename = $_tzname;
	}

	public static function setFilterSelectSummaries($_sums) {
		TVEpisodes::$filter_summaries = $_sums;
	}

	public static function getShowFilteredEpisodeCount($_userid) {
		$cache_options = array("type" => 'epselect',"key" => "qry_getshowfilteredepisodecount_" . md5($_userid . TVEpisodes::$filter_period_start . TVEpisodes::$filter_period_end),"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_EPISODES . " eps, " . TBL_USERSELECTIONS . " sel WHERE sel.usr_id = :userid AND sel.sh_id = eps.sh_id AND eps.ep_date >= :periodstart AND eps.ep_date <= :periodend");
	

		$_qry->bindValue(":userid",$_userid);
		$_qry->bindValue(":periodstart",TVEpisodes::$filter_period_start);	
		$_qry->bindValue(":periodend",TVEpisodes::$filter_period_end);	

		$_qry->execute($cache_options);

		return $_qry->fetchColumnCached();

	}

	public static function getAllShowsFilteredEpisodeCount() {
		$cache_options = array("type" => 'epselect',"key" => "qry_getallshowsfilteredepisodecount_" . md5(TVEpisodes::$filter_period_start . TVEpisodes::$filter_period_end),"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_EPISODES . " eps, " . TBL_SHOWS . " shw WHERE shw.sh_id = eps.sh_id AND eps.ep_date >= :periodstart AND eps.ep_date <= :periodend");
	
		$_qry->bindValue(":periodstart",TVEpisodes::$filter_period_start);	
		$_qry->bindValue(":periodend",TVEpisodes::$filter_period_end);	

		$_qry->execute($cache_options);

		return $_qry->fetchColumnCached();

	}

	public static function returnFilteredEpisodesForShow($_showid) {
		$o = array();

		foreach(TVEpisodes::$episodes as $ep) {
			if($ep->show_id == $_showid) {
				$o[] = $ep;
			}
		}

		return $o;
	
	}

	public static function getFilteredEpisodesByShows($_shows, $reverse = False) {
		
		if(is_array($_shows) && count($_shows) > 0) {

			$qry = " sh_id IN (";
			$i = 1;
		
			foreach($_shows as $sh) {
				$qry .= $sh->id;
				if($i < count($_shows)) {
					$qry .= ", "; 
				}
				$i++;
			
			}

			$qry .= ") ";
		} else {
			$qry = "1";
		}
		
		if(TVEpisodes::$filter_summaries) {
			
			$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " . TVEpisode::$fetchfields['summary'] . ", nzbt.hash, nzb.subject FROM " . TBL_EPISODES . " LEFT JOIN (nzb.nzb_tv nzbt) ON (sh_id = nzbt.show AND ep_season = nzbt.season AND ep_number = nzbt.episode AND nzbt.format = 'x264') LEFT JOIN (nzb.nzb nzb) ON (nzb.hash = nzbt.hash) WHERE " . $qry  . " AND ep_date >= :periodstart AND ep_date <= :periodend ORDER BY ep_date ASC");
		} else {
	
			$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " . TVEpisode::$fetchfields['no_summary'] . ", nzbt.hash, nzb.subject FROM " . TBL_EPISODES . " LEFT JOIN (nzb.nzb_tv nzbt) ON (sh_id = nzbt.show AND ep_season = nzbt.season AND ep_number = nzbt.episode AND nzbt.format = 'x264') LEFT JOIN (nzb.nzb nzb) ON (nzb.hash = nzbt.hash) WHERE " . $qry  . " AND ep_date >= :periodstart AND ep_date <= :periodend ORDER BY ep_date ASC");
		}

	
        $user = User::getInstance();
        
        $timeout = ($user->s_premium)? 600 : 86400;
            
		$cache_options = array("type" => 'epselect',"key" => "qry_getfilteredepisodes_by_show_" . md5($qry . TVEpisodes::$filter_period_start . TVEpisodes::$filter_period_end . TVEpisodes::$filter_timezonename. TVEpisodes::$filter_summaries), "timeout" => $timeout);

		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVEpisode',array(null,TVEpisodes::$filter_summaries,TVEpisodes::$filter_timezonename));

		$_qry->bindValue(":periodstart",TVEpisodes::$filter_period_start);	
		$_qry->bindValue(":periodend",TVEpisodes::$filter_period_end);	

		$_qry->execute($cache_options);
        
        TVEpisodes::$episodes =  ($reverse)? array_reverse($_qry->fetchAllCached()) : $_qry->fetchAllCached();


	}

	public static function getFilteredNextEpisodesByShows($_shows) {
		if(is_array($_shows) && count($_shows) > 0) {

			$qry = " sh_id IN (";
			$i = 1;
		
			foreach($_shows as $sh) {
				$qry .= $sh->id;
				if($i < count($_shows)) {
					$qry .= ", "; 
				}
				$i++;
			
			}

			$qry .= ") ";
		} else {
			$qry = "1";
		}
		
		
		if(TVEpisodes::$filter_summaries) {
			
			$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT DISTINCT(sh_id) as show_id, ep_season as season, ep_date as date, ep_title as name, ep_number as episode, ep_summary_url as summary_url, ep_tvrage_url as tvrage_url, ep_screen_cap as screen_cap, ep_summary as episode_summary, ep_id as id  FROM " . TBL_EPISODES . " WHERE " . $qry  . " AND ep_date >= :periodstart AND ep_date <= :periodend GROUP BY sh_id ORDER BY ep_date ASC");
		} else {
			$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT DISTINCT(sh_id) as show_id, ep_season as season, ep_date as date, ep_title as name, ep_number as episode, ep_id as id FROM " . TBL_EPISODES . " WHERE " . $qry  . " AND ep_date >= :periodstart AND ep_date <= :periodend GROUP BY sh_id ORDER BY ep_date ASC");
		}

		$cache_options = array("type" => 'epselect', "key" => "qry_getfilterednextepisodes_by_show_" . md5($qry . TVEpisodes::$filter_period_start . TVEpisodes::$filter_period_end . TVEpisodes::$filter_timezonename . TVEpisodes::$filter_summaries), "timeout" => 86400);
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVEpisode',array(null,TVEpisodes::$filter_summaries,TVEpisodes::$filter_timezonename));

		$_qry->bindValue(":periodstart",TVEpisodes::$filter_period_start);	
		$_qry->bindValue(":periodend",TVEpisodes::$filter_period_end);	

		$_qry->execute($cache_options);

		TVEpisodes::$episodes = $_qry->fetchAllCached();


	}




	public static function getNextEpisode($_show_id,$_episode,$_season,$_date) {

		$cache_options = array("type" => 'epselect',"key" => "qry_getnextepisode_" . md5($_show_id . "/" . $_episode . "/" . $_season . "/" . $_date . "/" . TVEpisodes::$filter_summaries),"timeout" => 86400);

		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT  " . TVEpisode::$fetchfields['no_summary']. " FROM " . TBL_EPISODES . " WHERE sh_id = :showid AND ep_date >= :periodstart AND (ep_season > :season OR ep_number > :episode) ORDER BY ep_date ASC LIMIT 1");
	
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVEpisode',array(null,TVEpisodes::$filter_summaries));

		$_qry->bindValue(":showid",$_show_id);
		$_qry->bindValue(":periodstart",$_date);	
		$_qry->bindValue(":season",$_season);	
		$_qry->bindValue(":episode",$_episode);
		$_qry->execute($cache_options);
		if($_qry->foundRows() > 0) {
			$f = $_qry->fetchCached();
			$_qry->closeCursor();
			return $f;
		} else {
			return false;
		}

	}

	public static function getEpisodeByID($_epid) {

		$cache_options = array("type" => 'epselect',"key" => "qry_getepisodebyid_" . $_epid . "_" . md5(DEFAULT_TIMEZONE . TVEpisodes::$filter_timezonename . TVEpisodes::$filter_summaries),"timeout" => 86400);

		if(isset(TVEpisodes::$filter_timezonename)) {
			$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " .  TVEpisode::$fetchfields['summary'] . ", CONVERT_TZ(ep_date,'" . DEFAULT_TIMEZONE . "','" . TVEpisodes::$filter_timezonename . "') as ep_tzfix_date FROM " . TBL_EPISODES . " WHERE ep_id = :epid ORDER BY ep_date DESC LIMIT 1");
		} else {
			$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT " .  TVEpisode::$fetchfields['summary'] . " FROM " . TBL_EPISODES . " WHERE ep_id = :epid ORDER BY ep_date DESC LIMIT 1");
		}
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVEpisode',array(null,TVEpisodes::$filter_summaries,TVEpisodes::$filter_timezonename));

		$_qry->bindValue(":epid",$_epid);	

		$_qry->execute($cache_options);
		if($_qry->foundRows() > 0) {
			return $_qry->fetchCached();
		} else {
			return new TVEpisode();
		}
		
	}

	public static function getEpisode($_show_id,$_season,$_episode) {
		if(isset(TVEpisodes::$filter_timezonename)) {

			if(!is_a(self::$_qry['fetchepisode_filtered'],'DBStat')) self::$_qry['fetchepisode_filtered'] = DBConn::getInstance(__CLASS__)->prepare("SELECT ep_id as id, CONVERT_TZ(ep_date,'" . DEFAULT_TIMEZONE . "','" . TVEpisodes::$filter_timezonename . "') as ep_tzfix_date FROM " . TBL_EPISODES . " WHERE ep_season = :season AND ep_number = :episode AND sh_id = :showid ORDER BY ep_date DESC LIMIT 1");
	
			$_qry = self::$_qry['fetchepisode_filtered'];

		} else {

			if(!is_a(self::$_qry['fetchepisode_unfiltered'],'DBStat')) self::$_qry['fetchepisode_unfiltered'] = DBConn::getInstance(__CLASS__)->prepare("SELECT ep_id as id FROM " . TBL_EPISODES . " WHERE ep_season = :season AND ep_number = :episode AND sh_id = :showid ORDER BY ep_date DESC LIMIT 1");

			$_qry = self::$_qry['fetchepisode_unfiltered'];

		}

		$cache_options = array("type" => 'epselect',"key" => "qry_getepisode_" .  md5($_show_id . "_" . $_season . "_" . $_episode . DEFAULT_TIMEZONE . TVEpisodes::$filter_timezonename . TVEpisodes::$filter_summaries),"timeout" => 86400);

		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVEpisode',array(null,TVEpisodes::$filter_summaries,TVEpisodes::$filter_timezonename));

		$_qry->bindValue(":season",$_season);	
		$_qry->bindValue(":episode",$_episode);	
		$_qry->bindValue(":showid",$_show_id);	

		$_qry->execute($cache_options);

		if($_qry->foundRows() > 0) {
			return $_qry->fetchCached();
		} else {
			return new TVEpisode();
		}
	}

	public static function returnLatestEpisodeForShow($_showid) {

		foreach(TVEpisodes::$latestepisodes as $ep) {
			if($ep->show_id == $_showid) {
				return $ep;
			}
		}
	
	}

	public static function getLatestEpisodes($_shows) {

		$qry = " (";
		$i = 1;
	
		foreach($_shows as $sh) {
			$qry .= " ep.sh_id = " . $sh->id;
			if($i < count($_shows)) {
				$qry .= " OR"; 
			}
			$i++;
		
		}

		$qry .= ") ";

//SELECT sh.sh_id as show_id, ep.ep_id as id, ep.ep_date as date FROM shows sh, episodes ep WHERE sh.sh_id = ep.show_id AND ep.ep_date = (SELECT ep_date FROM episodes WHERE show_id = sh.sh_id ORDER BY ep_date DESC LIMIT 1) AND (ep.show_id = 108 OR ep.show_id = 110) GROUP BY sh.sh_id;

		$cache_options = array("type" => 'epselect',"key" => "qry_getlatestepisodes_" .  md5($qry),"timeout" => 3600);

		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT sh.sh_id as show_id, ep.ep_id as id, ep.ep_date as date, ep.ep_season as season, ep.ep_number as episode FROM " . TBL_SHOWS . " sh, " . TBL_EPISODES . " ep WHERE sh.sh_id = ep.sh_id AND ep.ep_date = (SELECT ep_date FROM " . TBL_EPISODES . " WHERE sh_id = sh.sh_id ORDER BY ep_date DESC LIMIT 1) AND " . $qry . " GROUP BY sh.sh_id;");
	
		$_qry->setFetchMode(PDO::FETCH_CLASS, 'TVEpisode',array(null,true));

		$_qry->execute($cache_options);

		TVEpisodes::$latestepisodes = $_qry->fetchAllCached();
		

	}

	public static function setWatched($_show_id,$_season,$_episode,$_user) {

		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT COUNT(*) FROM " . TBL_WATCHEDEPS . " WHERE sh_id = :showid AND usr_id = :userid AND ep_season = :season AND ep_episode = :episode LIMIT 1");
		$_qry->bindValue(":showid",$_show_id);	
		$_qry->bindValue(":userid",$_user);	
		$_qry->bindValue(":season",$_season);	
		$_qry->bindValue(":episode",$_episode);	
		$_qry->execute();
		
		if($_qry->fetchColumn() < 1) {
	
			$_qry = DBConn::getInstance(__CLASS__)->prepare("INSERT INTO " . TBL_WATCHEDEPS . " (sh_id,usr_id,ep_season,ep_episode,ts) VALUES (:showid,:userid,:season,:episode,NOW())");

			$_qry->bindValue(":showid",$_show_id);	
			$_qry->bindValue(":userid",$_user);	
			$_qry->bindValue(":season",$_season);	
			$_qry->bindValue(":episode",$_episode);	
            
            try {
                $_qry->execute();
			} catch(PDOException $e) {
                // Probably means that show or user no longer exists, just return false after invalidating cache
                DBConn::clearCacheGroup('epselect');
                DBConn::clearCacheGroup('showselect');
                
                return false;
            }
            
			DBConn::clearCacheItem(MEMCACHE_PREFIX . "qry_getwatched_" . $_user);

			if($_qry->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function setUnWatched($_show_id,$_season,$_episode,$_user) {
		
		$_qry = DBConn::getInstance(__CLASS__)->prepare("DELETE FROM " . TBL_WATCHEDEPS . " WHERE sh_id = :showid AND usr_id = :userid AND ep_season = :season AND ep_episode = :episode");

		$_qry->bindValue(":showid",$_show_id);	
		$_qry->bindValue(":userid",$_user);	
		$_qry->bindValue(":season",$_season);	
		$_qry->bindValue(":episode",$_episode);	

		try {
            $_qry->execute();
        } catch(PDOException $e) {
            // Probably means that show or user no longer exists, just return false after invalidating cache
            DBConn::clearCacheGroup('epselect');
            DBConn::clearCacheGroup('showselect');
            
            return false;
        }

		DBConn::clearCacheItem(MEMCACHE_PREFIX . "qry_getwatched_" . $_user);

		if($_qry->rowCount() > 0) {
			return true;
		} else {
			return false;
		}
		
	}

	public static function getWatched($_user) {
        
		$cache_options = array("key" => "qry_getwatched_" . $_user,"timeout" => 86400);
		$_qry = DBConn::getInstance(__CLASS__)->prepare("SELECT CONCAT_WS('-',sh_id,ep_season,ep_episode) as t FROM " . TBL_WATCHEDEPS . " WHERE usr_id = :userid");
		$_qry->setFetchMode(PDO::FETCH_COLUMN,0);
		$_qry->bindValue(":userid",$_user);	

		$_qry->execute($cache_options);

        $r = $_qry->fetchAllCached();
        
		if($_qry->foundRows() > 0) {
			return $r;
		} else {
			return false;
		}

	}

	public static function killWatched() {
		$_qry = DBConn::getInstance(__CLASS__)->prepare("DELETE FROM " . TBL_WATCHEDEPS . " WHERE ts < (NOW() - INTERVAL 2 MONTH)");
		$_qry->execute();
		
		
	}


	public static function killRandoms($_parsedepids) {
		if(is_array($_parsedepids)) {
			$dshows = array();

			foreach($_parsedepids as $eid) {
				if(!empty($eid)) {
					$dshows[] = $eid;
				}
			}
			$deleteshows = implode(",",$dshows);
			

			if(count($_parsedepids) > 0) {

				$_qry = DBConn::getInstance(__CLASS__)->prepare("DELETE FROM " . TBL_EPISODES . " WHERE ep_id NOT IN (" . $deleteshows .")");
				$_qry->execute();
				return $_qry->rowCount();

			} else {
				return 0;
			}

		} else {
			return 0;
		}



	}

	/*public static function killRandoms($_show_id,$parsedeps) {
		$sql = '';
		for($i = 0; $i < count($parsedeps); $i++) {
			$sql .= ($i > 0)? ' AND	' : '';
			$sql .= " (ep_season != :epseas_" . $i . " OR ep_number != :epno_" . $i . ")";

		}


		$_qry = DBConn::getInstance(__CLASS__)->prepare("DELETE FROM " . TBL_EPISODES . " WHERE show_id = :showid AND (" . $sql . ")");
		for($i = 0; $i < count($parsedeps); $i++) {
			$_qry->bindValue(":epseas_" . $i,$parsedeps[$i]['s']);	
			$_qry->bindValue(":epno_" . $i,$parsedeps[$i]['e']);	
		}

		$_qry->bindValue(":showid",$_show_id);	

		$_qry->execute();
		if($_qry->rowCount() > 0) {
			return true;
		} else {
			return false;
		}
	}*/


}
?>