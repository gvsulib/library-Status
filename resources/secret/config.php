
<?php

// Database connection details
/*$db_host = 'mysql.gvsuliblabs.com';
$db_user = 'gvsu_sustain_use';
$db_pass = 'Htf4eJcKCeGq2P';
$db_database = 'library_status';
*/
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_database = 'status-test2';

// Styles
$library_name = 'GVSU University Libraries';
$header_image = '//gvsu.edu/homepage/files/img/gvsu_logo.png';
$header_url = 'http://gvsu.edu';
$banner_color = '#0065A4';

// Subscriptions
$rss_url = 'http://feeds.feedburner.com/gvsulibstatus';
$email_subscription_url = 'http://feedburner.google.com/fb/a/mailverify?uri=gvsulibstatus&amp;loc=en_US';

// Set default variables for sending emails
  $to_email = 'x+12209374156716@mail.asana.com';
  $from_email = 'library@gvsu.edu';
  $email_subject = 'Library Systems Problem Report';

  //Recaptcha API Keys
$recaptchaSiteKey = '6LdAMAATAAAAAGU60ya3otW3jpD6WFPn5TFMqE8-';
$recaptchaSecretKey = '6LdAMAATAAAAAF74Rc3yTsDaDR_Jyr9WIpg5Rb42';
//Native login or not
$use_native_login = false;
$not_native_login_url = 'http://labs.library.gvsu.edu/login';
?>
