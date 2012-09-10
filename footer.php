<div class="bottomtext" style="margin-top: 5px;">
<?
if($xml_available || $rss_available) {
	echo '<div style="padding: 0px; display: inline;">';
	$uidex = ($user->name == null && $user->password == null)? '' : '&uid=' . $user->uid;
	if($xml_available) {
		echo '<a href="' . $_SERVER['REQUEST_URI'] . '?xml' . $uidex . '"><img src="' . HOMEPAGE_URL . '/imgs/xml.gif" alt="Download XML Data" title="XML Output" /></a>';
	} 
	if($xml_available && $rss_available) {
		echo "&nbsp;";
	}

	if($rss_available) {
		echo '<a href="' . $_SERVER['REQUEST_URI'] . '?rss' . $uidex . '"><img src="' . HOMEPAGE_URL . '/imgs/rss.gif" alt="Download RSS Data" title="RSS Output" /></a>';
	} 

	 echo '</div>';
}
?>

<p><a href="<?=THIS_WEEKS_URL?>"><strong>Rolling Week</strong></a> | <a href="<?=TODAYS_URL?>"><strong>Todays TV</strong></a> | <a href="<?=TOMORROWS_URL?>"><strong>Tomorrows TV</strong></a> | <a href="<?=HOMEPAGE_URL?>"><strong>This Month</strong></a> | <a href="/frequently-asked-questions"><strong>FAQ</strong></a> | <a href="irc://irc.efnet.net/tvcat"><strong>IRC</strong></a> | <a href="/donate"><strong>Donate</strong></a> | <a href="mailto:&#099;&#111;&#110;&#116;&#097;&#099;&#116;&#064;&#111;&#110;&#045;&#109;&#121;&#046;&#116;&#118;" ><strong>Contact</strong></a></p><br />
<? 

	$lu = @file_get_contents(DATA_LAST_UPDATED);
	if(!empty($lu)) {
		$lu = "<strong>Last updated</strong> " . $lu . " " . date('e');
	}
	$load = sys_getloadavg();
	DBConn::getInstance('');
	if(class_exists('Memcache')) {
		$dbs =  DBConn::$memcached->getStats();
	}

	$dbu = number_format($dbs['bytes'] / (1024 * 1024),2);
	$dbt = number_format($dbs['limit_maxbytes'] / (1024 * 1024),2) . "MB";
	$hitp = number_format(($dbs['get_hits'] / ($dbs['get_hits'] + $dbs['get_misses'])) * 100,2) . "%"; 



 ?>
  <p> <?  echo "<strong>[</strong>".number_format(end_time($s),5)."s<strong>] | [</strong>". DBConn::queryCount()." queries | " . DBConn::cachedCount() . " cached | " . $dbu . "/" . $dbt . " | " . $hitp . " global hit rate<strong>] | [</strong>";
echo number_format($load[0],2) . ", " . number_format($load[1],2) . ", " . number_format($load[2],2) . "<strong>]</strong>";  ?>
<br /><strong>Data by</strong> <a href="http://www.tvrage.com/" rel="nofollow">TVRage.com</a> &amp; <a href="http://epguides.com">EpGuides</a> - Thanks Guys! | <?=$lu?><br /> 
	
</p> 
 
  <p>Copyright &copy; 2005-2009 <a href="mailto:&#099;&#111;&#110;&#116;&#097;&#099;&#116;&#064;&#111;&#110;&#045;&#109;&#121;&#046;&#116;&#118;">B. Agricola</a> All Rights Reserved</p> 
 
</div>  
<!-- UID: <?= $user->uid ?> -->

<!--<img src="http://whos.amung.us/cwidget/r621jt3avvr6/000000f7941d.png" style="display: none;" />-->
