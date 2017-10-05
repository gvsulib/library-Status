<?php

/*
	When a user submits a problem report, this function checks
	the input to make sure it isn't malicous (or a spambot!) and
	then sends an email with a problem report to the specified
	email address.
*/

function send_email($name,$email,$message) {

	global $m, $to_email, $from_email, $email_subject;

	// If there is an error, the message suggests calling the library. Add your
	// Library's phone number here.
	$library_phone = '(616) 331-3500';

/*
	Uncomment the following line if you want to require emails. You should also
	see the README for information on how to check for emails on the client side
	with JavaScript.
*/
//require_field($email, "email address");

/*
	Uncomment the following line if you want to require names. You should also
	see the README for information on how to check for names on the client side
	with JavaScript.
*/
//require_field($name, "name");

	// Build the headers
	$headers = "From: " . $from_email . "\r\n";
	$headers .= "Reply-To: " . $from_email . "\r\n";
	$headers .= "X-Mailer: PHP/".phpversion();

	// Check to make sure there are no really sneaky naught bits in the message
	contains_bad_str($message);
	contains_bad_str($email);
	contains_bad_str($name);
	contains_newlines($email);
	contains_newlines($name);

	// Build the message
	$error_report = $message;
	$error_report .= "\n\n" . 'From: ' . $name;
	$error_report .= "\n" . 'Email: ' . $email;

	// Attempt to send the mail, then set the message variable $m to success or
	// error.
	if($m == NULL) { // There have been no errors, send the email
		if(mail($to_email, $email_subject, $error_report, $headers)) {
				$m = '<div class="lib-success">Thanks for reporting this issue! We&#8217;ll get right on it.</div>';
			} else {
				$m = '<div class="lib-error">Uh-oh. There was a problem sending your report. Maybe try calling the library at ' . $library_phone . '?</div>';
			}
	}
}

/*
	This function checks to see if the email address provided is actually an email
	address. If you decide to require email addresses in the send_email() function,
	you'll be using this.
*/

function is_valid_email($email) {

  return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
}

/*
	This function looks for common spam injection terms that bots use to turn your
	email form into a mass mailer. It rejects any submission with those terms. You
	can add additional terms by adding items to the $bad_strings array.
*/

function contains_bad_str($str_to_test) {

	global $m;

  $bad_strings = array(
                "content-type:"
                ,"mime-version:"
                ,"multipart/mixed"
		,"Content-Transfer-Encoding:"
                ,"bcc:"
		,"cc:"
		,"to:"
  );

	// Check the field for each of the bad strings, and if you we find one, set the
	// message variable $m to show the error.
  foreach($bad_strings as $bad_string) {
    if(preg_match($bad_string, strtolower($str_to_test))) {
      $m = '<div class="lib-error">Your message looks a lot like what a spambot would try to submit. Try again without the naughty bits.</div>';
    }
  }
}

/*
	This function checks the email address field to see if the bot is trying to sneak
	something into the mail header to send out mass spam. It rejects any submission
	that meets the criteria.
*/

function contains_newlines($str_to_test) {

	global $m;

   if(preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str_to_test) != 0) {
     $m = '<div class="lib-error">Your email address was formatted in a way that looks a lot like what a spambot would do if they wanted to send out a zillion emails.  Try again without the naughty bits.</div>';
     exit;
   }
}

/*
	This function checks to make sure that a required value was provided and
	prompts the user to supply it before continuing.
*/
function require_field($value, $key) {

	global $m;

	if(($value == NULL) || ($value == "")) {
		$m = '<div class="lib-error">Please include your ' . $key . '.</div>';
	}

}

/*
	This function adds a comment form at the end of an open issue, only if the
	item has not been resolved and the user is logged in.
*/

function add_comment_field($issue_id, $status_type_id) {

	global $logged_in, $resolved;

	if(($logged_in == 1) && ($resolved == 0)) {

		echo '<div class="lib-form add-comment-form" style="margin-top: .5em; padding-top: .5em; border-top: 1px dotted #bbb;">

			<form action="" method="POST" name="status-form">
				<fieldset>
				<legend>Add a Status Update</legend>
				<label for="status-' . $issue_id . '" style="display:none;">Update Status</label>
				<textarea style="margin-top: .5em; height: 5em; font-size: 1em; width: 100%;" id="status-' . $issue_id . '" name="status" placeholder="Update the Status of this Issue"></textarea>

			<div class="row" style="margin-top:.5em;">
				<div class="span2" >

					<label style="margin-left: 1em;display:inline;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
					<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1">

					<label class="lib-inline" style="display:inline;margin-left:1em;" for="comment-when-' . $issue_id . '" >When</label>
					<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-' . $issue_id . '" value="Now" />
				</div>
				<div class="left unit span1 lastUnit" style="text-align:right;">
					<input class="status-button" name="submit_status" type="submit" value="Update" />
				</div>
																						<div class="cms-clear" style="padding-bottom:.5em;"></div>

			</div>


				<input type="hidden" name="issue_id" value="' . $issue_id . '" />
				<input type="hidden" name="status_type_id" value="' . $status_type_id . '" />
			</fieldset>
			</form>

		</div>';

	}
}

//function to wrap error messages in HTML if we need to display them without loading the page
///this is most often done because we can't connect to the database
function HTML_error_message($errormsg) {
	echo <<<EOD
	<!DOCTYPE html>
	<html lang="en">
	
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Status Error</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
	Error:  Status app cannot load.  System error message noted below
	<P>$errormsg</P>
</body>
</html>
EOD;
}
//FORMATTING AND VERIFICATION FUNCTIONS

//formats the DATETIME values returned form the database into more readable format for display
function formatDateTime ($string) {
	$date = new DateTime($string);
	return $date-> format('Y-m-d @ g:i A');
}


//used to verify and format the time returned from user-filled forms into a unix timestamp
function verifyFormatTime($time) {
	// Create a time one year back to see use to check if posting time is in range.
	$time_check = time();
	$time_check = strtotime('-1 month');

	// If time is something special or ready or for now and is within the last year.
	if (($time != 'Now') && ($time > $time_check)) {
		$time = strtotime($time);
	} else {
		$time = time();
	}
	return $time;

}

//FUNCTIONS TO GET DATA FROM DATABASE


//get the status of a specific system
function getSystemStatus($systemID, $dataBaseConnection) {
	$query = "SELECT status_type_id FROM issue_entries WHERE system_id = $systemID AND (end_time > NOW() OR end_time IS NULL)";
	$ids = $dataBaseConnection->query($query);
	if (!$ids) {
		return $dataBaseConnection->error;
	}
	if ($ids->num_rows <= 0) {
		return "Online";

	}

	while ($row = $ids->fetch_assoc()) {
		if ($row["status_type_id"] == 2 || $row["status_type_id"] == 4 || $row["status_type_id"] == 7) {
			return "Offline";
		
		}
	}

	return "Minor Issue";

	

}

//get updates from the updates table based on different criteria.  Return the update data as an array,
//or an error message if none are found
function getUpdates($building, $system, $public, $user, $limit, $recent, $dataBaseConnection) {
	
	
	$query = "SELECT u.*, s.system_name, s.building FROM updates u, systems s WHERE u.system_id = s.system_id ";
	
	if ($recent == true) {
		$current_date = new DateTime();
		$year_ago = $current_date->modify("-1 year");
		$yearAgo = $current_date->getTimestamp();
		$query = $query . "AND u.timestamp > FROM_UNIXTIME($yearAgo) ";
	}
	
	//if public is set to true, bring only public display items, otherwise, get all of them.
	
	if ($public) {
		
		$query = $query . "AND u.public = 1 ";
			
	}
		
	
	//the acceptable values are the name of the building, "EVERYTHING" (all issues)
	//"ALL"  (all non-system issues) and "NONE" (all non-building issues)
	
	if ($building == "EVERYTHING") {
		$query = $query;

	} elseif ($building == "ALL") {
		$query = $query . "AND s.building IS NOT NULL ";

	} elseif ($building == "NONE") {
		$query = $query . "AND s.building IS NULL ";
	} else {
		$query = $query . "AND s.building = \"$building\" ";
	}
	if ($system) {
		$query = $query . "AND s.system_id = $system ";

	}
	if ($user) {
		
		$query = $query . "AND u.created_by = $user ";
	}
	
	$query = $query . "ORDER BY u.timestamp DESC ";
	
	if ($limit) {
		
		$query = $query . "LIMIT $limit";
	}
	
	$updates = $dataBaseConnection->query($query);
	
	if (!$updates) {
		return $dataBaseConnection->error;
	} elseif ($updates->num_rows <= 0) {
		return "No updates found.";
	} else {
		$return_array = array();
		while ($row = $updates->fetch_assoc()) {
			$return_array[] = array("timestamp" => $row["timestamp"], 
			"text" => $row["text"],
			"system_name" => $row["system_name"],
			"system_id" => $row["system_id"],
			"user" => $row["created_by"],
			"building" => $row["building"],
			"id" => $row["update_id"],
			"type" => "update"
			);
			
			
		}
	}
	return $return_array;
}


//gets issue data by various criteria.  Will either return an array of all issue IDs matching criteria, a database
//error, or a message indicating no data could be found for those criteria.
function getIssues($building, $status, $system, $open, $public, $user, $limit, $recent, $dataBaseConnection) {

	//start building the query
	$query = "SELECT i.*, s.system_name, s.building, r.status_type_text FROM  issue_entries i, systems s, status_type r 
	WHERE s.system_id = i.system_id AND i.status_type_id = r.status_type_id ";
	//start checking the various parameters. 
	
	//if recent is set to true, bring only issues created in the past year
	if ($recent == true) {
		$current_date = new DateTime();
		$year_ago = $current_date->modify("-1 year");
		$yearAgo = $current_date->getTimestamp();
		$query = $query . "AND i.created_on > FROM_UNIXTIME($yearAgo) ";
	}
	
	//if public is set to true, bring only public display items, otherwise, get all of them.
	if ($public) {
		$query = $query . "AND i.public = 1 ";
	}
	
	//$building flag: the acceptable values are to leave it blank (all issues)
	//"ALL"  (all non-system issues) and "NONE" (all non-building issues)
	
	if ($building == "ALL") {
		$query = $query . "AND s.building IS NOT NULL ";

	} elseif ($building == "NONE") {
		$query = $query . "AND s.building IS NULL ";
	} 

	//if you set these to be anything other than false, use the numbers provided
	if ($status) {
		$query = $query . "AND i.status_type_id = $status ";

	}
	if ($system) {
		$query = $query . "AND s.system_id = $system ";

	}

	//$open can be set to "OPEN" (all open issues)  "CLOSED" (all closed issues) or "ALL" for all open or closed issues
	if ($open == "OPEN") {
		$query = $query . "AND (i.end_time IS NULL OR (i.start_time > NOW() AND i.end_time < NOW())) ";

	} elseif ($open == "ALL") {
		$query = $query;
	} else {
		$query = $query . "AND (i.end_time IS NOT NULL AND i.end_time < NOW()) ";
	}
	if ($user) {
		$query = $query . "AND i.created_by = $user ";
	}

	//order issues by most recently updated.  This makes peeling off recent ones and sorting easy
	$query = $query . " ORDER BY i.last_updated DESC ";

	if ($limit) {

		$query = $query . "LIMIT $limit";
	}

	$ids = $dataBaseConnection->query($query);

	if (!$ids) {
		return $dataBaseConnection->error;
	} elseif ($ids->num_rows <= 0) {
		return "No issues found.";
	} else {
		$return_array = array();
		while ($row = $ids->fetch_assoc()) {
			$return_array[] = array("start_time" => $row["start_time"], 
			"end_time" => $row["end_time"], 
			"system_name" => $row["system_name"],
			"status" => $row["status_type_id"],
			"user" => $row["created_by"],
			"building" => $row["building"],
			"status_name" => $row["status_type_text"],
			"created_on" => $row["created_on"],
			"last_updated" => $row["last_updated"],
			"id" => $row["issue_id"],
			"type" => "issue"
		);
		}
		return $return_array;
	}
}

//used to check and see if any systems currently have an outstanding major issue
function areUnresolvedSystemIssues($dataBaseConnection) {
	$time = time();
	//first check to see if there are any major issues with no end time set
	$query = "SELECT i.issue_id FROM issue_entries i, systems s WHERE s.system_id = i.system_id AND s.building IS NULL AND i.status_type_id in (2,4) AND (i.end_time IS NULL OR i.end_time > NOW)) ";
	$unresolvedIssues = $dataBaseConnection->query($query);
	if (!$unresolvedIssues) {
		return $dataBaseConnection->error;
	}

	if ($unresolvedIssues->num_rows > 0) {
		return true;
	} else {
		return false;
	}
	

}


//get all status IDs for a given issue and return them as an array
function getStatusIDs($issueID, $dataBaseConnection) {
	$query = "SELECT status_id FROM status_entries WHERE issue_id = '$issueID' ORDER BY status_timestamp ASC";
	$IDs = $dataBaseConnection->query($query);
	if ($IDs && ($IDs->num_rows > 0)) {
		$IDsArray = array();
		while($row = $IDs->fetch_assoc()) {
			$IDsArray[] = $row["status_id"];
		}
		return $IDsArray;
	} else {
		return "Status IDs could not be retrieved for this issue: " . $issueID;
	}
}

//get data on an issue and return it as an array of values
function getIssueData($issue_id, $dataBaseConnection) {
	$query = "SELECT * from issue_entries WHERE issue_id = '$issueID'";
	$issue = $dataBaseConnection->query($query);
	if ($issue && ($issue->num_rows > 0)) {
		$issue_array = array("issueID" => $issue_id);
		while($row = $issue->fetch->assoc()) {
			$issue_array["systemID"] = $row["system_id"];
			$issue_array["status"] = $row["status_type_id"];
			$issue_array["startTime"] = $row["start_time"];
			$issue_array["endTime"] = $row["end_time"];
			$issue_array["userID"] = $row["created_by"];
			$issue_array["resolved"] = $row["resolved"];
			$issue_array["created_on"] = $row["created_on"];
			$issue_array["last_updated"] = $row["last_updated"];
		}
		return $issue_array;
	} else {
		return $dataBaseConnection->error;
	}

}
//get a status entry by ID number
function getStatusData($statusID, $dataBaseConnection) {
	$query = "SELECT * from status_entries WHERE status_id = $statusID";
	$status = $dataBaseConnection->query($query);
	if (!$status) {
		return $dataBaseConnection->error;
	}
	if ($status->num_rows <= 0) {
		return "No status found in database.";

	}
	$row = $status->fetch_assoc(); 
	
	$status_array = array("statusID" => $statusID);
	$status_array["timestamp"] = $row["status_timestamp"];
	$status_array["userID"] = $row["status_user_id"];
	$status_array["text"] = $row["status_text"];	
			
	
	return $status_array;
	
}
//gets user information about a user and returns it as a PHP array for easy reference
function MakeUserArray ($userName, $userid, $dataBaseConnection) {
	if ($userName) {
		$criteria = "WHERE user_username = '$userName'";
	} else {
		$criteria = "WHERE user_id = '$userid'";
	}
	$user_result=$dataBaseConnection->query("SELECT * FROM user $criteria LIMIT 1");
	
	if(($user_result) && ($user_result->num_rows > 0)) { // Query was successful, a user was found
	
		$user = array("username" => $userName);
		while($row = $user_result->fetch_assoc()) {
			$user["access"] = $row["user_access"];
			$user["id"] = $row["user_id"];
			$user["fn"] = $row["user_fn"];
			$user["ln"] = $row["user_ln"];
		}
	
		
		return $user;
	} else {
		return "User could not be found in table";
	}
}

//FUNCTIONS TO DISPLAY THINGS

function displayNewStatusForm($issue_id, $userid, $text, $resolved, $when, $system, $filter) {
	
	if ($resolved) {
		$resolved = "checked";
	} else {
		$resolved = '';
	}

	if (!$when) {
		$when = "Now";
	}
	
	$str = <<< EOD

	<div name="new_status" style="clear: all">
	<form name="status-form" method="POST">
		<fieldset>
			<input name="issue_id" type="hidden" value="$issue_id">
			<input name="user_id" type="hidden" value="$userid">
			<input name="system" type="hidden" value="$system">
			<input name="filter" type="hidden" value="$filter">
			<legend>Update the Status of this issue</legend>
			<textarea required style="margin-top: .5em; height: 5em; font-size: 1em; width: 100%;" id="status-872" name="status" placeholder="Update the status of this issue (required)">$text</textarea>
			<div class="row" style="margin-top:.5em;">
				<div class="span2">

					<label style="margin-left: 1em;display:inline;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
					<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1" $resolved>

					<label class="lib-inline" style="display:inline;margin-left:1em;" for="comment-when-872">When (required)</label>
					<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-872" value="$when" required>
					<span class="date_format">date format: YYYY-MM-DD hh:mm AM/PM</span>
				</div>
				<div class="left unit span1 lastUnit" style="text-align:right;">
					<input class="status-button" name="submit_status" type="submit" value="Update">
				</div>
				<div class="cms-clear" style="padding-bottom:.5em;"></div>

			</div>


		</fieldset>
	</form>
</div>
EOD;

return $str;
}

//code to format an issue for display
function displayIssue($issue, $logged_in) {
	//what's the status of the issue?  Display the correct flag
	if (strtotime($issue["end_time"]) > time() || is_null($issue["end_time"])) {
		$current_status = '<span class="tag-unresolved">Unresolved</span>';
		$resolved = 0;
	} else {
		$current_status = '<span class="tag-resolved">Resolved</span>';
		$resolved = 1;
	}
	if (!is_null($issue["building"])) {
		$building = "at " . $issue["building"];
	} else {
		$building = '';
	}
	if ($logged_in == 1 && $resolved == 0) {echo '<div class="right status-update has-js">Add Update</div>';}
	echo '<h2><a href="detail.php?id=' . $issue["id"] . '">' . $issue['status_name'] . ' for ' . $issue['system_name'] . $building . $current_status .'</a></h2>';
	echo '<span name="issue_times"><strong>Began:</strong> ' . formatDateTime($issue["start_time"]);
	if (!is_null($issue["end_time"])) {echo " <strong>Resolved:</strong> " . formatDateTime($issue["end_time"]); }
	echo '</span>';
	echo '<br><span name="created_updated"><strong>Created On:</strong>' . formatDateTime($issue["created_on"]) . ' <strong>Last Updated:</strong> ' . formatDatetime($issue["last_updated"]); 
	
	echo "</span>";

}

//format an update for display. note that this one needs a database object because it has to look up the name of the user
//who created it
function displayUpdate($update, $dataBaseConnection) {
	$user = MakeUserArray ('', $update["user"], $dataBaseConnection);
	

	if (!is_null($update["building"])) {
		$building = "at " . $update["building"];
	} else {
		$building = '';
	}
	
	echo '<h2><a href="detail.php?id=' . $update["user"] . '">Update for ' . $update['system_name'] . $building . '</a></h2>';
	echo '<span name="issue_times"><strong>Created On:</strong> ' . formatDateTime($update["timestamp"]);
	echo "</span>";
	echo '<div class"comment-text">';
	echo '<strong class="timestamp">By: ' . $user["fn"] . " " . $user["ln"] . "</strong>";
	echo Markdown($update["text"]);
	echo '</div>';
}



//FUNCTIONS THAT MODIFY DATA IN THE DATABASE

//inserts new issues into the database.  All issues MUST HAVE at least one status.
function createNewIssue($system_id, $status_type_id, $time, $end_time, $userid, $issue_text, $dataBaseConnection) {
	//if the user doesn't supply a start time, create one
	if ($time == "") {$time = time();}

	//currently, only safety issues (status type 7) are private.
	if ($status_type_id = 7) {$public = 0;} else {$public = 1;}
	
	$query = "INSERT INTO issue_entries VALUES ('', $system_id, $status_type_id, FROM_UNIXTIME($time),";
	
	
	if ($end_time) { 
		$query = $query . " FROM_UNIXTIME($end_time), ";}
	else {
		$query = $query . " NULL, ";
	}
	 $query = $query . "'$userid', '$public', NOW(), NOW()";
	//we need to do multiple updates simultaneously, and submit them all at once, so we need to turn autocommit off.  
	//this ensures the entire commit succeeds or fails.  We don't want issues without status updates or vice versa!
	$dataBaseConnection->autocommit(FALSE);
	$dataBaseConnection->query($query);
	$issue_id = $dataBaseConnection->insert_id;
	$query = "INSERT INTO status_entries VALUES ('',$issue_id,FROM_UNIXTIME($time),$userid,'$issue_text')";
	$dataBaseConnection->query($query);
	
	//does the commit work?
	if ($dataBaseConnection->commit()) {
		$returnValue = 1;
	} else {
		$returnValue = $dataBaseConnection->error;
	}
	//things in the function shouldn't modify the main DB object, but just in case, make sure to turn autocommit back on.
	$dataBaseConnection->autocommit(TRUE);
	return $returnValue;
	
}

//delete an issue.  Note that because of the way the database is set, deleting an issue automatically deletes all status updates
//associated with an issue
function deleteIssue($issueID, $dataBaseConnection) {
	$query = "DELETE FROM issue_entries WHERE issue_id = '$issue_id'";
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Could not delete issue: " . $dataBaseConnection->error;
	}


}

//create a new status message.  If the new status sets the status of the main issue, change the status of the issue
function createNewStatus( $issue_id, $time, $userID, $status_text, $dataBaseConnection) {
	$dataBaseConnection->autocommit(FALSE);
	// Create the new status entry
	$query = "INSERT INTO status_entries VALUES ('','$issue_id',FROM_UNIXTIME($time),'$userID','$status_text')";
	
	$dataBaseConnection->query($query);

	$setTime = "UPDATE issue_entries SET last_updated = FROM_UNIXTIME($time) WHERE issue_id = $issue_id";
	
	$dataBaseConnection->query($setTime);

	if ($dataBaseConnection->commit()) {
		$returnValue = 1;
	} else {
		$returnValue = "Problem creating new status: " . $dataBaseConnection->error;
	}
	$dataBaseConnection->autocommit(FALSE);
	return $returnValue;
}

//closes an issue by inserting an end time.  If a time is not supplied, the current time is used.  
//If passed a time, it must be a unix timestamp.
function closeIssue($issueid, $time, $dataBaseConnection) {
	
	$query = "UPDATE issue_entries SET end_time = FROM_UNIXTIME($time), last_updated = NOW() WHERE issue_id = $issueid ";
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Issue could not be closed: " . $dataBaseConnection->error . $query;
	}

	
}


function changeIssueStatus($issueID, $statusID, $dataBaseConnection) {
	
	$query = "UPDATE issue_entries SET status_type_id = '$statusID' WHERE issue_id = '$issueID'";
	if ($dataBaseConnection->query($query)) {
		
		return 1;
	} else {
		
		return $dataBaseConnection->error;
	}
  	
}

//function to delete a status update.  Note that this does not work if there's only one status update left.
//in that case, use $deleteIssue to get rid of both the issue and all associated status updates
function deleteStatus($statusID, $dataBaseConnection) {
	//first figure out if this is the last remaining status for this issue.  If it is, we can't delete.
	$query = "SELECT issue_id from status_entries WHERE issue_id = (SELECT issue_id from status_entries WHERE status_id = $statusID)";
	$result = $dataBaseConnection->query($query);
	if (!$result) {
		return "Could not contact database: " . $dataBaseConnection->error;
	}
	if ($result->num_rows <= 1) {
		//this is the only status for this issue.  Tell user to delete the issue instead.
		return "Only remaining status for this issue.  Delete the issue instead.";
	}

	
	$query = "DELETE from status_entries WHERE status_id = $statusID";
		if ($dataBaseConnection->query($query)) {
			return 1;
		} else {
			return "Could not Delete Issue: " . $dataBaseConnection->error;
		}
	

}
//the only things you are allowed to edit on status messages is wether they are public, the text of the status, and the time
function editStatus($status_id, $public, $status_text, $time, $dataBaseConnection) {
	$status_text = $dataBaseConnection->real_escape_string($status_text);
	$query = "UPDATE status_entries SET status_timestamp = '$time', status_public = '$public', status_text = '$status_text' WHERE status_id = $status_id";
	if (!$dataBaseConnection->query($query)) {
		return $dataBaseConnection->error;
	} else {
		return 1;
	}
	
}

//function that creates a new update
function createNewUpdate($userid, $text, $time, $systemid, $public, $dataBaseConnection) {
	

	$query = "INSERT INTO updates values('', FROM_UNIXTIME($time), $userid, '$text', $public, $systemid)";
	
	if ($dataBaseConnection->query($query)) {
		return 1;
	} else {
		return "Could not add Update: " . $dataBaseConnection->error;
	}

}

