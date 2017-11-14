<?php
if (!defined('OT'))
	die();

?>
<form method="POST">
	<label for='message'>Enter your one time message:</label>
	<textarea name='message'></textarea>

	<label for='email'>E-mail address of the recipient:</label>
	<input name='email' type='email'/><br>

	<!--<label for='password'>Optionally, enter a password to encrypt the message:</label>
	<input name='password' type='password'/><br>-->

	<input type="submit" value="Submit"/>
</form>
