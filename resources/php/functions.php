<?php

/*
	When a user submits a problem report, this function checks
	the input to make sure it isn't malicous (or a spambot!) and
	then sends an email with a problem report to the specified
	email address.
*/

function send_email($name,$email,$message) {

	global $m, $to_email, $from_email, $email_subject;

	// If there is an error, the message suggests calling the library. Add your
	// Library's phone number here.
	$library_phone = '(616) 331-3500';

/*
	Uncomment the following line if you want to require emails. You should also
	see the README for information on how to check for emails on the client side
	with JavaScript.
*/
//require_field($email, "email address");

/*
	Uncomment the following line if you want to require names. You should also
	see the README for information on how to check for names on the client side
	with JavaScript.
*/
//require_field($name, "name");

	// Build the headers
	$headers = "From: " . $from_email . "\r\n";
	$headers .= "Reply-To: " . $from_email . "\r\n";
	$headers .= "X-Mailer: PHP/".phpversion();

	// Check to make sure there are no really sneaky naught bits in the message
	contains_bad_str($message);
	contains_bad_str($email);
	contains_bad_str($name);
	contains_newlines($email);
	contains_newlines($name);

	// Build the message
	$error_report = $message;
	$error_report .= "\n\n" . 'From: ' . $name;
	$error_report .= "\n" . 'Email: ' . $email;

	// Attempt to send the mail, then set the message variable $m to success or
	// error.
	if($m == NULL) { // There have been no errors, send the email
		if(mail($to_email, $email_subject, $error_report, $headers)) {
				$m = '<div class="lib-success">Thanks for reporting this issue! We&#8217;ll get right on it.</div>';
			} else {
				$m = '<div class="lib-error">Uh-oh. There was a problem sending your report. Maybe try calling the library at ' . $library_phone . '?</div>';
			}
	}
}

/*
	This function checks to see if the email address provided is actually an email
	address. If you decide to require email addresses in the send_email() function,
	you'll be using this.
*/

function is_valid_email($email) {

	global $m;

  return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
}

/*
	This function looks for common spam injection terms that bots use to turn your
	email form into a mass mailer. It rejects any submission with those terms. You
	can add additional terms by adding items to the $bad_strings array.
*/

function contains_bad_str($str_to_test) {

	global $m;

  $bad_strings = array(
                "content-type:"
                ,"mime-version:"
                ,"multipart/mixed"
		,"Content-Transfer-Encoding:"
                ,"bcc:"
		,"cc:"
		,"to:"
  );

	// Check the field for each of the bad strings, and if you we find one, set the
	// message variable $m to show the error.
  foreach($bad_strings as $bad_string) {
    if(preg_match($bad_string, strtolower($str_to_test))) {
      $m = '<div class="lib-error">Your message looks a lot like what a spambot would try to submit. Try again without the naughty bits.</div>';
    }
  }
}

/*
	This function checks the email address field to see if the bot is trying to sneak
	something into the mail header to send out mass spam. It rejects any submission
	that meets the criteria.
*/

function contains_newlines($str_to_test) {

	global $m;

   if(preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str_to_test) != 0) {
     $m = '<div class="lib-error">Your email address was formatted in a way that looks a lot like what a spambot would do if they wanted to send out a zillion emails.  Try again without the naughty bits.</div>';
     exit;
   }
}

/*
	This function checks to make sure that a required value was provided and
	prompts the user to supply it before continuing.
*/
function require_field($value, $key) {

	global $m;

	if(($value == NULL) || ($value == "")) {
		$m = '<div class="lib-error">Please include your ' . $key . '.</div>';
	}

}

/*
	This function adds a comment form at the end of an open issue, only if the
	item has not been resolved and the user is logged in.
*/

function add_comment_field($issue_id, $status_type_id) {

	global $logged_in, $resolved;

	if(($logged_in == 1) && ($resolved == 0)) {

		echo '<div class="lib-form add-comment-form" style="margin-top: .5em; padding-top: .5em; border-top: 1px dotted #bbb;">

			<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" name="status-form">
				<fieldset>
				<legend>Add a Status Update</legend>
				<label for="status-' . $issue_id . '" style="display:none;">Update Status</label>
				<textarea style="margin-top: .5em; height: 5em; font-size: 1em; width: 100%;" id="status-' . $issue_id . '" name="status" placeholder="Update the Status of this Issue"></textarea>

			<div class="row" style="margin-top:.5em;">
				<div class="span2" >

					<label style="margin-left: 1em;display:inline;" class="lib-inline" for="issue_resolved">Issue Resolved:</label>
					<input type="checkbox" name="issue_resolved" id="issue_resolved" value="1">

					<label class="lib-inline" style="display:inline;margin-left:1em;" for="comment-when-' . $issue_id . '" >When</label>
					<input type="text" style="width:6em; display:inline-block;" name="when" id="comment-when-' . $issue_id . '" value="Now" />
				</div>
				<div class="left unit span1 lastUnit" style="text-align:right;">
					<input class="status-button" name="submit_status" type="submit" value="Update" />
				</div>
																						<div class="cms-clear" style="padding-bottom:.5em;"></div>

			</div>


				<input type="hidden" name="issue_id" value="' . $issue_id . '" />
				<input type="hidden" name="status_type_id" value="' . $status_type_id . '" />
			</fieldset>
			</form>

		</div>';

	}
}



function edit_comment_field($issue_id, $status_entry_id) {


}
