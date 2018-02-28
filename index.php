<?php
$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
setcookie("referrer", $actual_url, 0, "/");

if (!isset($_COOKIE["login"])) {
	setcookie("login", "", 0, "/");
        $_COOKIE["login"] = "";
}

if (isset($_GET["url"])) {
	$url = $_GET["url"];
}

if (isset($_GET["problem"])) {
	if (isset($url)) {
		header('Location: https://prod.library.gvsu.edu/status/?url=' . $url . "#problem");
	} else {
		header('Location: https://prod.library.gvsu.edu/status/#problem');
	}
}

 //as well as loads required library files
require 'resources/config/config.php';
require 'resources/php/functions.php';


//markdown is used to display the status entries for issues and the text of updates
require ('resources/php/markdown.php');

//sets session and other variables that have to be initialized when any page of the status app is loaded

	
date_default_timezone_set('America/Detroit');

//if the user is trying to log out, send them to the logout script with a flag set

if (isset($_GET["logout"])) {
	setcookie("login", "", 0, "/");
	$_COOKIE["login"] = "";
	Location ("https://prod.library.gvsu.edu/loginstatus/?>logout=true");
}

//if using native login, set the URL
if ($use_native_login == true){
    $loginUrl = "login.php";

} else {
    $loginUrl = $non_native_login_url;
}
//can we connect to the database?  If not, display an error and cease loading the app
$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
if ($db->connect_errno) {
    HTML_error_message($db->connect_error);
    exit;
}


//holds system messages we want to show to the user.  By default, there are none
$userMessage = NULL;

//variable to track if user is logged in
$logged_in = 0; 



if ($_COOKIE['login'] != "") { // User has logged in
	
		

	//attempt to make a user object
		
	$user = MakeUserArray($_COOKIE['login'],'', $db);
	if (is_array($user)) {
		$logged_in = 1;
	} else {
		//set a message for the user if they couldn't be found
		$userMessage = "<div class=\"alert alert-danger\">" . $user . "</div>";
	}
	
}

// User has filled out the asana problem submission form, process and submit
if (isset($_POST["email-asana"])) {
	
	//verify the captcha
	if (isset($_POST["g-recaptcha-response"]) && $_POST["g-recaptcha-response"] != "") {
		$verify = verifyRecaptcha($_POST["g-recaptcha-response"], $recaptchaSecretKey);
	} else {
		$verify = false;
	}


	if ($verify === true) {
		if (isset($_POST["name"])) {$name = $_POST["name"];} else {$name = "none";}
		if (isset($_POST["email"])) {$email = $_POST["email"];} else {$email = "";}
		$message = $_POST["feedback"];
		if (isset($_POST["url"])) {
			$message = $message . " url=" . $_POST["url"];
		}

		$result = send_email($name,$email,$message);

		if ($result) {
			$userMessage = '<div class="alert alert-success">Report sent!  We will get on it ASAP!</div>';
		} else {
			$userMessage = '<div class="lib-error">Uh-oh. There was a problem sending your report. Maybe try calling the library at ' . $library_phone . '?</div>';
		
		}
	} else {
		$userMessage = '<div class="lib-error">You need to verify that you are not a robot by using the captcha.  Please try again.</div>';
		
	}

}


// post a new issue or update to the database
if (isset($_POST['submit_issue']) && $logged_in == 1) {


	if (isset($_POST["public"])) {
		
		$public = 1;
	} else {
		$public = 0;
	}
	
	
	$text = $db->real_escape_string($_POST['issue_text']);
	$systemid = $_POST['system_id'];
	$statusid = $_POST['status_type_id'];
	$userid = $_POST['userid'];
	$time = $_POST['when'];
		
	if (!isset($_POST['end_time'])) {
		$end_time = '';
		
	} else {
			$end_time = $_POST['end_time'];
		
	}

	if ($time == "Now") {
		$time = date("Y-m-d H:i:s");
	}

	//check data for problems
	
	$verify = verifyReportFormData($time, $end_time, $statusid, $systemid, $db);
				
	
	if ($verify !== true) {
		$userMessage = '<div class="alert alert-danger">' . $verify . '</div>';
		$verify = false;
	}
	
	

				
	if($statusid == 0 && $verify) { // Update
		
		$created = createNewUpdate($userid, $text, $time, $systemid, $public, $db);
		if (is_string($created)) {
			$userMessage = '<div class="alert alert-danger">' . $created . '</div>';
			$submitted = false;
		} else {
			$userMessage = '<div class="alert alert-success">update created.</div>';
			
		}
	} else if ($statusid != 0 && $verify) {//otherwise issue

		//is someone trying to report a building problem?
		$building = getBuilding($_POST['system_id'], $db);

		echo $building;
		//do you have access to report building problems?
		if (is_null($building)) {
	
			$can_post = true;

		} else if ($building === false) {
			$can_post = false;
			$userMessage = '<div class="alert alert-danger">Unable to post: cannot get information on system from the database.</div>';
		} else {
			if ($_POST['status_type_id'] == 7 && ($user["access"] != 2 || $user["access"] != 9)) {
				$can_post = false;
				$userMessage = '<div class="alert alert-danger">You are not authorized to log this type of issue on this system.</div>';

			}
		}

		if ($can_post) {

			$created = createNewIssue($systemid,$statusid,$time,$end_time,$userid,$text,$public,$db);
			if (is_string($created)) {
				$userMessage = '<div class="alert alert-danger">' . $created . '</div>';
				$submitted = false;
			} else {
				$userMessage = '<div class="alert alert-success">issue created.</div>';
			
			}
		}
	}
}

	// create new status post for an existing issue
	if (isset($_POST['submit_status']) && $logged_in = 1) {
		$userid = $_POST['user_id'];
		$issue_id = $_POST['issue_id'];
		$status_text = $db->real_escape_string($_POST['status']);
		//verify and format time 
	
	
	//is the user closing the issue?  If so, try to close it, and if that doesn't work, STOP
	//if it does work, or the user is not closing the issue, make the new status update
	if (isset($_POST['issue_resolved'])) {
		


		$closed = closeIssue($issue_id, $db);
		if (is_string($closed)) {
			$userMessage = "<div class=\"alert alert-danger\">Problem Closing this issue: " . $closed . "</div>";
		} else {
			$statusCreated = createNewStatus( $issue_id, $userid, $status_text, $db);
			
			if ($statusCreated === true) {
				$userMessage = '<div class="alert alert-success">Your status has been added.</div>';	
			} else {
				$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $statusCreated  . '</div>';
			}
		}
	} else {
		$statusCreated = createNewStatus( $issue_id, $userid, $status_text, $db);
		
		if ($statusCreated === true) {
			$userMessage = '<div class="alert alert-success">Your status has been added.</div>';	
		} else {
			$userMessage = '<div class="alert alert-danger">There was a problem adding your status. ' . $statusCreated  . '</div>';
		}
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

//has the user asked to look at issues for a specific system?

if (isset($_GET['systemID'])) {
	$systemID = $_GET['systemID'];


} else {
	$systemID = "ALL";
}



if(isset($_GET['url'])) {
	$problem_url = urldecode($_GET['url']);
	
}
//load the header HTML
include 'resources/php/header.php';	

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

	<!--login link-->
	<div class="span2 unit right lib-horizontal-list" style="text-align: right;margin-top:.65em; overflow:visible;">
		<ul>

				
				<li style="float:right;margin-right: 8%;"><?php  echo (($logged_in == 1) ? 'Hello, ' . $user["fn"] . '&nbsp;//&nbsp;<a href="' . $loginUrl . "/index.php?logout=true" . '" title="Log out" style="text-decoration: none; font-size: .9em;">Log out</a>' : "<a href=\"$loginUrl\" title=\"Log in\" style=\"text-decoration: none; font-size: .9em;\">Log in</a>"); ?></li>
		</ul>
	</div>
</div> <!-- end line -->

<div class="cms-clear"></div>
	<div class="row break" style="margin-top: 1em;">
	<div class="alert alert-success" style="margin: 0;"> Welcome to the new Status App!  We have migrated all currently-open issues form the old system to the new one.  If you experience problems or bugs, email felkerk@gvsu.edu and our app developer will get right on it.  The old status app can be accessed at: <a href="http://prod.library.gvsu.edu/oldstatus">http://prod.library.gvsu.edu/oldstatus</A></div>

		<?php

			if ($userMessage != "") {
	
				
				echo $userMessage;
		
	
			}

			$systemQuery = "SELECT i.issue_id FROM issue_entries i, systems s WHERE s.system_id = i.system_id AND s.building IS NULL AND i.status_type_id = 2 AND start_time < NOW() AND (i.end_time IS NULL OR i.end_time > NOW()) ";
			$unResolvedIssues = $db->query($systemQuery);

			if ($unResolvedIssues === false) {
				$status = '<div class="alert alert-danger" style="margin: 0;"> <p>Unable to retrieve status information.</p>' . $db->error . '</div>';

			} elseif ($unResolvedIssues->num_rows > 0) {
				$status = '<div class="alert alert-danger" style="margin: 0;">
									<p>Uh-oh, we have a system down. You can bet that we&#8217;re working on it!</p>
									</div>';
			} else {
				$status = '<div class="alert alert-success" style="margin: 0;"> <p>All systems are online.</p></div>';
			}
			echo $status;

		?>

	</div> <!-- end line -->

	<div class="row status-bar" style="clear: both; margin: 2em 0; padding: .75em 1%; background: #eee; border: 1px solid #bbb;">
			

			<!--select wether you want to look at systems or buildings.  
			0=systems, 1=building issues, 3 = both-->

		<div class="span2 unit left lib-horizontal-list">
			<ul>
				<li><a href="index.php?system=0&filter=<?php echo $filter; ?>&systemID=<?php echo $systemID; ?>" class="status-button btn btn-default <?php echo ($system == 0 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-recent"><?php echo ($system == 0 ? 'Showing' : 'Show'); ?> Systems</a></li>
				<li><a href="index.php?system=1&filter=<?php echo $filter; ?>&systemID=<?php echo $systemID; ?>" class="status-button btn btn-default <?php echo ($system == 1 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-unresolved"><?php echo ($system == 1 ? 'Showing' : 'Show'); ?> Buildings</a></li>
				<li><a href="index.php?system=2&filter=<?php echo $filter; ?>&systemID=<?php echo $systemID; ?>" class="status-button btn btn-default <?php echo ($system == 2 ? 'active' : ''); ?>" style="margin-top: -.5em" id="filter-resolved"><?php echo ($system == 2 ? 'Showing' : 'Show'); ?> All</a></li>
				
			</ul>
		</div>

		<div class="cms-clear"></div>
	</div>

	<div class="row break status-table">

		<div class="half">

						<!-- build the status table for systems -->
						<?php
						//get all the systems from the database for the tab the user has chosen


						//also prepare an array of systems for the user to filter blog posts by

						$systemArray = array();

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
								
								//populate the array of systems for later filtering
								$systemArray[$row['system_id']] = $row["system_name"];

								echo '<dl class="system">';
								echo '<dt>' . $row["system_name"];
								if (!is_null($row["building"])){
									echo '<br>' . $row["building"];

								}
								
								echo '</dt> ';
								echo '<dd class = "col2 name" style="';

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
		if the user is logged in, show the report form for logged in users.
		Otherwise, show the form for non-logged in users (submits to Asana)
	
	-->

	
	<?php 
	
	if ($logged_in == 1) {
		require "resources/php/report_problem_logged_in.php";
	
	}  else {
		require "resources/php/report_problem_not_logged_in.php";
	}
	?>

	<!-- Add blog-like view of incidents -->
	<div class="cms-clear"></div>
	

	<div class="row status-bar">

		<div class="span2 unit left">
			
			<form action="index.php" method="get">
	
			<input type="hidden" name="system" value="<?php echo $system; ?>">

			
			<label for="filter"> Filter by type of post:</label>
			<select name="filter">
	
				<option value="0" <?php if ($filter == "0") {echo "selected";}?>>All Recent</option>
				<option value="1" <?php if ($filter == "1") {echo "selected";}?>>Unresolved</option>
				<option value="2" <?php if ($filter == "2") {echo "selected";}?>>Resolved</option>
				<option value="3" <?php if ($filter == "3") {echo "selected";}?>>Updates</option>
			</select>
		</div>
		<div class="span1 unit right">
			<label for="systemID"> Filter by system:</label>
				<select name="systemID">
				<option value="ALL">All</option>
				<?php
					foreach ($systemArray as $id => $name) {
					if ($id == $systemID) {$selected = "selected";} else {$selected = "";}
					echo '<option value="' . $id . '" '. $selected .'>' . $name . '</option>';

					}
				?>
				</select>
				<input type="submit" value="Filter">
				</form>
		</div>
		<div class="cms-clear"></div>
	</div>
	
	
<div class="row">
	<div class="span3 unit left subscription-list" style="text-align:right;">
                        <p>Subscribe: <a href="<?php echo $rss_url; ?>" title="Subscribe to the RSS feed">RSS</a>&nbsp;
                                //&nbsp;
                                <a href="<?php echo $email_subscription_url; ?>" title="Subscribe to updates via Email">Email</a></p>
    </div>
</div>

		

	
	<div class="cms-clear"></div>
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
	if ($logged_in == 1) {
		$public = false;
	} else {
		$public = true;
	}

	if ($systemID == "ALL") {
		$systemID = '';
	} 

	//get issues and updates.  Depending on the filter, we may want issues, updates, or both.  This will return them as 
	//multidimesional arrays, sorted 
	//by the most recently updated (see the functions to see how this works).  If both are requested, we need to do some work to 
	//merge and sort the resulting arrays by most recent update
	if ($filter == 0 ) {
		$open = "ALL";
		$issues = getIssues($building, '', $systemID, $open, $public, "", $limit, true, $db);
		$updates = getUpdates($building, $systemID, $public, '', $limit, true, $db);

		
		$results = array();

		

		//if there's an error, echo it and set the variable to be an empty array
		if (is_string($issues) || is_string($updates)) {
			echo "problem getting data: " . $issues;
			$issues = array();
			$updates = array();
		} else if ($issues == false && $updates == false) {
		 echo '<P>No entries found.  Try changing your filter options.</P>';
		} 
		if ($issues == false) {$issues = array();}

		if (is_string($issues)) {$issues = array();}

		if (is_string($updates)) {$updates = array();}

		if ($updates == false) {$updates = array();}
		

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
		$results = getIssues($building, '', $systemID, $open, $public, "", $limit, true, $db);
		if (is_string($results)) {
			echo "problem getting issues: " . $results;
			$results = array();
		} else if ($results === false) {
			echo "<P>No entries found.  Try changing your filter options.</P>";
			$results = array();
		}

	} else if ($filter == 2) {
		$open = "CLOSED";
		$results = getIssues($building, '', $systemID, $open, $public, "", $limit, true, $db);
		if (is_string($results)) {
			echo "problem getting issues: " . $results;
			$results = array();
		} else if ($results === false) {
			echo "<P>No entries found.  Try changing your filter options.</P>";
			$results = array();
		}
	} else if ($filter == 3) {
		$results = getUpdates($building, $systemID, $public, '', $limit, true, $db);
		if (is_string($results)) {
			echo "problem getting updates: " . $results;
			$results = array();
		} else if ($results === false) {
			echo "<P>No entries found.  Try changing your filter options.</P>";
			$results = array();
		}
	}
	
	
	
	//display results if we have 'em

	foreach ($results as $result) {
		if ($result["type"] == "issue") {
			echo '<!-- Issue --> <div class = "row issue-box span3" id="'. $result["id"] . '">';

			
			displayIssue($result);
			//now start getting the status updates for this issue.  The function should sort them by date, we just have to display them
			$status_ids = getStatusIDs($result["id"], $db); //this should always return something, all issues have at least one status
			
			foreach ($status_ids as $statusid) {
				
				$status = getStatusData($statusid, $db);

				displayStatus($status, $db);

			}
			if ($logged_in == 1) {echo '<P><a href="detail.php?type=' . $result["type"] . '&id=' . $result["id"] . '">Edit, delete, or reopen this issue</a></P>';}
			//if the user is logged in and the issue is open, display a status update form
			
			if (strtotime($result["end_time"]) > time() || is_null($result["end_time"])) {
				$resolved = false;
			} else {
				$resolved = true;
			}

			if ($logged_in == 1  && $resolved === false) {
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
			
			echo '<!-- Issue --> <div class = "row issue-box span3" id="'. $result["update_id"] . '">';
			displayUpdate($result, $db);
			if ($logged_in == 1  && $user["id"] == $result["user"]) {echo '<a href="detail.php?type=' . $result["type"] . '&id=' . $result["update_id"] . '">Edit or delete this update</a>';}
			echo '</div>';
			
		}	
	}
		
		
	?>


	<div class="row break footer">
	<div class="span3 unit break">
		<p>Written by <a href="mailto:felkerk@gvsu.edu" title="K-felk">Kyle Felker</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>
	</div> <!-- end span -->
	</div> <!-- end line -->
</div><!-- End #cms-content -->
				</div><!-- End #cms-body-table -->
			</div><!-- End #cms-body-inner -->
		</div><!-- end #cms-body -->
	</div><!-- end #cms-body-wrapper -->

<?php include "resources/php/footer.php"; ?>

</body>

</html>
