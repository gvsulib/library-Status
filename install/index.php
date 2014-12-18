<?php
if (file_exists("../reosources/secret/config.php")){
	header('location: ../index.php');
}
session_start(); $_SESSION = array(); session_destroy();?>
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
	<link rel="stylesheet" type="text/css" href="../resources/css/styles.css"/>
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
					<h1>Welcome!</h1>
					<p>This wizard will guide you through the installation of the Library Status web app.</p>
				</div> <!-- end span -->
			</div> <!-- end line -->
		</div>
		<div id="footer-wrapper">
			<footer>
				<a style="margin: 0 0 0 10px" class="lib-button" href="step1.php">Next &gt;</a>
			</footer>
		</div>
	</body>
</html>