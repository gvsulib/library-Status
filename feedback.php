<?php
session_start();

date_default_timezone_set('America/Detroit');
// Include additional libraries that make this work
require 'resources/secret/config.php';
require 'resources/php/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?php echo $library_name; ?> Status</title>

	<style>
	@font-face {
	    font-family: 'AlternateGothicFSNo3';
	    src: url('//gvsuliblabs.com/libs/fonts/AlternateGothicNo3-webfont.eot');
	    src: url('//gvsuliblabs.com/libs/fonts/AlternateGothicNo3-webfont.eot?#iefix') format('embedded-opentype'),
	         url('//gvsuliblabs.com/libs/fonts/AlternateGothicNo3-webfont.woff') format('woff'),
	         url('//gvsuliblabs.com/libs/fonts/AlternateGothicNo3-webfont.ttf') format('truetype'),
	         url('//gvsuliblabs.com/libs/fonts/AlternateGothicNo3-webfont.svg#AlternateGothicFSNo3') format('svg');
	    font-weight: normal;
	    font-style: normal;

	}
	</style>
	<link rel="stylesheet" type="text/css" href="resources/css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="resources/css/layout.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">

<body>

	<div id="header-wrapper" style="<?php echo 'background-color:' . $banner_color . ';'; ?>">
		<div id="header">
			<div id="logo">
				<a href="<?php echo $header_url; ?>">
					<img src="<?php echo $header_image; ?>" alt="<?php echo $library_name; ?>" border="0">
				</a>
			</div>
		</div>
	</div>

	<div class="line break">
		<div class="span2of3 unit left">
			<h2><a href="index.php"><?php echo $library_name; ?> Status</a></h2>
		</div> <!-- end span -->
	</div> <!-- end line -->

	<div class="lib-form" style="margin-top: 1em; max-width: 90%; margin: 0 auto;">
		<form method="post" action="index.php">

			<div class="span2 unit left">
				<label for="name">Your Name:</label>
				<input type="text" name="name" id="name" placeholder="Optional" />
			</div>
			
			<div class="span2 unit left">
				<label for="name">Your Email:</label>
				<input type="text" name="email" id="email" placeholder="Optional" />
			</div>

			<label for="feedback">Have an idea? See a problem?</label>
			<textarea name="feedback"></textarea>
			<input type="text" name="bot-check" style="display:none;" />

			<div class="right">
				<a href="index.php" style="display: inline-block; margin-right: 2em;">Cancel</a>
				<input class="lib-button" type="submit" name="problem-report" value="Send Feedback" style="margin-top: 1em;" />
			</div>

		</form>
	</div>

</body>

</html>