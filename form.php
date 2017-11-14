<?php
if (!defined('OT'))
	die();
?>
<script>
       function onSubmit(token) {
         document.getElementById("message-form").submit();
       }
 </script>

<form id='message-form' method="POST">

	<label for='message'>Enter your one time message:</label>
	<textarea name='message'></textarea>

	<label for='email'>E-mail address of the recipient:</label>
	<input name='email' type='email'/><br>

	<!--<label for='password'>Optionally, enter a password to encrypt the message:</label>
	<input name='password' type='password'/><br>-->
	<p class="small">This message will expire on: <?php echo $expiry_time;?>.</p>
	<button class="g-recaptcha" data-sitekey="6Lc4iCoUAAAAABd8KZGKZOaEiBjjfYu9EeoQKymL" data-callback="onSubmit">Submit</button>
</form>
