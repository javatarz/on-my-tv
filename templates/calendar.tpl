<table id="cal" summary="layout table" >
<thead>


<tr>
	<td class="month_name"><div class="prev-month"><a href="http://{$prev-month}.{$cookie-domain}/">&lt;&lt; <strong>{$prev-month-name}</strong></a></div><h3>{$month_name}</h3><div class="next-month"><a href="http://{$next-month}.{$cookie-domain}/"><strong>{$next-month-name}</strong> &gt;&gt;</a></div></td>
</tr>

</thead>
<tfoot>
<tr>
			<td class="month_name"><div class="prev-month"><a href="http://{$prev-month}.{$cookie-domain}/">&lt;&lt; <strong>{$prev-month-name}</strong></a></div><h3>{$month_name}</h3><div class="next-month"><a href="http://{$next-month}.{$cookie-domain}/"><strong>{$next-month-name}</strong> &gt;&gt; </a></div></td>
		</tr> 
	</tfoot>
	<tbody> 
		
		<tr>
			<td>
				<table id="month_box" class="month_box" summary="layout table">
					<tbody>
						<tr>	{%DAYOFWEEKLOOP%}
							<th class="thisday">{$shortdayofweek}</th>
							{%DAYOFWEEKLOOP%}
						</tr>
						<tr>
						{$month}
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		
	</tbody>
</table>
