<?php

 //as well as loads required library files
include 'resources/secret/config.php';
include 'resources/php/functions.php';
include ('resources/php/markdown.php');

//load all starting session and other variables and required libraries
require 'resources/php/startup.php';

//holds system messages we want to show to the user.  By default, there are none
$userMessage = NULL;

// uncomment to force a login
//$_SESSION['username'] = 'felkerk';


if (isset($_SESSION['username'])) { // User has logged in
	//if the user is logging out, don't bother to check anything, destroy the session
	//and reload the page
	if (isset($_REQUEST['logout'])) {
		$_SESSION = array();
		session_destroy();
		
	} else {
		//otherwise, attempt to make a user object
		
		$user = MakeUserArray($_SESSION['username'], $db);
		if (is_array($user)) {
			$logged_in = 1;
		} else {
			//set a message for the user if they couldn't be found
			$userMessage = "<div class=\"alert alert-danger\">" . $user . "</div>";
		}
	}
}
if (isset($_POST['status_delete']) && $logged_in = 1) {
	$statusID = $_POST['update_status_id'];
	$deleteStatus = deleteStatus($statusID, $db);
	if ($deleteStatus == 1) {
		$userMessage = '<div class="alert alert-success">Status Deleted.</div>';
	} else {
		$userMessage = '<div class="alert alert-danger">There was a problem deleting your status. ' . $deleteStatus . '</div>';
	}
}


// post a new issue
if (isset($_POST['submit_issue']) && $logged_in = 1) {

	$issue_text = $db->real_escape_string($_POST['issue_text']);
	$system_id = $_POST['system_id'];
	$status_type_id = $_POST['status_type_id'];
					

	// Create a time one year back to see use to check if posting time is in range.
	$time_check = time();
	$time_check = strtotime('-1 month');

	// If time is an acceptable value, attach the value, otherwise use the current time
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
	$issueCreated = createNewIssue($system_id,$status_type_id,$time,$end_time,$user["id"],$issue_text,$db);
	if ($issueCreated == 1) {
		$userMessage = '<div class="alert alert-success">Your issue has been added.</div>';
		
	} else {
		$userMessage = '<div class="alert alert-danger">There was a problem adding your issue. ' . $issueCreated . '</div>';
	}
	
}

// new status post
if (isset($_POST['submit_status']) && $logged_in = 1) {

	$issue_id = $_POST['issue_id'];
	$status_type_id = $_POST['status_type_id'];
	$status_text = $db->real_escape_string($_POST['status']);
	$issue_resolved = false;
	if (isset($_POST['issue_resolved'])) {
		$issue_resolved = true;
	}

	//verify and format time 
	$time = verifyFormatTime($_POST['when']);

	//currently all issues are public be default, that may change in future
	$public = 1;

	//create the status.  Note that if the status code is "closed,"  the function automagaically closes the entire issue
	$statusCreated = createNewStatus( $issue_id, $time, $public, $user['id'], $status_text, $issue_resolved, $db);
	
	if ($statusCreated == 1) {
		$userMessage = '<div class="alert alert-success">Your status has been added.</div>';	
	} else {
		$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $statusCreated  . '</div>';
	}
	
}

//edit an existing status
if (isset($_POST['update_status']) && $logged_in = 1) {

	$issue_id = $_POST['issue_id'];
	$status_id = $_POST['update_status_id'];
	$status_type_id = $_POST['update_status_type_id'];
	$status_text = $_POST['update_status_text'];

	//verify and format time 
	if (isset($_POST['update-when'])) {
		$time = verifyFormatTime($_POST['update-when']);
	} else {
		$time = time();
	}
	//currently all issues are public by default, that may change in future
	$public = 1;
	
	$statusEdited = editStatus($status_id, $public, $status_text, $time, $db);
	if ($statusEdited ==1) {
		
		$issueChanged = changeIssueStatus($issue_id, $status_type_id, $db);	
		if ($issueChanged == 1) {
			$userMessage = '<div class="alert alert-success">Your status has been updated.</div>';
		} else {
			$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $issueChanged  . '</div>';
		}
	} else {
		$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $statusEdited  . '</div>';
	}
	
}



//has the user chosen to filter the issues?
// 0 = all issues, 1 = unresolved, 2= resolved
$filter = 0; // all issues by default

if (isset($_GET['status'])) {

	switch ($_GET['status']) {
		case "resolved":
			$filter = 2; // Show Resolved
			break;
		case "unresolved":
			$filter = 1; // Show Unresolved
			break;
	
	}
}

//get the correct query for the specified filter
$issue_query = constructQuery($filter);

if(isset($_GET['thankyou'])) {
	$userMessage = '<div class="alert alert-success">Thanks! We&#8217;ll get right on that. If you shared your email, we&#8217;ll follow up with you soon.</div>';
}

if(isset($_GET['url'])) {
	$problem_url = urldecode($_GET['url']);
	
}
//load the header HTML
include 'resources/HTML/header.php';	

?>




<div id="cms-body-wrapper">
	<div id="cms-body">
		<div id="cms-body-inner">
			<div id="cms-body-table">
				<div id="cms-content">

<div class="row break">
	<div class="span3 unit left">
		<h2><a href="index.php"><?php echo $library_name; ?> Status</a></h2>
	</div> <!-- end span -->
</div>
<div class="row">
<div class="span1">&nbsp;</div>
	<div class="span2 unit right lib-horizontal-list" style="text-align: right;margin-top:.65em; overflow:visible;">
		<ul>

				<li style="float:right;"><?php echo (($logged_in == 1) ? '<a href="#" class="status-button btn btn-default has-js issue-trigger" style="margin-top:-.5em;" id="issue-trigger">Report an Issue</a>' : '<a href="feedback.php" class="btn btn-primary issue-trigger" style="margin-top:-.5em;overflow:visible;" id="feedback-trigger">Report a Problem</a>'); ?></li>

				<li style="float:right;margin-right: 8%;"><?php  echo (($logged_in == 1) ? 'Hello, ' . $user["fn"] . '&nbsp;//&nbsp;<a href="?logout" title="Log out" style="text-decoration: none; font-size: .9em;">Log out</a>' : "<a href=\"$loginUrl\" title=\"Log in\" style=\"text-decoration: none; font-size: .9em;\">Log in</a>"); ?></li>
		</ul>
	</div>
</div> <!-- end line -->
<?php

if(isset($userMessage)) {
	echo '<div id="message-update">' . $userMessage . '</div>';
}

if($logged_in == 1) {

	?>
	<div class="row lib-form feedback">
			<div>
				<h3>Report an Issue</h3>
			</div>

			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="issue-form" onsubmit="return validateForm()">
				<div class="row">
				<div class="span1">
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

				<div class="span1">
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

				<div class="when_box span1">

					<label style="padding-top: .2em; " for="when">When:</label>
					<input type="text" name="when" value = "Now" style="width: 40%; font-size: .8em; font; color: #575757; display: inline">
					<div class="end-time-box">
						<label style="padding-top: .2em;" for="end_time">Ends:</label>
						<input type="text" name="end_time"  style="width: 40%; font-size: .8em; font; color: #575757; display: inline">
					</div>
				</div>
				</div>

				<div class = "span3 unit" style="float: left; padding: 1em 0">
					<textarea style="font-size: 1em;width:96%" name="issue_text" placeholder="Describe issue..."></textarea>
				</div>

				<input class="status-button" style="float: left;" name="submit_issue" type="submit" value="Submit Issue" />

				<div style="float: left; margin-left: 3%; color: #0065A4; text-decoration: underline; cursor:pointer;" class="has-js issue-trigger" id="cancel-issue">Cancel</div>

			</form>

		</div> <!-- end span -->

	<?php
}
	?>
<div class="cms-clear"></div>
	<div class="row break" style="margin-top: 1em;">

			<?php

				$result = $db->query("SELECT * FROM systems WHERE system_category = 0 ORDER BY system_name ASC");
				$now = time();

				while($row = $result->fetch_assoc())
				{

					$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
													FROM issue_entries i
													WHERE i.system_id = {$row['system_id']}
													AND i.start_time < '$now'");

					$status = '<div class="alert alert-success" style="margin: 0;">
						<p>All systems are online.</p>
						</div>';

					while ($rw = $system_result->fetch_assoc()) {

						// Check if there is a resolution or if a scheduled resolution has not happened yet
						if (($rw['end_time'] == 0) || ($rw['end_time'] > $now)) {
							if ($rw['status_type_id'] == 2) {
								$status = '<div class="alert alert-danger" style="margin: 0;">
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

	<div class="row break status-table">

		<div class="half">

						<!-- load system names -->
						<?php

							$result = $db->query("SELECT * FROM systems WHERE system_category = 0 ORDER BY system_name ASC");
							$now = time();
							$i = 0;
							$system_count = $result->num_rows;

							// Calculate where to drop in the code for a second column
							$half = round($system_count/2);

							// loop through each system
							while($row = $result->fetch_assoc())
							{
								if($i == $half) {
									echo '</div><div class="half">';
								}
								

								echo '<dl class="system">';
								echo '<dt><a href="detail.php?system='. $row['system_id'] . '" style="text-decoration: none;">' . $row["system_name"] . '</a></dt> ';
								echo '<dd class = "col2 name"><a href="detail.php?system='. $row['system_id'] .'" style = "text-decoration: none;';

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id, s.status_type_text, i.issue_id
																FROM issue_entries i, status_type s
																WHERE i.system_id = {$row['system_id']} 
																AND i.status_type_id = s.status_type_id 
																AND i.start_time < '$now' 
																AND (i.end_time = 0 OR i.end_time > '$now') 
																ORDER BY s.status_type_text ASC");

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
								$i++;
								echo '</dl>'; // close row
							}

						?>

			</div>
	</div> <!-- end line -->
	<div class="cms-clear"></div>
	<!-- Add blog-like view of incidents -->
	<div class="row status-bar" style="clear: both; margin: 2em 0; padding: .75em 1%; background: #eee; border: 1px solid #bbb;">

		<div class="span2 unit left lib-horizontal-list">
			<ul>
				<li><a href="index.php" class="status-button btn btn-default <?php echo ($filter == 0 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-recent"><?php echo ($filter == 0 ? 'Showing' : 'Show'); ?> Recent</a></li>
				<li><a href="?status=unresolved" class="status-button btn btn-default <?php echo ($filter == 1 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-unresolved"><?php echo ($filter == 1 ? 'Showing' : 'Show'); ?> Unresolved</a></li>
				<li><a href="?status=resolved" class="status-button btn btn-default <?php echo ($filter == 2 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($filter == 2 ? 'Showing' : 'Show'); ?> Resolved</a></li>
			</ul>
		</div>

		<div class="span1 unit right subscription-list" style="text-align:right;">
			<p>Subscribe: <a href="<?php echo $rss_url; ?>" title="Subscribe to the RSS feed">RSS</a>&nbsp;
				//&nbsp;
				<a href="<?php echo $email_subscription_url; ?>" title="Subscribe to updates via Email">Email</a></p>
		</div>

		<div class="cms-clear"></div>
	</div>
	<?php

			$issue_result = $db->query($issue_query);
			$n_rows = $issue_result->num_rows;
			
			if ($n_rows > 0) {

				while ($issue_entries = $issue_result->fetch_assoc()) {

					$result = $db->query("SELECT s.status_id, s.issue_id, s.status_timestamp, s.status_public, s.status_user_id, s.status_text, s.status_delete, u.user_id, u.user_fn, u.user_ln, i.status_type_id, st.status_type_text
						FROM status_entries s, user u, status_type st, issue_entries i
						WHERE s.issue_id = '{$issue_entries['issue_id']}' 
						AND i.issue_id = s.issue_id
						AND s.status_user_id = u.user_id 
						AND i.status_type_id = st.status_type_id
						AND s.status_delete != 1
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
							<div class = "row issue-box span3">
								' . ($logged_in == 1 && $resolved == 0 ? '<div class="right status-update has-js">Add Update</div>' : '') .'
								<h2 id="issue_' . $issue_entries['issue_id'] . '"><a href="detail.php?id=' . $issue_entries['issue_id'] . '">' . $status_entries['status_type_text'] . ' for ' . $issue_entries['system_name'] . ' ' . $current_status .'</a></h2>
								<div class="comment-text" id="' . $status_entries['status_id'] . '">' 
								. (($logged_in == 1) && ($status_entries['status_user_id'] == $user["id"]) ? '<span class="edit-link" id="entry-' . $status_entries['status_id'] . '"  data-timestamp="' . $status_entries['status_timestamp'] . '" data-issue="' . $issue_entries['issue_id'] . '" data-type="' . $status_type_id . '">Edit</span>' : '') . 
								'<strong class="timestamp">[' . date("n/j @ g:i a", $status_entries['status_timestamp']) . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>
								<div style="display: none;" id="raw-' . $status_entries['status_id'] . '">' . $status_entries['status_text'] . '</div>';

								// Note the use of the date
								$displayed_date = date("y-n-j", $status_entries['status_timestamp']);
								$attribution_verb = ' was reported on ' . date("n/j/y", $status_entries['status_timestamp']) . ($resolved == 1 ? ' and resolved on ' . date('n/j/y', $issue_entries['end_time']) : '');
								
								if($issue_entries['status_type_id'] == 4) { // Maintenance
									$attribution_verb = ' is scheduled from ' . date("h:ia n/j/y", $status_entries['status_timestamp']) . ' until ' . date('h:ia n/j/y', $issue_entries['end_time']);
								}

								if($issue_entries['status_type_id'] == 5) { // Update
										$attribution_verb = ' happened on ' . date("n/j/y", $status_entries['status_timestamp']);
								}

								if($rc == $num_rows) { // Last comment, add the comment field
												add_comment_field($issue_entries['issue_id'], $status_type_id);
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
									<div class ="comment-text" id="' . $status_entries['status_id'] . '">'
										. (($logged_in == 1) && ($status_entries['status_user_id'] == $user["id"]) ? '<span class="edit-link" id="entry-' . $status_entries['status_id'] . '"  data-timestamp="' . $status_entries['status_timestamp'] . '" data-issue="' . $issue_entries['issue_id'] . '" data-type="' . $status_type_id . '">Edit</span>' : '') .
										
										'<strong class="timestamp">[' . $comment_time . ' - ' .$status_entries['user_fn'] . ']</strong> 
										' . Markdown($status_entries['status_text']) . '
										<div style="display: none;" id="raw-' . $status_entries['status_id'] . '">' . $status_entries['status_text'] . '</div>
									</div><!-- end comment-text -->
								</div> <!-- end comment-list --> ';

							if($rc == $num_rows) { // Last comment, add the comment form
								add_comment_field($issue_entries['issue_id'], $status_type_id);
								//echo '</div>';
							}

					
							

						}
						
					} 

					echo '<!--rc = ' . $rc . '-->';

					if($attribution != NULL) {

						echo $attribution . ' </div><!-- End .line -->';


					}

					

					 
				} // close status loop
			}


	?>


	<div class="row break footer">
	<div class="span3 unit break">
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

	<script src="https://prod.library.gvsu.edu/labs/chatbutton/chatbutton.js"></script>
	<script src="https://www.gvsu.edu/cms4/skeleton/0/files/js/cms4.0.min.js"></script>
	<script>cmsInit()</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src='https://www.google.com/recaptcha/api.js'></script>
<script>
$(document).ready(function() {

	$('#message-update').css('position','fixed');

	function timeConverter(UNIX_timestamp){
		  var a = new Date(UNIX_timestamp * 1000);
		  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		  var year = a.getFullYear();
		  var month = months[a.getMonth()];
		  var date = a.getDate();
		  var hour = a.getHours();
		  var min = a.getMinutes();
		  var sec = a.getSeconds();
		  var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
		  return time;
		}

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

	$('.edit-link').css('font-size','.9em').css('cursor','pointer').css('color','#0065A4');

	$('.edit-link').click(function(e){
		e.preventDefault();

		var issueId = $(this).attr('data-issue');
		console.log('Issue: ' + issueId);

		var statusId = $(this).parent('.comment-text').attr('id');
		console.log(statusId);

		var statusText = $('#raw-' + statusId).text();
		console.log(statusText);

		// Build the select menu

		var selectMenu = '<select name="update_status_type_id" id="update_status_type_id">' + $('select#status_type_id').html() + '</select>';

		var oldTime = timeConverter($(this).attr('data-timestamp'));
		console.log(oldTime);

		var statusType = $(this).attr('data-type');

		$(this).parent('.comment-text').html('<form method="post" name="update_comment_form" style="margin-top: .5em; font-size: 1em; width: 100%;"><input type="hidden" name="update_status_id" value="' + statusId + '"/><input type="hidden" name="issue_id" value="' + issueId + '" /><div style="float:left;"><label class="lib-inline" for="id="update-when">Time:</label>&nbsp;<input type="text" id="update-when" name="update-when" class="lib-inline" value="' + oldTime + '" /></div><div style="margin-left:2em;float:left"><label for="update_status_type_id">Issue Type:</label>' + selectMenu + '</div><br /><textarea name="update_status_text" style="height:5em;width:94%;margin: 1em 0;">' + statusText + '</textarea><br /><button class="btn btn-default" style="color: red !important;" id="status_delete" name="status_delete" type="submit" value="1">Delete Entry</button> <input type="submit" name="update_status" class="btn btn-primary" style="display:inline-block; float: right;margin-right:4%;" /></form>');

		$('select#update_status_type_id').find('option').each(function() {
			if($(this).val() == statusType) {
				$(this).attr('selected',true);
			}
		});

		$('#status_delete').click(function() {
			confirm('Are you sure you want to delete this entry?');
			return true;
		});
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
"		<input type=\"hidden\" name=\"url\" value=\"<?php echo urldecode($_GET['url']); ?>\">" +
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
	$(".issue-trigger").click(function(e) {

		e.preventDefault();

		$(".feedback").slideToggle(400);

	});

<?php

if(isset($_GET['problem'])) { // Force problem form to open
	
} else {

?>
	$(".feedback").hide();
	
	
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
