<?php
if (!file_exists('resources/secret/config.php')){header('location: install/index.php');}

session_start();
error_reporting(0);

$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	$_SESSION['location'] = $actual_url;
	
	date_default_timezone_set('America/Detroit');
	$logged_in = 0; // By default, user is logged out
	$m = NULL; // By default, there are no messages

	// Are you logged in?

	// Debug the user login by a force login
	//$_SESSION['username'] = 'bloomj';

	// Include additional libraries that make this work
	require 'resources/secret/config.php';
	require 'resources/php/functions.php';
	require 'resources/php/markdown.php';

	if(isset($_GET['login']) && !(isset($_SESSION['username']))) { // No $_SESSION['username'] variable, send to login script

		// User has not logged in
		if ($use_native_login == true){
			header('Location: login.php');
		} else {
			header('Location: ' . $non_native_login_url);
		}

	}

	

	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	if(isset($_SESSION['username'])) { // User has logged in

		if (isset($_REQUEST['logout'])) {
			$_SESSION = array();
			session_destroy();
			header('Location: index.php');
		}

		$username = $_SESSION['username'];
		// User names are unique, so only need a single row
		// Get all the bits from the user name so you don't have to ask again
		$user_result=$db->query("SELECT * FROM user WHERE user_username = '$username' LIMIT 1");

		if(($user_result) && ($user_result->num_rows > 0)) { // Query was successful, a user was found


			while($row = $user_result->fetch_assoc()) {
				$user_access = $row["user_access"];
				$user_id = $row["user_id"];
				$user_fn = $row["user_fn"];
			}

			$logged_in = 1;

			// Create the user object as $user.
			// User id is then $loggedin_user->user_id
			$loggedin_user = $user_result->fetch_object();

			// new issue post
			if (isset($_POST['submit_issue'])) {

				$issue_text = $db->real_escape_string($_POST['issue_text']);
				$system_id = $_POST['system_id'];
				$status_type_id = $_POST['status_type_id'];
					

				// Create a time one year back to see use to check if posting time is in range.
				$time_check = time();
				$time_check = strtotime('-1 month');

				// If time is something special or ready or for now and is within the last year.
				if (($_POST['when'] != 'Now') && (strtotime($_POST['when']) > $time_check)) {
					$time = strtotime($_POST['when']);
				} else {
					$time = time();
				}
				
				$end_time = 0;
				
				if($status_type_id == 4) { // Scheduled Maintenance
					$end_time = strtotime($_POST['end_time']);
				} 
				
				if($status_type_id == 5) { // Update
					$end_time = $time;
				}

				// Create new issue
				$db->query("INSERT INTO issue_entries
				VALUES ('','$system_id', $status_type_id, '$time', '$end_time', '$user_id')");
				$issue_id = $db->insert_id;

				// Create a new status entry for issue
				if($db->query("INSERT INTO status_entries
				VALUES ('','$issue_id','$time','1','$status_type_id','$user_id','$issue_text','0')")) {
					$m = '<div class="lib-success">Your issue has been added.</div>';
				} else {
					$m = '<div class="lib-error">There was a problem adding your issue. ' . $db->error . '</div>';
				}



			}

			// new status post$loggedin
			if (isset($_POST['submit_status'])) {

				$issue_id = $_POST['issue_id'];
				$status_type_id = $_POST['status_type_id'];
				$status_text = $db->real_escape_string($_POST['status']);

				$issue_resolved = $_POST['issue_resolved'];

				// Create a time one year back to see use to check if posting time is in range.
				$time_check = time();
				$time_check = strtotime('-1 month');

				// If time is something special or ready or for now and is within the last year.
				if (($_POST['when'] != 'Now') && (strtotime($_POST['when']) > $time_check)) {
					$time = strtotime($_POST['when']);
				} else {
					$time = time();
				}

				$status_value = $status_type_id;
				if ($issue_resolved == 1) {
					$status_value = 3;

					//update issue end_time and close issue
					$db->query("UPDATE issue_entries
								SET issue_entries.end_time = '$time'
								WHERE $issue_id = issue_entries.issue_id");
				}

				// Create a new status entry
				if($db->query("INSERT INTO status_entries
				VALUES ('','$issue_id','$time','1','$status_value','$user_id','$status_text','0')")) {
						$m = '<div class="lib-success">Your status update has been added.</div>';
				} else {
						$m = '<div class="lib-error">There was a problem saving your update. ' . $db->error . '</div>';
				}


			}

		} // End loop for logged in user

}

		

	/*
		A non-logged-in user has submitted a problem report. Check for basic bad
		bits and then send the email.
	*/

		if(isset($_POST['problem-report'])) {



			// Check the Google Recaptcha API
				$url =  "https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptchaSecretKey . "&response=" . $_POST['g-recaptcha-response'] . "&remoteip=" . $_SERVER['HTTP_REMOTE_ADDR'];
				$response = file_get_contents($url);
				$data = json_decode($response, TRUE);
				if ($data['success']){		

					$name = stripslashes($_POST['name']);
					$email = stripslashes($_POST['email']);
					$message = stripslashes($_POST['feedback']);

					send_email($name, $email, $message); 
					$m = '<div class="lib-success">Thanks! We&#8217;ll get right on that. If you shared your email, we&#8217;ll follow up with you soon.</div>'; 
				} else {
					$m = '<div class="lib-error">Sorry, your captcha didn\'t work. Either it\'s our fault, or you\'re a robot. Please try again.</div>'; 
				}
			

		}

		$issue_query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time, issue_entries.status_type_id FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id ORDER BY issue_entries.issue_id DESC LIMIT 10";
		$filter = 0; // Most Recent Filter is active

		if(isset($_GET['status']) && ($_GET['status'] == 'resolved')) {
			$issue_query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time, issue_entries.status_type_id FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id  AND issue_entries.end_time > 0 ORDER BY issue_entries.issue_id DESC";
			$filter = 2; // Show Resolved
		}

		if(isset($_GET['status']) && ($_GET['status'] == 'unresolved')) {
				$issue_query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time, issue_entries.status_type_id FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id AND (issue_entries.end_time BETWEEN 0 AND 0) ORDER BY issue_entries.issue_id DESC";
				$filter = 1; // Show Unresolved
		}

         		if(isset($_GET['thankyou'])) {
			$m = '<div class="lib-success">Thanks! We&#8217;ll get right on that. If you shared your email, we&#8217;ll follow up with you soon.</div>';
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
			<h1><a href="index.php"><?php echo $library_name; ?> Status</a></h1>
		</div> <!-- end span -->

		<div class="span2 unit right lib-horizontal-list" style="text-align: right;margin-top:.65em;">
			<ul>

					<li style="float:right;"><?php echo (($logged_in == 1) ? '<a href="#" class="status-button has-js issue-trigger" style="margin-top:-.5em" id="issue-trigger">Report an Issue</a>' : '<a href="feedback.php" class="lib-button-small issue-trigger" style="margin-top:-.5em" id="feedback-trigger">Report a Problem</a>'); ?></li>
						<li style="float:right;margin-right: 8%;"><?php  echo (($logged_in == 1) ? 'Hello, ' . $user_fn . '&nbsp;//&nbsp;<a href="?logout" title="Log out" style="text-decoration: none; font-size: .9em;">Log out</a>' : '<a href="?login" title="Log in" style="text-decoration: none; font-size: .9em;">Log in</a>'); ?></li>
			</ul>
		</div>
	</div> <!-- end line -->
	<?php

		if(isset($m)) {
			echo '<div id="message-update">' . $m . '</div>';
		}

		if($logged_in == 1) {

	?>
	<div class="line lib-form feedback">
		<div class = "span1 unit">
			<div class = "span1 unit">
				<h4>Report an Issue</h4>
			</div>

			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="issue-form" onsubmit="return validateForm()">
				<div style="float: left; padding-right: 1em;">
					<label class="lib-inline">System:</label>
					<select name="system_id">

						<!-- load system names -->
						<?php
						$result = $db->query("SELECT * FROM systems");

						while($row = $result->fetch_assoc())
						{
							echo '<option value="' . $row["system_id"] . '">' . $row["system_name"] . '</option>';
						}
						?>

					</select>
				</div>

				<div style="float: left;">
					<label class="lib-inline">Status:</label>
					<select name="status_type_id" id="status_type_id">

						<!-- Load status types -->
						<?php
						$result = $db->query("SELECT * FROM status_type");
						while($row = $result->fetch_assoc())
						{
							if ($row["status_type_id"] != 3)
							echo '<option value="' . $row["status_type_id"] . '">' . $row["status_type_text"] . '</option>';
						}
						?>

					</select>
				</div>

				<div class="when_box">

					<label style="padding-top: .2em; " for="when">When:</label>
					<input type="text" name="when" value = "Now" style="width: 70%; font-size: .8em; font; color: #575757; display: inline">
					<div class="end-time-box">
						<label style="padding-top: .2em;" for="end_time">Ends:</label>
						<input type="text" name="end_time"  style="width: 70%; font-size: .8em; font; color: #575757; display: inline">
					</div>
				</div>


				<div class = "span1 unit" style="float: left; padding: 1em 0">
					<textarea style="font-size: 1em" name="issue_text" placeholder="Describe issue..."></textarea>
				</div>

				<input class="status-button" style="float: left;" name="submit_issue" type="submit" value="Submit Issue" />

				<div style="float: left; margin-left: 3%; margin-top: .8em; color: #0065A4; text-decoration: underline; cursor:pointer;" class="has-js issue-trigger" id="cancel-issue">Cancel</div>

			</form>

		</div> <!-- end span -->
	</div> <!-- end line -->

	<?php
}
	?>

	<div class="line break">

			<?php

				$result = $db->query("SELECT * FROM systems ORDER BY system_name ASC");
				$now = time();

				while($row = $result->fetch_assoc())
				{

					$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
													FROM issue_entries i
													WHERE i.system_id = {$row['system_id']}
													AND i.start_time < '$now'");

					$status = '<div class="lib-online" style="margin: 0;">
						<p>All systems are online.</p>
						</div>';

					while ($rw = $system_result->fetch_assoc()) {

						// Check if there is a resolution or if a scheduled resolution has not happened yet
						if (($rw['end_time'] == 0) || ($rw['end_time'] > $now)) {
							if ($rw['status_type_id'] == 2) {
								$status = '<div class="lib-error" style="margin: 0;">
									<p>Uh-oh, we have a system down. You can bet that we&#8217;re working on it!</p>
									</div>';
								break 2;
							}
						}
					} // end while

				} // end system while

				echo $status;

			?>

	</div> <!-- end line -->

	<div class="line break status-table">
		<div class="span2 unit left">

						<!-- load system names -->
						<?php

							$result = $db->query("SELECT * FROM systems ORDER BY system_name ASC");
							$now = time();
							$i = 0;
							$system_count = $result->num_rows;

							// Calculate where to drop in the code for a second column
							$half = round($system_count/2);

							// loop through each system
							while($row = $result->fetch_assoc())
							{

								if($i == $half) {
									echo '</div><div class="span2 unit right lastUnit">';
								}

								echo '<dl class="system">';
								echo '<dt><a href="detail.php?system='. $row['system_id'] . '" style="text-decoration: none;">' . $row["system_name"] . '</a></dt> ';
								echo '<dd class = "col2 name"><a href="detail.php?system='. $row['system_id'] .'" style = "text-decoration: none;';

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id, s.status_type_text, i.issue_id
								FROM issue_entries i, status_type s
								WHERE i.system_id = {$row['system_id']} AND i.status_type_id = s.status_type_id AND i.start_time < '$now' ORDER BY i.status_type_id DESC");

								$currently = 'color: #147D11">Online'; // currently displayed. Color difference is WCAG2 AA compliant

								// Display Day
								while ($rw = $system_result->fetch_assoc()) {

									// Check if there is no resolution or a scheduled resolution is still in the future
									if (($rw['end_time'] == 0) || ($rw['end_time'] > $now) || ($rw['start_time'] > $now)) {
                                                                                $day = date('Ymd',$now);

										//echo '<p>color</p>';

										$currently = 'color: #cb0000;">'.$rw['status_type_text'];

									}
								}

								echo $currently . '</a></dd>'; // close currently displayed

								echo '</dl>'; // close row
								$i++;
							}

						?>

			</div>
	</div> <!-- end line -->

	<!-- Add blog-like view of incidents -->
	<div class="line lib-horizontal-list status-bar" style="clear: both; margin: 2em 0; padding: .75em 1%; background: #eee; border: 1px solid #bbb;">

		<div class="span3of4 unit left">
			<ul>
				<li><a href="index.php" class="status-button <?php echo ($filter == 0 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-recent"><?php echo ($filter == 0 ? 'Showing' : 'Show'); ?> Recent</a></li>
				<li><a href="?status=unresolved" class="status-button <?php echo ($filter == 1 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-unresolved"><?php echo ($filter == 1 ? 'Showing' : 'Show'); ?> Unresolved</a></li>
				<li><a href="?status=resolved" class="status-button <?php echo ($filter == 2 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($filter == 2 ? 'Showing' : 'Show'); ?> Resolved</a></li>
			</ul>
		</div>

		<div class="span4 unit right subscription-list" style="text-align:right;">
			<p>Subscribe: <a href="<?php echo $rss_url; ?>" title="Subscribe to the RSS feed">RSS</a>&nbsp;
				//&nbsp;
				<a href="<?php echo $email_subscription_url; ?>" title="Subscribe to updates via Email">Email</a></p>
		</div>


	</div>
	<?php

			$issue_result = $db->query($issue_query);
			$n_rows = $issue_result->num_rows;
			
			if ($n_rows > 0) {

				while ($issue_entries = $issue_result->fetch_assoc()) {

					$result = $db->query("SELECT s.status_id, s.issue_id, s.status_timestamp, s.status_public, s.status_user_id, s.status_text, s.status_delete, u.user_id, u.user_fn, u.user_ln, st.status_type_id, st.status_type_text
						FROM status_entries s, user u, status_type st
						WHERE s.issue_id = '{$issue_entries['issue_id']}' AND s.status_user_id = u.user_id AND s.status_type_id = st.status_type_id
						ORDER BY s.status_timestamp ASC");

					$num_rows = $result->num_rows;
					$issue_id = $issue_entries['issue_id'];

					$rc = 0;
					$attribution = NULL;
					// display issues and check for comments
					while ($status_entries = $result->fetch_assoc()) {
						$rc++;
						if ($rc == 1) {

							$status_type_id = $status_entries['status_type_id'];

							if($issue_entries['end_time'] > 0) {
								$resolved = 1;
								if($issue_entries['status_type_id'] != 4) {
									$current_status = '<span class="tag-resolved">Resolved</span>';
								} else {
									$current_status = '<span class="tag-maintenance">Maintenance</span>';
								}
							} else {
								$current_status = '<span class="tag-unresolved">Unresolved</span>';
								$resolved = 0;
							}

							echo '
							<!-- Issue -->
							<div class = "line issue-box span1">
								' . ($logged_in == 1 && $resolved == 0 ? '<div class="right status-update has-js">Add Update</div>' : '') .'
								<h2 id="issue_' . $issue_entries['issue_id'] . '"><a href="detail.php?id=' . $issue_entries['issue_id'] . '">' . $status_entries['status_type_text'] . ' for ' . $issue_entries['system_name'] . ' ' . $current_status .'</a></h2>
								<div class="comment-text"><strong class="timestamp">[' . date("n/j @ g:i a", $status_entries['status_timestamp']) . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>';

								// Note the use of the date
								$displayed_date = date("y-n-j", $status_entries['status_timestamp']);
								$attribution_verb = ' was reported on ' . date("n/j/y", $status_entries['status_timestamp']) . ($resolved == 1 ? ' and resolved on ' . date('n/j/y', $issue_entries['end_time']) : '');
								
								if($issue_entries['status_type_id'] == 4) { // Maintenance
									$attribution_verb = ' is scheduled from ' . date("h:ia n/j/y", $status_entries['status_timestamp']) . ' until ' . date('h:ia n/j/y', $issue_entries['end_time']);
								}

								if($issue_entries['status_type_id'] == 5) { // Update
										$attribution_verb = ' happened on ' . date("n/j/y", $status_entries['status_timestamp']);
								}
								
								$attribution = '<p class="tagline">This ' . $status_entries['status_type_text'] . $attribution_verb . '.</p>';
								
								

						} else {

							// Comment wrapper

							// Do we need to show the date again?
							$comment_date = date('y-n-j', $status_entries['status_timestamp']);

							if($displayed_date != $comment_date) {
								$displayed_date = $comment_date;
								$comment_time = date('n/j @ g:i a', $status_entries['status_timestamp']);
							} else {
								$comment_time = date('g:i a', $status_entries['status_timestamp']);
							}

							echo '<div class="comment-list">
									<div class ="comment-text"><strong class="timestamp">[' . $comment_time . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>
								</div> <!-- end comment-list --> ';

						}

					// Add the comments entry if logged in and the item is unresolved

					if(($logged_in == 1) && ($resolved == 0)) {

						echo '<div class="lib-form add-comment-form" style="margin-top: .5em; padding-top: .5em; border-top: 1px dotted #bbb;">

							<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" name="status-form">
								<fieldset>
								<legend>Add a Status Update</legend>
								<label for="status-' . $issue_entries['issue_id'] . '" style="display:none;">Update Status</label>
								<textarea style="margin-top: .5em; height: 5em; font-size: 1em" id="status-' . $issue_entries['issue_id'] . '" name="status" placeholder="Update the Status of this Issue"></textarea>

							<div class="line" style="margin-top:.5em;">
								<div class="span2 right unit" style="text-align:right;">

									<label style="margin-left: 1em;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
									<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1">

									<label class="lib-inline" for="comment-when-' . $issue_entries['issue_id'] . '" >When</label>
									<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-' . $issue_entries['issue_id'] . '" value="Now" />
								</div>
								<div class="left unit span4 lastUnit">
									<input class="status-button" name="submit_status" type="submit" value="Update" />
								</div>
							</div>


								<input type="hidden" name="issue_id" value="' .$issue_entries['issue_id'] . '" />
								<input type="hidden" name="status_type_id" value="' . $status_type_id . '" />
							</fieldset>
							</form>
						</div>';
						

					}
				} 	if($attribution != NULL) {
						echo $attribution . ' </div><!-- End .line -->';
					}
					 
			} // close status loop
				}


	?>


	<div class="line break footer">
		<div class="span1 unit break">
			<p>Written by <a href="http://jonearley.net/">Jon Earley</a>, <a href="http://jon.tw" title="Jon Bloom">Jon Bloom</a>, and <a href="http://matthewreidsma.com" title="Matthew Reidsma Writes about Libraries, Technology, and the Web">Matthew Reidsma</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>
		</div> <!-- end span -->
	</div> <!-- end line -->
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script>
$(document).ready(function() {

	$('#message-update').css('position','fixed');

	setTimeout(function() {
	    $('#message-update').fadeOut('slow');
	}, 5000);

<?php

	if($logged_in == 1) {

?>
	$(".end-time-box").hide();
	$(".feedback").hide();
	$(".add-comment-form").hide();
	$(".has-js").css("display","inline-block");
	$(".issue-trigger").click(function(e) {

		$(".feedback").slideToggle(400);

	});
	$(".status-update").click(function() {
		console.log('Click');
		$(this).parent("div.issue-box").find('div.add-comment-form').last().slideToggle(400);

	});
	$("#status_type_id").change(function(){
	    if ($("#status_type_id").val() == "4") {
	       $('.end-time-box').show();
	    } else {
	       $('.end-time-box').hide();
	    }
	});
});

<?php

	} else {

?>
	var problemReportFormHTML = 
"	<div class=\"feedback lib-form line\">" +
"		<form method=\"post\" action=\"\">" +
"		<div class=\"span2 unit left\">" +
"			<label for=\"name\">Your Name:</label>" +
"			<input type=\"text\" name=\"name\" id=\"name\" placeholder=\"Optional\" />" +
"		</div>" +
"		<div class=\"span2 unit left lastUnit\">" +
"			<label for=\"email\">Your Email:</label>" +
"			<input type=\"text\" name=\"email\" id=\"email\" placeholder=\"Optional\" />" +
"		</div>" +
"		<label for=\"feedback\">Have an idea? See a problem?</label>" +
"		<textarea name=\"feedback\"></textarea>" +
"		<div class=\"g-recaptcha\" data-sitekey=\"<?php echo $recaptchaSiteKey; ?>\" style=\"padding: 10px; display:inline-block\"></div>" +
"		<noscript>" +
"		  <div style=\"width: 302px; height: 352px;\">" +
"		    <div style=\"width: 302px; height: 352px; position: relative;\">" +
"		      <div style=\"width: 302px; height: 352px; position: absolute;\">" +
"		        <iframe src=\"https://www.google.com/recaptcha/api/fallback?k=<?php echo $recaptchaSiteKey; ?>\"" +
"		                frameborder=\"0\" scrolling=\"no\"" +
"		                style=\"width: 302px; height:352px; border-style: none;\">" +
"		        </iframe>" +
"		      </div>" +
"		      <div style=\"width: 250px; height: 80px; position: absolute; border-style: none;" +
"		                  bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;\">" +
"		        <textarea id=\"g-recaptcha-response\" name=\"g-recaptcha-response\"" +
"		                  class=\"g-recaptcha-response\"" +
"		                  style=\"width: 250px; height: 80px; border: 1px solid #c1c1c1;" +
"		                         margin: 0px; padding: 0px; resize: none;\" value=\"\">" +
"		        </textarea>" +
"		      </div>" +
"		    </div>" +
"		  </div>" +
"		</noscript>" +
"		<div class=\"right\">" +
"			<div style=\"display: inline-block; margin-right: 2em; color: #0065A4; text-decoration: underline; cursor:pointer;\" class=\"issue-trigger\">Cancel</div>" +
"				<input class=\"lib-button\" type=\"submit\" value=\"Report a Problem\" name=\"problem-report\" style=\"margin-top: 1em;\" />" +
"			</div>" +
"		</form>" +
"	</div>";
	$('body').append(problemReportFormHTML);

<?php

if(isset($_GET['problem'])) { // Force problem form to open
	
} else {

?>
	$(".feedback").hide();
	$(".issue-trigger").click(function(e) {

		e.preventDefault();

		$(".feedback").slideToggle(400);

	});
	
<?php

}

?>
	
});

<?php
	}
?>

</script>
</body>

</html>
