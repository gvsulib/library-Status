<?php
session_start();
error_reporting(0);
	require 'resources/secret/config.php';
	require 'resources/php/functions.php';
	require 'resources/php/markdown.php';
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	if(isset($_SESSION['username'])) { // User has logged in
		if (isset($_REQUEST['logout'])) {
			$_SESSION = array();
			session_destroy();
		}
		header('Location: index.php');	
	}
	if ($_POST){
		$formUsername = $_POST['username'];
		$formPW = sha1($_POST['password']);
		$user_result=$db->query("SELECT * FROM user WHERE user_username = '$formUsername' AND password = '$formPW' LIMIT 1");

		if(($user_result) && ($user_result->num_rows > 0)) {
			$_SESSION['username'] = $formUsername;
			header('Location: index.php');
		} else {
			$m = "Your username or password was incorrect.<br> Please try again.";
		}

	}
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

</head>
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
		<div id="wrapper">

	<div class="line break">
		<div class="span2 unit left">
			<h1>Login to <?php echo $library_name;?> Status</h1>
			<?php
			if(isset($m)) {
			echo '<div class="lib-error">' . $m . '</div>';
			}
			?>
			<form class="lib-form" action="login.php" method="POST">
				<div class="line">
					<div class="left span2 unit">
						<label for="username">Username</label>
						<input name="username" type="text"/>
					</div>
				</div>
				<div class="line">
					<div class="left span2 unit">
						<label for="password">Password</label>
						<input name="password" type="password"/>
					</div>
				</div>
				<div class="line">
					<div class="left span2 unit" style="padding-top: 10px">
						<input class="lib-button-small" name="post" type="submit" value="Login" />
					</div>
				</div>

		</div> <!-- end span -->
	</div> <!-- end line -->
	</div>
	</body>
</html>