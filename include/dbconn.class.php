<?
require_once("config.inc.php");
require_once("error.class.php");

class DBConn extends PDO
{

	static $instance;
	static $queries;
	static $executed = 0;
	static $cached = 0;
	static $memcached;
    static $uid = 'std';
    
	static $cachegroups = array();
    static $debug = True;
	static $cshit = array();
	static $noncshit = array();
	function __construct($_usememcache)
	{
		try {
			self::$queries = array();
            
			parent::__construct(DB_DSN . ':dbname=' . DB_NAME . ';host=' . DB_SERVER,DB_USER,DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;"));
            
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DBStat', array($this)));
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
			$this->setAttribute(PDO::ATTR_PERSISTENT,true);
			$this->setAttribute(PDO::ATTR_TIMEOUT,60);
			//Run in emulate mode until all these fucking bugs get fixed - and it seems to be faster, wtfbbq
			$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            
			
			//Open connection to memcached
			if(class_exists('Memcache') && $_usememcache) {
				self::$memcached = new Memcache();
				if(!self::$memcached->pconnect(MEMCACHE_HOST,MEMCACHE_PORT)) {
					//echo "<!-- MemcacheD Connection Error - NOT using Memory Cached Queries -->\n";
				} else {
					self::$cachegroups = self::$memcached->get(MEMCACHE_PREFIX . 'cache_groups');
					if(self::$cachegroups === false) {
						self::$cachegroups = array();
					}
					
					//echo "<!-- MemcacheD Connection OK - Using Memory Cached Queries -->\n";
				}
			}			

            $this->query('SET CHARACTER SET utf8;');
            $this->query('SET character_set_connection=utf8;');
		} catch (PDOException $e) {
			die("Connection Error - database server is down.");
		}

		
	}

	function has_memcache() {
		return (is_a(self::$memcached,'Memcache'))? true : false;
	}

	static function clearCacheItem($key) {
		if(self::has_memcache()) {
			return self::$memcached->delete($key,0);
		} else {
			return false;
		}
	}

	static function clearCacheGroup($group) {
		if(self::has_memcache()) {
			if(array_key_exists($group,self::$cachegroups)) {
                if(array_key_exists(self::$uid,self::$cachegroups[$group])) {
                    foreach(self::$cachegroups[$group][self::$uid] as $k => $v) {
                        self::clearCacheItem($v);
                    }
                    unset(self::$cachegroups[$group][self::$uid]);
                }
			}
		}
	}

	static function getInstance($_cname = null,$_usememcache = true) {
		
		if (!self::$instance) {
			self::$instance = &new DBConn($_usememcache);

		}
		return self::$instance;
		
		
	}

	static function closeInstances() {

		foreach(self::$instance as $i) {
			$i = null;
		}

	}
	
	function prepare($statement,$options = array()) {
		$chk = $statement . implode("",$options);
		$chk = md5($chk);
		if(!isset(self::$queries[$chk])) {
			$q = parent::prepare($statement,$options);
			self::$queries[$chk]['cnt'] = 0;
			self::$queries[$chk]['stat'] = $q;

		}
		self::$queries[$chk]['cnt']++;
		return self::$queries[$chk]['stat'];

	}

	static function queryCount() {
		return self::$executed;
	}

	static function cachedCount() {
		return self::$cached;
	}

}

class DBStat extends PDOStatement
{
	private $dbh;

	private $rows;

	private $has_cached_output = false;

	private $data = array();

	const QUERY_FETCHALL = 42;
	const QUERY_FETCHCOLUMN = 43;
	const QUERY_FETCH = 44;
	const QUERY_FETCHINTO = 45;

	protected function __construct($dbh)
	{
		$this->dbh = $dbh;
	
		$this->setFetchMode(PDO::FETCH_OBJ);
	}

	public function returnSQL() {
		$os = $this->queryString;
		foreach($this->data as $key => $value) {
			$value = $this->dbh->quote($value);
			$os = str_replace($key, $value, $os);

		}
		if(substr($os, -1) != ";") {
			$os .= ";";
		}
		return $os;
	}

	public function fetchAllCached($fetch_style = null,$column_index = null, $ctor_args = null) {

		return $this->cacheQuery(self::QUERY_FETCHALL,array("fetch_style" => $fetch_style, "column_index" => $column_index, "ctor_args" => $ctor_args));

	}

	public function fetchColumnCached($column_index = null) {

		return $this->cacheQuery(self::QUERY_FETCHCOLUMN,array("column_index" => $column_index));

	}

	public function fetchIntoCached($instance = null) {

		return $this->cacheQuery(self::QUERY_FETCHINTO,array("instance" => $instance));

	}

	public function fetchCached($fetch_style = null,$cursor_orientation = null, $cursor_offset = null) {

		return $this->cacheQuery(self::QUERY_FETCH,array("fetch_style" => $fetch_style, "cursor_orientation" => $cursor_orientation, "cursor_offset" => $cursor_offset));

	}

	public function bindValue($key, $value) {
		$this->data[$key] = $value;
		parent::bindValue($key,$value);

	}

	public function isCached() {
		if(!$this->dbh->has_memcache()) {

			$this->has_cached_output = false;
			return false;

		}

		
		if(empty($this->cache_options['key'])) { $this->cache_options['key'] = md5($this->queryString); }

		$this->cached_output = DBConn::$memcached->get(MEMCACHE_PREFIX . $this->cache_options['key']);
		
		if($this->cached_output !== false) {
			
			$this->has_cached_output = true;
			return true;
		} else {
			$this->has_cached_output = false;
			return false;
		}

	}

    
	public function execute($cache_options = null,$input_parameters = null) {
		DBConn::$executed++;
		if($cache_options == null) { 
            if(DBConn::$debug) {
                @DBConn::$noncshit[$this->queryString]++;
            }

            return parent::execute($input_parameters); 
            
		} else {


			$this->cache_options = $cache_options;
			//echo $this->cache_options['type'] . "<br />";
			if(array_key_exists('type',$this->cache_options)) {
			
				if(!array_key_exists($this->cache_options['type'],DBConn::$cachegroups)) {
					DBConn::$cachegroups[$this->cache_options['type']] = array();
				}
                
                if(!array_key_exists(DBConn::$uid,DBConn::$cachegroups[$this->cache_options['type']])) {
					DBConn::$cachegroups[$this->cache_options['type']][DBConn::$uid] = array();
				}
                
				if(!in_array(MEMCACHE_PREFIX . $this->cache_options['key'],DBConn::$cachegroups[$this->cache_options['type']][DBConn::$uid])) {
					DBConn::$cachegroups[$this->cache_options['type']][DBConn::$uid][] = MEMCACHE_PREFIX . $this->cache_options['key'];
				}
				DBConn::$memcached->set(MEMCACHE_PREFIX . 'cache_groups', DBConn::$cachegroups, MEMCACHE_COMPRESSED,0);
			}

			if($this->isCached()) {
                if(DBConn::$debug) {
                    @DBConn::$cshit[$this->queryString]['cnt']++;
                    DBConn::$cshit[$this->queryString]['uid'] = MEMCACHE_PREFIX . $this->cache_options['key'];
                }
				DBConn::$cached++;
				return true;
			} else {
                if(DBConn::$debug) {
                    @DBConn::$noncshit[$this->queryString]['cnt']++;
                    DBConn::$cshit[$this->queryString]['uid'] = MEMCACHE_PREFIX . $this->cache_options['key'];
                }
                
     
                return parent::execute($input_parameters); 
                
				
			}
		}
	}


	public function fetchInto($instance) {
		if($this->has_cached_output) {
			$d = $this->cached_output;
		} else {
			$d = $this->fetch();
		}
		
		foreach(@$d as $s => $v) {
			$instance->$s = $v;
		}

		return $d;
	}

	public function cacheQuery($method,$custom_opts)
	{
		if($this->has_cached_output) {
				
				if($method == self::QUERY_FETCHINTO) {
					$this->fetchInto($custom_opts['instance']);
				}
			return $this->cached_output;
		}
		

		if(!$this->dbh->has_memcache()) {
			switch($method) {
				case self::QUERY_FETCHALL:
					return $this->fetchAll();
				break;

				case self::QUERY_FETCHCOLUMN:
					return $this->fetchColumn();
				break;

				case self::QUERY_FETCH:
					return $this->fetch();
				break;

				case self::QUERY_FETCHINTO:
					
					return $this->fetchInto($custom_opts['instance']);
				break;

				default:
					return false;
				break;
			}
		}

		if(!is_array($this->cache_options)) {
			throw new Exception("Cached Queries always require first argument of fetch to be cache options array");
			return array();
		} else {
			if(empty($this->cache_options['timeout'])) { $this->cache_options['timeout'] = 600; }

			if(empty($this->cache_options['key'])) { $this->cache_options['key'] = md5($this->queryString); }

			switch($method) {
				case self::QUERY_FETCHALL:
					$mcr = $this->fetchAll();
				
				break;

				case self::QUERY_FETCHCOLUMN:
					$mcr = $this->fetchColumn();
				break;

				case self::QUERY_FETCH:
					$mcr = $this->fetch();
				break;

				case self::QUERY_FETCHINTO:
					
					$mcr = $this->fetchInto($custom_opts['instance']);
				break;

				default:
					return false;
				break;
			}
		
			DBConn::$memcached->set(MEMCACHE_PREFIX . $this->cache_options['key'], $mcr, MEMCACHE_COMPRESSED, $this->cache_options['timeout']);
		

			return $mcr;

		}

	}

	

	public function foundRows()
	{
		if($this->has_cached_output) {
			return 1;
		} else {
			$rows = $this->dbh->prepare('SELECT found_rows() AS rows');
			$rows->execute();
			$rowsCount = $rows->fetchColumn();
			$rows->closeCursor();
			return $rowsCount;
		}
	}

}


?>
