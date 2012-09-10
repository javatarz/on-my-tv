<div id="login" class="optioonsarea" {^hidden^style="display: none;"^}>
<p>{$errormessage}</p>
<form action="{$request_uri}" id="login_form" method="post">
<label for="logemail">Enter your e-mail :</label><input id="logemail" name="username" type="text" />
<label for="logpword">Enter your password :</label><input id="logpword" name="password" type="password" />
<a id="forgottenpasswordlink" href="/forgotten-password">[PW Reset]</a><input type="submit" id="sub_login" name="sub_login" class="optionsbutton" value="Account Login" />
<a id="closeloginlink" href="{$closelogin}">[Close]</a>
</form> 
</div>