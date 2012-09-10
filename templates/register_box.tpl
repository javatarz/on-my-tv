<div id="register" class="optioonsarea" {^hidden^style="display: none;"^}>
<p>{$errormessage}</p>
<form action="{$request_uri}" id="register_form" method="post">
<label for="regemail">Enter your e-mail :</label><input id="regemail" name="email" type="text" value="{$email}" />
<label for="regpassword">Enter new password :</label><input id="regpassword" name="password" type="password" />
<label for="regpasswordconf">Enter password conf :</label><input id="regpasswordconf" name="passwordconf" type="password" />
<a id="closeregisterlink" href="{$closeregister}">[Close]</a><input type="submit" id="submit_register" name="submit_register" class="optionsbutton" value="Register Account" />
</form> 
</div>