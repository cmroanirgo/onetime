<?php
if (!defined('OT'))
	die();

$expiry_time = calc_expiry();

?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

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

	<label for='password' class='small'>Optionally, enter a long password<sup>*</sup>:</label>
	<input name='password' type='text' class='small'/><br>
	
	<label class="small">This message will expire on: <?php echo $expiry_time;?>.</label>
	<button class="g-recaptcha" data-sitekey="<?php echo OT_RECAPTCHA; ?>" data-callback="onSubmit">Submit</button>
	<p class='small italics'><sup>*</sup>You will need to SMS your password to the recipient manually.</p>
</form>
