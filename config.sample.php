<?php
if (!defined('OT'))
	die('Bad config');

define('OT_BASE_PATH', '/home/onetime-user/private/'); // trailing slash important! Put this beside your public_html folder
define('OT_BASE_URL',  'https://somewhere.com/'); // trailing slash important!
define('OT_RECAPTCHA', '<some-key>');
//define('OT_EXPIRE', 7*24); // link exists for 7 days, in hours
define('OT_KEY_LENGTH', 24);

/*
//Access the how/why a message was closed by adding '&letme=<pwd>' to a key:
//eg. http://onetime.dev/32abe828b5fec3df0e716875&letmein=<pwd>

//By default, this functionality is completely disabled.
if (!defined('OT_SECRET_STATS'))
	define('OT_SECRET_STATS', 'letmein');
if (!defined('OT_SECRET_STATS_PASSWORD'))
	define('OT_SECRET_STATS_PASSWORD', 'do not use this as a password');
*/

/*
Enable these for debugging only.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
