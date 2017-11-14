<?php
if (!defined('OT'))
	die();
?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
       function onSubmit(token) {
         document.getElementById("message-form").submit();
       }
 </script>

<form id='message-form' method="POST">

	<label for='password'>Enter the password to open the message:</label>
	<input name='password' type='password'/><br>

	<button class="g-recaptcha" data-sitekey="<?php echo OT_RECAPTCHA; ?>" data-callback="onSubmit">Submit</button>
</form>
