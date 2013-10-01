<?php

	session_start();
	$_SESSION['location'] = 'http://' . $_SERVER['SERVER_NAME'] . "/status/admin.php";

	include ('resources/php/markdown.php');

	include 'resources/secret/config.php';
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	date_default_timezone_set('America/Detroit');


	$open_issues=$db->query("SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id AND (issue_entries.end_time BETWEEN 0 AND 0) ORDER BY issue_entries.issue_id DESC");


	if ($open_issues->num_rows > 0) {

		while($issue = $open_issues->fetch_assoc()) {

			$open_status=$db->query("SELECT u.user_id, u.user_fn, u.user_ln, s.issue_id, s.status_text, u.user_email
				FROM status_entries s, user u, status_type st
				WHERE s.issue_id = '{$issue['issue_id']}' AND s.status_user_id = u.user_id AND s.status_type_id = st.status_type_id
				GROUP BY s.issue_id");

			while ($status = $open_status->fetch_assoc()) {

				echo $status['issue_id'] . ' ' . $status['user_fn'] . ' ' . $status['user_ln'] . '<br>';

				$message = "Testing library status email script.";

				mail('jonathan.a.earley@gmail.com', 'My Subject', $message);

			}

		}

	}


?>

</body>
</html>






