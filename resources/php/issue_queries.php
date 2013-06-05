<?php

// First name
$query = "SELECT user_fn FROM user WHERE user_id='{$row['status_user_id']}'";
$result_user_fn = $db->query($query);
$obj = $result_user_fn->fetch_object(); 
$fn = $obj->user_fn;

// Last name
$query = "SELECT user_ln FROM user WHERE user_id='{$row['status_user_id']}'";
$result_user_ln = $db->query($query);
$obj = $result_user_ln->fetch_object(); 
$ln = $obj->user_ln;

// System ID
$query = "SELECT system_id FROM issue_entries WHERE issue_id = '{$row['issue_id']}'";
$result_system = $db->query($query);
$obj = $result_system->fetch_object(); 
$s_id = $obj->system_id;

// System name tag
$query = "SELECT system_name FROM systems WHERE system_ID = '$s_id'";
$result_system = $db->query($query);
$obj = $result_system->fetch_object(); 
$s_name = $obj->system_name;

// status name tag
$query = "SELECT status_text FROM statuses WHERE statuses_id = '{$row['statuses_id']}'";
$result_status = $db->query($query);
$obj = $result_status->fetch_object(); 
$status_name = $obj->status_text;

// status id tag
$query = "SELECT statuses_id FROM statuses WHERE statuses_id = '{$row['statuses_id']}'";
$result_status = $db->query($query);
$obj = $result_status->fetch_object(); 
$status_id = $obj->statuses_id;

// status resolved
$query = "SELECT status_resolved FROM statuses WHERE statuses_id = '{$row['statuses_id']}'";
$r_status = $db->query($query);
$obj = $r_status->fetch_object(); 
$status_resolved = $obj->status_resolved;

// status outage
$query = "SELECT status_outage FROM statuses WHERE statuses_id = '{$row['statuses_id']}'";
$rs_status = $db->query($query);
$obj = $rs_status->fetch_object(); 
$status_outage = $obj->status_outage;