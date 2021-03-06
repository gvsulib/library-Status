<?php
$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
setcookie("referrer", $actual_url, 0, "/");
if (!isset($_COOKIE["login"])) {
	setcookie("login", "", 0, "/");
        $_COOKIE["login"] = "";
}

 //loads required library files
require 'resources/config/config.php';

//markdown is used to display the status entries for issues and the text of updates
require $markdown_path;

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



// uncomment to force a login
//$_SESSION['username'] = 'felkerk';

//you should only get to this page by following a link from index.php or by sumbitting a form on the page.
//in all cases, a type and an id for the issue or update must be passed or the page won't display
if (isset($_GET['id'])) {
	$ID = $_GET['id'];
	settype($ID, "integer");
	
} else if (isset($_POST['id'])) {
	$ID = $_POST['id'];	
	set_type($ID, "integer");
} else {
	$ID = false;
	$userMessage = "<div class=\"alert alert-danger\">No system ID provided.</div>";

}

//get the type of posting (issue or update), either from GET or POST.
if (isset($_GET['type'])) {
	if ($_GET['type'] == "issue") {
		$type = "issue";
	} else if ($_GET['type'] == "update") {
		$type = "update";
	} else {
		$type = false;
		$userMessage = "<div class=\"alert alert-danger\">Type of data not specified</div>";
	}

} else if (isset($_POST['type'])) {
	$ID = $_POST['type'];	
	set_type($ID, "integer");
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



//now check to make sure we can get data for the parameters supplied-if we have deleted the entry, then we need to signal to the rest
//of the script not to show anything
if ($type == "issue") {
	$issue = getIssueData($ID, $db);
	
	if (!is_array($issue)) {
		$userMessage = $issue;
		$issue = false;
		

	}

} else {
	$update = getUpdateData($ID, $db);
	if (!is_array($update)) {
		$userMessage = $update;
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
				echo "<P>";
		
	
			}

		?>

	</div> <!-- end line -->


	<a href="index.php">Back to Main Page</a>
	<div class="row">
	<P></P>
	
	<?php
			if ($ID && $type) {
				if ($type == "issue" && $issue) {
					echo '<div class="span3">';
					 displayIssue($issue);

					
					 if (is_null($issue["end_time"]) || strtotime($issue["end_time"]) > time()) {
						$resolved = false;
					 } else {
						$resolved = true;
					 }
					
					
					 

					 $statusIDs = getStatusIDs($issue["id"], $db);

				

					 foreach ($statusIDs as $statusID) {

						$statusData = getStatusData($statusID, $db);
						
						displayStatus($statusData, $db);
						
					 } 
					 echo "</div>";

					 

					 
				} else if ($type == "update" && $update) {
					echo '<div class="span3">';
					displayUpdate($update, $db);
					echo '</div>';
			
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
