<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Library Status Installation</title>

	<link rel="stylesheet" type="text/css" href="../resources/css/styles.css">
	<link rel="stylesheet" type="text/css" href="../resources/css/layout.css">
	<link rel="stylesheet" type="text/css" href="resources/css/install.css">
	<link rel="stylesheet" type="text/css" href="resources/css/spectrum.css">
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
		.sp-replacer{
			display: block;
		}
		.sp-dd{
			float: right;
		}
		.sp-preview{
			width: 80%;
		}
	</style>
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
					<h1>Step 2: Library Information</h1>
					<form class="lib-form" action="resources/php/install.php" method="POST">
						<input type="hidden" name="step" value="2">
						<input type="hidden" id="go" name="go">
						<div class="line">
							<div class="left span1of2 unit">
								<label for="lib-name">Library Name</label>
								<input name="lib-name" type="text" value="<?php echo $_SESSION['lib-name']?>" placeholder="Your Awesome Library">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="This is the name of your library, as it will display at the top of the app (and in the &lt;title&gt; element).">(?)</abbr>
							</div>
						</div>	
						<div class="line">
							<div class="left span1of2 unit">
								<label for="lib-image">Header Image</label>
								<input name="lib-image" type="text" value="<?php echo $_SESSION['lib-image']?>" placeholder="resources/img/">
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The URL of the logo you'd like to appear in the upper left hand corner. This can be an absolute path, or a relative one. This should be a 255x75 image file, preferably PNG.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="lib-image-link">Header LinK</label>
								<input name="lib-image-link" type="text" value="<?php echo $_SESSION['lib-image-link']?>"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The URL you'd like the logo to link to.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="lib-header-hex">Header Color</label>
								<input id="lib-header-hex" name="lib-header-hex" type="text" value="<?php echo $_SESSION['lib-header-hex']?>"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="The hex value of the background color you'd like the banner to be.">(?)</abbr>
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
		<script src="resources/js/spectrum.js"></script>
		<script src="resources/js/scripts.js"></script>
		<script>
		jQuery(document).ready(function(){
			jQuery('#lib-header-hex').spectrum({
				preferredFormat: 'hex',
				showInput: true,
				clickoutFiresChange: true
			});
		});
		</script>
	</body>
</html>