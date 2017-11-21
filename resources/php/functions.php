<?php

/*
	When a user submits a problem report, this function checks
	the input to make sure it isn't malicous (or a spambot!) and
	then sends an email with a problem report to the specified
	email address.
*/

function send_email($name,$email,$message) {

	global $to_email, $from_email, $email_subject;

	

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
	if (contains_bad_str($message)) {
		return false;
	}
	if (contains_bad_str($email)) {
		return false;
	}
	if (contains_bad_str($name)){
		return false;
	}
	if (contains_newlines($email)) {
		return false;
	}
	if (contains_newlines($name)) {
		return false;
	}

	// Build the message
	$error_report = $message;
	$error_report .= "\n\n" . 'From: ' . $name;
	$error_report .= "\n" . 'Email: ' . $email;

	// Attempt to send the mail, then set the message variable $m to success or
	// error.
	
	if(mail($to_email, $email_subject, $error_report, $headers)) {
			return true;
	} else {
			return false;
	}
	
}


/*

This function verifies that the user has filled out the captcha

*/


function verifyRecaptcha($token, $secret){

	$fields = array("secret" => $secret,
					"response" => $token
					);

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify"); 
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

	$output = curl_exec($ch); 


	if($output === false) {
		
		return 'Curl error: ' . curl_error($ch);
	} 

	curl_close($ch);

	$response = json_decode($output, true);

	if (is_null($response)) {
		return "Json object could not be decoded.";

	}

	if ($response["success"] === true) {
		return true;
	} else {
		return false;
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

  $bad_strings = array(
                "content-type:"
                ,"mime-version:"
                ,"multipart/mixed"
		,"Content-Transfer-Encoding:"
                ,"bcc:"
		,"cc:"
		,"to:"
  );
  foreach ($bad_strings as $key => $string) {
	  $bad_strings[$key] = preg_quote($string);


  }
	// Check the field for each of the bad strings, and if you we find one, set the
	// message variable $m to show the error.
  foreach($bad_strings as $bad_string) {
    if(preg_match($bad_string, strtolower($str_to_test))) {
      return true;
	}
  }

  return false;
}

/*
	This function checks the email address field to see if the bot is trying to sneak
	something into the mail header to send out mass spam. It rejects any submission
	that meets the criteria.
*/

function contains_newlines($str_to_test) {

   if(preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str_to_test) != 0) {
     return true;
   } else {
	return false;
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
	return $date->format('Y-m-d g:i A');
}


//used to verify and format the time returned from user-filled forms into a unix timestamp
function verifyTime($time) {
	$unixtime = strtotime($time);
	if (!$unixtime) {
		return false;
	}
	// Create a time one year back to see use to check if posting time is in range.
	$year_ago = strtotime('-1 year');
	$year_from_now = strtotime('+1 year');

	// Check if time is something special or ready or for now and is within the last year.
	if ($unixtime < $year_ago || $unixtime >  $year_from_now) {
		return false;
	
	}
	return true;

}


//verify issue form data
function verifyReportFormData($starttime, $endtime, $statusid, $systemid, $dataBaseConnection) {
	//if it's a safety issue, make sure it's being attached to a building-related system
	$return = "";
	if ($statusid == 7) {
		$isBuildingSystem = FALSE;
		$query = "SELECT system_id FROM systems WHERE building IS NOT NULL";
		$result = $dataBaseConnection->query($query);
		while ($row = $result->fetch_assoc()) {
			if ($row["system_id"] == $systemid) {
				$isBuildingSystem = TRUE;

			}

		}
	
		if (!$isBuildingSystem) {
			$return = "Non-building systems cannot have safety issues.</br>";

		}
	}

	if($endtime == '' && $statusid == 4) { // Scheduled Maintenance requires an End time
		
		$return = $return . "Scheduled maintenance requires an end time be set.</br>";
	}

	//verify that time data can be changed to timestamps and that start date is within the last month
	$time_check = strtotime('-1 month');
	if (!strtotime($starttime)) {
		$return = $return . "Invalid start time</br>";

	} else if (strtotime($starttime) < $time_check) {
		$return = $return . "Start time cannot be more than a month ago.</br>";
	}

	if ($endtime != "") {
		if (!strtotime($endtime)) {
			$return = $return . "Invalid end time</br>";
		} 
		if (strtotime($starttime) >= strtotime($endtime)) {
			$return = $return . "End time cannot be earlier than start time.</br>";
		}
	}
	return $return;
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
		return false;
	} else {
		$return_array = array();
		while ($row = $updates->fetch_assoc()) {
			$return_array[] = array("timestamp" => $row["timestamp"], 
			"text" => $row["text"],
			"system_name" => $row["system_name"],
			"system_id" => $row["system_id"],
			"user" => $row["created_by"],
			"building" => $row["building"],
			"update_id" => $row["update_id"],
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
		return false;
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
	$query = "SELECT * from issue_entries WHERE issue_id = $issue_id";
	$issue = $dataBaseConnection->query($query);
	if (!$issue) {
		return "Problem getting data: " . $dataBaseConnection->error;
	}

	if ($issue->num_rows <= 0) {
		return "Issue Not Found.";
	}
		
	$issue_array = array("id" => $issue_id);
	$row = $issue->fetch_assoc();
	$issue_array["systemID"] = $row["system_id"];
	$issue_array["status"] = $row["status_type_id"];
	$issue_array["start_time"] = $row["start_time"];
	$issue_array["end_time"] = $row["end_time"];
	$issue_array["userID"] = $row["created_by"];
	$issue_array["created_on"] = $row["created_on"];
	$issue_array["last_updated"] = $row["last_updated"];
	$issue_array["public"] = $row["public"];
	$status_type_id = $row["status_type_id"];
	$query = "SELECT status_type_text from status_type WHERE status_type_id = $status_type_id";
	$result = $dataBaseConnection->query($query);
	$status_name = $result->fetch_assoc();
	$issue_array["status_name"] = $status_name["status_type_text"];
	$systemid = $row["system_id"];
	$query = "SELECT system_name, building FROM systems WHERE system_id = $systemid";
	$result = $dataBaseConnection->query($query);
	$system = $result->fetch_assoc();
	$issue_array["system_name"] = $system["system_name"];
	$issue_array["building"] = $system["building"];

	return $issue_array;

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

//get update data by update ID number

function getUpdateData($updateID, $dataBaseConnection) {
	$query = "SELECT * from updates WHERE update_id = $updateID";
	$update = $dataBaseConnection->query($query);
	if (!$update) {
		return $dataBaseConnection->error;
	}
	if ($update->num_rows <= 0) {
		return "No update found in database.";

	}
	$row = $update->fetch_assoc(); 
	$systemID = $row["system_id"];
	$query = "SELECT system_name FROM systems WHERE system_id = $systemID";
	$result = $dataBaseConnection->query($query);
	$systemName = $result->fetch_assoc();


	$update_array = array("update_id" => $updateID);
	$update_array["timestamp"] = $row["timestamp"];
	$update_array["user"] = $row["created_by"];
	$update_array["text"] = $row["text"];
	$update_array["systemID"] = $row["system_id"];
	$update_array["public"] = $row["public"];
	$update_array["system_name"] = $systemName["system_name"];

	return $update_array;

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

//display the new status form.  This works better as a function than an include, because it needs to be 
//prepopulated with a bunch of data.
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

//code to format an issue for display, expects an array of issue data in the format output by the issue lookup function, above
//it needs to know if the user is logged in so that it can decide to show a form to enter a new status or close the issue.
function displayIssue($issue) {
	//what's the status of the issue?  Display the correct flag
	if (strtotime($issue["end_time"]) > time() || is_null($issue["end_time"])) {
		$current_status = '<span class="tag-unresolved">Unresolved</span>';
		$resolved = 0;
	} else {
		$current_status = '<span class="tag-resolved">Resolved</span>';
		$resolved = 1;
	}
	if (!is_null($issue["building"])) {
		$building = " at " . $issue["building"];
	} else {
		$building = '';
	}
	
	echo '<h2>' . $issue['status_name'] . ' for ' . $issue['system_name'] . $building . $current_status .'</h2>';
	echo '<span name="issue_times"><strong>Began:</strong> ' . formatDateTime($issue["start_time"]);
	if (!is_null($issue["end_time"])) {echo " <strong>Resolved:</strong> " . formatDateTime($issue["end_time"]); }
	echo '</span>';
	echo '<br><span name="created_updated"><strong>Created On:</strong>' . formatDateTime($issue["created_on"]) . ' <br><strong>Last Updated:</strong> ' . formatDatetime($issue["last_updated"]); 
	
	echo "</span>";

}

//format an update for display. note that this one needs a database object because it has to look up the name of the user
//who created it
function displayUpdate($update, $dataBaseConnection) {
	$user = MakeUserArray ('', $update["user"], $dataBaseConnection);
	

	if (isset($update["building"])) {
		$building = "at " . $update["building"];
	} else {
		$building = '';
	}
	
	echo '<h2><a href="detail.php?type=update&id=' . $update["update_id"] . '">Update for ' . $update['system_name'] . $building . '</a></h2>';
	echo '<span name="issue_times"><strong>Created On:</strong> ' . formatDateTime($update["timestamp"]);
	echo "</span>";
	echo '<div class"comment-text">';
	echo '<strong class="timestamp">By: ' . $user["fn"] . " " . $user["ln"] . "</strong>";
	echo Markdown($update["text"]);
	echo '</div>';
}


//format and display status data for issues.  Expects an array of status data arranged in the form provided by the getStatusData function
function displayStatus($statusData, $dataBaseConnection) {
	$status_user = MakeUserArray('', $statusData["userID"], $dataBaseConnection);
	echo '<div class"comment-text" id="status_id"' . $statusData ["statusID"]. '">';
	echo '<strong class="timestamp">[' . formatDateTime($statusData["timestamp"]) . ']-' . $status_user["fn"] . " " . $status_user["ln"] . "</strong>";
	echo Markdown($statusData["text"]);
	echo '</div>';
}


//FUNCTIONS THAT MODIFY DATA IN THE DATABASE

//inserts new issues into the database.  All issues MUST HAVE at least one status.
function createNewIssue($system_id, $status_type_id, $time, $end_time, $userid, $issue_text, $public, $dataBaseConnection) {
	//if the user doesn't supply a start time, create one
	if ($time == "") {$time = time();} else {$time = strtotime($time);}
	

	//currently, safety issues (status type 7) are always private
	if ($status_type_id == 7) {$public = 0;} 
	
	$query = "INSERT INTO issue_entries VALUES ('', $system_id, $status_type_id, FROM_UNIXTIME($time), ";
	
	
	if ($end_time != '') { 
		$end_time = strtotime($end_time);
		$query = $query . "FROM_UNIXTIME($end_time), ";}
	else {
		$query = $query . "NULL, ";
	}
	 $query = $query . "$userid, $public, NOW(), NOW())";
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
	$query = "DELETE FROM issue_entries WHERE issue_id = '$issueID'";
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Could not delete issue: " . $dataBaseConnection->error;
	}


}

//function to edit an existing issue.  Once an issue is created, the only things you can edit are the start and end time
// and the status.  All the values are required except endTime.  Time values must be strings that can be parsed by strtotime() 
//(verify beforehand)

function updateIssue($issueID, $startTime, $endTime, $statusID, $public, $building, $dataBaseConnection) {

	if ($endTime != "") {
		$endTime = strtotime($endTime);
		
		if ($statusID != 4 && $endTime > strtotime("now")) {
			return "Only Scheduled Maintenance can have a future scheduled end time.";

		}
		$endTime = "FROM_UNIXTIME($endTime)";
	} else {
		$endTime = "NULL";
	}

	if ($statusID == 7 && !$building) {
		return "Non-Building systems cannot have a safety issues.";
	}

	$startTime = strtotime($startTime);
	$startTime = "FROM_UNIXTIME($startTime)";

	$query = "UPDATE issue_entries SET public = $public, status_type_id = $statusID, end_time = $endTime, start_time = $startTime WHERE issue_id = $issueID";

	$result = $dataBaseConnection->query($query);

	if ($result) {
		return true;
	} else {
		return "Could not update issue:" . $dataBaseConnection->error .$query;
	}

}

//remove an update from the database.  This is very straightforward.
function deleteUpdate($updateID, $dataBaseConnection) {
	$query = "DELETE FROM updates WHERE update_id = '$updateID'";
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Could not delete update: " . $dataBaseConnection->error;
	}
}


//create a new status message.  If the new status sets the status of the main issue, change the status of the issue
function createNewStatus( $issue_id, $userID, $status_text, $dataBaseConnection) {
	$dataBaseConnection->autocommit(FALSE);
	// Create the new status entry
	$query = "INSERT INTO status_entries VALUES ('','$issue_id',NOW(),'$userID','$status_text')";
	
	$dataBaseConnection->query($query);

	$setTime = "UPDATE issue_entries SET last_updated = NOW() WHERE issue_id = $issue_id";
	
	$dataBaseConnection->query($setTime);

	if ($dataBaseConnection->commit()) {
		$returnValue = true;
	} else {
		$returnValue = "Problem creating new status: " . $dataBaseConnection->error;
	}
	$dataBaseConnection->autocommit(FALSE);
	return $returnValue;
}

//closes an issue by inserting an end time.  If a time is not supplied, the current time is used.  
//If passed a time, it must be a unix timestamp.
function closeIssue($issueid, $dataBaseConnection) {
	
	$query = "UPDATE issue_entries SET end_time = NOW(), last_updated = NOW() WHERE issue_id = $issueid ";
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Issue could not be closed: " . $dataBaseConnection->error;
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
			return true;
		} else {
			return "Could not Delete Status: " . $dataBaseConnection->error;
		}
	

}
//the only things you are allowed to edit on status messages is the text of the status
function editStatus($status_id, $status_text, $dataBaseConnection) {
	$status_text = $dataBaseConnection->real_escape_string($status_text);
	$query = "UPDATE status_entries SET status_timestamp = NOW(), status_text = '$status_text' WHERE status_id = $status_id";
	if (!$dataBaseConnection->query($query)) {
		return $dataBaseConnection->error;
	} else {
		return true;
	}
	
}

//function that creates a new update
function createNewUpdate($userid, $text, $time, $systemid, $public, $dataBaseConnection) {
	
	$time = strtotime($time);
	$query = "INSERT INTO updates values('', FROM_UNIXTIME($time), $userid, '$text', $public, $systemid)";
	
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Could not add Update: " . $dataBaseConnection->error;
	}

}

//edit an existing update.  You can only change the text and wether it's public
function editUpdate($updateID, $text, $public, $dataBaseConnection) {
	$query = "UPDATE updates SET public = $public, text = '$text' WHERE update_id = $updateID";
	if ($dataBaseConnection->query($query)) {
		return true;
	} else {
		return "Could not edit Update: " . $dataBaseConnection->error;
	}



}
