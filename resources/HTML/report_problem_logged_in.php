<div class="row lib-form">
			<div>
				<h3>Report an Issue/Update</h3>
			</div>

			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="issue-form" onsubmit="return validateForm()">
				<div class="row">
				<div class="span1">
				<input type="hidden" name="userid" value="<?php echo $user["id"]; ?>"> 
					<label class="lib-inline">System:</label>
					<select name="system_id">

						<!-- load system names -->
						<?php
						$result = $db->query("SELECT * FROM systems");

						while($row = $result->fetch_assoc())
						{
							echo '<option value="' . $row["system_id"] . '">' . $row["system_name"] . '</option>';
						}
						?>

					</select>
				</div>

				<div class="span1">
					<label class="lib-inline">Status:</label>
					<select name="status_type_id" id="status_type_id">
						<option value="0">Update</option>
						<!-- Load status types -->
						<?php
						$result = $db->query("SELECT * FROM status_type");
						while($row = $result->fetch_assoc()) {
							
							echo '<option value="' . $row["status_type_id"] . '">' . $row["status_type_text"] . '</option>';
						}
						?>

					</select>
				</div>

				<div class="when_box span1">

					<label style="padding-top: .2em; " for="when">When:</label>
					<input type="text" name="when" value = "Now" style="width: 40%; font-size: .8em; font; color: #575757; display: inline">
					<div class="end-time-box">
						<label style="padding-top: .2em;" for="end_time">Ends:</label>
						<input type="text" name="end_time"  style="width: 40%; font-size: .8em; font; color: #575757; display: inline">
					</div>
				</div>
				</div>

				<div class = "span3 unit" style="float: left; padding: 1em 0">
					<textarea required style="font-size: 1em;width:96%" name="issue_text" placeholder="describe the issue (required)"></textarea>
				</div>

				<input class="status-button" style="float: left;" name="submit_issue" type="submit" value="Submit Issue" />

				
			</form>

</div> 

	
