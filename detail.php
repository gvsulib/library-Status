<?php
	session_start();
	$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	$_SESSION['location'] = $actual_url;
	
	date_default_timezone_set('America/Detroit');
	$logged_in = 0;
	
	// Are you logged in?
	
	// Debug the user login by a force login
	//$_SESSION['username'] = 'reidsmam';

	
	include 'resources/secret/config.php';
	include ('resources/php/markdown.php');
	
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
				$db->query("INSERT INTO status_entries
				VALUES ('','$issue_id','$time','1','$status_type_id','$user_id','$issue_text','0')");
				
				$m = '<div class="lib-success">Your issue has been added.</div>';
				
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
				$db->query("INSERT INTO status_entries
				VALUES ('','$issue_id','$time','1','$status_value','$user_id','$status_text','0')") or die($db->error);
				
				$m = '<div class="lib-success">Your status update has been added.</div>';
			}

		} // End loop for logged in user
	}
	
	if(isset($_GET['thankyou'])) {
		$m = '<div class="lib-success">Thanks! We&#8217;ll get right on that. If you shared your email, we&#8217;ll follow up with you soon.</div>';
	}

	if(isset($_GET['system'])) {
		$system_id = $_GET['system'];
		$detail = 0; // Systems view

		$result=$db->query("SELECT systems.system_name FROM systems WHERE systems.system_id = '$system_id'");

		while ($row = $result->fetch_assoc()) {
			$system_name = $row['system_name'];
		}
		
		$issue_query = 'SELECT issue_entries.start_time, issue_entries.end_time, issue_entries.issue_id, issue_entries.status_type_id FROM issue_entries WHERE issue_entries.system_id = ' . $system_id . ' ORDER BY issue_entries.issue_id DESC';
		$filter = 0; // All Filter is active
		
		if(isset($_GET['status']) && ($_GET['status'] == 'resolved')) {
			$issue_query = 'SELECT issue_entries.start_time, issue_entries.end_time, issue_entries.issue_id, issue_entries.status_type_id FROM issue_entries WHERE issue_entries.system_id = ' . $system_id . ' AND issue_entries.end_time > 0 ORDER BY issue_entries.issue_id DESC';
			$filter = 2; // Show Resolved
		} 
		
		if(isset($_GET['status']) && ($_GET['status'] == 'unresolved')) {
				$issue_query = 'SELECT issue_entries.start_time, issue_entries.end_time, issue_entries.issue_id, issue_entries.status_type_id FROM issue_entries WHERE issue_entries.system_id = ' . $system_id . ' AND (issue_entries.end_time BETWEEN 0 AND 0) ORDER BY issue_entries.issue_id DESC';
				$filter = 1; // Show Unresolved
		}
	} 
	
	if(isset($_GET['id'])) {
		$id = $_GET['id'];
		$detail = 1; // Issue view

		$result=$db->query("SELECT * FROM issue_entries WHERE issue_id = '$id'");

		while ($row = $result->fetch_assoc()) {
			$system_id = $row['system_id'];
			$status_type_id = $row['status_type_id'];
			$start_time = $row['start_time'];
			$end_time = $row['end_time'];
			
			// Is issue resolved?
			if ( ( ($end_time > 0) && ($end_time > time()) ) || ($end_time <= 0)) {
				$resolved = 0;
			} else {
				$resolved = 1;
			}
		}
	}

		

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<title>GVSU University Libraries Status</title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" type="text/css" href="resources/css/styles.css" />


</head>

<body>
		<div id="gvsu-cf_header" class="responsive">
		<div id="gvsu-cf_header-inner">
			<div id="gvsu-cf_header-logo">
				<a href="http://gvsu.edu/">
					<img src="//gvsu.edu/includes/topbanner/3/gvsu_logo.png" alt="Grand Valley State University">
				</a>
			</div><!-- End #gvsu-cf_header-logo -->
		</div><!-- End #gvsu-cf_header-inner -->
	</div><!-- End #gvsu-cf_header -->

		<div id="cms-header-wrapper">
		<div id="cms-header">
			<div id="cms-header-inner">
				<a id="cms-navigation-toggle" href="cms-siteindex-index.htm" onclick="return cmsToggleMenu(document.getElementById('cms-navigation'))">
					<img src=" //gvsu.edu/cms4/skeleton/0/files/img/transparent.png" alt="Menu">
				</a>
				<h1>
					<a href="http://gvsu.edu/library">University Libraries</a>
				</h1>
				<div id="library-search">
					<form action="//gvsu.summon.serialssolutions.com/search">
						<input type="hidden" name="spellcheck" value="true">
						<p>
							<label for="library-search-box" class="hide-accessible">Search the Library for Books, Articles, Media, and More</label>
							<input id="library-search-box" type="text" name="s.q" placeholder="Find articles, books, &amp; more" size="35">
							<input type="submit" value="Find It!">
						</p>
					</form>
				</div><!-- End #library-search -->
					
			<div class="cms-navigation" id="cms-navigation">
				<ul>
					<li><a href="http://gvsu.edu/library/find">Find Materials</a></li>
					<li><a href="http://gvsu.edu/library/allservices">Services</a></li>
					<li><a href="http://gvsu.edu/library/about">About Us</a></li>
					<li><a href="http://help.library.gvsu.edu">Help</a></li>
				</ul>
			</div><!-- End #cms-navigation -->

			<div class="cms-clear"></div>
		
			</div> <!-- End #cms-header-inner -->
		</div><!-- End #cms-header -->
	</div><!-- End #cms-header-wrapper -->


	<div id="cms-body-wrapper">
		<div id="cms-body">
			<div id="cms-body-inner">
				<div id="cms-body-table">
					<div id="cms-content">

		<div class="row break">
			<div class="span2 unit left">
				<h2><a href="index.php"><?php echo $library_name; ?> Status</a></h2>
			</div> <!-- end span -->

			<div class="span1 unit right lib-horizontal-list" style="text-align: right;margin-top:.65em;overflow:visible;">
				<ul>

						<li style="float:right;"><?php echo (($logged_in == 1) ? '<a href="#" class="status-button btn btn-default has-js issue-trigger" style="margin-top:-.5em" id="issue-trigger">Report an Issue</a>' : '<a href="feedback.php" class="btn btn-primary issue-trigger" style="margin-top:-.5em" id="feedback-trigger">Report a Problem</a>'); ?></li>
							<li style="float:right;margin-right: 8%;"><?php  echo (($logged_in == 1) ? 'Hello, ' . $user_fn . '&nbsp;//&nbsp;<a href="?logout" title="Log out" style="text-decoration: none; font-size: .9em;">Log out</a>' : '<a href="index.php?login" title="Log in" style="text-decoration: none; font-size: .9em;">Log in</a>'); ?></li>
				</ul>
			</div>
		</div> <!-- end line -->
		<?php

			if(isset($m)) {
				echo '<div id="message-update">' . $m . '</div>';
			}
			
			
			if($logged_in == 1) { ?>

			<div class="row lib-form feedback">
				<div class = "span3 unit">	
					
						<h4>Report an Issue</h4>
		

					<form action="<?php echo $actual_url; ?>" method="POST" name="issue-form">
						<div style="float: left; padding-right: 1em;">
							<label class="lib-inline">System:</label>
							<select name="system_id">

								<!-- load system names -->
								<?php
								$result = $db->query("SELECT * FROM systems");

								while($row = $result->fetch_assoc())
								{
									// Restrict issue submission on user access limits
									if ($user_access == 1 && $row['system_category'] == 0) {
										echo '<option value="' . $row["system_id"] . '">' . $row["system_name"] . '</option>';
									} else if ($user_access == 2 && $row['system_category'] == 1) {
										echo '<option value="' . $row["system_id"] . '">' . $row["system_name"] . '</option>';
									} else if ($user_access == 9) {
										echo '<option value="' . $row["system_id"] . '">' . $row["system_name"] . '</option>';
									}
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


						<div class = "span3 unit" style="float: left; padding: 1em 0">
							<textarea style="font-size: 1em" name="issue_text" placeholder="Describe issue..."></textarea>
						</div>

						<input class="status-button" style="float: left;" name="submit_issue" type="submit" value="Submit Issue" />

						<div style="float: left; margin-left: 3%; margin-top: .8em; color: #0065A4; text-decoration: underline; cursor:pointer;" class="has-js issue-trigger" id="cancel-issue">Cancel</div>

					</form>

				</div> <!-- end span -->
			</div> <!-- end line -->

		<?php	}
		if($detail == 0) { // Show systems template
						
						echo '<div class="cms-clear"></div><h3>Issues for ' . $system_name . '</h3>';
						
				?>
				
				<!-- Add blog-like view of incidents -->
				<div class="row status-bar" style="clear: both; margin: 2em 0; padding: .75em 1%; background: #eee; border: 1px solid #bbb;">

					<div class="span2 lib-horizontal-list unit left">
						<ul>
							<li><a href="<?php echo $actual_url; ?>&amp;status=recent" class="status-button btn btn-default <?php echo ($filter == 0 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-recent"><?php echo ($filter == 0 ? 'Showing' : 'Show'); ?> All</a></li>
							<li><a href="<?php echo $actual_url; ?>&amp;status=unresolved" class="status-button btn btn-default <?php echo ($filter == 1 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-unresolved"><?php echo ($filter == 1 ? 'Showing' : 'Show'); ?> Unresolved</a></li>
							<li><a href="<?php echo $actual_url; ?>&amp;status=resolved" class="status-button btn btn-default <?php echo ($filter == 2 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($filter == 2 ? 'Showing' : 'Show'); ?> Resolved</a></li>
						</ul>
					</div>

					<div class="span1 unit right subscription-list" style="text-align:right;">
						<p>Subscribe: <a href="http://feeds.feedburner.com/gvsulibstatus" title="Subscribe to the RSS feed">RSS</a>&nbsp;
							//&nbsp;
							<a href="http://feedburner.google.com/fb/a/mailverify?uri=gvsulibstatus&amp;loc=en_US" title="Subscribe to updates via Email">Email</a></p>
					</div>

<div class="cms-clear"></div>
				</div>
				
				<?php
						$test = 0;
						$issue_result = $db->query($issue_query) or die(mysqli_error());
						
							while ($issue_entries = $issue_result->fetch_assoc()) {

								$issue_id = $issue_entries['issue_id'];
								$start_day = date('Ymd', $issue_entries['start_time']);
								$end_day = date('Ymd', $issue_entries['end_time']);

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

									$result = $db->query("SELECT s.status_id, s.issue_id, s.status_timestamp, s.status_public, s.status_user_id, s.status_text, s.status_delete, u.user_id, u.user_fn, u.user_ln, st.status_type_id, st.status_type_text
										FROM status_entries s, user u, status_type st
										WHERE s.issue_id = '{$issue_entries['issue_id']}' AND s.status_user_id = u.user_id AND s.status_type_id = st.status_type_id
										ORDER BY s.status_timestamp ASC") or die(mysqli_error());

									$num_rows = $result->num_rows;

									$rc = 0;
									$test++;
									// display issues and check for comments
									while ($status_entries = $result->fetch_assoc()) {

										$rc++;

										// first post
										if ($rc == 1) {
											
											$time_format = 'n/j/y @ g:i a';
											$status_type_id = $status_entries['status_type_id'];
									
											echo '
											<!-- Issue -->
											<div class="row issue-box span3">
											' . ($logged_in == 1 && $resolved == 0 ? '<div class="right status-update has-js">Add Update</div>' : '') .'
														<h2 id="issue_' . $issue_entries['issue_id'] . '"><a href="detail.php?id=' . $issue_entries['issue_id'] . '">' . $status_entries['status_type_text'] . '</a> ' . $current_status .'</h2>
														<div class="comment-text"><strong class="timestamp">[' . date($time_format, $status_entries['status_timestamp']) . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>';

														// Note the use of the date
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


										// list last comment
										} else {
											
											// Comment wrapper

											// Do we need to show the date again?
											$comment_date = date('y-n-j', $status_entries['status_timestamp']);

											if($displayed_date != $comment_date) {
												$displayed_date = $comment_date;
												
												
												
												$comment_time = date($time_format, $status_entries['status_timestamp']);
											} else {
												$comment_time = date($time_format, $status_entries['status_timestamp']);
											}

											echo '<div class="comment-list">
													<div class ="comment-text"><strong class="timestamp">[' . $comment_time . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>
												</div> <!-- end comment-list --> ';	
											
										if($rc == $num_rows) {
											if(($logged_in == 1) && ($resolved == 0)) {

										echo '<div class="lib-form add-comment-form" style="margin-top: .5em; padding-top: .5em; border-top: 1px dotted #bbb;">

											<form action="' . $actual_url . '" method="POST" name="status-form">
												<fieldset>
												<legend>Add a Status Update</legend>
												<label for="status-' . $issue_entries['issue_id'] . '" style="display:none;">Update Status</label>
												<textarea style="margin-top: .5em; height: 5em; font-size: 1em;width:100%;" id="status-' . $issue_entries['issue_id'] . '" name="status" placeholder="Update the Status of this Issue"></textarea>

											<div class="row" style="margin-top:.5em;">
												<div class="span2">

													<label style="margin-left: 1em;display:inline;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
													<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1">

													<label class="lib-inline" style="display:inline;margin-left:1em;" for="comment-when-' . $issue_entries['issue_id'] . '" >When</label>
													<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-' . $issue_entries['issue_id'] . '" value="Now" />
												</div>
												<div class="span1" style="text-align:right;">
													<input class="status-button" name="submit_status" type="submit" value="Update" />
												</div>
																							<div class="cms-clear" style="padding-bottom:.5em;"></div>

											</div>	


												<input type="hidden" name="issue_id" value="' .$issue_entries['issue_id'] . '" />
												<input type="hidden" name="status_type_id" value="' . $status_type_id . '" />
											</fieldset>
											</form>
										</div>';

									}
										}


										}



											
										
									
									
									
								}

						echo '<!--rc = ' . $rc . ' // test = ' . $test . ' and id = ' . $issue_entries['issue_id'] . '-->';

					if($attribution != NULL) {

						echo $attribution . ' </div><!-- End .line -->';

					}
				} // close status loop
						
					} else { // Show issue template
						
						if($end_time > 0) {
							$current_status = '<span class="tag-resolved">Resolved</span>';
							$resolved = 1;
						} else {
							$current_status = '<span class="tag-unresolved">Unresolved</span>';
							$resolved = 0;
						}

						$result = $db->query("SELECT s.status_id, s.issue_id, s.status_timestamp, s.status_public, s.status_user_id, s.status_text, s.status_delete, u.user_id, u.user_fn, u.user_ln, s.status_type_id, st.status_type_text
							FROM status_entries s, user u, status_type st
							WHERE s.issue_id = '$id' AND s.status_user_id = u.user_id AND s.status_type_id = st.status_type_id
							ORDER BY s.status_timestamp ASC") or die(mysqli_error());

						$num_rows = $result->num_rows;

						$rc = 0;

						// display issues and check for comments
						while ($status_entries = $result->fetch_assoc()) {
							
							$status_type_id = $status_entries['status_type_id'];

							$rc++;

							// first post
							if ($rc == 1) {
								
								$time_format = 'n/j/y @ g:i a';
						
								echo '
								<!-- Issue -->
								<div class="row issue-box span1">
								' . ($logged_in == 1 && $resolved == 0 ? '<div class="right status-update has-js">Add Update</div>' : '') .'
											<h2 id="issue_' . $id . '"><a href="detail.php?id=' . $id . '">' . $status_entries['status_type_text'] . '</a> ' . $current_status .'</h2>
											<div class="comment-text"><strong class="timestamp">[' . date($time_format, $status_entries['status_timestamp']) . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>';

											// Note the use of the date
											$displayed_date = date("y-n-j", $status_entries['status_timestamp']);

											$attribution = '<p class="tagline">This issue was reported on ' . date("n/j/y", $status_entries['status_timestamp']) . ($resolved == 1 ? ' and resolved on ' . date('n/j/y', $end_time) : '') .'.</p>';


							// list last comment
							} else {
								
								// Comment wrapper

								// Do we need to show the date again?
								$comment_date = date('y-n-j', $status_entries['status_timestamp']);

								if($displayed_date != $comment_date) {
									$displayed_date = $comment_date;
									
									
									
									$comment_time = date($time_format, $status_entries['status_timestamp']);
								} else {
									$comment_time = date($time_format, $status_entries['status_timestamp']);
								}

								echo '<div class="comment-list">
										<div class ="comment-text"><strong class="timestamp">[' . $comment_time . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>
									</div> <!-- end comment-list --> ';	

								if($rc == $num_rows) {

									if(($logged_in == 1) && ($resolved == 0)) {

										echo '<div class="lib-form add-comment-form" style="margin-top: .5em; padding-top: .5em; border-top: 1px dotted #bbb;">

											<form action="' . $actual_url . '" method="POST" name="status-form">
												<fieldset>
												<legend>Add a Status Update</legend>
												<label for="status-' . $issue_entries['issue_id'] . '" style="display:none;">Update Status</label>
												<textarea style="margin-top: .5em; height: 5em; font-size: 1em;width:100%;" id="status-' . $issue_entries['issue_id'] . '" name="status" placeholder="Update the Status of this Issue"></textarea>

											<div class="row" style="margin-top:.5em;">
												<div class="span2">

													<label style="margin-left: 1em;display:inline;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
													<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1">

													<label class="lib-inline" style="display:inline;margin-left:1em;" for="comment-when-' . $issue_entries['issue_id'] . '" >When</label>
													<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-' . $issue_entries['issue_id'] . '" value="Now" />
												</div>
												<div class="span1" style="text-align:right;">
													<input class="status-button" name="submit_status" type="submit" value="Update" />
												</div>
																							<div class="cms-clear" style="padding-bottom:.5em;"></div>

											</div>	


												<input type="hidden" name="issue_id" value="' .$issue_entries['issue_id'] . '" />
												<input type="hidden" name="status_type_id" value="' . $status_type_id . '" />
											</fieldset>
											</form>
										</div>';

									}
								}
								
							}
							
							
						}
							
						echo $attribution . '</div>';
						
					}
				?>
				

		<div class="row break footer">
			<div class="span3 unit">
				
				<p>Written by <a href="http://jonearley.net/">Jon Earley</a>, <a href="http://jon.tw" title="Jon Bloom">Jon Bloom</a>, and <a href="http://matthewreidsma.com" title="Matthew Reidsma Writes about Libraries, Technology, and the Web">Matthew Reidsma</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>

			</div> <!-- end span -->
		</div> <!-- end line -->
		
	</div><!-- End #cms-content -->
				</div><!-- End #cms-body-table -->
			</div><!-- End #cms-body-inner -->
		</div><!-- end #cms-body -->
	</div><!-- end #cms-body-wrapper -->

	<div id="cms-footer-wrapper">
		<div id="cms-footer">
			<div id="cms-footer-inner">
				<ul>
					<li><h4>Contact</h4>
						<p class="vcard">
							<span class="tel"> 
								<span class="type">Phone</span>:
								<span class="value">(616) 331-3500</span>
							</span>
							<br />
							<a href="mailto:library@gvsu.edu" class="email" target="_blank">library@gvsu.edu</a>
							<br />
						</p>
					</li>
					<li><h4>Social Media</h4>
						<p>
							<a href="http://twitter.com/gvsulib" title="http://twitter.com/gvsulib" class="socialmedia-icon socialmedia-icon-twitter">
								<span class="cms-screen-reader">http://twitter.com/gvsulib</span>
							</a>
							<a href="http://youtube.com/user/gvsulib" title="http://youtube.com/user/gvsulib" class="socialmedia-icon socialmedia-icon-youtube">
								<span class="cms-screen-reader">http://youtube.com/user/gvsulib</span>
							</a>
							<a href="http://instagram.com/gvsulib" title="http://instagram.com/gvsulib" class="socialmedia-icon socialmedia-icon-instagram"><span class="cms-screen-reader">http://instagram.com/gvsulib</span></a>
						</p>
					</li>
					<li id="library-fdlp">
								<p>
									<a href="http://gvsu.edu/library/govdoc" target="_blank">
										<img src="//gvsu.edu/cms4/asset/0862059E-9024-5893-1B5AAAC2F83BDDD8/fdlp-new.png" alt="Federal Depository Library Program Logo">
									</a>
									<br>
									Federal Depository<br>
									Library Program
								</p>
							</li>
				</ul>
			</div><!-- End #cms-footer-inner -->
		</div><!-- End #cms-footer -->
	</div><!-- End #cms-footer-wrapper -->

	<div id="cms-copyright-wrapper">
		<div id="cms-copyright">
			<div id="cms-copyright-inner">
				<ul>
					<li><a href="http://gvsu.edu/affirmativeactionstatement.htm">GVSU is an EO/AA Institutio</a></li>
					<li><a href="http://gvsu.edu/privacystatement.htm">Privacy Policy</a></li>
					<li><a href="http://gvsu.edu/disclosures">Disclosures</a></li>
					<li>Copyright © 1995-2015 GVSU</li>
				</ul>
			</div><!-- End #cms-copyright-inner -->
		</div><!-- End #cms-copyright -->
	</div><!-- End #cms-copyright-wrapper -->

	<!-- Special div custom to Illiad -->
	<div id="renewalHack" style="display: none;"></div>

	<script src="//labs.library.gvsu.edu/labs/chatbutton/chatbutton.js"></script>
	<script src="//gvsu.edu/cms4/skeleton/0/files/js/cms4.0.min.js"></script>
	<script>cmsInit()</script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script>
	$(document).ready(function() {

		$('#message-update').css('position','fixed');

		setTimeout(function() {
		    $('#message-update').fadeOut('slow');
		}, 5000);

	<?php

		if($logged_in == 1) {

	?>
		$('.end-time-box').hide();
		$(".feedback").hide();
		$(".add-comment-form").hide();
		$(".has-js").css("display","inline-block");
		$(".issue-trigger").click(function(e) {

			$(".feedback").slideToggle(400);

		});
		$(".status-update").click(function() {
			console.log('Click');
			$(this).parent("div.issue-box").find('div.add-comment-form').slideToggle(400);

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
"	<div class=\"feedback lib-form row\">" +
"		<form method=\"post\" action=\"\">" +
"		<div class=\"span2 unit left\">" +
"			<label for=\"name\">Your Name:</label>" +
"			<input type=\"text\" name=\"name\" id=\"name\" placeholder=\"Optional\" />" +
"		</div>" +
"		<div class=\"span1 unit left lastUnit\">" +
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
"				<input class=\"btn btn-primary\" type=\"submit\" value=\"Report a Problem\" name=\"problem-report\" style=\"margin-top: 1em;\" />" +
"			</div>" +
"		</form>" +
"	</div>";

	$('body').append(problemReportFormHTML);
		$(".feedback").hide();

		$(".issue-trigger").click(function(e) {

			e.preventDefault();

			$(".feedback").slideToggle(400);

		});
	});

	<?php 
		}
	?>

	</script>
</body>

</html>

