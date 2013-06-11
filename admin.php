<?php
	session_start();
	$_SESSION['location'] = 'http://' . $_SERVER['SERVER_NAME'] . "/status/admin.php";

	include 'resources/secret/config.php';
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	date_default_timezone_set('America/Detroit');

	$logged_in = 0; // By default, user is logged out

	// Debug
	//$_SESSION['username'] = 'earleyj';

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

			$logged_in = 1; // Boolean to know if 

			// Create the user object as $user.
			// User id is then $loggedin_user->user_id
			$loggedin_user = $user_result->fetch_object();

			// Open or all issues
			if(isset($_GET['issues']) && ($_GET['issues'] == 'all')) {
				$all_issues = 1;
				$issue_query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id ORDER BY issue_entries.issue_id DESC";
			} else {
				$all_issues = 0;
				$issue_query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id AND (issue_entries.end_time BETWEEN 0 AND 0) ORDER BY issue_entries.issue_id DESC";
			}

			// new issue post
			// Inside the loop so you can't submit without being logged in
			if ($_POST['submit_issue']) {

				$issue_text = $_POST['issue_text'];
				$system_id = $_POST['system_id'];
				$status_type_id = $_POST['status_type_id'];

				// If scheduled time chosen
				if ($_POST['when'] != 'Now') { 
					$now = strtotime($_POST['when']);
					echo 'WHEN: ' . $_POST['when'];
				} else { 
					$now = time();
				}

				// Create new issue
				$db->query("INSERT INTO issue_entries
				VALUES ('','$system_id', $status_type_id, '$now', '0')");
				$issue_id = $db->insert_id;

				// Create a new status entry
				$db->query("INSERT INTO status_entries
				VALUES ('','$issue_id','$now','1','$status_type_id',$loggedin_user->user_id,'$issue_text','0')");
			}

			// new status post
			// Inside the loop so you can't submit without being logged in
			if ($_POST['submit_status']) {

				//echo "We are trying here";

				$issue_id = $_POST['issue_id'];
				$status_type_id = $_POST['status_type_id'];
				$status_text = $_POST['status'];

				$issue_resolved = $_POST['issue_resolved'];

				$now = time();

				$status_value = $status_type_id;
				if ($issue_resolved == 'on') {
					$status_value = 3;

					//update issue end_time and close issue
					$db->query("UPDATE issue_entries
								SET issue_entries.end_time = '$now'
								WHERE $issue_id = issue_entries.issue_id");
				}

				// Create a new status entry
				$db->query("INSERT INTO status_entries
				VALUES ('','$issue_id','$now','1','$status_value',$loggedin_user->user_id,'$status_text','0')") or die(mysqli_error());
			}

		} // End loop for logged in user

} else { // No $_SESSION['username'] variable, send to login script

	// User has not logged in
	header('Location: http://labs.library.gvsu.edu/login');

}

/*
If you want to show something to logged-in users, check for $logged_in == 1

To show to not-logged-in users, look for $logged_in == 0;
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>GVSU University Libraries Status</title>

	<link rel="stylesheet" type="text/css" href="resources/css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/libui.css" />
	<link rel="stylesheet" type="text/css" href="resources/css/layout.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">

</head>

<body>
<div id="gvsu-header-wrapper">
		<div id="gvsu-header">
			<div id="gvsu-logo">
				<a href="http://www.gvsu.edu/">
					<img src="http://www.gvsu.edu/homepage/files/img/gvsu_logo.png" alt="Grand Valley State University" border="0">
				</a>
			</div>
		</div>
	</div>
	<div id="wrapper">
	<div class="line break">
		<div class="span2of3 unit left">
			<h2><a href="index.php" title="University Libraries System Status">University Libraries Status</a></h2>
		</div> <!-- end span -->

		<div class="span3 unit left login">
			<?php echo '<p style="text-align:right;">' . ($logged_in == 1 ? '<a href="?logout" title="Log out">Log out</a></p>' : '</p>'); ?>
		</div>
	</div> <!-- end line -->

<?php 
	if($logged_in != 1) {

?>
	<div class="line">
		<div class="span1 unit">
			<span class="lib-error">Whoops! You don't have access to this page. Think this is wrong? Email <a href="mailto:reidsmam@gvsu.edu">reidsmam@gvsu.edu</a></span>
		</div>
	</div>

<?php
	} else { // User is logged in, show the page
?>

	<!-- Form Submit Status -->
	<div class="line lib-form box">
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
					<select name="status_type_id">

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

				<div style="float: right">
					
					<label style="overflow:auto; padding-top: .2em; ">When:</label>

					<input type="text" name="when" value = "Now" style="width: 70%; font-size: .8em; font; color: #575757; float: right">

				</div>


				<div class = "span1 unit" style="float: left; padding-top: 1em">
					<textarea style="font-size: 1em" name="issue_text" placeholder="Describe issue..."></textarea>
				</div>

				<input class="lib-button-small-grey" style="float: left;" name="submit_issue" type="submit" value="Submit Issue" />

			</form>

		</div> <!-- end span -->
	</div> <!-- end line -->


	<div class="line break">
		<div class="span1 unit">
			<div class="lib-tabs">
				<ul>
					<li<?php echo ($all_issues == 0 ? ' class="active">' : '>'); ?> <a href="admin.php">Open Issues</a></li>
					<li<?php echo ($all_issues == 1 ? ' class="active">' : '>'); ?><a href="admin.php?issues=all">All Issues</a></li>
				</ul>
			</div>
		</div>

		<!-- Create issues -->
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
				$issue_id = $status_entries['issue_id'];

				$rc = 0;

				// display issues and check for comments
				while ($status_entries = $result->fetch_assoc()) {

					$rc++;

					// first post
					if ($rc == 1) {

						echo '
						<!-- Issue -->
						<div class = "line">
							<div class="issue-box">
								<div class="span1 unit issue">
									<div style="float: left;">
										<p class="name">' . $status_entries['user_fn'] . " " . $status_entries['user_ln'] .'</p>
										<p class="time">' . date("D g:i a - n/j/y", $status_entries['status_timestamp']) . '</p>
									</div>
									<div style="float: right;">
										<p class="name tag-system">' . $issue_entries['system_name'] . '</p>';
										
										if ($status_entries['status_type_text'] == 'Outage') {
											echo '<p class="name tag-outage">' . $status_entries['status_type_text'] . '</p>';
										} else if ($status_entries['status_type_text'] == 'Disruption') {
											echo '<p class="name tag-disruption">' . $status_entries['status_type_text'] . '</p>';
										} else {
											echo '<p class="name tag-resolution">' . $status_entries['status_type_text'] . '</p>';
										}

										echo '
									</div>
									<p class="comment-text">' . $status_entries['status_text'] . '</p>
								</div> <!-- end span --> ';

								//echo '<br>ISSUE ID: ' . $status_entries['issue_id'];
								//echo '<br>STATUS TYPE ID: ' . $status_entries['status_type_id'];

								if ($rc == $num_rows) {
									?>

									<div class="span1 unit comment-box lib-form">

										<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="status-form">

											<textarea style="margin-top: .5em; max-width: 95%; height: 2em; font-size: 1em" name="status" placeholder="Add a comment"></textarea>

											<div style = "padding: .5em;">
												<input class="lib-button-small-grey" style="float: left; margin-top: 0em;" name="submit_status" type="submit" value="Submit Status" />

												<label style="margin-left: 1em;" class="lib-inline">Issue Resolved:</label>
												<input type="checkbox" name="issue_resolved">
											</div>

											<input type="hidden" name="issue_id" value="<?php echo $status_entries['issue_id'] ?>" />
											<input type="hidden" name="status_type_id" value="<?php echo $status_entries['status_type_id'] ?>" />

										</form>

									</div>

									<?php
								}

								// comment show/hide
								if ($num_rows >= 2) {
									echo '
									<div style = "cursor: hand; cursor: pointer;" class="comment-toggle">
										<div class="span1 unit comment-box">
											<p class="name">'.($num_rows -1).' comment';
											
											if ($num_rows > 2) 
												echo 's'; 

											echo '</p>
										</div>
									</div>';
								}

					// Comment wrapper
					echo '<div class="comment-wrapper">';

					// list comments
					} else if ($num_rows >= 2) {
							echo '
								<div class="span1 unit comment-box">
									<div style="float: left;">
										<p class="name">' . $status_entries['user_fn'] . " " . $status_entries['user_ln'] .'</p>
										<p class="time">' . date("D g:i a - n/j/y", $status_entries["status_timestamp"]) . '</p>
									</div>
									<p class ="comment-text">' . $status_entries['status_text'] . '</p>
								</div> <!-- end span --> ';


						// Add a comment
						if ($rc == $num_rows && $status_entries['status_type_id'] != 3) {
							?>

							<div class="span1 unit comment-box lib-form">

								<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="status-form">

									<textarea style="margin-top: .5em; max-width: 95%; height: 2em; font-size: 1em" name="status" placeholder="Add a comment"></textarea>

									<div style = "padding: .5em;">
										<input class="lib-button-small-grey" style="float: left; margin-top: 0em;" name="submit_status" type="submit" value="Submit Status" />

										<label style="margin-left: 1em;" class="lib-inline">Issue Resolved:</label>
										<input type="checkbox" name="issue_resolved">
									</div>

									<input type="hidden" name="issue_id" value="<?php echo $status_entries['issue_id'] ?>" />
									<input type="hidden" name="status_type_id" value="<?php echo $status_entries['status_type_id'] ?>" />

								</form>
							</div>
							<?php
						}
					}


				} // close status loop

				// close comment wrapper
				echo '</div>';

				echo '
				</div> 
				</div> <!-- close issue line -->';

			} // close issue loop

		} else {

			echo '<p style="margin-top: 1em">No open issues.</p>';
		}

		?>

	</div>

	<div class="line footer">
		<div class="span1 unit">
			
			<p>Written by <a href="http://jonearley.net/">Jon Earley</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>

		</div> <!-- end span -->
	</div> <!-- end line -->
</div>
	 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
 <script>

	 $(document).ready(function() {

		// Hide the items you don't want to show if JS is available
		$(".comment-wrapper").hide();
		
		// Make the div toggle visible/invisible on click
		$(".comment-toggle").click(function() {

			$(this).next(".comment-wrapper").slideToggle(400);

		});

	});

</script>

<?php
	} // End logged-in user loop
?>

</body>
</html>






