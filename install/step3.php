<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Library Status Installation</title>
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
	<link rel="stylesheet" type="text/css" href="../resources/css/styles.css">
	<link rel="stylesheet" type="text/css" href="../resources/css/layout.css">
	<link rel="stylesheet" type="text/css" href="resources/css/install.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
	<body>
		<div id="header-wrapper" style="background-color: #333">
			<div id="header">
				<div id="logo">
					<h1 style="font-size: 40px; color: #fff">Library Status Installation</h1>
				</div>
			</div>
		</div>
		<div id="wrapper">
			<div class="line break">
				<div class="left">
					<h1>Step 3: Email and Subscriptions</h1>
					<form class="lib-form" action="resources/php/install.php" method="POST">
						<input type="hidden" name="step" value="3">
						<input type="hidden" id="go" name="go">
						<div class="line">
							<div class="left span1of2 unit">
								<label for="rss-url">RSS URL</label>
								<input name="rss-url" type="text" value="<?php echo $_SESSION['rss-url']?>" placeholder="/rss">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The URL of the RSS feed folks will subscribe to. The Library status app has it's own RSS feed, but you may want to route that through something like feedburner to gather stats.">(?)</abbr>
							</div>
						</div>	
						<div class="line">
							<div class="left span1of2 unit">
								<label for="email-url">Email Substcription URL</label>
								<input name="email-url" type="text" value="<?php echo $_SESSION['email-url']?>"placeholder="http://feeds.feedburner.com/xxxx/xxxxx/">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The URL for folks to subscribe via email. We use feedburner for this service.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="email-to">To Email(s)</label>
								<input name="email-to" type="text" value="<?php echo $_SESSION['email-to']?>" placeholder="email@domain.com"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The email you'd like the problem reports to be sent to. (We have ours go to Asana, our project management app.) If you want them to go to multiple emails, separate them with commas.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="email-from">From Email</label>
								<input name="email-from" type="text" value="<?php echo $_SESSION['email-from']?>" placeholder="email@domain.com"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The email address your problem reports will be sent from.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="email-subject">Email Subject</label>
								<input name="email-subject" type="text" value="<?php echo $_SESSION['email-subject']?>" placeholder="New Issue in Your Library"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The subject line of your problem report email address.">(?)</abbr>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div id="footer-wrapper">
			<div style="float:left"><h1 id="help">Hover over a (?) for a description of the field.</h1></div>
			<footer>
				<button style="margin: 0 0 10px 0;"class="lib-button " type="submit" name="go" value="-1"> &lt; Previous</button>
				<button style="margin: 0 0 0 10px"class="lib-button" type="submit" name="go" value="+1">Next &gt;</button>
			</footer>
		</div>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="resources/js/jquery.validate.js"></script>
		<script src="resources/js/scripts.js"></script>
	</body>
</html>