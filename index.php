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
				<p>Chat</p>
			</div>
			<div class="lib-button-small-grey">
				<p>Email</p>
			</div>
		</div> <!-- end span -->
		<div class="span2 unit right">
			<h3>Report a Problem</h3>
			<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In posuere felis nec tortor. Pellentesque faucibus.</p>
			<div class="lib-button-small-grey">
				<p>Report a Problem</p>
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
								echo'<td class = "col2 name" style="text-align: center;';

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
																FROM issue_entries i
																WHERE i.system_id = {$row['system_id']}");
	
								$currently = ' color: #149811">online'; // currently displayed

								// Display Day
								while ($rw = $system_result->fetch_assoc()) {
									if ($rw['end_time'] == 0) { 
										if ($rw['status_type_id'] == 2) { 
											$currently = ' color: red">outage';
										}
										else {
											$currently = ' color: orange">disruption';
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
										echo  '<td style="text-align: center">';
										echo'<img src="resources/img/checkmark.png">';
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

											//echo '<br>check cell';
							
											$start_day = date('Ymd', $rw['start_time']);
											$end_day = date('Ymd', $rw['end_time']);

											// error checking
											/*
											echo 'Day: ' . ($day-$cnt-1);
											echo '<br><br>Start time: ' . $start_day;
											echo '<br><br>End time: ' . $end_day;
											echo '<br><br>';
											*/


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
			<p>Footer - Grand Valley State University Libraries</p>
		</div> <!-- end span -->
	</div> <!-- end line -->
</body>

</html>

