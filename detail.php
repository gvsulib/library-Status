<?php
$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
setcookie("referrer", $actual_url, 0, "/");
if (!isset($_COOKIE["login"])) {
	setcookie("login", "", 0, "/");
        $_COOKIE["login"] = "";
}

 //loads required library files
require 'resources/config/config.php';
require 'resources/php/functions.php';


//markdown is used to display the status entries for issues and the text of updates
require ('resources/php/markdown.php');

//load all starting session and other variables and required libraries
date_default_timezone_set('America/Detroit');



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
//variable to track if user is logged in
$logged_in = 0; 

//holds system messages we want to show to the user.  By default, there are none
$userMessage = NULL;

//if the user is trying to log out, send them to the logout script with a flag set

if (isset($_GET["logout"])) {
	setcookie("login", "", 0, "/");
	$_COOKIE["login"] = "";
	header("Location: index.php");
}

//get list of status types for issue editing drop-down
$query = "SELECT * from status_type";

$results = $db->query($query);

if (!$results) {
	$userMessage = "<div class=\"alert alert-danger\">unable to contact database.</div>";
	$status_types = false;
	

} else {
	$status_types = array();
	while($row = $results->fetch_assoc()) {
		$status_types[$row["status_type_id"]] = $row["status_type_text"];

	}
}

// uncomment to force a login
//$_SESSION['username'] = 'felkerk';

//you should only get to this page by following a link from index.php or by sumbitting a form on the page.
//in all cases, a type and an id for the issue or update must be passed or the page won't display
if (isset($_GET['id'])) {
	$ID = $_GET['id'];
} else if (isset($_POST['id'])) {
	$ID = $_POST['id'];	
} else {
	$ID = false;
	$userMessage = "<div class=\"alert alert-danger\">No system ID provided.</div>";

}

//get the type of posting (issue or update), either from GET or POST.
if (isset($_GET['type'])) {
	$type = $_GET['type'];

} else if (isset($_POST['type'])) {
	$ID = $_POST['type'];	

} else {
	$type = false;
	$userMessage = "<div class=\"alert alert-danger\">Type of data not specified</div>";

}

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

//delete an issue entirely.  This cannot be reversed and will also delete all the issue status updates automatically
if ($logged_in == 1 && isset($_POST["deleteIssue"])) {
	$result = deleteIssue($ID, $db);

	if ($result === true) {
		$userMessage = '<div class="alert alert-success">Issue Deleted.</div>';
	} else {
		$userMessage = "<div class=\"alert alert-danger\">" . $result . "</div";
	}
}


//delete an update message.  this will throw an error if it is the last update message left-all issues must have at least one status
if ($logged_in == 1 && isset($_POST["deleteUpdate"])) {
	$result = deleteUpdate($ID, $db);

	if ($result === true) {
		$userMessage = '<div class="alert alert-success">Update Deleted.</div>';
	} else {
		$userMessage = "<div class=\"alert alert-danger\">" . $result . "</div";
	}
}

//edit an update
if ($logged_in ==1 && isset($_POST["editUpdate"])) {
	if (isset($_POST["public"])) {
		$public = 1;
	} else {
		$public = 0;
	}
	$updateText = $db->real_escape_string($_POST["updatetext"]);
	$result = editUpdate($ID, $updateText, $public, $db);

	if ($result === true) {
		$userMessage = '<div class="alert alert-success">Update updated.</div>';
	} else {
		$userMessage = "<div class=\"alert alert-danger\">" . $result . "</div";
	}
}

//delete a status entirely.  
if ($logged_in == 1 && isset($_POST["deleteStatus"])) {
	$result = deleteStatus($_POST["status_id"], $db);

	if ($result === true) {
		$userMessage = '<div class="alert alert-success">Status Deleted.</div>';
	} else {
		$userMessage = "<div class=\"alert alert-danger\">" . $result . "</div";
	}
}

//edit an issue
if ($logged_in == 1 && isset($_POST["editIssue"])) {
	
	//start extracting and processing all the data from the form
	if (verifyTime($_POST["when"])) {
		$startTime = $_POST["when"];
	} else {
		$startTime = false;
	}

	if (isset($_POST["public"])) {
		$public = 1;
	} else {
		$public = 0;
	}

	if ($_POST["stop"] != "") {
		if (verifyTime($_POST["stop"])) {
			$endTime = $_POST["stop"];
		} else {
			$endTime = false;
		}

	} else {
		$endTime = "";
	}

	$statusID = $_POST["status"];
	if ($_POST["building"] == "true") {
		$building = true;

	} else {
		$building = false;
		
	}

	if ($endTime !== false && $startTime) {
		$result = updateIssue($ID, $startTime, $endTime, $statusID, $public, $building, $db);

		if ($result === true) {
			$userMessage = '<div class="alert alert-success">Issue Updated.</div>';
		} else {
			$userMessage = "<div class=\"alert alert-danger\">" . $result . "</div";
		}

	} else {
		$userMessage = "<div class=\"alert alert-danger\">Start or end times in incorrect format. </div";
	}


}

//Edit a status.  This is actually done by making a new status, then deleting the old one.  
//This forces the staus to show that it's been updated
if ($logged_in == 1 && isset($_POST["editStatus"])) {
	
	$statusText = $db->real_escape_string($_POST["statustext"]);
	$createResult = editStatus($_POST["status_id"], $statusText, $db);
	if ($createResult === true) {
		$userMessage = '<div class="alert alert-success">Status Updated.</div>';
	} else {
		$userMessage = "<div class=\"alert alert-danger\">Cannot edit status:". $createResult ." </div";
	}
	
	

}


//now check to make sure we can get data for the parameters supplied-if we have deleted the entry, then we need to signal to the rest
//of the script not to show anything
if ($type == "issue") {
	$issue = getIssueData($ID, $db);
	if (!is_array($issue)) {
		$m = $issue;
		$issue = false;

	}

} else {
	$update = getUpdateData($ID, $db);
	if (!is_array($update)) {
		$m = $update;
		$update = false;

	}
}



include 'resources/php/header.php';
?>



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

						<li style="float:right;margin-right: 8%;"><?php  echo (($logged_in == 1) ? 'Hello, ' . $user["fn"] . '&nbsp;//&nbsp;<a href="?logout" title="Log out" style="text-decoration: none; font-size: .9em;">Log out</a>' : '<a href="index.php?login" title="Log in" style="text-decoration: none; font-size: .9em;">Log in</a>'); ?></li>
				</ul>
			</div>
		</div> <!-- end line -->


		<div class="cms-clear"></div>
		<div class="row break" style="margin-top: 1em;">

		<?php

			if ($userMessage != "") {
	
				
				echo $userMessage;
		
	
			}

		?>

	</div> <!-- end line -->


	<a href="index.php">Back to Main Page</a>
	<div class="row">
	<P></P>
	
	<?php
			if ($ID && $type && $logged_in == 1) {
				if ($type == "issue" && $issue) {
					echo '<div class="span2">';
					 displayIssue($issue);

					echo '<form action="" method="POST">';
					echo '<input type="hidden" name="id" value="' . $ID . '">';
					echo '<input type="hidden" name="type" value="' . $type . '">';
					echo '<input style="margin-top: 1em; margin-bottom: 1em" type="submit" class="issue-change" name="deleteIssue" value="Delete This Issue">';
					

					 if (is_null($issue["end_time"]) || strtotime($issue["end_time"]) > time()) {
						$resolved = false;
					 } else {
						$resolved = true;
					 }
					
					echo '</form>';
				

					 $statusIDs = getStatusIDs($ID, $db);

					 foreach ($statusIDs as $statusID) {

						$statusData = getStatusData($statusID, $db);
						displayStatus($statusData, $db);
						
					 } 
					 echo "</div>";

					 

					 
				} else if ($type == "update" && $update) {
					echo '<div class="span2">';
					displayUpdate($update, $db);
					echo '</div>';
					echo '<div class="span1">';
					if ($logged_in == 1 && $user["id"] == $update["user"]) {
						
						
						echo '<form action="" method="POST">';
						echo '<input type="hidden" name="id" value="' . $ID . '">';
					
						echo '<input type="hidden" name="type" value="' . $type . '">';
						echo '<label for="updatetext">Update text of this update</label>';
						echo '<textarea name="updatetext" required>' . $update["text"] . '</textarea>';
						
						echo '<label for="public"> Public </label><input name="public" type="checkbox" ';
						if ($update["public"] == "1") {
							echo 'checked ';
						}
						echo '><br>';
						echo '<input type="submit" style="margin-top:1em; margin-right: 1em" name="editUpdate" value="Save Changes">';
						echo '<input type="submit" style="margin-top:1em; margin-right: 1em" name="deleteUpdate" value="Delete This Update" onclick="return confirm(\'Deleted updates cannot be recovered.  Are you sure?\')">';
						echo '</form>';
						
					}
					echo '</div>';
				}

				if ($logged_in == 1 && $type == "issue" && $issue) {
					echo '<div class="span1">';
					if (is_null($issue["end_time"])) {$end_time = "";} else {$end_time = formatDateTime($issue["end_time"]);}
					
					echo '<form action="" method="POST">';
					echo '<input type="hidden" name="id" value="' . $ID . '">';
				
					echo '<input type="hidden" name="type" value="' . $type . '">';
					echo '<label for="when">Change start time for this issue</label>';
					echo '<input required type="text" name="when" value="' . formatDateTime($issue["start_time"]) . '">';
					echo '<label for="stop">Change or set end time for this issue</label>';
					echo '<input type="text" name="stop" value="' . $end_time . '">';
					echo '<br><em>time format: YYYY-MM-DD HH:MM AM/PM<br> Erasing the end time or setting it in the future reopens the issue</em>';
					if ($status_types) {
						if (is_null($issue["building"])) {
							$building = false;
						} else {
							$building = true;
						}
						echo '<input type="hidden" name="building" value="true">';
						echo '<label for="status">Change Status of this issue:</label>';
						echo '<select style="margin-bottom: 1em" name="status">';
							foreach ($status_types as $status_number => $status_name) {
								if ($issue["status"] == $status_number) {$selected = "selected";} else {$selected = "";}
								if (!$building && $status_number == 7){continue;}
					
								echo '<option value="' . $status_number . '" '. $selected .'>' . $status_name . '</option>';
							}
						echo "</select>";
					}
					echo '<label for="public"> Public </label><input name="public" type="checkbox" ';
					if ($issue["public"] == "1") {
						echo 'checked ';
					}
					echo '>';
					echo "<P>";
					echo '<input type="submit" class="issue-change" name="editIssue" value="Save Changes">';
					echo '</form></div>';

					foreach ($statusIDs as $statusID) {
						
						$statusData = getStatusData($statusID, $db);
						if ($statusData["userID"] == $user["id"]) {						
												
							echo '<div class="row"><div class="span3">';
							echo '<form action="" method="POST">';
							echo '<input type="hidden" name="id" value="' . $ID . '">';
							echo '<input type="hidden" name="status_id" value="' . $statusData["statusID"] . '">';
												
							echo '<input type="hidden" name="type" value="' . $type . '">';

							echo '<label for="when">Edit this status:</label>';
							echo '<textarea name="statustext">' . $statusData["text"] . '</textarea>';
							
							echo '<input style="margin-left: 1em" type="submit" name="editStatus" value="Save Changes">';
							echo '<input style="margin-left: 1em" type="submit" name="deleteStatus" value="Delete This Status">';
							echo '</form></div></div>';
						}
					
					}

					
				}
			}
			

		


			





		
	
		?>
				

		<div class="row break footer">
			<div class="span3 unit">
				
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
