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

	echo $error_report;

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
