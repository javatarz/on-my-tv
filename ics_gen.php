<?
include('./include/config.inc.php');

//Disable output compression for the moment, dunno if clients can handle it.
//ob_start('ob_gzhandler');
ob_start();

//Quick BR2NL to convert summary br's back to newlines


if(isset($_GET['uid'])) {
	$user = new User($_GET['uid'],True);
} else {
	$user = User::getInstance();
}

if($user->isValid()) {
	$ics = new Template('ics_output');

	$selected_timezone = $user->timezone;
    if($selected_timezone == '') {
        $selected_timezone = DEFAULT_TIMEZONE;
    }
    
	$shows = TVShows::getUserFilter($user->id);

	if(count($shows) == 0) {
		$shows = TVShows::getShows();
	}

	$d = new DateTime();
	$in_tz = new DateTimeZone($selected_timezone);
	$d->setTimezone($in_tz);

	$d_start = clone $d;
	$d_end = clone $d;

	$d_start->setDate($d_start->format('Y'),$d_start->format('n'),$d_start->format('j'));
	$d_start->setTime(00,00,00);

	$d_end->modify('+2 weeks');
	$d_end->setTime(23,59,59);

	$ics->assign('caldesc',wordwrap('Calendar for TV from On-My.tv',63,"\n ",true));
	$ics->assign('calname','TV Shows');

	TVEpisodes::setFilterTimezone(DEFAULT_TIMEZONE);
	TVEpisodes::setFilterPeriod($d_start,$d_end);
	TVEpisodes::setFilterSelectSummaries(true);
	TVEpisodes::getFilteredEpisodesByShows($shows);


	$uoutput = ($user->name != null)? strtolower($user->name) : 'anon';
	$ics->assign('prodid',wordwrap('-//Calendar For TV//' . $user->uid . '//usr_' . $uoutput,63,"\n  ",true));
	$episodes = $ics->startLoop('EVENTLOOP');
	foreach($shows as $sh) {
		
		$eps = TVEpisodes::returnFilteredEpisodesForShow($sh->id);
		

		foreach($eps as $ep) {
	

            $epdate = new DateTime($ep->ep_tzfix_date);
            $episodes->assign('tzname',$selected_timezone);
            $episodes->assign('starttime',$epdate->format('Ymd\THis'));
            if($sh->length != 0) {
                $enddate = clone $epdate;
                $enddate->modify('+ ' . ($sh->length / 60) . ' minutes');
                $episodes->assign('endtime',$enddate->format('Ymd\THis'));
                $episodes->assign('epduration',$sh->length . 'S');
            } else {
                $enddate = clone $epdate;
                $enddate->modify('+60 minutes');
                $episodes->assign('endtime',$enddate->format('Ymd\THis'));
                $episodes->assign('epduration','1800S');
            }

            $episodes->assign('summarystring',wordwrap(summary_format_ics($sh->name) . ' ' . $ep->season . 'x' . $ep->episode . ' - ' . summary_format_ics($ep->name),63,"\n  ",true));
            
            $episodes->assign('description',wordwrap(summary_format_ics($ep->episode_summary),63,"\n  ",true));

            $episodes->assign('uid',wordwrap(strtoupper($sh->stringid) . '_' . $ep->season . '_' . $ep->episode,63,"\n  ",true));

            $now = new DateTime();

            $episodes->assign('now',$now->format('Ymd\THis'));

            $episodes->assign('showname',wordwrap($sh->name,63,"\n  ",true));

            $episodes->nextLoop();
			

		}

	}

	$ics->endLoop($episodes);
 
	header('Content-Type: text/calendar');
	if($_GET['g'] == "0") {
		header('Content-Disposition: attachment; filename="' . $d_start->format('Y-m-d') . '_-_' . $d_end->format('Y-m-d') . '_-_' . $uoutput . '.ics"');
	}
    
	echo str_replace("\n","\r\n",$ics->output());


} else {
	header('Location: ' . SITE_BASE);
}

?>