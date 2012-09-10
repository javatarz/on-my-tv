<?

class Calendar {

	var $months;
	var $year;
	var $sundayfirst;

	var $daynames = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");

	static $timezone;

	function __construct($_year,$_timezone,$_sundayfirst = false) {
		$this->months = array();
		$this->year = $_year;
		$this->sundayfirst = $_sundayfirst;
		if($_sundayfirst) {
			$d = array_pop($this->daynames);
			array_unshift($this->daynames,$d);
		}

		self::$timezone = $_timezone;

		
	}

	function addMonth($_month) {
		if($_month > 0 && $_month <= 12) {
			$_newmonth =& new CalendarMonth($_month,$this->year,$this->sundayfirst);
			$this->months[$_month] = $_newmonth;
			return $_newmonth;
		} else {
			return false;
		}
	}

	function getMonth($_month) {
		if(isset($this->months[$_month]) && is_object($this->months[$_month])) {
			return $this->months[$_month];
		}
	}

	function generate($c) {
		$op = '';

		foreach($this->months as $m) {
			$out = '';

			$dowl = $c->startLoop('DAYOFWEEKLOOP');
			foreach($this->daynames as $k => $d) {
				$dowl->assign('shortdayofweek',$d);
				$dowl->nextLoop();
			}
			

		

			$c->endLoop($dowl);

			$mout =  $m->generate();

		
			for($i = 0; $i < count($mout); $i++) {
				if($i % 7 == 0 && $i != 0) {
					$out .= '</tr><tr>';
				}
				$out .= $mout[$i];
				
			}


			$mdate = new DateTime();
			$mdate->setDate($this->year,$m->month,1);

			$lm = clone $mdate;
			$nm = clone $mdate;

			$lm->modify('-1 month');
			$nm->modify('+1 month');
			$c->assign('cookie-domain',COOKIE_DOMAIN);
			$c->assign('month_name',$mdate->format('F') . ' ' . $this->year);
			$c->assign('prev-month-name',$lm->format('M'));
			$c->assign('next-month-name',$nm->format('M'));
			$c->assign('prev-month',$lm->format('n') . '.' . $lm->format('Y'));
			$c->assign('next-month',$nm->format('n') . '.' . $nm->format('Y'));
			$c->assign('month',$out);
			$op .= $c->output();
		}
		

		return $op;
	}
}

class CalendarMonth {
	var $month;
	var $numdays;
	var $days;
	var $firstday;
	var $lastday;
	var $sundayfirst;

	function __construct($_month,$_year,$_sundayfirst = false) {
		$this->days = array();

		$this->month = $_month;

		$this->sundayfirst = $_sundayfirst;
		

		//Generate datetime object to work out how many days there are in the requested month

		$dayofweekstring = ($this->sundayfirst)? 'w' :'N';
		$month_dt = new DateTime();
		$month_dt->setDate($_year,$_month,1);
		$this->numdays = $month_dt->format('t');
		$this->firstday = $month_dt->format($dayofweekstring);
		$month_dt->setDate($_year,$_month,$this->numdays);
		$this->lastday = $month_dt->format($dayofweekstring);

		for($i = 1; $i <= $this->numdays; $i++) {
			$this->addDay($i,$_month,$_year);
		}
	}

	private function addDay($_day,$_month,$_year) {

		if($_day >= 1 && $_day <= $this->numdays) {
			$_newday =& new CalendarDay($_day,$_month,$_year);
			$this->days[$_day] = $_newday;
			return $_newday;
		} else {
			return false;
		}

	}

	function getDay($_day) {
		if(isset($this->days[$_day]) && is_object($this->days[$_day])) {
			return $this->days[$_day];
		}
	}

	function generate() {
		$startweek = ($this->sundayfirst)? 0 : 1;
		
		for($i = $startweek; $i < $this->firstday; $i++) {
			$t = new Template('day_empty');
			$out[] = $t->output();
		}

		foreach($this->days as $d) {
			$out[] = $d->generate();
		}

		$endweek = ($this->sundayfirst)? 6 : 7;

		for($i = $this->lastday; $i < $endweek; $i++) {
			$t = new Template('day_empty');
			$out[] = $t->output();
		}

		return $out;

	}

}


class CalendarDay {
	var $content;
	var $day;
	var $month;
	var $year;

	function __construct($_day,$_month,$_year) {
		$this->content = array();
		$this->day = $_day;
		$this->month = $_month;
		$this->year = $_year;
	
	}

	function addContent($_content,$_order) {
		$this->content[$_order] = $_content;
	}

	function generate() {
		ksort($this->content);
		$date = new DateTime();
		$date->setTimezone(Calendar::$timezone);

		$now = clone $date;
		$date->setDate($this->year,$this->month,$this->day);
		if($date->format('Ymd') == $now->format('Ymd')) {
			$t = new Template('day_today');
		} else {
			$t = new Template('day');
		}
		$t->assign('day_id','d_' . $this->day . '_' . $this->month . '_' . $this->year);
		$t->assign('day_link','#');
		$t->assign('day_title',$date->format('l jS F Y'));
		$t->assign('day_date',$this->day);

		$l = $t->startLoop('CONTENTLIST');
		foreach($this->content as $c) {
			$l->assign('contentext',$c);
			$l->nextLoop();
		}
		$t->endLoop($l);

		return $t->output();

	}

}

?>