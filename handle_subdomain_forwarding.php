<?
include('./include/config.inc.php');
ob_start();

$time_modifiers = array
(
	"last" => -1,
	"next" => +1
);

$daily_forwards = array
(
	"monday" => 0,
	"tuesday" => 1,
	"wednesday" => 2,
	"thursday" => 3,
	"friday" => 4,
	"saturday" => 5,
	"sunday" => 6,
	"mon" => 0,
	"tue" => 1,
	"wed" => 2,
	"thu" => 3,
	"fri" => 4,
	"sat" => 5,
	"sun" => 6,
	"tues" => 1,
	"thur" => 3

);

$monthly_forwards = array
(
	"january" => 1,
	"february" => 2,
	"march" => 3,
	"april" => 4,
	"may" => 5,
	"june" => 6,
	"july" => 7,
	"august" => 8,
	"september" => 9,
	"october" => 10,
	"november" => 11,
	"december" => 12,
	"jan" => 1,
	"feb" => 2,
	"mar" => 3,
	"apr" => 4,
	"may" => 5,
	"jun" => 6,
	"jul" => 7,
	"aug" => 8,
	"sep" => 9,
	"sept" => 9,
	"oct" => 10,
	"nov" => 11,
	"dec" => 12

);

$user = User::getInstance();

$styles = Styles::getStyles();


$subdomain = strtolower(str_replace("." . COOKIE_DOMAIN,"",$_SERVER['HTTP_HOST']));


$parts = split("\.",$subdomain);

$now = new DateTime();

$csn = un_clean_name($subdomain);

if($subdomain == 'on-my.tv') {
    $parts = array();
    $csn = '';
}

    
$return_url = '<a href="' . HOMEPAGE_URL . '">Return to the Calendar</a>';
if(count($parts) >= 3) {

	$string_part = '';
	$poss_mod = '';

	if(count($parts) == 3) {
		if($parts[0] == 'vote' && is_numeric($parts[1]) && is_numeric($parts[2])) {
			
			if($user->isValid()) {
				$_epid = $parts[1];
				$_vote = $parts[2];
				$ep = new TVEpisode($_epid);

				if($ep->isValid()) {
					if($ep->updateRating($_vote,$user->id)) {
						
						DBConn::clearCacheGroup('epselect');
						
						Header("Location: " . $_SERVER['HTTP_REFERER']);
					} else {

						die("No, you cannot vote more than once. Stop haxing the votes! :(<br />" . $return_url);
					}
				

				} else {
					die("Episode number is invalid. Stop haxing the votes! :(<br />" . $return_url);
				}
			} else {
				die("You must be a user to vote. Stop haxing the votes! :(<br />" . $return_url);
			}
		

		} elseif(is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
			$day = $parts[0];
			$month = $parts[1];
			$year = $parts[2];
			$string_part = 'dmy';
		} else {

				$poss_mod = $parts[0];
				$poss_numeric = $parts[1];
				$poss_string = $parts[2];
			
		}
	
	} else {
		
			$poss_mod = $parts[0];

			$poss_numeric = array_slice($parts,1,-1);
			$poss_numeric = implode(" ",$poss_numeric);
			$poss_string = $parts[count($parts)-1];

		
	}


	if(array_key_exists($poss_mod,$time_modifiers)) {
			
		
			$time_mod = $time_modifiers[$poss_mod];	
			$time_part = $poss_mod;
			
			if(!is_numeric($poss_numeric)) {
				
				$poss_numeric = word_to_number($poss_numeric);
			
			}

			
			$rolling_part = $poss_numeric . " " . $poss_string;
		
			$string_part = 'rolling';
			
	} elseif($string_part != 'dmy') {
            $tsr = TVShows::getShowByName($csn);
            if(is_a($tsr,'TVShow')) {
                $string_part = 'show';
            }
	}
	

} elseif(count($parts) == 2) {
	if(is_numeric($parts[0]) && is_numeric($parts[1])) {

		//If first part is longer than second, flip the parts (someone typed year before month)
		if(strlen($parts[0]) > strlen($parts[1])) {
			$parts = array_reverse($parts);
		}

		$month = $parts[0];
		$year = $parts[1];
		$string_part = 'my';

	} else {
		if(array_key_exists($parts[0],$time_modifiers)) {
			$time_mod = $time_modifiers[$parts[0]];	
			
			$time_part = $parts[0];
			$string_part = $parts[1];
		} else {
			$tsr = TVShows::getShowByName($csn);
			if(is_a($tsr,'TVShow')) {

				$string_part = 'show';
			} else {
				$time_mod = 0;
				
				$time_part = $parts[0];
				$string_part = $parts[1];
			}
		}

	}

	
} elseif(count($parts) == 1) {
	if(!array_key_exists($parts[0],$monthly_forwards) && !array_key_exists($parts[0],$daily_forwards)) {
		
		
		$string_part = 'dmy';
		$d = new DateTime();
		$day = $d->format('j');
        $dim = $d->format('t');
        
		if($subdomain == 'tomorrow') {
			$day++;
		} elseif($subdomain == 'yesterday') {
			$day--;
		} elseif($subdomain == 'today') {
			$string_part = 'today';
		} else {
			
			$tsr = TVShows::getShowByName($csn);
			if(is_a($tsr,'TVShow')) {
				$string_part = 'show';
			}
		}
        
		$month = $d->format('n');
		$year = $d->format('Y');
        
        if($day > $dim) {
            $month++;
            $day = 1;
        }
        
        if($month > 12) {
            $year++;
            $month = 1;
        }
        
	
	} elseif(array_key_exists($parts[0],$monthly_forwards)) {
		$string_part = 'my';
		$d = new DateTime();
		$month = $monthly_forwards[$parts[0]];
		$year = $d->format('Y');



	} elseif(array_key_exists($parts[0],$daily_forwards)) {
		$string_part = 'dmy';
		$d = new DateTime();
		$day = $daily_forwards[$parts[0]];
		$month = $d->format('n');
		$year = $d->format('Y');

	} else {

		$string_part = 'my';
		$month = $d->format('n');
		$year = $d->format('Y');

	}
} else {
	
	$string_part = $subdomain;
}

switch($string_part) {

	case 'rolling':
		define("INPUT_ROLLING",$rolling_part);

		switch($time_part) {
			case 'next':
				$mod = "+";
			break;

			case 'last':
				$mod = "-";
			break;
		}
		define("INPUT_ROLLING_MOD",$mod);
		define("INPUT_ROLLING_MOD_STRING",$time_part);
		
   
        include("next_airing.php");
        

		exit();
	case 'show':
		define("INPUT_SHOW_ID",$tsr->id);
	
        include("next_airing.php");
        
		exit();

	case 'today':
		define("INPUT_TODAY",true);
	case 'dmy':
		define("INPUT_DAY",$day);
		define("INPUT_MONTH",$month);
		define("INPUT_YEAR",$year);
		define("INPUT_LIMIT",'1 day');
		
		
        include("next_airing.php");
        
		exit();

	case 'my':
		define("INPUT_MONTH",$month);
		define("INPUT_YEAR",$year);
		
		include("index.php");
		exit();

	case 'month':
		$rewire_date = clone $now;
		$rewire_date->modify($time_part . " " . $string_part);
		
		define("INPUT_MONTH",$rewire_date->format('n'));
		define("INPUT_YEAR",$rewire_date->format('Y'));
		//Header('Location: ' . SITE_BASE . '/' . $rewire_date->format('n-Y') . '/');
		include("index.php");
		exit();
	break;

	case 'week':
		$rewire_date = clone $now;
		$rewire_date->modify($time_part . " " . $string_part);
		
		define("INPUT_WEEK",$rewire_date->format('W'));
		define("INPUT_YEAR",$rewire_date->format('Y'));

		
        include("next_airing.php");
        

		exit();
	break;

}


if(array_key_exists($string_part,$monthly_forwards)) {
	$rewire_date = clone $now;

	$difference = $rewire_date->format('n') - $monthly_forwards[$string_part];

	if($difference >= 5) {
		$rewire_date->modify('+1 year');
	}
	
	define("INPUT_MONTH",$monthly_forwards[$string_part]);
	define("INPUT_YEAR",$rewire_date->format('Y'));
	include("index.php");
	exit();
}

if(array_key_exists($string_part,$daily_forwards)) {
	$rewire_date = clone $now;

	$rewire_date->modify($time_part . " " . $string_part);
	
	define("INPUT_DAY",$rewire_date->format('j'));
	define("INPUT_MONTH",$rewire_date->format('n'));
	define("INPUT_YEAR",$rewire_date->format('Y'));
	define("INPUT_LIMIT",'1 day');
		
	
    include("next_airing.php");
    
	exit();


}



include("index.php");
	
die();



?>