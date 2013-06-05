<?php
	session_start();

	include 'resources/secret/config.php';
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	date_default_timezone_set('America/Detroit');

	$system_id = $_GET['system_id'];
	$day = $_GET['day'];

	$result=$db->query("SELECT systems.system_name FROM systems WHERE systems.system_id = '$system_id'");

	while ($row = $result->fetch_assoc()) {
		$system_name = $row['system_name'];
	}

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
				<h2><a href="index.php">University Libraries Status</a></h2>
			</div> <!-- end span -->

			<div class="span3 unit left login">
				<?php echo '<p>' . (isset($_SESSION['username']) ? '<a href="?logout" title="Log out">Log out</a></p>' : '<a href="admin.php" title="Log in">Log in</a></p>'); ?>
			</div>
		</div> <!-- end line -->


		<div class="line break">
			<div class="span1 unit">

			<!-- Create issues -->
			<?php

			echo '<h4>System Details</h4>';

			$issue_result = $db->query("SELECT issue_entries.issue_id FROM issue_entries WHERE issue_entries.system_id = $system_id ORDER BY issue_entries.issue_id DESC") or die(mysqli_error());

			while ($issue_entries = $issue_result->fetch_assoc()) {

				$result = $db->query("SELECT s.status_id, s.issue_id, s.status_timestamp, s.status_public, s.status_user_id, s.status_text, s.status_delete, u.user_id, u.user_fn, u.user_ln, st.status_type_id, st.status_type_text
					FROM status_entries s, user u, status_type st
					WHERE s.issue_id = '{$issue_entries['issue_id']}' AND s.status_user_id = u.user_id AND s.status_type_id = st.status_type_id
					ORDER BY s.status_timestamp ASC") or die(mysqli_error());

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
										<p class="name tag-system">' . $system_name . '</p>';
										
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


					// list last comment
					} else if ($num_rows = $rc) {
						echo '
						<div class="span1 unit comment-box">
							<div style="float: left;">
								<p class="name">' . $status_entries['user_fn'] . " " . $status_entries['user_ln'] .'</p>
								<p class="time">' . date("D g:i a - n/j/y", $status_entries["status_timestamp"]) . '</p>
							</div>
							<div style="float: right;">';
								
								if ($status_entries['status_type_id'] == '3') {
									echo '<p class="name tag-resolution" style="margin-right: 1.3em">Resolved</p>';
								}

								echo '
							</div>
							<p class ="comment-text">' . $status_entries['status_text'] . '</p>
						</div> <!-- end span --> ';

					} else if ($num_rows >= 2) {
						echo '
						<div class="span1 unit comment-box">
							<div style="float: left;">
								<p class="name">' . $status_entries['user_fn'] . " " . $status_entries['user_ln'] .'</p>
								<p class="time">' . date("D g:i a - n/j/y", $status_entries["status_timestamp"]) . '</p>
							</div>
							<p class ="comment-text">' . $status_entries['status_text'] . '</p>
						</div> <!-- end span --> ';
					}

				} // close status loop

				// close comment wrapper
					echo '</div>';

				echo '
							</div> 
						</div> <!-- close issue line -->';

			} // close issue loop

			?>
		
			</div> <!-- end span -->

		<div class="line footer">
			<div class="span1 unit">
				
				<p>Written by <a href="http://jonearley.net/">Jon Earley</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>

			</div> <!-- end span -->
		</div> <!-- end line -->
	</div> <!-- end span -->

</body>

</html>

