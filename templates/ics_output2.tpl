BEGIN:VCALENDAR
VERSION:2.0
X-WR-CALDESC:{$caldesc}
X-WR-CALNAME:{$calname}
PRODID:-//Calendar For TV//{$uniqueid}//usr_{$username}
CALSCALE:GREGORIAN
METHOD:PUBLISH

{%EVENTLOOP%}
BEGIN:VEVENT
DTSTART;TZID={$tzname}:{$starttime}
DTEND;TZID={$tzname}:{$endtime}
SUMMARY:{$summarystring}
DESCRIPTION:{$description}
URL:{$showurl}
UID:{$uid}
SEQUENCE:0
DURATION:{$epduration}
DTSTAMP:{$now}
TRANSP:TRANSPARENT
CATEGORIES: {$showname} Episodes, TV Shows
END:VEVENT
{%EVENTLOOP%}
END:VCALENDAR