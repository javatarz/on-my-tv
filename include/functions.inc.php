<?

function br2nl($str) {
   $str = preg_replace("/(\r\n|\n|\r)/", "", $str);
   return preg_replace("=<br */?>=i", "\\n", $str);
}

function mod_zone_local($show_tz,$input_tz,$airtime,$mod_timezones) {
    $iptz = explode(' ',$show_tz);
    $iptz = $iptz[0];

    if(array_key_exists($iptz,$mod_timezones)) {
        $tz_mods = $mod_timezones[$iptz];
        
        $in_tz_name = $input_tz->getName();
        
        // We have a valid modifier for this timezone
        if(array_key_exists($in_tz_name,$tz_mods)) {
            $airtime->modify($tz_mods[$in_tz_name]);
            
        }
    }
    
    return $airtime;
}

            
function summary_format_ics($text)
{	
	
	$c = preg_replace('%\\\\%','\\\\\\',$text);
	$c = preg_replace('%\n%i','',$c);
	$c = preg_replace('%\r%i','',$c);
	$c = br2nl($c);
	$c = preg_replace('%,%','\,',$c);
	$c = preg_replace('%;%i','\;',$c);
	$c = strip_tags($c);
	$c = trim($c);
	return $c;
}

function zerofill($mStretch, $iLength = 2)
{
    $sPrintfString = '%0' . (int)$iLength . 's';
    return sprintf($sPrintfString, $mStretch);
}

function summary_format_ajax($text)
{	
	$c = str_replace('<br />',"\n",$text);
	$c = strip_tags($c);
	$c = nl2br($c);
	$c = preg_replace('%’%','\'',$c);
	$c = preg_replace('%–%','-',$c);
	$c = preg_replace('%“%','"',$c);
	$c = preg_replace('%”%','"',$c);

	
	$c = trim($c);
	return $c;
}

function tvrage_thumb($id) {
  return 'http://images.tvrage.com/shows/'.(floor($id / 1000) +
1).'/'.$id.'.jpg';
}

function fixoutput($str){
    $good[] = 9;  #tab
    $good[] = 10; #nl
    $good[] = 13; #cr
    for($a=32;$a<127;$a++){
        $good[] = $a;
    }   
    $len = strlen($str);
    for($b=0;$b < $len+1; $b++){
        if(in_array(ord($str[$b]), $good)){
            $newstr .= $str[$b];
        }//fi
    }//rof
    return $newstr;
}

function TimeAgoLimited($timestamp) {

	$current_time = time();
	$difference = $current_time - $timestamp;

	// Set the periods of time
	$periods = array("second","minute", "hour", "day", "week", "month", "year", "decade");

	// Set the number of seconds per period
	$lengths = array(1,60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

	for($i = sizeof($periods)-1; $i >= 0; $i--) {
		$l = $lengths[$i];

		if(($difference / $l) >= 1) { 
			$n = round($difference / $l);
			if($n != 1) {
				$p = $periods[$i] . "s";
			} else {
				$p = $periods[$i];
			}
			echo $n . " " . $p;
			break;
		}

	}

}

/*function TimeUntilLimited($timestamp) {

	$current_time = time();
	$difference = $timestamp - $current_time;

	// Set the periods of time
	$periods = array("minute", "hour", "day", "week", "month", "year", "decade");

	// Set the number of seconds per period
	$lengths = array(60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

	for($i = sizeof($periods)-1; $i >= 0; $i--) {
		$l = $lengths[$i];

		if(($difference / $l) >= 1) { 
			$n = round($difference / $l);
			if($n != 1) {
				$p = $periods[$i] . "s";
			} else {
				$p = $periods[$i];
			}
			echo $n . " " . $p;
			break;
		}

	}

}*/

function TimeAgo($timestamp,$ctime = null,$max,$short = false){
	if($max >= 2) {
		return "";
	}
	// Store the current time
	if($ctime == null) {
		$current_time = time();
	} else {
		$current_time = $ctime;
	}
	// Determine the difference, between the time now and the timestamp
	$difference = $current_time - $timestamp;

	// Set the periods of time
	if($short) {
		$periods = array("m", "h", "d", "w", "mo", "y", "dec");
	} else {
		$periods = array("min", "hour", "day", "week", "month", "year", "decade");
	}
	// Set the number of seconds per period
	$lengths = array(60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
	

	// Determine which period we should use, based on the number of seconds lapsed.
	// If the difference divided by the seconds is more than 1, we use that. Eg 1 year / 1 decade = 0.1, so we move on
	// Go from decades backwards to seconds       
	for ($val = sizeof($lengths) - 1; ($val >= 0) && (($number = $difference / $lengths[$val]) < 1); $val--);

	// Ensure the script has found a match
	if ($val < 0) $val = 0;

	// Determine the minor value, to recurse through
	$new_time = $current_time - ($difference % $lengths[$val]);

	// Set the current value to be floored
	$number = floor($number);

	// If required create a plural
	if($number != 1 && !$short) $periods[$val] .= "s";

	// Return text
	if($short) {
		$text = sprintf("%d%s ", $number, $periods[$val]);   
	} else {
		$text = sprintf("%d %s ", $number, $periods[$val]);   
	}
	// Ensure there is still something to recurse through, and we have not found 1 minute and 0 seconds.
	if (($val >= 1) && (($current_time - $new_time) > 0)){
	   $max++;
	   $text .= TimeAgo($new_time,$current_time,$max,$short);
	}
	  
	return $text;
}

function TimeUntil($timestamp,$ctime = null,$max){
	if($max >= 7) {
		return "";
	}
	// Store the current time
	if($ctime == null) {
		$current_time = time();
	} else {
		$current_time = $ctime;
	}
	// Determine the difference, between the time now and the timestamp
	$difference = $timestamp - $current_time;
	if($difference < 0) {
		return false;
	}
	// Set the periods of time
	$periods = array("min", "hour", "day", "week", "month", "year", "decade");

	// Set the number of seconds per period
	$lengths = array(60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
	

	// Determine which period we should use, based on the number of seconds lapsed.
	// If the difference divided by the seconds is more than 1, we use that. Eg 1 year / 1 decade = 0.1, so we move on
	// Go from decades backwards to seconds       
	for ($val = sizeof($lengths) - 1; ($val >= 0) && (($number = $difference / $lengths[$val]) <= 1); $val--);

	// Ensure the script has found a match
	if ($val < 0) $val = 0;

	// Determine the minor value, to recurse through
	$new_time = $current_time - ($difference % $lengths[$val]);

	// Set the current value to be floored
	$number = floor($number);

	// If required create a plural
	if($number != 1) $periods[$val] .= "s";

	// Return text
	$text = sprintf("%d %s ", $number, $periods[$val]);   

	// Ensure there is still something to recurse through, and we have not found 1 minute and 0 seconds.
	if (($val >= 1) && (($current_time - $new_time) > 0)){
	   $max++;
	   $text .= TimeUntil($new_time,$current_time,$max);
	}
	  
	return $text;
}


function check_email_address($email) {
  // First, we check that there's one @ symbol, and that the lengths are right
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
     if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
      return false;
    }
  }  
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}
function checkEmail($email) 
{
	if(!check_email_address($email)) 
	{
		return FALSE;
	} else { return true; }
	/*
	list($Username, $Domain) = split("@",$email);

	if(@getmxrr($Domain, $MXHost)) 
	{
		return TRUE;
	}
	else 
	{
		if(@fsockopen($Domain, 25, $errno, $errstr, 30)) 
		{
			return TRUE; 
		}
		else 
		{
			return FALSE; 
		}
	}*/
}

function clearURLSet($_url) {

	$_url = str_replace("/os","",$_url);
	$_url = str_replace("/ol","",$_url);
    $_url = str_replace("/or","",$_url);
	$_url = rtrim($_url,"/");

	return $_url;

}

function clean_up_numerics($nums) {
	$nums = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $nums);
	$nums = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $nums);

	return $nums;
}

function un_clean_name($name) {
	$name = str_replace("-"," ",$name);
	$name = str_replace("."," ",$name);
	$name = str_replace("-and-","&",$name);
	return $name;
}

function clean_name($name) {
	$name = strtolower($name);
	$name = str_replace("\"", "", $name);
	$name = str_replace("'", "", $name);
	$name = str_replace("/", "-", $name);
	$name = str_replace("&amp;", "-and-", $name);
	$name = str_replace("&", "-and-", $name);
	$name = str_replace(":", "", $name);
	$name = str_replace(";", "", $name);
	$name = str_replace(",", "", $name);
	$name = str_replace(".", "", $name);
	$name = str_replace("!", "", $name);
	$name = str_replace("+", "", $name);
	$name = str_replace("(", "", $name);
	$name = str_replace(")", "", $name);
	$name = str_replace(" ", "-", $name);
	$name = str_replace("--", "-", $name); 
	$name = str_replace("--", "-", $name); 
	return $name;
}

function word_to_number($x) {
	$nos = array(
		'zero' => 0,
		'one' => 1,
		'two' => 2,
		'three' => 3,
		'four' => 4,
		'five' => 5,
		'six' => 6,
		'seven' => 7,
		'eight' => 8,
		'nine' => 9,
		'ten' => 10,
		'eleven' => 11,
		'twelve' => 12,
		'thirteen' => 13,
		'fourteen' => 14,
		'fifteen' => 15,
		'sixteen' => 16,
		'seventeen' => 17,
		'eighteen' => 18,
		'nineteen' => 19,
		'twenty' => 20,
		'thirty' => 30,
		'fourty' => 40,
		'fifty' => 50,
		'sixty' => 60,
		'seventy' => 70,
		'eighty' => 80,
		'ninety' => 90
	);
	
	if(stripos($x," ") !== false) {
			$value = 0;
		foreach(explode(" ",$x) as $npart) {
			$value += word_to_number($npart);
			
		}
		return $value;
	} else {
		return $nos[$x];
	}
}

function int_to_words($x) {
   $nwords = array( "zero", "one", "two", "three", "four", "five", "six", "seven",
                   "eight", "nine", "ten", "eleven", "twelve", "thirteen",
                   "fourteen", "fifteen", "sixteen", "seventeen", "eighteen",
                   "nineteen", "twenty", 30 => "thirty", 40 => "forty",
                   50 => "fifty", 60 => "sixty", 70 => "seventy", 80 => "eighty",
                   90 => "ninety" );

   if(!is_numeric($x))
      $w = '#';
   else if(fmod($x, 1) != 0)
      $w = '#';
   else {
      if($x < 0) {
         $w = 'minus ';
         $x = -$x;
      } else
         $w = '';
      // ... now $x is a non-negative integer.

      if($x < 21)   // 0 to 20
         $w .= $nwords[$x];
      else if($x < 100) {   // 21 to 99
         $w .= $nwords[10 * floor($x/10)];
         $r = fmod($x, 10);
         if($r > 0)
            $w .= '-'. $nwords[$r];
      } else if($x < 1000) {   // 100 to 999
         $w .= $nwords[floor($x/100)] .' hundred';
         $r = fmod($x, 100);
         if($r > 0)
            $w .= ' and '. int_to_words($r);
      } else if($x < 1000000) {   // 1000 to 999999
         $w .= int_to_words(floor($x/1000)) .' thousand';
         $r = fmod($x, 1000);
         if($r > 0) {
            $w .= ' ';
            if($r < 100)
               $w .= 'and ';
            $w .= int_to_words($r);
         }
      } else {    //  millions
         $w .= int_to_words(floor($x/1000000)) .' million';
         $r = fmod($x, 1000000);
         if($r > 0) {
            $w .= ' ';
            if($r < 100)
               $word .= 'and ';
            $w .= int_to_words($r);
         }
      }
   }
   return $w;
}



class ArrayToXML
{
	/**
	 * The main function for converting to an XML document.
	 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
	 *
	 * @param array $data
	 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
	 * @param SimpleXMLElement $xml - should only be used recursively
	 * @return string XML
	 */
	public static function toXml($data, $rootNodeName = 'data', $xml=null)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set ('zend.ze1_compatibility_mode', 0);
		}
 
		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}
 
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "unknownNode_". (string) $key;
			}
 
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z]/i', '', $key);
 
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				ArrayToXML::toXml($value, $rootNodeName, $node);
			}
			else 
			{
				// add single node.
                                $value = htmlentities($value);
				$xml->addChild($key,$value);
			}
 
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}
}
?>
