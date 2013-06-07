<?php
	include 'resources/secret/config.php';
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	date_default_timezone_set('America/Detroit');
	
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>GVSU University Libraries Status</title>

	<link rel="stylesheet" type="text/css" href="resources/css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/libui.css" />
	<link rel="stylesheet" type="text/css" href="resources/css/layout.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">

</head>

<body>

	<div id="gvsu-header-wrapper">
		<div id="gvsu-header">
			<div id="gvsu-logo">
				<a href="http://www.gvsu.edu/">
					<img src="http://www.gvsu.edu/homepage/files/img/gvsu_logo.png" alt="Grand Valley State University" border="0">
				</a>
			</div>
		</div>
	</div>

	<div id="wrapper">

	<div class="line break">
		<div class="span2of3 unit left">
			<h2><a href="index.php">University Libraries Status</a></h2>
		</div> <!-- end span -->

		<div class="span3 unit left login">
			<?php echo '<p>' . (isset($_SESSION['username']) ? '<a href="?logout" title="Log out">Log out</a></p>' : '<a href="admin.php" title="Log in">Log in</a></p>'); ?>
		</div>
	</div> <!-- end line -->

	<div class="line break">
		<div class="span1 unit left">

			<?php 

				$result = $db->query("SELECT * FROM systems ORDER BY system_name ASC");

				while($row = $result->fetch_assoc())
				{

					$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
													FROM issue_entries i
													WHERE i.system_id = {$row['system_id']}");

					$status = '<div class="lib-success" style="margin: 0;">
						<p>All systems are online.</p>
						</div>';

					while ($rw = $system_result->fetch_assoc()) {
						if ($rw['end_time'] == 0) { 
							if ($rw['status_type_id'] == 2) { 
								$status = '<div class="lib-error" style="margin: 0;">
									<p>Uh-oh, we have a system down. You can bet that we&#8217;re working on it!</p>
									</div>';
								break 2;
							}
						}
					} // end while

				} // end system while

				echo $status;

			?>

		</div> <!-- end span -->
	</div> <!-- end line -->

	<div class="line break">
		<div class="span2 unit left">
			<h3>Get Help</h3>

			<p>We&#8217;re available to help even if you&#8217;re off campus. Stop in at <a href="http://gvsu.edu/library/directions">any location</a>, or contact us during <a href="http://gvsu.edu/library/hours">Kirkhof library's regular hours</a>.</p>
			<div class="lib-button-small-grey">

				<a href='#' style="text-decoration: none;" onclick='window.open("https://libraryh3lp.com/chat/gvsulibs-queue@chat.libraryh3lp.com?skin=16489&identity=Librarian", "chat", "resizable=1,width=225,height=280"); return false;'><span>Chat</span></a>
			</div>

			<div class="lib-button-small-grey">
				<a style="text-decoration: none;" href="mailto:library@gvsu.edu">Email</a>
			</div>
		</div> <!-- end span -->

		<div class="span2 unit right">
			<h3>Report a Problem</h3>
			<p>Having trouble with any of the University Library's online systems? Drop us a line and let us know. We&#8217;ll do our best to sort it out.</p>
			<div class="lib-button-small-grey">
				<a href="feedback.php" id="feedback-trigger">Report a Problem</a>
			</div>

		</div> <!-- end span -->
	</div> <!-- end line -->

	<div class="line break">
		<div class="span1 unit">

			<div class="lib-table">
				<table>	
					<tbody>

						<thead>
							<tr>
								<th colspan="9">Library Status</th>
							</tr>

							<tr colspan="9" class="lib-row-headings name">
								<th style="text-align: right" >System</th>
								<th style="text-align: center">Currently</th>

								<?php foreach(range(0,5) as $cnt) {
										echo  '<th style="text-align: center;">' . date("M d", mktime(0, 0, 0, date("m")  , date("d")-$cnt, date("Y")))
										. '</th>';
										
									}?>
							</tr>
						</thead>

						<!-- load system names -->
						<?php

							$result = $db->query("SELECT * FROM systems ORDER BY system_name ASC");

							// loop through each system
							while($row = $result->fetch_assoc())
							{
								echo '<tr>';
								echo '<td style="text-align: right ">' . $row["system_name"] . '</td> ';
								echo '<td class = "col2 name" style="text-align: center;';

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id, s.status_type_text
								FROM issue_entries i, status_type s
								WHERE i.system_id = {$row['system_id']} AND i.status_type_id = s.status_type_id");
	
								$currently = ' color: #147D11">Online'; // currently displayed. Color difference is WCAG2 AA compliant

								// Display Day
								while ($rw = $system_result->fetch_assoc()) {
									if ($rw['end_time'] == 0) { 
										if ($rw['status_type_id'] == 2) { 
											// Color difference is WCAG2 AA compliant
											$currently = '"><a href="detail.php?system_id='. $row['system_id'] .'&day='. ($day-$cnt-1) .'" style = "text-decoration: none; color: #cb0000;">'.$rw['status_type_text'].'</a>';
										}
										else {
											$currently = '"><a href="detail.php?system_id='. $row['system_id'] .'&day='. ($day-$cnt-1) .'" style = "text-decoration: none; color: #147D11;">'.$rw['status_type_text'].'</a>';
										}
									}
								}
								echo $currently . '</td>'; // close currently displayed

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
																FROM issue_entries i
																WHERE i.system_id = {$row['system_id']}");

								$num_rows = $system_result->num_rows;

								// If no issues, then all is well
								if ($num_rows == 0) {

									$cnt = 0;
									foreach(range(0,5) as $cnt) {
										echo '<td style="text-align: center">';
										echo '<img alt="' . $rw['status_type_text'] . '" src="resources/img/checkmark.png">';
										echo '</td>';
									}

								// display specific status types
								} else {

									$day = date('Ymd', time());

									$cnt = 0;
									foreach(range(0,5) as $cnt) {

										echo'<td style="text-align: center">';


										$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
																FROM issue_entries i
																WHERE i.system_id = {$row['system_id']}");

										// Display Day
										while ($rw = $system_result->fetch_assoc()) {
							
											$start_day = date('Ymd', $rw['start_time']);
											$end_day = date('Ymd', $rw['end_time']);

											// Moved inside the while loop for the alt tags
											$day_status = '<img  alt="' . $rw['status_type_text'] . '" src="resources/img/checkmark.png">';

											if ((( ($day-$cnt) >= $start_day) && ($rw['end_time'] == 0)) || 
												(( ($day-$cnt) >= $start_day && ($day-$cnt-1) <= $end_day))) {

												echo '<a href="detail.php?system_id='. $row['system_id'] .'&day='. ($day-$cnt) .'" style = "text-decoration: none;">';

												if ($rw['status_type_id'] == 2) {
													$day_status = '<b style= "color: red" title="' . $rw['status_type_text'] . '">X</b>';
												}

												else {
													$day_status = '<img  alt="' . $rw['status_type_text'] . '" src="resources/img/minorissue.png">';
												}
											}
										}

										echo $day_status . "</td>"; // close currently displayed
									}

								} // end else if

								echo '</tr>'; // close row
							}

						?>

					</tbody>
				</table>

			</div>
		</div> <!-- end span -->
	</div> <!-- end line -->

	<div class="line break footer">
		<div class="span1 unit break">
			<p>Written by <a href="http://jonearley.net/">Jon Earley</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>
		</div> <!-- end span -->
	</div> <!-- end line -->
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>

$(document).ready(function() {

	$("body").append('<div class="feedback lib-form"> <form method="post" action="http://www.gvsu.edu/library/customemail-post.htm?keyId=9D7CB431-E6EB-A2DB-48089384265083C9"> <label for="name">Your Name:</label> <input type="text" name="name" id="name" placeholder="Optional" /> <label for="feedback">Have an idea? See a problem?</label> <textarea name="feedback"></textarea> <div class="right"> <a href="index.php" style="display: inline-block; margin-right: 2em;">Cancel</a> <input class="lib-button" type="submit" value="Send Feedback" style="margin-top: 1em;" /> </div> </form> </div>');

	$(".feedback").hide();

	$("#feedback-trigger").click(function(e) {

		e.preventDefault();

		$(".feedback").slideToggle(400);

	});
});

</script>
</body>

</html>

