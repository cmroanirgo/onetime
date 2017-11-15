<?php
if (!defined('OT'))
	die();
?>
<style>
form p { margin-bottom: 1em; font-size:0.7em;}
</style>
<form id='message-form' method="POST">

	<p>Your message was protected with a password, which you should have received seperately.</p>
	<label for='password'>
		Enter the password to open the message:</label>
	<input name='password' type='password'/><br>

	<input type="submit" value="Submit" />
</form>
