<?
header("Content-Type: text/css");
$f = file_get_contents("./catdark.css");

function random_hex_color($min = 0, $max = 255){
    return sprintf("%02X%02X%02X", mt_rand($min, $max), mt_rand($min, $max), mt_rand($min, $max));
}
$f = preg_replace("%background: url\((.*);%iU","background: #000000;",$f);
preg_match_all ( "%(background|color|border-color|border-(top|right|bottom|left)-color): ?(#[0-9A-F]{2,6});%Ui" , $f, $m,PREG_SET_ORDER );

$t = array();

foreach($m as $k => $x) {
	$x[3] = strtolower($x[3]);

	if(!in_array($x[3],$t) && !in_array($x[3] . $x[3],$t)) {

		$t[] = $x[3];
	} else {
		unset($m[$k]);
	}
}

foreach($m as $r) {
	if(!empty($r[2])) {
		$r[1] = str_ireplace('-' . $r[2] . '-','-',$r[1]);
	}
	switch($r[1]) {
		case 'background':
			$min = 0;
			$max = 200;
			break;

		case 'color':
			$min = 50;
			$max = 220;
			break;
		case 'border-color':
			$min = 30;
			$max = 150;
		break;

		default:
			$min = 0;
			$max = 255;
		break;
	}
	$f = str_ireplace($r[3],"#" . random_hex_color($min,$max) . "",$f);
}
echo $f;
?>