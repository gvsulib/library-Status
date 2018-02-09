<?php
if (isset($_POST["submit_issue"]) && isset($submitted)) {
	
	$systemid = $_POST["system_id"];
	$statusid = $_POST["status_type_id"];
	$start = $_POST["when"];
	$text = $_POST["issue_text"];
	if (isset($_POST["end_time"])) {$endtime = $_POST["end_time"];} else {$endtime = "";}
} else {
	$systemid = "0";
	$statusid = "0";
	$start = "Now";
	$text = "";
	$endtime = "";
}

?>


<div class="row lib-form">
			<div>
			<a name="problem"><h3>Report an Issue/Update</h3></a>
			</div>

			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="issue-form">
				<div class="row">
				<div class="span1">
				<input type="hidden" name="userid" value="<?php echo $user["id"]; ?>"> 
					<label class="lib-inline">System:</label>
					<select name="system_id">

						<!-- load system names -->
						<?php
						$result = $db->query("SELECT * FROM systems");

						while($row = $result->fetch_assoc()) {
							
							echo '<option ';
							if ($systemid == $row["system_id"]) { echo 'selected ';}
							echo 'value="' . $row["system_id"] . '">' . $row["system_name"] . '</option>';
						}
						?>
						
					</select>
					<label for="public"> Public </label><input name="public" type="checkbox" checked>
				</div>

				<div class="span1">
					<label class="lib-inline">Status:</label>
					<select name="status_type_id" id="status_type_id">
						
						<option value="0">Update</option>
						<!-- Load status types -->
						<?php
						$result = $db->query("SELECT * FROM status_type");
						while($row = $result->fetch_assoc()) {
							
							echo '<option '; 
							if ($statusid == $row["status_type_id"]) {echo 'selected ';} 
							echo 'value="' . $row["status_type_id"] . '">' . $row["status_type_text"] . '</option>';
						}
						?>

					</select>
				</div>

				<div class="when_box span1">

					<label style="padding-top: .2em; " for="when">When:</label>
					<input type="text" name="when" required value = "<?php echo $start; ?>" style="width: 40%; font-size: .8em; font; color: #575757; display: inline">
					<div class="end-time-box">
						<label style="padding-top: .2em;" for="end_time">Ends:</label>
						<input type="text" name="end_time" <?php if ($endtime != "") { echo 'value="'. $endtime . '"';} ?> style="width: 40%; font-size: .8em; font; color: #575757; display: inline">
					</div>
				</div>
				</div>

				<div class = "span3 unit" style="float: left; padding: 1em 0">
					<textarea required style="font-size: 1em;width:96%" name="issue_text" placeholder="describe the issue (required)"><?php echo $text; ?></textarea>
				</div>

				<input class="status-button" style="float: left;" name="submit_issue" type="submit" value="Submit Issue" />

				
			</form>

</div> 

	
