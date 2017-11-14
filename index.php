<?php 
// Copyright (c) kodespace.com 2017. All rights reserved.

define('OT', true); // this package

// Read config
require_once('config.php');

if (!defined('OT_BASE_PATH'))
	die('Incorrect configuration. OT_BASE_PATH not defined');
if (!defined('OT_BASE_URL'))
	die('Incorrect configuration. OT_BASE_URL not defined');
if (!defined('OT_RECAPTCHA'))
	die('Incorrect configuration. OT_RECAPTCHA not defined');

if (!defined('OT_PATH_CURRENT'))
	define('OT_PATH_CURRENT', OT_BASE_PATH . 'current/'); // path to current list of one time messages
if (!defined('OT_PATH_USED'))
	define('OT_PATH_USED', OT_BASE_PATH . 'used/'); // path to list of previously used one time messages (just stubs, the files are empty)

if (!defined('OT_EXPIRE'))
	define('OT_EXPIRE', 7*24); // in hours 
if (!defined('OT_KEY_LENGTH'))
	define('OT_KEY_LENGTH', 32); // length of our keys (aka hashes)

/*
if (!defined('OT_SECRET_STATS'))
	define('OT_SECRET_STATS', 'somerandomkey');
if (!defined('OT_SECRET_STATS_PASSWORD'))
	define('OT_SECRET_STATS_PASSWORD', 'somerandompassword');
*/


// ensure folders exist
if (!file_exists(OT_PATH_CURRENT)) {
	if (!mkdir(OT_PATH_CURRENT, 0770, true)) {
		die('Failed to create: '. OT_PATH_CURRENT);
	}
}
if (!file_exists(OT_PATH_USED)) {
	if (!mkdir(OT_PATH_USED, 0770, true)) {
		die('Failed to create: '. OT_PATH_USED);
	}
}


function randomToken($length = 32){
    $length = $length/2;// 32 bytes = 64chars after bin2hex
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
    die('Can\'t make a random token');
}



function expire($file, $expired=false) {
	// remove it & register in OT_PATH_USED
	unlink(OT_PATH_CURRENT . $file); // could do a safe delete here for security

	$msg = "";
	if ($expired)
		$msg = "Message Expired";
	else {
		$msg = "IP: ".$_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$msg .="\nX-Forwarded-For".$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	$msg .="\nTime: ".date('D, d M Y H:i:s');
	file_put_contents(OT_PATH_USED. $file, $msg);
}

function expire_messages() {

	$expired_time = new DateTime("now");
	$expired_time->sub(new DateInterval('PT'. OT_EXPIRE . 'H'));

	if ($handle = opendir(OT_PATH_CURRENT)) { // iterate all the files in the current folder
	    while (false !== ($file = readdir($handle))) {
	        if ('.' === $file) continue;
	        if ('..' === $file) continue;

	        // check the timestamp
			$filedate = new DateTime();
			$filedate->setTimestamp(filemtime(OT_PATH_CURRENT . $file));
			if ($filedate<$expired_time) {
				// expired! remove it & register in OT_PATH_USED
				expire($file, true);
			}
	    }
	    closedir($handle);
	}
}

function template($message) {
	// sanitises the message to ensure it's html safe. eg < becomes &lt;
	templateRawHtml(htmlspecialchars($message));
}
function templateRawHtml($message) {
	// uses the template, allowing unescaped HTML to be provided
	$contents = $message;
	require('template.php');
}

function calc_expiry()
{
	$expiry = new DateTime("now");
	$expiry->add(new DateInterval('PT'. OT_EXPIRE . 'H'));
	return date('D, d M Y H:i', $expiry->getTimestamp());
}

function main() {
	$id = '';
	if (isset($_GET["id"])) {
		//echo 'id is set: '. $_GET["id"];
		$id = urldecode($_GET["id"]);
	}

	$expiry_time = calc_expiry();

	if ($id!=='' && preg_match('/^[a-f0-9]{'.OT_KEY_LENGTH.'}$/i', $id)) {
		// a valid _seeming_ id

		// does it exist as a file?
		if (!file_exists(OT_PATH_CURRENT . $id)) {
			if (file_exists(OT_PATH_USED . $id)) {
				if (defined('OT_SECRET_STATS') && defined('OT_SECRET_STATS_PASSWORD') && 
						isset($_GET[OT_SECRET_STATS]) && $_GET[OT_SECRET_STATS]==OT_SECRET_STATS_PASSWORD) 
				{
					// show the contents of the expired message, if it exists
					$message = file_get_contents(OT_PATH_USED. $id);
					templateRawHtml('<p>The details of the used message are:</p><div id="message">'.$message.'</div>');
				}
				else 
				{
					// only found as an expired/used link
					http_response_code(410);
					templateRawHTML('Expired/Already Used.<br>If you believe this to be an error, please contact us immediately.<br>Nevertheless, the message no longer exists.');
				}

			}
			else {
				http_response_code(404);
				//include('my_404.php'); // provide your own HTML for the error page
				template('Not found');
			}
			die();
		}
		else
		{
			// a valid filename exists
			$message = file_get_contents(OT_PATH_CURRENT. $id);
			expire($id); // don't let it be read again!
			templateRawHtml('<p>Your unique message is:</p><div id="message">'.htmlspecialchars($message).'</div><p class="small">For security purposes, this message has already been deleted and cannot be retrieved.</p>');
		}
	}
	else if (!empty($_POST) && !empty($_POST["message"]))
	{
		$file =  randomToken(OT_KEY_LENGTH);
		$n = 0;
		while (file_exists(OT_PATH_CURRENT. $file) || file_exists(OT_PATH_USED. $file)) {
			$file =  bin2hex(random_bytes(OT_KEY_LENGTH));
			if ($n++ > 100) {
				template('Error. Can\'t generate a unique filename: '.$file);
				die();
			}
		}
		$message  = $_POST["message"];
		$email    = $_POST['email'];
		//$password = $_POST['password'];

		file_put_contents(OT_PATH_CURRENT. $file, $message);
		if (!file_exists(OT_PATH_CURRENT. $file)) {
			template('Error. Failed to save file: '.$file);
			die();
		}
		$url = OT_BASE_URL.$file ;

		$contents = 'The message<sup>*</sup> is now available as:<br><code>'.$url.'</code><br>';

		if (!empty($email)) {
			// send the email
			$email_str = "A secure message has been sent to you that you can retrieve only once. You can retrieve it from this url:\n".$url;

			$email_str .= "\n\nIt is recommended that you copy the contents of that message to a safe location as soon as possible, as it will also expire on ".$expiry_time.
					".\n\nIf this message was not expected, please disregard it.\n\nYours truly,\nOne Time Message\n(Please do not reply to this email)";
			//$email_str = str_replace('\n', '\r\n', $email_str);
			if (!mail($email, 'One Time Message', $email_str)) {
				http_response_code(501);
				templateRawHtml($contents.'<br>Unfortunately, the email could not be sent due to server configuration.<br><br>The contents of the email were:<div id="message">'.htmlspecialchars($email_str)."</div>");
				die();
			}
			$contents .= '<br>An email has been sent to: <b>'.htmlspecialchars($email).'</b>';
		}
		$contents .= '<br><br><p class="small"><sup>*</sup> Don\'t open this link, otherwise you\'ll lock out the recipient!</p>';
		templateRawHtml($contents);
	}
	else
	{
		ob_start();
		include('form.php');
		templateRawHtml(ob_get_clean());
	}
}

// do this before anyone does anything. (It's a poor-man's cron-job)
expire_messages();

main();

