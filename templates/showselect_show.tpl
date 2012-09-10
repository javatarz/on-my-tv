{%SHOWALPH%}
{^ischanged^<div class="showclear"></div><div class="boldletter">{$showchar}</div>^}
<div id="check{$checkid}" class="{^ischecked^checkedletter^normalletter^} {^newclass^new^^} {^premieringclass^prem^^}">
<label><input class="checkbox" type="checkbox" name="selected_shows[]" value="{$checkid}" style="margin-right: 5px;" {^ischecked^checked="checked"^} />{$showname}</label>
<a class="selectsummary" href="http://{$show_name_clean}.{$main_url}" title="View Show Summary for {$showname}">{$showname}</a>
</div>
{%SHOWALPH%}
<div class="showclear"></div>

</div>
<div>
<input type="submit" name="filter" value="Save Filter" class="searchbutton" style="margin: 15px 10px; " />
<br /></div>
</form>
</body>
</html>