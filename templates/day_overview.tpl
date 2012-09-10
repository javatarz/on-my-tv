<div>
<a id="{$order}"></a>
</div>

<table class="calendarbody">
<tr>
<td class="dayhead"><a class="calendarbody" href="{$showlink}" rel="nofollow">{$show_name} - {$episode_name} {^hasairtime^({$airtime}{$endairtime}{$network})^}</a><div class="epnum">Season {$ep_season}, Episode {$ep_episode}</div></td>
</tr>
<tr>
<td class="daysyn">{^has_episode_summary^{$episode_summary}^No Summary Available...^}</td>
</tr>
	{^hasnextepisode^<tr>
	<td class="nextep"><a href="{$nexteplink}">Next Episode Airs: {$nextepairs}</a></td>
	</tr>^}
</table> 
<div style="clear: both;">
<a href="{$sitebase}" class="gobackday">Back to the TV Calendar</a>
</div>
