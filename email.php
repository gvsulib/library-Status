<?php
$query = 
"SELECT
	s.system_name,
	s.system_id,
	t.status_type_text as type,
	COUNT(t.status_type_text) as amount,
	u.user_fn as firstName,
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
 	u.user_email,
 	s.system_name,
 	t.status_type_text
 ORDER BY
 	u.user_email,
 	s.system_name";
include 'resources/secret/config.php';
$con = new mysqli($db_host,$db_user,$db_pass,$db_database);
$results = $con->query($query);
?>
<html>
	<head>
		<title>Email Test</title>
		<style>
		html, body{
			font-family: Arial;
		}
		</style>
	</head>
	<body>
	<table>
			<?php
			$lastEmail = null;
			$lastSystem = null;
			$lastType = null;

			while ($result = $results->fetch_assoc()){
				if ($lastEmail != $result['email']){
					echo "<h2>Hello " . $result['firstName'] . ",</h2>";
					echo "<p>You have at least one open ticket in the <a href='http://labs.library.gvsu.edu/status'>Library Status app</a></p><p>Please see the list below and update the tickets if possible.</p>";
				}
				if ($lastSystem != $result['system_name']){
					echo "<h3><a href='http://labs.library.gvsu.edu/status/detail.php?system=" . $result['system_id'] . "&status=unresolved'>" . $result['system_name'] . "</a></h3>";
				}
				echo "<p>" . $result['type'] . ": " . $result['amount'] . " open ticket(s)</p>";
				
				$lastEmail = $result['email'];
				$lastSystem = $result['system_name'];
				$lastType = $result['type'];
			}
			?>
			</table>
			</body>
			</html>