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
					<h1>Step 5: Set Up Your Systems</h1>
					<form class="lib-form" action="resources/php/install.php" method="POST">
						<input type="hidden" name="step" value="5">
						<input type="hidden" id="go" name="go">
						<?php for ($i = 0; $i < 10; $i++){ ?>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="system-name[<?php echo $i; ?>]">System Name</label>
								<input class="not" name="system-name[<?php echo $i; ?>]" type="text" value="<?php echo $_SESSION['system-name'][$i];?>" placeholder="System">
							</div>
							<div class="left span2of2 unit" style="padding-left: 20px">
								<label for="system-URL[<?php echo $i;?>]">System URL</label>
								<input class="not" name="system-URL[<?php echo $i;?>]" type="text" value="<?php echo $_SESSION['system-URL'][$i];?>" placeholder="https://yourdomain.edu/system">
							</div>
						</div>	
						<?php } ?>
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
		<script>
		jQuery(document).ready(function(){
			jQuery('.required').removeClass('.required');
		});
		</script>
	</body>
</html>