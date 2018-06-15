<?php


use Michelf\Markdown;

if ($use_SMTP) {
	require_once "Mail.php";
}

/*
	When a user submits a problem report, this function checks
	the input to make sure it isn't malicous (or a spambot!) and
	then sends an email with a problem report to the specified
	email address.
*/

function send_email_SMTP($name,$email,$message, $url) {

	//get general email settings
	global $to_email, $from_email, $email_subject, $use_SMTP;

	//get SMTP settings
	global $SMTP_username, $SMTP_password, $SMTP_server, $SMTP_port;
	
	// Build the message
	$error_report = $message;
	$error_report .= "\n\n" . 'From: ' . $name;
	$error_report .= "\n" . 'Email: ' . $email;

	if ($url !== "") {
		$error_report .= "\n" . 'URL: ' . $url;
	}
	

	$headers = array ('From' => $from_email,
	'To' => $to_email,
	'Subject' => $email_subject,
	'Cc' => $email,
	'Reply-To' => 'felkerk@gvsu.edu',
	"X-Mailer" =>  "PHP/" . phpversion()
	);

	$smtp = Mail::factory('smtp',
		array ('host' => $SMTP_server,
		'port' => $SMTP_port, 
		'auth' => true,
		'username' => $SMTP_username,
		'password' => $SMTP_password));

	$mail = $smtp->send($to_email, $headers, $error_report);

	if (PEAR::isError($mail)) {
          	return $mail->getMessage();
	} else {
          return true;
	} 


	
}

function send_email($name,$email,$message, $url) {

	global $to_email, $from_email, $email_subject, $use_SMTP;

	

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
	$headers .= "Reply-To: felkerk@gvsu.edu\r\n";
	$headers .= "X-Mailer: PHP/".phpversion() . "\r\n";

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

	$headers .= "Cc: " . $email;

	// Build the message
	$error_report = $message;
	$error_report .= "\n\n" . 'From: ' . $name;
	$error_report .= "\n" . 'Email: ' . $email;

	if ($url !== "") {
		$error_report .= "\n" . 'URL: ' . $url;
	}

	// Attempt to send the mail, return false if unsuccessful
	
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
	if ($return == "") {
		return true;
	} else {
		return $return;
	}
}

//FUNCTIONS TO GET DATA FROM DATABASE


//figure out if a system is attached to a building or not, return the building if so.

function getBuilding($systemID, $dataBaseConnection) {
	settype($systemID, "integer");

	$query = "SELECT building from systems WHERE system_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $systemID)) {
		return "Could not prepare query:" . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Could not execute query:" . $stmt->error;
	}
	$stmt->bind_result($building);
	$buildingID = FALSE;

	while ($stmt->fetch()) {
        $buildingID = $building;
    }

		return $buildingID;


		

}

//get the status of a specific system
function getSystemStatus($systemID, $dataBaseConnection) {
	settype($systemID, "integer");
	$query = "SELECT status_type_id FROM issue_entries WHERE system_id = ? AND (end_time > NOW() OR end_time IS NULL) AND start_time < NOW()";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $systemID)) {
		return "Failed to prepare statement";
	}
	if (!$stmt->execute()) {
		return "Could not execute query.";
	}
	$stmt->store_result();

	if ($stmt->num_rows < 1) {
		return "Online";
	} else {
		$stmt->bind_result($id);
		while ($stmt->fetch()) {
			if ($id == 2 || $id == 7) {
				return "Offline";
			
			} elseif ($id == 4) {
				return "Maintenance";
			} elseif ($id == 1) {
				return "Minor Issue";
	
			}
			
		}

	}
	return "Minor Issue";

	

}

//get updates from the updates table based on different criteria.  Return the update data as an array,
//or an error message if none are found
function getUpdates($building, $system, $public, $user, $limit, $recent, $dataBaseConnection) {
	//can't use prepared statements here-so have to be careful about typing and sanitizing inputs.
	
	
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
		$bulding = $dataBaseConnection->real_escape_string($building);
		$query = $query . "AND s.building = \"$building\" ";
	}
	if ($system) {
		settype($system, "integer");

		$query = $query . "AND s.system_id = $system ";

	}
	if ($user) {
		settype($user, "integer");
		$query = $query . "AND u.created_by = $user ";
	}
	
	$query = $query . "ORDER BY u.timestamp DESC ";
	
	if ($limit) {
		settype($limit, "integer");
		
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
	//prepared queries won't work for this function because of the way the query is built.  I'm therefore relying on explicitly
	//typing integers, escaping strings, and indirect input
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

		settype($status, "int");
		$query = $query . "AND i.status_type_id = $status ";

	}
	if ($system) {
		settype($system, "int");
		$query = $query . "AND s.system_id = $system ";

	}

	//$open can be set to "OPEN" (all open issues)  "CLOSED" (all closed issues) or "ALL" for all open or closed issues
	if ($open == "OPEN") {
		$query = $query . "AND (i.end_time IS NULL OR i.end_time > NOW()) ";

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
		settype($limit, "int");
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




//get all status IDs for a given issue and return them as an array
function getStatusIDs($issueID, $dataBaseConnection) {
	settype($issueID, "integer");
	
	$query = "SELECT status_id FROM status_entries WHERE issue_id = ? ORDER BY status_timestamp ASC";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $issueID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}

	$stmt->store_result();
	$stmt->bind_result($status_id);
	$status_ids = array();
	while ($stmt->fetch()) {
		$status_ids[] = $status_id;
	}
	return $status_ids;


	
}

//get data on an issue and return it as an array of values
function getIssueData($issue_id, $dataBaseConnection) {
	settype($issue_id, "integer");
	$query = "SELECT * from issue_entries WHERE issue_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $issue_id)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}

	$stmt->store_result();

	if ($stmt->num_rows <= 0) {
		return "Issue Not Found.";
		$stmt->close();
	} else {
		$stmt->bind_result($issue_id, $system_id, $status_type_id, $start_time, $end_time, $created_by, $public, $last_updated, $created_on);
		$issue_array = array();
		$stmt->fetch();
		$issue_array["id"] = $issue_id;
		$issue_array["systemID"] = $system_id;
		$issue_array["status"] = $status_type_id;
		$issue_array["start_time"] = $start_time;
		$issue_array["end_time"] = $end_time;
		$issue_array["userID"] = $created_by;
		$issue_array["created_on"] = $created_on;
		$issue_array["last_updated"] = $last_updated;
		$issue_array["public"] = $public;
		$query = "SELECT status_type_text from status_type WHERE status_type_id = $status_type_id";
		$result = $dataBaseConnection->query($query);
		$status_name = $result->fetch_assoc();
		$issue_array["status_name"] = $status_name["status_type_text"];
		$systemid = $system_id;
		$query = "SELECT system_name, building FROM systems WHERE system_id = $systemid";
		$result = $dataBaseConnection->query($query);
		$system = $result->fetch_assoc();
		$issue_array["system_name"] = $system["system_name"];
		$issue_array["building"] = $system["building"];
		$stmt->close();
		
		return $issue_array;
		
		
	}


}
//get a status entry by ID number
function getStatusData($statusID, $dataBaseConnection) {
	settype($statusID, "integer");
	$query = "SELECT * from status_entries WHERE status_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $statusID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}

	$stmt->store_result();
	
	if ($stmt->num_rows < 1) {
		return "No status found in database.";
	} else {
		$stmt->bind_result($status_id, $issue_id, $status_timestamp, $status_user_id, $status_text);
		$status_array = array();
		$stmt->fetch();
		$status_array["statusID"] = $status_id;
		$status_array["issueID"] = $issue_id;
		$status_array["timestamp"] = $status_timestamp;
		$status_array["userID"] = $status_user_id;
		$status_array["text"] = $status_text;
		return $status_array;
	}

	
}

//get update data by update ID number

function getUpdateData($updateID, $dataBaseConnection) {
	$query = "SELECT * from updates WHERE update_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $updateID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}

	$stmt->store_result();

	if ($stmt->num_rows < 1) {
		return "No update found in database.";
	} else {
		$stmt->bind_results($update_id, $timestamp, $created_by, $text, $public, $system_id);
		
		$stmt->fetch();
		
		$query = "SELECT system_name FROM systems WHERE system_id = $system_id";
		$result = $dataBaseConnection->query($query);
		$systemName = $result->fetch_assoc();
		$update_array = array("update_id" => $update_id);
		$update_array["timestamp"] = $timestamp;
		$update_array["user"] = $created_by;
		$update_array["text"] = $text;
		$update_array["systemID"] = $system_id;
		$update_array["public"] = $public;
		$update_array["system_name"] = $systemName["system_name"];

		return $update_array;


	}

	
	

}

//gets user information about a user and returns it as a PHP array for easy reference
//you can select by username or ID number, the function will prefer username if both are provided	
function MakeUserArray($userName, $userID, $dataBaseConnection) {

	if ($userID) {
		settype($userID, "int");
	}
	
	if ($userName) {
		$criteria = "user_username LIKE ?";
	} else {
		$criteria = "user_id = ?";
	}

	$query = "SELECT * FROM user WHERE $criteria";

	
	if (!$stmt = $dataBaseConnection->prepare($query)) {
		return "" . $stmt->error;
	}

	

	if ($userName) {
		if (!$stmt->bind_param("s", $userName)) {
			return "Failed to bind parameters: " . $stmt->error;
		}
	} else {
		if (!$stmt->bind_param("i", $userID)) {
			return "Failed to bind parameters: " . $stmt->error;
		}
	}



	

	if (!$stmt->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}
	
	//for reasons opaque to me, I can't use the same bind_results method I use elsewhere to retrieve user data from the perpared statement
	//no idea why.  I get an error that says the bind method does not exist.
	//I do not get this error anywhere else in the program.
	$result = $stmt->get_result();

	if ($result->num_rows < 1) {
		return "No user found";
	} else {

		$row = $result->fetch_array(MYSQLI_NUM);
		$user = array();
		$user["id"] = $row[0];
		$user["username"] = $row[1];
		$user["fn"] = $row[2];
		$user["ln"] = $row[3];
		$user["email"] = $row[4];
		$user["access"] = $row[7];

		return $user;

	}


}

//FUNCTIONS TO DISPLAY THINGS

//display the new status form.  This works better as a function than an include, because it needs to be 
//prepopulated with a bunch of data if the user has previously tried to submit the form
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
	echo "<a class='permalink' href='detail_nologin.php?type=issue&id=" . $issue['id'] . "'>Link to this Issue</a><br>";
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
	
	echo '<h2>Update for ' . $update['system_name'] . $building . '</h2>';
	echo "<a class='permalink' href='detail_nologin.php?type=update&id=" . $update['update_id'] . "'>Link to this Update</a><br>";
	echo '<span name="issue_times"><strong>Created On:</strong> ' . formatDateTime($update["timestamp"]);
	echo "</span>";
	echo '<div class"comment-text">';
	echo '<strong class="timestamp">By: ' . $user["fn"] . " " . $user["ln"] . "</strong>";
	echo Markdown::defaultTransform($update["text"]);
	echo '</div>';
}


//format and display status data for issues.  Expects an array of status data arranged in the form provided by the getStatusData function
function displayStatus($statusData, $dataBaseConnection) {
	
	$status_user = MakeUserArray('', $statusData["userID"], $dataBaseConnection);
	echo '<div class"comment-text" id="status_id"' . $statusData["statusID"]. '">';
	echo '<strong class="timestamp">[' . formatDateTime($statusData["timestamp"]) . ']-' . $status_user["fn"] . " " . $status_user["ln"] . "</strong>";
	echo Markdown::defaultTransform($statusData["text"]);
	echo '</div>';
}


//FUNCTIONS THAT MODIFY DATA IN THE DATABASE

//inserts new issues into the database.  All issues MUST HAVE at least one status.
function createNewIssue($system_id, $status_type_id, $time, $end_time, $userid, $issue_text, $public, $dataBaseConnection) {
	//we need to do multiple updates simultaneously, and submit them all at once, so we need to turn autocommit off.  
	//this ensures the entire commit succeeds or fails.  We don't want issues without status updates or vice versa!

	$dataBaseConnection->autocommit(FALSE);
	//type integer variables.  
	settype($system_id, "int");
	settype($status_type_id, "int");
	settype($userid, "int");
	settype($public, "int");

	

	//figure our the start time of the issue, if it's not supplied, use the current time
	if ($time == "") {$time = time();} else {$time = strtotime($time);}
	

	//currently, safety issues (status type 7) are always private
	if ($status_type_id == 7) {$public = 0;} 

	//begin building the query
	
	$query = "INSERT INTO issue_entries VALUES ('', ?, ?, FROM_UNIXTIME(?), ";
	
	
	if ($end_time != '') { 
		$end_time = strtotime($end_time);
		$query = $query . "FROM_UNIXTIME(?), ";}
	else {
		$query = $query . "NULL, ";
	}
	 $query = $query . "?, ?, NOW(), NOW())";

	$stmt = $dataBaseConnection->prepare($query);

	if ($end_time != '') {
		if (!$stmt->bind_param("iissii", $system_id, $status_type_id, $time, $end_time, $userid, $public)) {
			return "Failed to bind parameters: " . $stmt->error;
		}
	} else {
		if (!$stmt->bind_param("iisii", $system_id, $status_type_id, $time, $userid, $public)) {
			return "Failed to bind parameters: " . $stmt->error;
		}
	}

	

	if (!$stmt->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}

	
	//get the id of the last inserted row
	$issue_id = $dataBaseConnection->insert_id;

	//prepare the second insert for the status
	$query = "INSERT INTO status_entries VALUES ('',?,FROM_UNIXTIME(?),?,?)";
	$stmt2 = $dataBaseConnection->prepare($query);
	if (!$stmt2->bind_param("ssss", $issue_id, $time, $userid, $issue_text)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt2->execute()) {
		return "Failed to execute query: " . $stmt->error;
	}
	
	//does the commit work?
	if ($dataBaseConnection->commit()) {
		$returnValue = 1;
	} else {
		$returnValue = "Cannot Create Issue:  " . $dataBaseConnection->error;
	}
	//things in the function shouldn't modify the main DB object, but just in case, make sure to turn autocommit back on.
	$dataBaseConnection->autocommit(TRUE);
	return $returnValue;
	
}

//delete an issue.  Note that because of the way the database is set up, deleting an issue automatically deletes all status updates
//associated with an issue
function deleteIssue($issueID, $dataBaseConnection) {

	settype($issueID, "int");
	$query = "DELETE FROM issue_entries WHERE issue_id = ?";

	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $issueID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Failed to delete issue: " . $stmt->error;
	} else {
		return true;
	}





}

//function to edit an existing issue.  Once an issue is created, the only things you can edit are the start and end time, 
//visibility to the public, and the status.  
//The building value is neccessary to make sure a status inappropriate for building issues isn't assigned.
//  All the values are required except endTime.  
//Time values must be strings that can be parsed by strtotime() 
//(verify beforehand)

function updateIssue($issueID, $startTime, $endTime, $statusID, $public, $building, $dataBaseConnection) {

	settype($public, "int");
	settype($statusID, "int");
	settype($issueID, "int");


	if ($endTime != "") {
		$endTime = strtotime($endTime);
		
		if ($statusID != 4 && $endTime > strtotime("now")) {
			return "Only Scheduled Maintenance can have a future scheduled end time.";

		}

		$queryEnd = "FROM_UNIXTIME(?)";
		
	} else {
		$queryEnd = "NULL";
	}

	if ($statusID == 7 && !$building) {
		return "Non-Building systems cannot have a safety issues.";
	}

	$startTime = strtotime($startTime);
	

	$query = "UPDATE issue_entries SET public = ?, status_type_id = ?, end_time = $queryEnd, start_time = FROM_UNIXTIME(?) WHERE issue_id = ?";
	
	$stmt = $dataBaseConnection->prepare($query);

	if ($queryEnd == "NULL") {

		
		if (!$stmt->bind_param("iisi", $public, $statusID, $startTime, $issueID)) {
			return "Failed to bind parameters: " . $stmt->error;
		}
	} else {
		if (!$stmt->bind_param("iissi", $public, $statusID, $endTime, $startTime, $issueID)) {
			return "Failed to bind parameters: " . $stmt->error;
		}
	}

	if (!$stmt->execute()) {
		return "Could not update issue: " . $stmt->error;
	} else {
		return true;
	}
	

}

//remove an update from the database.  This is very straightforward.
function deleteUpdate($updateID, $dataBaseConnection) {
	settype($updateID, "int");

	$query = "DELETE FROM updates WHERE update_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $updateID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}

	if (!$stmt->execute()) {
		return "Could not remove update: " . $stmt->error;
	} else {
		return true;
	}
}


//create a new status message.  Update the last updated time of the issue to reflect the update
function createNewStatus( $issue_id, $userID, $status_text, $dataBaseConnection) {
	settype($issue_id, "int");
	settype($userID, "int");

	//we need to make mutliple updates at once, so turn autocommit off
	$dataBaseConnection->autocommit(FALSE);
	// Create the new status entry
	$query = "INSERT INTO status_entries VALUES ('',?,NOW(),?,?)";
	$stmt = $dataBaseConnection->prepare($query);

	if (!$stmt->bind_param("iis", $issue_id, $userID, $status_text)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "Could not change status: " . $stmt->error;
	} 
	

	$setTime = "UPDATE issue_entries SET last_updated = NOW() WHERE issue_id = ?";

	$stmt = $dataBaseConnection->prepare($setTime);

	if (!$stmt->bind_param("i", $issue_id)) {
		return "Failed to bind parameters: " . $stmt->error;
	}

	if (!$stmt->execute()) {
		return "Could not change update time of issue: " . $stmt->error;
	} 
	
	//does the commit work?
	if ($dataBaseConnection->commit()) {
		$returnValue = true;
	} else {
		$returnValue = "Problem creating new status: " . $dataBaseConnection->error;
	}
	$dataBaseConnection->autocommit(FALSE);
	return $returnValue;
}

//closes an issue.  Note that this is done by setting an end time for the issue-issues without end times are considered open.

function closeIssue($issueid, $dataBaseConnection) {
	settype($issueid, "int");
	$query = "UPDATE issue_entries SET end_time = NOW() WHERE issue_id = ?";

	$stmt = $dataBaseConnection->prepare($query);

	if (!$stmt->bind_param("i", $issueid)) {
		return "Failed to bind parameters: " . $stmt->error . $issueid;
	}

	if (!$stmt->execute()) {
		return "Issue could not be closed: " . $stmt->error;
	} else {
		return true;
	}

	
}


//function to delete a status update.  Note that this does not work if there's only one status update left.
//ALL ISSUES MUST HAVE AT LEAST ONE STATUS MESSAGE EXPLAINING THE ISSUE.
function deleteStatus($statusID, $dataBaseConnection) {
	//first figure out if this is the last remaining status for this issue.  If it is, we can't delete.
	$query = "SELECT issue_id from status_entries WHERE issue_id = (SELECT issue_id from status_entries WHERE status_id = ?)";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $statusID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}

	if (!$stmt->execute()) {
		return "cannot extract data from database: " . $stmt->error;
	}

	$stmt->store_result();

	if ($stmt->num_rows <= 1) {
		//this is the only status for this issue.  Tell user to delete the issue instead.
		return "Only remaining status for this issue.  Delete the issue instead.";
	}

	//if we get this far, then delete the status
	$query = "DELETE from status_entries WHERE status_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("i", $statusID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "cannot extract data from database: " . $stmt->error;
	} else {
		return true;
	}
	

}
//the only things you are allowed to edit on status messages is the text of the status
function editStatus($status_id, $status_text, $dataBaseConnection) {
	settype($status_id, "int");
	$query = "UPDATE status_entries SET status_text = ? WHERE status_id = ?";
	$stmt = $dataBaseConnection->prepare($query);
	if (!$stmt->bind_param("si", $status_text, $status_id)) {
		return "Failed to bind parameters: " . $stmt->error;
	}

	if (!$stmt->execute()) {
		return "cannot update status: " . $stmt->error;
	} else {
		return true;
	}
	
}

//function that creates a new update
function createNewUpdate($userid, $text, $time, $systemid, $public, $dataBaseConnection) {
	settype($userid, "int");
	settype($systemid, "int");
	settype($public, "int");
	$time = strtotime($time);
	$query = "INSERT INTO updates values('', FROM_UNIXTIME(?), ?, ?, ?, ?)";
	$stmt = $dataBaseConnection->prepare($query);

	if (!$stmt->bind_param("sisii", $time, $userid, $text, $public, $systemid)) {
		return "Failed to bind parameters: " . $stmt->error;
	}
	if (!$stmt->execute()) {
		return "cannot create update: " . $stmt->error;
	} else {
		return true;
	}

}

//edit an existing update.  You can only change the text and wether it's public
function editUpdate($updateID, $text, $public, $dataBaseConnection) {
	settype($updateID, "int");
	settype($public, "int");
	$query = "UPDATE updates SET public = ?, text = ? WHERE update_id = ?";

	$stmt = $dataBaseConnection->prepare($query);

	if (!$stmt->bind_param("isi", $public, $text, $updateID)) {
		return "Failed to bind parameters: " . $stmt->error;
	}

	if (!$stmt->execute()) {
		return "cannot edit update: " . $stmt->error;
	} else {
		return true;
	}

	



}
