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

	global $m;

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

//FUNCTIONS TO GET DATA FROM DATABASE

//used to check and see if any systems currently have an outstanding major issue
function areUnresolvedSystemIssues($dataBaseConnection) {
	$time = time();
	//first check to see if there are any major issues with no end time set
	$query = "SELECT i.end_time FROM issue_entries i, systems s WHERE s.system_id = i.system_id AND s.building IS NULL AND i.status_type_id=2 AND i.end_time IS NULL";
	$unresolvedIssues = $dataBaseConnection->query($query);
	if (!$unresolvedIssues) {
		return $dataBaseConnection->error;
	}

	if ($unresolvedIssues->num_rows > 0) {
		return true;
	}
	//now check for any scheduled maintenance
	$query = "SELECT i.end_time FROM issue_entries i, systems s WHERE s.system_id = i.system_id AND s.building IS NULL AND i.status_type_id = 4 AND i.end_time IS NOT NULL AND NOW() < i.end_time ";
	$unresolvedIssues = $dataBaseConnection->query($query);
	if (!$unresolvedIssues) {
		return $dataBaseConnection->error;
	}

	if ($unresolvedIssues->num_rows > 0) {
		return true;
	} 

	return false;

}

//function to construct queries for issue data to the database based on the designated filter
function constructQuery($filter) {
	$query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time, issue_entries.status_type_id FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id";

	switch ($filter) {
		case 0:
			$query = $query . " ORDER BY issue_entries.issue_id DESC LIMIT 10";
			break;
		case 2:
			$query = $query . " AND issue_entries.end_time > 0 ORDER BY issue_entries.issue_id DESC";
			break;
		case 1:
			$query = $query . " AND (issue_entries.end_time BETWEEN 0 AND 0) ORDER BY issue_entries.issue_id DESC";

	}
	return $query;

}
//get all status IDs for a given issue and return them as an array
function getStatusIDs($issueID, $dataBaseConnection) {
	$query = "SELECT status_id FROM status_entries WHERE issue_id = '$issueID'";
	$IDs = $dataBaseConnection->query();
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
		}
		return $issue_array;
	} else {
		return $dataBaseConnection->error;
	}

}
//get a status entry by ID number
function getStatusData($statusID, $dataBaseConnection) {
	$query = "SELECT * from status_entries WHERE status_id = '$statusID'";
	$status = $dataBaseConnection->query($query);
	if (!$status) {
		return $dataBaseConnection->error;
	}
	if ($status->num_rows > 0) {
		return "No user found in database.";

	}

	
	$status_array = array("statusID" => $issue_id);
	while($row = $status->fetch->assoc()) {
		$status_array["issueID"] = $row["issue_id"];
		$status_array["timestamp"] = $row["issue_timestamp"];
		$status_array["public"] = $row["status_public"];
		$status_array["userID"] = $row["status_user_id"];
		$status_array["text"] = $row["status_text"];	
			
	}
	return $status_array;
	
}
//gets user information about a logged-in user and returns it as a PHP array for easy reference
function MakeUserArray ($userName, $dataBaseConnection) {

	$user_result=$dataBaseConnection->query("SELECT * FROM user WHERE user_username = '$userName' LIMIT 1");
	
	if(($user_result) && ($user_result->num_rows > 0)) { // Query was successful, a user was found
	
		$user = array("username" => $userName);
		while($row = $user_result->fetch_assoc()) {
			$user["access"] = $row["user_access"];
			$user["id"] = $row["user_id"];
			$user["fn"] = $row["user_fn"];
		}
	
		
		return $user;
	} else {
		return "User could not be found in table";
	}
}
//verifies and formats user-supplied time values
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

//FUNCTIONS THAT MODIFY DATA IN THE DATABASE

//inserts new issues into the database.  All issues MUST HAVE at least one status.
function createNewIssue($system_id,$status_type_id, $time, $end_time, $userid, $issue_text, $dataBaseConnection) {
	//if the user doesn't supply a start time, create one
	if ($time == "") {$time = time();}

	//currently, only safety issues (status type 7) are private.
	if ($status_type_id = 7) {$public = 0;} else {$public = 1;}
	$query = "INSERT INTO issue_entries VALUES ('','$system_id', $status_type_id, FROM_UNIXTIME($time), FROM_UNIXTIME($end_time), '$userid', '$public')";
	//we need to do multiple updates simultaneously, and submit them all at once, so we need to turn autocommit off.  
	//this ensures the entire commit succeeds or fails.  We don't want issues without status updates or vice versa!
	$dataBaseConnection->autocommit(FALSE);
	$dataBaseConnection->query($query);
	$issue_id = $dataBaseConnection->insert_id;
	$query = "INSERT INTO status_entries VALUES ('','$issue_id',FROM_UNIXTIME($time),'$public','$userid','$issue_text',)";
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
		return 1;
	} else {
		return $dataBaseConnection->error;
	}


}

//create a new status message.  If the new status sets the status of the main issue, change the status of the issue
function createNewStatus( $issue_id, $time, $public, $userID, $status_text, $closing, $dataBaseConnection) {
	//is this status closing the issue?  If so, change it in the issue table
	$dataBaseConnection->autocommit(FALSE);
	if ($time == "") {$time = time();}
	if ($closing) {
		$query = "UPDATE issue_entries SET end_time = FROM_UNIXTIME('$time') WHERE issue_id = '$issue_id' ";
		$dataBaseConnection->query($query);
	} 

	// Create the new status entry
	$query = "INSERT INTO status_entries VALUES ('','$issue_id',FROM_UNIXTIME('$time'),'$public','$userID','$status_text')";
	$dataBaseConnection->query($query);
	if ($dataBaseConnection->commit()) {
		$dataBaseConnection->autocommit(TRUE);
		$returnValue = 1;
	} else {
		$dataBaseConnection->autocommit(TRUE);
		$returnValue = $dataBaseConnection->error;
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
