<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 
'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'><head>
<title>CAT - Show Selector - Selection of House, Lost, 24, Eureka, Entourage, Stargate, Atlantis, Las Vegas, Boston Legal and more</title>  
<meta name="keywords" content="calendar, tv calendar, tv cat, tv listings, pog design, pogdesign, maz, pog, House, Lost, 24, Eureka, Entourage, Stargate, Atlantis, Las Vegas" />
<meta name="description" content="CAT - Calendar for TV" />
<meta name="Robots" content="indexall,followall" />
<meta name="revisit" content="7 days" /> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="{$sitebase}/mmccaatt.css" type="text/css" title="default" media="all" />
<link rel="stylesheet" href="{$stylename}" type="text/css" title="default" media="all" />
</head>

<body>

<div style="text-align:center"><h2>Settings</h2>
<div class="optioonsarea" id="optionsarea"><form action="{$request_uri}" method="post">
Timezone: <select name="timezone">
{%TZLOOP%}<option value="{$tzid}" {^iscurtz^selected="selected"^}>{$tzname}</option>{%TZLOOP%}
</select><br />
Style: <select name="style">
{%STYLOOP%}<option value="{$styid}" {^iscursty^selected="selected"^}>{$styname}</option>{%STYLOOP%}
</select><br />
Show Ep Numbers: <select name="s_numbers">
<option value="1" {^s_numberss_true^selected="selected"^}>On</option>
<option value="0" {^s_numberss_false^selected="selected"^}>Off</option>
</select><br />
Show Ep Names (big screens only): <select name="s_epnames">
<option value="1" {^s_epnamess_true^selected="selected"^}>On</option>
<option value="0" {^s_epnamess_false^selected="selected"^}>Off</option>
</select><br />
Show Airtime: <select name="s_airtimes">
<option value="1" {^s_airtimess_true^selected="selected"^}>On</option>
<option value="0" {^s_airtimess_false^selected="selected"^}>Off</option>
</select><br />
Show Network: <select name="s_networks">
<option value="1" {^s_networkss_true^selected="selected"^}>On</option>
<option value="0" {^s_networkss_false^selected="selected"^}>Off</option>
</select><br />
Popups: <select name="s_popups">
<option value="1" {^s_popupss_true^selected="selected"^}>On</option>
<option value="0" {^s_popupss_false^selected="selected"^}>Off</option>
</select><br />
Show Watched Indicator: <select name="s_wunwatched">
<option value="1" {^s_wunwatchedd_true^selected="selected"^}>On</option>
<option value="0" {^s_wunwatchedd_false^selected="selected"^}>Off</option>
</select><br />
<input type="submit" name="settings" value="Save Settings" class="optionsbutton" /></form>
</div><br />
</div>
</body>
</html>