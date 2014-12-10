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
		#db-created-wrapper{
			display:none;
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
					<h1>Step 1: Database Information</h1>
					<form class="lib-form" action="resources/php/install.php" method="POST">
					<input type="hidden" name="step" value="1">
					<input type="hidden" id="go" name="go">
						<div class="line">
							<div class="left span1of2 unit">
								<label for="db-host">MySQL Host</label>
								<input name="db-host" type="text" value="<?php echo $_SESSION['db-host']?>"  placeholder="localhost">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="URL for your MySQL Server. Often just localhost, but not always.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="db-user">Username</label>
								<input name="db-user" type="text" value="<?php echo $_SESSION['db-user']?>" placeholder="Username">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="MySQL username to connect to your database. Make sure it has the proper permissions!">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="db-pass">Password</label>
								<input class="pw" name="db-pass" id="db-pass" type="password" value="<?php echo $_SESSION['db-pass']?>">
								<label for="show">Show Password?</label>
								<input name="show" id="show" type="checkbox">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="Password for your MySQL user.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="db-name">Database Name</label>
								<input name="db-name" id="db-name" type="text" value="<?php echo $_SESSION['db-name']?>" placeholder="status">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="We recoommend 'status', but if you've already picked something else, put it here.">(?)</abbr>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div id="footer-wrapper">
		<div style="float:left"><h1 id="help">Hover over a (?) for a description of the field.</h1></div>
			<footer>
				<button style="margin: 0 0 0 10px"class="lib-button" type="submit" name="go" value="+1">Next &gt;</button>
			</footer>
		</div>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="resources/js/jquery.validate.js"></script>
		<script src="resources/js/scripts.js"></script>
	</body>
</html>