<div class="optioonsarea" id="optionsarea" {^hidden^style="display: none;"^}><form action="{$request_uri}" method="post">
<label for="timezone">Your Timezone:</label><select name="timezone" id="timezone">
{%TZLOOP%}<option value="{$tzid}" {^iscurtz^selected="selected"^}>{$tzname}</option>{%TZLOOP%}
</select> 
<label for="style">Calendar Style:</label><select name="style" id="style">
{%STYLOOP%}<option value="{$styid}" {^iscursty^selected="selected"^}>{$styname}</option>{%STYLOOP%}
</select> 
<label for="s_sortbyname">Sort Shows By:</label><select name="s_sortbyname" id="s_sortbyname">
<option value="1" {^s_sortbynamee_true^selected="selected"^}>Name</option> 
<option value="0" {^s_sortbynamee_false^selected="selected"^}>Airtime</option> 
</select>
<label for="s_numbers">Show Ep Numbers:</label><select name="s_numbers" id="s_numbers">
<option value="1" {^s_numberss_true^selected="selected"^}>On</option>
<option value="0" {^s_numberss_false^selected="selected"^}>Off</option>
</select> 
<label for="s_sundayfirst">Show Sunday First:</label><select name="s_sundayfirst" id="s_sundayfirst">
<option value="1" {^s_sundayfirsts_true^selected="selected"^}>On</option>
<option value="0" {^s_sundayfirsts_false^selected="selected"^}>Off</option>
</select> 
<label for="s_epnames">Show Ep Names:</label><select name="s_epnames" id="s_epnames">
<option value="1" {^s_epnamess_true^selected="selected"^}>On</option>
<option value="0" {^s_epnamess_false^selected="selected"^}>Off</option>
</select> 
<label for="s_airtimes">Show Airtime:</label><select name="s_airtimes" id="s_airtimes">
<option value="1" {^s_airtimess_true^selected="selected"^}>On</option>
<option value="0" {^s_airtimess_false^selected="selected"^}>Off</option>
</select> 
<label for="s_networks">Show Network:</label><select name="s_networks" id="s_networks">
<option value="1" {^s_networkss_true^selected="selected"^}>On</option>
<option value="0" {^s_networkss_false^selected="selected"^}>Off</option> 
</select> 
<label for="s_popups">Summary Popups:</label><select name="s_popups" id="s_popups">
<option value="1" {^s_popupss_true^selected="selected"^}>On</option> 
<option value="0" {^s_popupss_false^selected="selected"^}>Off</option>
</select> 
<label for="s_wunwatched">Show Watched Arrow:</label><select name="s_wunwatched" id="s_wunwatched">
<option value="1" {^s_wunwatchedd_true^selected="selected"^}>On</option> 
<option value="0" {^s_wunwatchedd_false^selected="selected"^}>Off</option> 
</select>
<label for="s_24hour">Time Format:</label><select name="s_24hour" id="s_24hour">
<option value="0" {^s_24hourr_false^selected="selected"^}>12 Hour</option> 
<option value="1" {^s_24hourr_true^selected="selected"^}>24 Hour</option> 
</select>
<br />
<a id="closesettingslink" href="{$closettings}">[Close Settings]</a><input type="submit" id="settingssubmit" name="settings" value="Save Settings" class="optionsbutton" /></form> 
</div> 