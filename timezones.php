<?
	/*$r = geoip_record_by_name($_SERVER['REMOTE_ADDR']);
	print_r($r);
	echo "<br />";*/
$timezone_identifiers = DateTimeZone::listIdentifiers();
foreach($timezone_identifiers as $tz) {
	$d = new DateTime();
	$in_tz = new DateTimeZone($tz);
	$d->setTimezone($in_tz);
	echo ($tz) . " : " . $d->format('H:i:s (P)') . "<br />";
}

include('include/config.inc.php');
echo "\n\n";
echo "ENUM(";
echo implode("','",$timezones);

echo ");\n\n";
?>