<?php
if (!defined('OT'))
	die('Bad config');

define('OT_BASE_PATH', '/home/craigm/sites/onetime.dev/private/'); // trailing slash important!
//define('OT_EXPIRE', 7*24); // link exists for 7 days, in hours
define('OT_KEY_LENGTH', 24);

/*
Access the how/why a message was closed by adding '&letme=<pwd>' to a key:
eg. http://onetime.dev/32abe828b5fec3df0e716875&letmein=<pwd>

By default, this functionality is completely disabled.
if (!defined('OT_SECRET_STATS'))
	define('OT_SECRET_STATS', 'letmein');
if (!defined('OT_SECRET_STATS_PASSWORD'))
	define('OT_SECRET_STATS_PASSWORD', 'do not use this as a password');
*/
