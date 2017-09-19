<?
//sets session and other variables that have to be initialized when any page of the status app is loaded


session_start();
//error_reporting(0);

$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

$_SESSION['location'] = $actual_url;
	
date_default_timezone_set('America/Detroit');

//variable to track if user is logged in
$logged_in = 0; 

//if using native login, set the URL
if ($use_native_login == true){
    $loginUrl = "login.php";

}
//can we connect to the database?  If not, display an error and cease loading the app
$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
if ($db->connect_errno) {
    HTML_error_message($db->connect_error);
    exit;
}
?>