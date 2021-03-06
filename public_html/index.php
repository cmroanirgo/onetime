<?php 
// Copyright (c) kodespace.com 2017. All rights reserved.

define('OT', true); // this package
define('OT_SRC_PATH', '../private/includes/');

// Read config
require_once(OT_SRC_PATH.'config.php');

if (!defined('OT_BASE_PATH'))
	die('Incorrect configuration. OT_BASE_PATH not defined');
if (!defined('OT_BASE_URL'))
	die('Incorrect configuration. OT_BASE_URL not defined');

if (!defined('OT_PATH_CURRENT'))
	define('OT_PATH_CURRENT', OT_BASE_PATH . 'current/'); // path to current list of one time messages
if (!defined('OT_PATH_USED'))
	define('OT_PATH_USED', OT_BASE_PATH . 'used/'); // path to list of previously used one time messages (just stubs, the files are empty)

if (!defined('OT_EXPIRE'))
	define('OT_EXPIRE', 7*24); // in hours 
if (!defined('OT_KEY_LENGTH'))
	define('OT_KEY_LENGTH', 32); // length of our keys (aka hashes)
if (!defined('OT_MAX_PASSWORD_RETRIES'))
	define('OT_MAX_PASSWORD_RETRIES', 5); // 5 attempts before message is deleted
if (OT_MAX_PASSWORD_RETRIES<1 || OT_MAX_PASSWORD_RETRIES>9)
	die('OT_MAX_PASSWORD_RETRIES must be less than 10 and more than 0');

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

function templateSimple($message, $data=[]) {
	// sanitises the message to ensure it's html safe. eg < becomes &lt;
	templateHtml(htmlspecialchars($message), $data);
}
function templateLoad($file, $data=[]) {
	ob_start();
	include(OT_SRC_PATH.'views/'.$file);
	templateHtml(ob_get_clean(), $data);
}
function templateHtml($contents, $data=[]) { // NB: This parameter's name is important. It is used in the template itself
	// uses the template, allowing unescaped HTML to be provided
	require(OT_SRC_PATH.'views/template.php');
}
function calc_expiry() {
	$expiry = new DateTime("now");
	$expiry->add(new DateInterval('PT'. OT_EXPIRE . 'H'));
	return date('D, d M Y H:i', $expiry->getTimestamp());
}

// hmac_xxx() from https://paragonie.com/blog/2015/05/using-encryption-and-authentication-correctly
// we only really need message tampering validation
if(!function_exists('hash_equals')) {
  function hash_equals($str1, $str2) {
    if(strlen($str1) != strlen($str2)) {
      return false;
    } else {
      $res = $str1 ^ $str2;
      $ret = 0;
      for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
      return !$ret;
    }
  }
}

function hmac_sign($message, $key)
{
    return hash_hmac('sha256', $message, $key) . $message;
}
function hmac_verify($bundle, $key)
{
    $msgMAC = mb_substr($bundle, 0, 64, '8bit');
    $message = mb_substr($bundle, 64, null, '8bit');
    return hash_equals(
        hash_hmac('sha256', $message, $key),
        $msgMAC
    );
}


function main() {
	$id = '';
	if (isset($_GET["id"])) {
		//echo 'id is set: '. $_GET["id"];
		$id = urldecode($_GET["id"]);
	}


	if ($id!=='' && preg_match('/^[a-f0-9]{'.OT_KEY_LENGTH.'}$/i', $id)) {
		// a valid _seeming_ id
		$password = '';
		if (isset($_GET["pwd"])) { // we have/need a password
			if (!empty($_POST) && !empty($_POST["password"])) {
				$password = $_POST["password"];
				// continue loading
			}
			else {
				templateLoad('password.php');
				die();
			}
		}

		// does it exist as a file?
		if (!file_exists(OT_PATH_CURRENT . $id)) {
			if (file_exists(OT_PATH_USED . $id)) {
				if (defined('OT_SECRET_STATS') && defined('OT_SECRET_STATS_PASSWORD') && 
						isset($_GET[OT_SECRET_STATS]) && $_GET[OT_SECRET_STATS]==OT_SECRET_STATS_PASSWORD) 
				{
					// show the contents of the expired message, if it exists
					$message = file_get_contents(OT_PATH_USED. $id);
					templateHtml('<p>The details of the used message are:</p><div id="message">'.$message.'</div>');
				}
				else 
				{
					// only found as an expired/used link
					http_response_code(410);
					templateHtml('Expired/Already Used.<br>If you believe this to be an error, please contact us immediately.<br>Nevertheless, the message no longer exists.');
				}

			}
			else {
				http_response_code(404);
				templateSimple('Not found');
			}
			die();
		}
		else
		{
			// a valid filename exists
			$message = file_get_contents(OT_PATH_CURRENT. $id);

			if (!empty($password)) {
				// the message contains:
				//  - how many attempts, 1st char
				//  - hmac sig
				//  - message
				$attempts = intval(substr($message, 0, 1));
				$message = substr($message, 1);
				if (!hmac_verify($message, $password)) {
					$attempts++;
					if ($attempts > OT_MAX_PASSWORD_RETRIES) {
						expire($id); // don't let it be read again!			
						http_response_code(410);
						templateHtml('Too many wrong tries. Message has been deleted.<br>Your IP has been recorded.');
						die();
					}
					// write the attempt out to file again & reshow the password box
					$message = $attempts.$message;
					file_put_contents(OT_PATH_CURRENT. $id, $message);
					templateLoad('password.php');
					die();
				}
				else {
					// all is good. verification of password ok.
				    $message = mb_substr($message, 64, null, '8bit');
				}
			}

			expire($id); // don't let it be read again!
			templateHtml('<p>Your unique message is:</p><div id="message">'.htmlspecialchars($message).'</div><p class="small">For security purposes, this message has already been deleted and cannot be retrieved.</p>');
		}
	}
	else {

		session_start();
		if (!isset($_SESSION['csrf_token']))
	    	$_SESSION['csrf_token'] = randomToken(); // make a new csrf token

		//echo "\nPOST CSRF".$_POST["csrf_token"];
		//echo "\nSESS CSRF".$_SESSION["csrf_token"];
		if (!empty($_POST) && !empty($_POST["message"]) && !empty($_POST["csrf_token"]) && !empty($_SESSION["csrf_token"]) && $_POST["csrf_token"]==$_SESSION['csrf_token'])
		{
			//reset the csrf session token.
			unset($_SESSION['csrf_token']); // force a new session token once a message sent. Stops Refresh errors

			$file =  randomToken(OT_KEY_LENGTH);
			$n = 0;
			while (file_exists(OT_PATH_CURRENT. $file) || file_exists(OT_PATH_USED. $file)) {
				$file =  bin2hex(random_bytes(OT_KEY_LENGTH));
				if ($n++ > 100) {
					templateSimple('Error. Can\'t generate a unique filename: '.$file);
					die();
				}
			}
			$message  = $_POST["message"];
			$email    = $_POST['email'];
			$password = $_POST['password'];
			unset($_POST);

			if (!empty($password)) {
				// the message contains:
				//  - how many attempts, 1st char. set to 0
				//  - hmac sig
				//  - message
				$message = '0'.hmac_sign($message, $password); 
			}

			file_put_contents(OT_PATH_CURRENT. $file, $message);
			if (!file_exists(OT_PATH_CURRENT. $file)) {
				templateSimple('Error. Failed to save file: '.$file);
				die();
			}
			$url = OT_BASE_URL.$file ;
			if (!empty($password))
			{
				$url .= "&pwd=1";
			}

			$contents = 'The message is now available as:<br><code class="small">'.$url.'</code> <sup class="small">*</sup><br>';
			if (!empty($password)) {
				$contents .= '<br><span class="small">The contents of this message are protected by the password:<br><code>'.$password.'</code></span><br>';
			}

			if (!empty($email)) {
				// send the email
				$expiry_time = calc_expiry();
				$email_str = "A secure message has been sent to you that you can retrieve only once & will be deleted once you've read it. You can retrieve it from this url:\n".$url;
				if (!empty($password)) {
					$email_str .= "\nNote that the message requires a password to open. You should know it, or have received it by the person who sent you this message.";				
				}

				$email_str .= "\n\nYou should read the message as soon as possible, as it will expire on ".$expiry_time.
						".\n\nIf this message was not expected, please disregard it.\n\nYours truly,\nOne Time Message System\n(Please do not reply to this email)";
				//$email_str = str_replace('\n', '\r\n', $email_str);
				$headers = '';
				if (defined('OT_EMAIL_SENDER')) {
					$headers = 'From: '. OT_EMAIL_SENDER;
					if (defined('OT_EMAIL_REPLY_TO'))
					 	$headers .= "\r\nReply-To: ". OT_EMAIL_REPLY_TO;
					else
					 	$headers .= "\r\nReply-To: ". OT_EMAIL_SENDER;
				}
				if (!mail($email, 'One Time Message', $email_str, $headers)) {
					http_response_code(501);
					templateHtml($contents.'<br>Unfortunately, the email could not be sent due to server configuration.');
					die();
				}
				$contents .= '<br>An email has been sent to: <b>'.htmlspecialchars($email).'</b>';
			}
			$contents .= '<br><br><p class="small"><sup>*</sup> Don\'t open this link, otherwise you\'ll lock out the recipient!</p>';
			templateHtml($contents);
		}
		else
		{
			$errors = '';
			if (!empty($_POST)) {
				if (empty($_POST["message"]))
					$errors .= "Please fill out a message!<br>\n";
				//if (empty($_POST["csrf_token"]))
				//	$errors .= "Missing CSRF Token<br>\n";
				//if (empty($_SESSION["csrf_token"]))
				//	$errors .= "Missing CSRF Session. Did you hit refresh?<br>\n";
				//if (!empty($_POST["csrf_token"]) && !empty($_SESSION["csrf_token"]) && $_POST["csrf_token"]!=$_SESSION['csrf_token'])
				//	$errors .= "CSRF Token mismatch!: <br>". $_POST["csrf_token"] ." vs ". $_SESSION["csrf_token"]."<br>\n";
			} 
		    unset($_POST);
		    $data["errors"] = $errors;
			templateLoad('form.php', $data);
		}
	}
}

// do this before anyone does anything. (It's a poor-man's cron-job)
expire_messages();

main();

