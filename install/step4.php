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
		#login-native-login-wrapper{
			display:none;
		}
		#login-non-native-login-wrapper{
			display: none;
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
					<h1>Step 4: Login Options</h1>
					<form class="lib-form" action="resources/php/install.php" method="POST">
						<input type="hidden" name="step" value="4">
						<input type="hidden" id="go" name="go">
						<div class="line">
							<div class="left span1of2 unit">
								<label for="login-native-login">Use Native Login?</label>
								<input name="login-native-login" id="y"type="radio" value="true" <?php echo $_SESSION['login-native-login'] == 'true' ? 'checked' : '';?>>
								<label for="y" class="lib-inline">Yes</label>
								<input name="login-native-login" id="n"type="radio" value="false" <?php echo $_SESSION['login-native-login'] == 'false' ? 'checked' : '';?>>
								<label for="n" class="lib-inline">No</label>

								<abbr style="padding-top: 0;"title="Use native login or not.">(?)</abbr>
							</div>
						</div>	
						<div id="login-non-native-login-wrapper">
							<div class="line">
								<div class="left span1of2 unit">
									<label for="login-non-native-url">Non Native Login URL</label>
									<input name="login-non-native-url" type="text" value="<?php echo $_SESSION['login-non-native-url']?>"/>
								</div>
								<div class="left span2of2 unit">
									<abbr data-text="URL to redirect to the non-native login system of your choosing. Be sure that your login system sets a $_SESSION['username'] variable. This is how the app knows whether you are logged in or not.">(?)</abbr>
								</div>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="native-user-fname">First Name</label>
								<input name="native-user-fname" type="text" value="<?php echo $_SESSION['native-user-fname']?>"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="First name of the ddefault user.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="native-user-lname">Last Name</label>
								<input name="native-user-lname" type="text" value="<?php echo $_SESSION['native-user-lname']?>"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="Last name of the ddefault user.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="native-user-email">Email Address</label>
								<input name="native-user-email" type="text" value="<?php echo $_SESSION['native-user-email']?>"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="Email address of the ddefault user.">(?)</abbr>
							</div>
						</div>
						<div class="line">
							<div class="left span1of2 unit">
								<label for="native-user-username">Username</label>
								<input name="native-user-username" type="text" value="<?php echo $_SESSION['native-user-username']?>"/>
							</div>
							<div class="left span2of2 unit">
								<abbr data-text="Username of the ddefault user.">(?)</abbr>
							</div>
						</div>
						<div id="login-native-login-wrapper">
							<div class="line">
								<div class="left span1of2 unit">
									<label for="native-user-password">Password</label>
									<input class="pw" name="native-user-password" id="native-user-password" type="password" value="<?php echo $_SESSION['native-user-password']?>"/>
									<label for="show">Show Password?</label>
									<input name="show" id="show" type="checkbox">
								</div>
								<div class="left span2of2 unit">
									<abbr data-text="Password of the ddefault user.">(?)</abbr>
								</div>
							</div>
							<div class="line">
								<div class="left span1of2 unit">
									<label for="native-user-password-c">Confirm Password</label>
									<input class="pw" name="native-user-password-c" id="native-user-password-c" type="password" value="<?php echo $_SESSION['native-user-password-c']?>"/>
								</div>
								<div class="left span2of2 unit">
									<abbr data-text="Enter the same password as above.">(?)</abbr>
								</div>
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
		<script>
		jQuery(document).ready(function(){
			updateLoginForm();
			jQuery('input:radio').click(updateLoginForm)
		});
		jQuery('form').validate({
			rules: {
				'login-non-native-url': {
					required: function(){
						return jQuery('input:radio:checked').val() == 'false';
					}
				},
				'native-user-password' : {
					required: function(){
						return jQuery('input:radio:checked').val() == 'true';
					}
				},
				'native-user-password-c': {
					required: function(){
						return jQuery('input:radio:checked').val() == 'true';
					},
					equalTo: '#native-user-password'
				}
			}
		})
		</script>
	</body>
</html>