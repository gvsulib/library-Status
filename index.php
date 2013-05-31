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
	<div class="line break" style="padding-top: 1em">
		<div class="span1 unit">
			<h2>University Libraries Status</h2>
		</div> <!-- end span -->
	</div> <!-- end line -->

	<div class="line">
		<div class="span1 unit">

			<?php
				// library status div
			?>

		</div> <!-- end span -->
	</div> <!-- end line -->

	<div class="line break">
		<div class="span2 unit left">
			<h3>Get Help</h3>
			<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In posuere felis nec tortor. Pellentesque faucibus.</p>
			<div class="lib-button-small-grey">

				<a href='#' style="text-decoration: none;" onclick='window.open("https://libraryh3lp.com/chat/gvsulibs-queue@chat.libraryh3lp.com?skin=16489&identity=Librarian", "chat", "resizable=1,width=225,height=280"); return false;' ><span>Chat</span></a>
			</div>
			<div class="lib-button-small-grey">
				<a style="text-decoration: none;" href="mailto:library@gvsu.edu">Email</a>
			</div>
		</div> <!-- end span -->
		<div class="span2 unit right">
			<h3>Report a Problem</h3>
			<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In posuere felis nec tortor. Pellentesque faucibus.</p>
			<div class="lib-button-small-grey">
				<a id="feedback-trigger">Report a Problem</a>
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
								<th colspan="8">Library Status</th>
							</tr>

							<tr colspan="8" class="lib-row-headings name">
								<th style="text-align: right" >System</th>
								<th style="text-align: center">Currently</th>

								<?php foreach(range(0,5) as $cnt) {
										echo  '<th style="text-align: center;">' . date("M d", mktime(0, 0, 0, date("m")  , date("d")-$cnt-1, date("Y")))
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

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
																FROM issue_entries i
																WHERE i.system_id = {$row['system_id']}");
	
								$currently = ' color: #149811">online'; // currently displayed

								// Display Day
								while ($rw = $system_result->fetch_assoc()) {
									if ($rw['end_time'] == 0) { 
										if ($rw['status_type_id'] == 2) { 
											$currently = ' color: red"><a href="detail.php?system_id='. $row['system_id'] .'&day='. ($day-$cnt-1) .'" style = "text-decoration: none; color: red;">outage</a>';
										}
										else {
											$currently = ' color: orange"><a href="detail.php?system_id='. $row['system_id'] .'&day='. ($day-$cnt-1) .'" style = "text-decoration: none; color: orange;">Disruption</a>';
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
										echo '<img src="resources/img/checkmark.png">';
										echo '</td>';
									}

								// display specific status types
								} else {

									$day = date('Ymd', time());

									$cnt = 0;
									foreach(range(0,5) as $cnt) {

										echo'<td style="text-align: center">';

										$day_status = '<img src="resources/img/checkmark.png">';

										$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
																FROM issue_entries i
																WHERE i.system_id = {$row['system_id']}");

										// Display Day
										while ($rw = $system_result->fetch_assoc()) {
							
											$start_day = date('Ymd', $rw['start_time']);
											$end_day = date('Ymd', $rw['end_time']);

											if ((( ($day-$cnt-1) >= $start_day) && ($rw['end_time'] == 0)) || 
												(( ($day-$cnt-1) >= $start_day && ($day-$cnt-1) <= $end_day))) {

												echo '<a href="detail.php?system_id='. $row['system_id'] .'&day='. ($day-$cnt-1) .'" style = "text-decoration: none;">';

												if ($rw['status_type_id'] == 2) {
													$day_status = '<b style= "color: red">X</b>';
												}

												else {
													$day_status = '<b style= "color: orange">---</b>';
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
			<p>Grand Valley State University Libraries</p>
		</div> <!-- end span -->
	</div> <!-- end line -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>

$(document).ready(function() {

	$("body").append('<div class="feedback lib-form"> <form method="post" action="http://www.gvsu.edu/library/customemail-post.htm?keyId=9D7CB431-E6EB-A2DB-48089384265083C9"> <label for="name">Your Name:</label> <input type="text" name="name" id="name" placeholder="Optional" /> <label for="feedback">Have an idea? See a problem?</label> <textarea name="feedback"></textarea> <div class="right"> <a href="index.php" style="display: inline-block; margin-right: 2em;">Cancel</a> <input class="lib-button" type="submit" value="Send Feedback" style="margin-top: 1em;" /> </div> </form> </div>');

	$(".feedback").hide();

	$("#feedback-trigger").click(function() {

		$(".feedback").slideToggle(400);

	});
});

</script>
</body>

</html>

