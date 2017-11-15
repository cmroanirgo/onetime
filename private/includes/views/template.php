<?php
if (!defined('OT'))
	die();

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,maximum-scale=2.0" />
	<title>One Time Messages - Secure, easy to use messages for one time use only</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body><div id="outer">
	<div id="banner">
		<h2><a href="<?php echo OT_BASE_URL;?>">One Time Messages</a></h2>
		<i>For the security conscious</i>
	</div>
	<div id="contents">
		<?php echo $contents ?>
	</div>
	<div id="footer">Copyright &copy; <a href="https://kodespace.com">kodespace.com</a> 2017. All rights reserved. <a href="https://github.com/cmroanirgo/onetime" ><img src="github12.png" alt="source on github"/></a></div>
</div>
</body>
</html>