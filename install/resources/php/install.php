<?php
session_start();
if (isset($_GET['done'])){
	finishInstall();
	header('location: ../../index.php');
}
foreach($_POST as $key=>$value){
	$_SESSION[$key] = $value;
}
$toGoTo = $_POST['step'] + $_POST['go'];
if ($toGoTo < 1) {
	die();
}
else if ($toGoTo == 6) {
	doInstall();
} else {
	header("location: ../../step$toGoTo.php");
}
function doInstall(){
	require 'mustache.php';
	$m = new Mustache_Engine;
	$cfg =  $m->render(file_get_contents('config.txt'), $_SESSION);
	$f = fopen('../../../resources/secret/config.php','w');
	fwrite($f,$cfg);
	require '../../../resources/secret/config.php';
	$user_pw = $_SESSION['login-native-login'] ? sha1($_SESSION['native-user-password']) : "NULL";
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	
	$a = explode(';',file_get_contents('../sql/install.sql'));
	
	for ($i = 0; $i < count($a); $i++){
		if ($db->query($a[$i] . ";") === TRUE){
			
		}
	}

	for ($i = 0; $i < 10; $i++){
		if (strlen($_SESSION['system-name'][$i]) > 0 && strlen($_SESSION['system-URL'] > 0)){
			if ($db->query("INSERT INTO `systems` VALUES(NULL, '{$_SESSION['system-name'][$i]}', '{$_SESSION['system-URL'][$i]}'")){

			}
		}
	}

	if($db->query("INSERT INTO `user` VALUES (NULL, '{$_SESSION['native-user-username']}', '{$user_pw}', '{$_SESSION['native-user-fname']}','{$_SESSION['native-user-lname']}',	'{$_SESSION['native-user-email']}',0,0,9);"))
	{
		header('location: ../../complete.php');
	}
}
function finishInstall(){
	exec('mv ../../install ../../_install');
	$indexRedir = "<?php header('location: ../index.php');?>";
	file_put_contents('../../index.php', $indexRedir);
}
?>