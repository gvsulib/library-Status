<?php
include('../resources/secret/config.php');
$tomorrow = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
$tomorrow = date("Y-m-d", $tomorrow);
$date = $tomorrow;
$i = 0;

$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

date_default_timezone_set('America/Detroit');


header("Content-type: text/xml"); 
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?> 
<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
<channel>
<title>GVSU University Libraries System Status</title>
<link>http://gvsu.edu/library/status</link>
<description>Current state of all online systems at Grand Valley State University Libraries</description>
<language>en-us</language>
<atom:link href=\"http://labs.library.gvsu.edu/status/rss\" rel=\"self\" type=\"application/rss+xml\" />
"; 

// Grab the last 10 status entries. 
$result = $db->query("SELECT s.status_id, s.status_id, s.status_timestamp, s.status_text,
							u.user_fn, u.user_ln, u.user_email,
							st.status_type_text,
							sy.system_name, sy.system_id
					 FROM status_entries s, user u, status_type st, systems sy, issue_entries ie
					 WHERE s.status_delete != 1
					 AND u.user_id = s.status_user_id
					 AND s.status_type_id = st.status_type_id
					 AND s.issue_id = ie.issue_id
					 AND sy.system_id = ie.system_id
					 ORDER BY status_timestamp DESC LIMIT 10");
	
	if($result) {

		$obj = $result->fetch_object();

		// Add the entry to the feed

echo '<item> 
<title>' . $obj->status_type_text . ' for ' . $obj->system_name . '</title>
<link>http://labs.library.gvsu.edu/status/detail.php?system_id=' . $obj->system_id . '&amp;day=' . date("Ymd", $obj->status_timestamp) . '</link>
<description><![CDATA[' . Markdown($obj->status_text) . ']]></description>
<author>' . $obj->user_email . ' (' . $obj->user_fn . ' ' . $obj->user_ln . ')</author>
<pubDate>' . date('D, d M Y g:i:s O', $obj->status_timestamp) . '</pubDate>
<guid>http://labs.library.gvsu.edu/status/detail.php?system_id=' . $obj->system_id . '&amp;day=' . date("Ymd", $obj->status_timestamp) . '</guid> 
</item>
';		

	}

	
echo "</channel></rss>"; 
?>