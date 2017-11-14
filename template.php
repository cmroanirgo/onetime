<?php
if (!defined('OT'))
	die();

?>
<!DOCTYPE html>
<html class="no-js">
<head>
	<meta charset="utf-8">
	<style type="text/css">
	* { margin:0; padding:0; box-sizing:border-box;}
	html,body{height:100%;}
	body { min-height:100%;font-size:18pt; font-family: sans-serif,sans,Arial;}
	#outer {
		min-height:100%;
		display: -webkit-box;
		display: -webkit-flex;
		display: flex;
		-webkit-box-orient: vertical;
		-webkit-box-direction: normal;
		-webkit-flex-direction: column;
		flex-direction: column;
	}
	#outer>div { padding: 2em 20%; word-spacing: nowrap}
	#banner,#footer { background-color: #5690f5; color:white; }
	#banner { height:6em; }
	#footer { height:2em; 
			padding-top: 0.5em; 
			padding-bottom:0; 
			font-size:9pt;}

	#contents { 
		-webkit-box-flex: 1;
		-webkit-flex: 1;
		flex: 1;
	}
	#message {
		background-color: #e0e0e0;
		border:1px solid rgba(0,0,0,0.1);
		padding: 0.8em;
		margin:0.2em 0;
		border-bottom-right-radius: 0.8em;
		border-top-right-radius: 0.8em;
		border-bottom-left-radius: 0.8em;
		white-space: pre-wrap;
	}
	.small { font-size: 0.6em;}

	form { position: relative; width: 100%; }
	form * { display: block; font-size: 0.8em; }
	textarea { width: 80%; height:6em; display: block; font-size: 0.8em;padding:0.2em;font-family: sans-serif;}
	
	label[for='email'] { margin-top: 1em; }
	input[type='email'] { padding: 0 0.2em; width:80%;}

	label[for='password'] { margin-top: 1em; font-size:0.6em;}
	input[type='password'] { padding: 0 0.3em; font-size:0.6em; width:80%;}
	input[type='submit'],button { padding: 0.3em; font-size: 1em; margin-top:1em;}

	@media only screen and (max-width: 760px) {
		body { font-size: 15pt; }
		#outer>div { padding: 2em 12% };
	}


	@media only screen and (max-width: 550px) {
		body { font-size: 13pt; }
		#outer>div { padding: 2em 4% };
	}
	</style>
</head>
<body><div id="outer">
	<div id="banner">
		<h2>One Time Messages</h2>
		<i>For the security conscious</i>
	</div>
	<div id="contents">
		<?php echo $contents ?>
	</div>
	<div id="footer">Copyright &copy; kodespace.com 2017. All rights reserved</div>
</div>
</body>
</html>