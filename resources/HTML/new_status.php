<div name="new_status" style="clear: all">
	<form name="status-form" method="POST">
		<fieldset>
			<input name="issue_id" type="hidden" value="<?php echo $issue_id; ?>">
			<input name="user_id" type="hidden" value="<?php echo $user['id']; ?>">
			<legend>Update the Status of this issue</legend>
			<textarea style="margin-top: .5em; height: 5em; font-size: 1em; width: 100%;" id="status-872" name="status" placeholder="Update the Status of this Issue"><?php if (isset($_POST['status-form'])) {echo $_POST['status'];} ?></textarea>
			<div class="row" style="margin-top:.5em;">
				<div class="span2">

					<label style="margin-left: 1em;display:inline;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
					<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1" <?php if (isset($_POST['status-form'])) {echo 'checked ';} ?>>

					<label class="lib-inline" style="display:inline;margin-left:1em;" for="comment-when-872">When</label>
					<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-872" value="<?php if (isset($_POST['status-form'])) {echo $_POST['when']; } else { echo "Now";}?>">
				</div>
				<div class="left unit span1 lastUnit" style="text-align:right;">
					<input class="status-button" name="submit_status" type="submit" value="Update">
				</div>
				<div class="cms-clear" style="padding-bottom:.5em;"></div>

			</div>


		</fieldset>
	</form>
</div>