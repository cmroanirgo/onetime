<?php
if (!defined('OT'))
	die('Bad config');

define('OT_BASE_PATH', '/home/onetime-user/private/'); // trailing slash important! Put this beside your public_html folder
define('OT_BASE_URL',  'https://somewhere.com/'); // trailing slash important!

// The definitions here are the default values. Uncomment and change them if you need:
//define('OT_KEEP_USED', true); // true/false, should the id of used messages be kept (along with the IP and time they were read). False == nothing is kept, but *rare* potential for collision
//define('OT_RECAPTCHA', '<some-key>'); // automatic use of reCaptcha on the main screen
//define('OT_EXPIRE', 7*24); // link exists for 7 days, in hours
//define('OT_KEY_LENGTH', 32); // use multiples of 2!
//define('OT_PATH_CURRENT', OT_BASE_PATH . 'current/');
//define('OT_PATH_USED', OT_BASE_PATH . 'used/');
//define('OT_MAX_PASSWORD_RETRIES', 5); // After this may tries, the message is deleted. For safety. Keep in range 1..10


//  Allow 'secret' access to *why* a message was used (ie expired, or read). It will list the IP and time of the message (the message itself is goooone)
//   By default, this is completely disabled.
//  USAGE:
//		eg. http://onetime.dev/32abe828b5fec3df0e716875&letmein=<pwd>
//define('OT_SECRET_STATS', 'letmein');
//define('OT_SECRET_STATS_PASSWORD', 'do not use this as a password');


/*
Enable these for debugging only.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
