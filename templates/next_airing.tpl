<table id="nextair-parent" summary="layout table" >
  
      <thead>
        <tr>
            <td colspan="7" class="month_name"><div class="prev-month {^nav_links^ ^nohighlight^}">{^nav_links^<a href="http://{$previous_link}">&lt;&lt; <strong>{$previous_link_text}</strong></a>^&nbsp;^}</div><h3>{$display_text}</h3><div class="next-month {^nav_links^ ^nohighlight^}">{^nav_links^<a href="http://{$next_link}"><strong>{$next_link_text}</strong> &gt;&gt;</a>^&nbsp;^}</div></td>
        </tr>
      </thead>
    <tbody> 
        <tr><td><table class="nextair" summary="layout table">{^is_not_daily^<tr class="spacer"><th colspan="7">&nbsp;</th></tr>^}
        {%EPLOOP%}
        
            {^not_top_split^<tr class="spacer"><th colspan="7">&nbsp;</th></tr></tbody></table><table class="nextair" summary="layout table">^}
            {^dodaysplit^

                <tbody>
                 <tr class="dayhead {^istoday^today^}"><th colspan="7">{$day_header}</th></tr>^}

                    <tr class="ep" id="{$order}"><th {^is_top^class="top"^}><a id="y_{$episode_id}" href="javascript:void(0)" class="openlink">+ {$show_name}</a> </th>
                        <td class="viewmore">{^is_showpage^<a href="http://{$show_name_clean}.{$main_url}"><img src="http://{$main_url}/imgs/el_icon.gif" title="View more upcoming episodes for {$show_name}" alt="View more upcoming episodes for {$show_name}" /></a>^}{^show_epnum^#{$ep_episode}&nbsp;^}</td>
                        
                        <td class="episodename">{^has_tvrage_url^<a href="{$tvrage_url}">{$episode_name_short}</a>^{$episode_name_short}^}</td>
                        <td class="airdate">{$airdate}</td>
                        <td class="until">{$timeuntil}</td>
                        <td class="airtime">{^hasairtime^{$airtime}^} {^haslength^{$endairtime}^}</td>
                        {^has_countryflag^<td class="countryflag {^is_top^top^}"><img src="{$flagimage_url}" title="{$country} Show" alt="{$country} Show" /></td>^<td></td>^}
                    </tr>
                    <tr class="epdesc" id="x_{$episode_id}" ><td colspan="7"><strong>Season  {$ep_season}, Episode {$ep_episode} - "{$episode_name}"</strong><br />{^has_episode_summary^{$episode_summary}^No Summary Available...^}
                        <br /><div class="info">
                        {^has_classification^<strong>Type:</strong> {$classification} | ^}
                        {^has_genres^<strong>Genre(s):</strong> {$genres} | ^}
                        {^has_network^<strong>Network:</strong> {$network} | ^}
                        {^has_status^<strong>Status:</strong> {$status} | ^}
                        <strong>Rating:</strong> {$rating} {^has_votes^({$votecount} votes)^} {^valid_user^[<a class="ratelink" href="http://vote.{$episode_id}.1.{$main_url}/">1</a>, <a class="ratelink" href="http://vote.{$episode_id}.2.{$main_url}/">2</a>, <a class="ratelink" href="http://vote.{$episode_id}.3.{$main_url}/">3</a>, <a class="ratelink" href="http://vote.{$episode_id}.4.{$main_url}/">4</a>, <a class="ratelink" href="http://vote.{$episode_id}.5.{$main_url}/">5</a>]^[ Login to vote ]^}
                        </div></td>
                    </tr>
        {%EPLOOP%}
            <tr class="spacer"><th colspan="7">&nbsp;</th></tr>
            </tbody>
        </table>
        
        
      
        </td></tr>
    </tbody>
    
    
  
     

    
</table>