<?php
if(!session_status() === PHP_SESSION_ACTIVE) {
	session_start();
	$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	$_SESSION['location'] = $actual_url;
}
 //as well as loads required library files
include 'resources/secret/config.php';
include 'resources/php/functions.php';


//markdown is used to display the status entries for issues and the text of updates
include ('resources/php/markdown.php');

//load all starting session and other variables and required libraries
require 'resources/php/startup.php';

//holds system messages we want to show to the user.  By default, there are none
$userMessage = NULL;

// uncomment to force a login
$_SESSION['username'] = 'felkerk';


if (isset($_SESSION['username'])) { // User has logged in
	//if the user is logging out, don't bother to check anything, destroy the session
	//and reload the page
	if (isset($_REQUEST['logout'])) {
		$_SESSION = array();
		session_destroy();
		
	} else {
		//otherwise, attempt to make a user object
		
		$user = MakeUserArray($_SESSION['username'],'', $db);
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

	$text = $db->real_escape_string($_POST['issue_text']);
	$systemid = $_POST['system_id'];
	$statusid = $_POST['status_type_id'];
	$userid = $_POST['userid'];
					
	$time = verifyFormatTime($_POST["when"]);
				
	if($statusid == 4) { // Scheduled Maintenance
		$end_time = strtotime($_POST['end_time']);
	} 
				
	if($statusid == 0) { // Update
		$created = createNewUpdate($userid, $text, $time, $systemid, $public, $dataBaseConnection);
	} else {//otherwise issue
		$created = createNewIssue($systemid,$statusid,$time,$end_time,$userid,$text,$db);
	}
	//did we successfully create the issue/update?
	if (is_string($created)) {
		$userMessage = '<div class="alert alert-danger">' . $created . '</div>';
	} else {
		'<div class="alert alert-success">update or issue created.</div>';
	}
	
}

// new status post
if (isset($_POST['submit_status']) && $logged_in = 1) {
	$userid = $_POST['user_id'];
	$issue_id = $_POST['issue_id'];
	$status_text = $db->real_escape_string($_POST['status']);
	//verify and format time 
	$time = verifyFormatTime($_POST['when']);
	
	//is the user closing the issue?  If so, try to close it, and if that doesn't work, STOP
	//if it does work, or the user is not closing the issue, make the new status update
	if (isset($_POST['issue_resolved'])) {
		$time = verifyFormatTime($_POST["when"]);


		$closed = closeIssue($issue_id, $time, $db);
		if (is_string($closed)) {
			$userMessage = "<div class=\"alert alert-danger\">Problem Closing this issue: " . $closed . "</div>";
		} else {
			$statusCreated = createNewStatus( $issue_id, $time, $userid, $status_text, $db);
			
			if ($statusCreated == 1) {
				$userMessage = '<div class="alert alert-success">Your status has been added.</div>';	
			} else {
				$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $statusCreated  . '</div>';
			}
		}
	} else {
		$statusCreated = createNewStatus( $issue_id, $time, $userid, $status_text, $db);
		
		if ($statusCreated == 1) {
			$userMessage = '<div class="alert alert-success">Your status has been added.</div>';	
		} else {
			$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $statusCreated  . '</div>';
		}
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

//has the user chosen to look at building issues?
//if so set the $system variable so we can build the correct status table later

$system = 0;

if (isset($_GET['system'])) {
	if ($_GET['system'] != 0) {
		$system = $_GET['system'];
		

	}
	
} else if (isset($_POST['system'])) {
	if ($_POST['system'] != 0) {
		$filter = $_POST['system'];
		

	}	
}
//set part of query string for the status display, and the blog display



//has the user chosen to filter the issues?
// 0 = all issues, 1 = unresolved, 2= resolved 3 = updates
$filter = 0; // all issues by default

if (isset($_GET['filter'])) {
	if ($_GET['filter'] != 0) {
			$filter = $_GET['filter'];
			
	
	}
} else if (isset($_POST['filter'])) {
	if ($_POST['filter'] != 0) {
		$filter = $_POST['filter'];
		

	}
}



if(isset($_GET['thankyou'])) {
	$userMessage = '<div class="alert alert-success">Thanks! We&#8217;ll get right on that. If you shared your email, we&#8217;ll follow up with you soon.</div>';
}

if(isset($_GET['url'])) {
	$problem_url = urldecode($_GET['url']);
	
}
//load the header HTML
include 'resources/HTML/header.php';	


//display any messages associated with any updates the user performed
if ($userMessage != "") {

	echo '<div ID="message-update">';
	
	echo $userMessage;
	
	echo '</div>';

}

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

	<!--login link and submit issue buttons-->
	<div class="span2 unit right lib-horizontal-list" style="text-align: right;margin-top:.65em; overflow:visible;">
		<ul>

				<li style="float:right;"><?php echo (($logged_in == 1) ? '<a href="#" class="status-button btn btn-default has-js issue-trigger" style="margin-top:-.5em;" id="issue-trigger">Report an Issue</a>' : '<a href="feedback.php" class="btn btn-primary issue-trigger" style="margin-top:-.5em;overflow:visible;" id="feedback-trigger">Report a Problem</a>'); ?></li>

				<li style="float:right;margin-right: 8%;"><?php  echo (($logged_in == 1) ? 'Hello, ' . $user["fn"] . '&nbsp;//&nbsp;<a href="?logout" title="Log out" style="text-decoration: none; font-size: .9em;">Log out</a>' : "<a href=\"$loginUrl\" title=\"Log in\" style=\"text-decoration: none; font-size: .9em;\">Log in</a>"); ?></li>
		</ul>
	</div>
</div> <!-- end line -->

<div class="cms-clear"></div>
	<div class="row break" style="margin-top: 1em;">

		<?php

			if (!areUnresolvedSystemIssues($db)) {
				$status = '<div class="alert alert-success" style="margin: 0;"> <p>All systems are online.</p></div>';
			} else {
				$status = '<div class="alert alert-danger" style="margin: 0;">
									<p>Uh-oh, we have a system down. You can bet that we&#8217;re working on it!</p>
									</div>';
			}
			echo $status;

		?>

	</div> <!-- end line -->

	<div class="row status-bar" style="clear: both; margin: 2em 0; padding: .75em 1%; background: #eee; border: 1px solid #bbb;">
			

			<!--select wether you want to look at systems or buildings.  
			0=systems, 1=building issues, 3 = both-->

		<div class="span2 unit left lib-horizontal-list">
			<ul>
				<li><a href="index.php?system=0&filter=<?php echo $filter; ?>" class="status-button btn btn-default <?php echo ($system == 0 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-recent"><?php echo ($system == 0 ? 'Showing' : 'Show'); ?> Systems</a></li>
				<li><a href="index.php?system=1&filter=<?php echo $filter; ?>" class="status-button btn btn-default <?php echo ($system == 1 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-unresolved"><?php echo ($system == 1 ? 'Showing' : 'Show'); ?> Buildings</a></li>
				<li><a href="index.php?system=2&filter=<?php echo $filter; ?>" class="status-button btn btn-default <?php echo ($system == 2 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($system == 2 ? 'Showing' : 'Show'); ?> All</a></li>
				
			</ul>
		</div>

		<div class="cms-clear"></div>
	</div>

	<div class="row break status-table">

		<div class="half">

						<!-- build the status table for systems -->
						<?php
							//get all the systems from the database for the tab the user has chosen

						if ($system == 2) {
							$building = '';
						} elseif ($system == 0) {
							$building = ' WHERE building IS NULL';
							
						} else {
							$building = ' WHERE building IS NOT NULL';
						}

						$query = "SELECT * FROM systems $building ORDER BY system_name ASC";

						$result = $db->query($query);
						if (!$result) {
							echo "There was an error accessing the database: " . $db->error;

						} elseif ($result->num_rows < 1) {

							echo "No systems found.";
							
						} else {
							$i = 0;
							$system_count = $result->num_rows;

							// Calculate where to drop in the code for a second column
							$half = round($system_count/2);

							// loop through each system
							while($row = $result->fetch_assoc()) {
								if($i == $half) {
									echo '</div><div class="half">';
								}
								

								echo '<dl class="system">';
								echo '<dt><a href="detail.php?system='. $row['system_id'] . '" style="text-decoration: none;">' . $row["system_name"] . '</a></dt> ';
								echo '<dd class = "col2 name"><a href="detail.php?system='. $row['system_id'] .'" style = "text-decoration: none;';

								$status = getSystemStatus($row['system_id'], $db);
								
								if ($status == "Online") {
									echo 'color: #147D11;">Online';
								} else {
									echo 'color: #cb0000;">' . $status;
								}

								echo '</a></dd>'; // close currently displayed
								$i++;
								echo '</dl>'; // close row
							}
						}
						?>

			</div>
	</div> <!-- end line -->
	<div class="cms-clear"></div>
	

	<!--
		if the user is logged in, show the report form for logged in users.$_COOKIE
		Otherwise, show the form for non-logged in users
	
	-->
	<?php 
	
	if ($logged_in = 1) {
		include "resources/HTML/Report_problem_logged_in.php";
	
	}  else {
		include "resources/HTML/Report_problem_logged_in.php";
		}
	?>

	<!-- Add blog-like view of incidents -->
	<div class="row status-bar" style="clear: both; margin: 2em 0; padding: .75em 1%; background: #eee; border: 1px solid #bbb;">

		<div class="span2 unit left lib-horizontal-list">
			<ul>
				<li><a href="index.php?filter=0&system=<?php echo $system; ?>" class="status-button btn btn-default <?php echo ($filter == 0 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-recent"><?php echo ($filter == 0 ? 'Showing' : 'Show'); ?> Recent</a></li>
				<li><a href="index.php?filter=1&system=<?php echo $system; ?>" class="status-button btn btn-default <?php echo ($filter == 1 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-unresolved"><?php echo ($filter == 1 ? 'Showing' : 'Show'); ?> Unresolved</a></li>
				<li><a href="index.php?filter=2&system=<?php echo $system; ?>" class="status-button btn btn-default <?php echo ($filter == 2 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($filter == 2 ? 'Showing' : 'Show'); ?> Resolved</a></li>
				<li><a href="index.php?filter=3&system=<?php echo $system; ?>" class="status-button btn btn-default <?php echo ($filter == 3 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($filter == 3 ? 'Showing' : 'Show'); ?> Updates</a></li>
			
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

	//Get ready to retrieve issues and updates based on the system and filter criteria the user has chosen for the blog display
	
	//for the blog display, we don't want a limit, but we are setting recent to be true, which will only bring us
	//issues and updates created in the past year.
	$limit = false;

	//are they asking to see building or nonbuilding data?
	if ($system == 0) {
		$building = "NONE";
	} else if ($system == 1) {
		$building = "ALL";
	} else {
		$building = "EVERYTHING";
	}

	//don't show non-public issues if you aren't logged in
	if ($logged_in) {
		$public = false;
	} else {
		$public = true;
	}

	//get issues and updates.  Depending on the filter, we may want issues, updates, or both.  This will return them as 
	//multidimesional arrays, sorted 
	//by the most recently updated (see the functions to see how this works).  If both are requested, we need to do some work to 
	//merge and sort the resulting arrays by most recent update
	if ($filter == 0 ) {
		$open = "ALL";
		$issues = getIssues($building, '', '', $open, $public, "", $limit, true, $db);
		$updates = getUpdates($building, '', $public, '', $limit, true, $db);

		//now compare the last updated date of each issue to the timestamp of each update, pulling the more recenet one off the end of the array and putting it 
		//into our new array, producing a mixed array or issues and updates, soreted by date
		$results = array();

		//if there's an error, echo it and set the variable to be an empty array
		if (is_string($issues)) {
			echo "problem getting issues: " . $issues;
			$issues = array();
		} 

		if (is_string($updates) ) {
			echo "Problem getting updates: " . $updates;
			$updates = array();
		}

		while (count($issues) != 0 || count($updates != 0)) {
			//if we've run out of updates, append all the remaining issues to the array in order
			if (count($updates) == 0) {
				foreach ($issues as $issue){
					$results[] = $issue;
				}
				break;
			}
			//ditto for issues
			if (count($issues) == 0) {
				foreach ($updates as $update){
					$results[] = $update;
				}
				break;
			}
			//still got both?  compare times, pull the more recent one and append to array
			if (strtotime($issues[0]["last_updated"]) >= strtotime($updates[0]["timestamp"])) {
				$results[] = array_shift($issues);
			} else {
				$results[] = array_shift($updates);
			}


		}

	} else if ($filter == 1) {
		$open = "OPEN";
		$results = getIssues($building, '', '', $open, $public, "", $limit, true, $db);
		if (is_string($results)) {
			echo "problem getting issues: " . $results;
			$results = array();
		} 

	} else if ($filter == 2) {
		$open = "CLOSED";
		$results = getIssues($building, '', '', $open, $public, "", $limit, true, $db);
		if (is_string($results)) {
			echo "problem getting issues: " . $results;
			$results = array();
		}
	} else if ($filter == 3) {
		$results = getUpdates($building, '', $public, '', $limit, true, $db);
		if (is_string($results)) {
			echo "problem getting updates: " . $results;
			$results = array();
		}
	}
	
	//do we have issues to display?  Were there errors in getting the issues or updates?
	//if so, display the errors, and set flags so that we don't try to display empty arrays

	
	//display results if we have 'em

	foreach ($results as $result) {
		if ($result["type"] == "issue") {
			echo '<!-- Issue --> <div class = "row issue-box span3" id="'. $result["id"] . '">';
			displayIssue($result, $logged_in);
			//now start getting the status updates for this issue.  The function should sort them by date, we just have to display them
			$status_ids = getStatusIDs($result["id"], $db); //this should always return something, all issues have at least one status
			
			foreach ($status_ids as $statusid) {
				
				$status = getStatusData($statusid, $db);

				$status_user = MakeUserArray('', $status["userID"], $db);//likewise, this should return something.
				echo '<div class"comment-text" id="status_id"' . $statusid . '">';
				echo '<strong class="timestamp">[' . formatDateTime($status["timestamp"]) . ']-' . $status_user["fn"] . " " . $status_user["ln"] . "</strong>";
				echo Markdown($status["text"]);
				echo '</div>';

			}
			//if the user is logged in and the issue is open, display a status update form
			
			if (strtotime($result["end_time"]) > time() || is_null($result["end_time"])) {
				$resolved = false;
			} else {
				$resolved = true;
			}

			if ($logged_in == 1  && $resolved == 0) {
				$issueid = $result["id"];

				//has the user already tried to submit this form?  If so, retain the data.
				if (isset($_POST["status_form"])  && $_POST['issue_id'] == $result['id']){
					
					$text = $_POST["text"];
					$when = $_POST["when"];
					if (isset($_POST["resolved"])) {$resolved = true;} else {$resolved = false;}
					
					$userid = $_POST["user_id"];

					
				} else {
					$text = '';
					$when = "Now";
					$resolved = false;
					
					$userid = $user["id"];
				}

				echo displayNewStatusForm($issueid, $userid, $text, $resolved, $when, $system, $filter);
			}
			echo '</div>';
		
		} else if ($result["type"] == "update") {
			
			echo '<!-- Issue --> <div class = "row issue-box span3" id="'. $result["id"] . '">';
			displayUpdate($result, $db);
			echo '</div>';
			
		}	
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

<?php include "resources/HTML/footer.php"; ?>

</body>

</html>
