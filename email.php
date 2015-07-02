<?php
error_reporting(E_ALL);
if (php_sapi_name() == "cli") {
	require 'resources/secret/config.php';
	$con = new mysqli($db_host,$db_user,$db_pass,$db_database);
	$query = 
	"SELECT
	s.system_name,
	s.system_id,
	t.status_type_text as type,
	COUNT(t.status_type_text) as amount,
	u.user_fn as firstName,
	u.user_ln as lastName,
	u.user_email as email
	FROM
	issue_entries i,
	user u,
	status_type t,
	systems s
	WHERE i.created_by = u.user_id
	AND i.end_time = 0
	AND i.status_type_id = t.status_type_id
	AND i.system_id = s.system_id
	GROUP BY
	u.user_email
	ORDER BY
	u.user_email,
	s.system_name";

	$results = $con->query($query);
	$lastEmail = null;
	$lastSystem = null;
	$lastType = null;
	$subject = "$library_name Status - Open Issues";
	$headers = 
	"MIME-Version: 1.0" . "\r\n" .
	"Content-type:text/html;charset=UTF-8" . "\r\n" . 
	'From: GVSU Libraries Status <' . $from_email . '>' . "\r\n" .
	'Reply-To: ' . $from_email . "\r\n" .
	'Return-Path: ' . $from_email . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	$message = "<html><head></head><body>";
	$i = 0;

	while ($result = $results->fetch_assoc()){
		if ($lastEmail != $result['email']){
			if ($i > 0){
				$message .= "</body></html>";
				$to = $lastFirstName . ' ' . $lastLastName . ' <' . $lastEmail . '>';
				if(mail($to, $subject, $message, $headers, '-f ' . $from_email)) {
					echo 'Mail sent to ' . $lastEmail;
				}
				$message = "<html><head></head><body>";
			}
			$message .= "<h2>Hello " . $result['firstName'] . ",</h2>";
			$message .= "<p>You have at least one open ticket in the <a href='http://labs.library.gvsu.edu/status'>Library Status app</a></p><p>Please see the list below and update the tickets if possible.</p>";
		}
		if ($lastSystem != $result['system_name']){
			$message .= "<h3><a href='http://labs.library.gvsu.edu/status/detail.php?system=" . $result['system_id'] . "&status=unresolved'>" . $result['system_name'] . "</a></h3>";
		}
		$message .= "<p>" . $result['type'] . ": " . $result['amount'] . " open ticket(s)</p>";
		
		$lastEmail = $result['email'];
		$lastSystem = $result['system_name'];
		$lastType = $result['type'];
		$lastFirstName = $result['firstName'];
		$lastLastName = $result['lastName'];
		$i++;
	}


 
} else {
	$url = basename(dirname(__FILE__));
	header("Location: /$url");
}
?>