<?php
session_start();
	include 'resources/secret/config.php';
	include ('resources/php/markdown.php');
	
	$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $db->connect_error);
    	exit();
	}

	date_default_timezone_set('America/Detroit');
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>GVSU University Libraries Status</title>

	<link rel="stylesheet" type="text/css" href="resources/css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="resources/css/layout.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">

</head>

<body>

	<div id="gvsu-header-wrapper">
		<div id="gvsu-header">
			<div id="gvsu-logo">
				<a href="http://www.gvsu.edu/">
					<img src="http://www.gvsu.edu/homepage/files/img/gvsu_logo.png" alt="Grand Valley State University" border="0">
				</a>
			</div>
		</div>
	</div>

	<div id="wrapper">

	<div class="line break">
		<div class="span2of3 unit left">
			<h1><a href="index.php">University Libraries Status</a></h1>
		</div> <!-- end span -->

		<div class="span3 unit left login">

			<?php echo '<p>' . (isset($_SESSION['username']) ? '<a href="?logout" title="Log out">Log out</a></p>' : '<a href="admin.php" title="Log in">Log in</a> | Subscribe: <a href="http://feeds.feedburner.com/gvsulibstatus" title="Subscribe to the RSS feed">RSS</a> | <a href="http://feedburner.google.com/fb/a/mailverify?uri=gvsulibstatus&amp;loc=en_US" title="Subscribe to updates via Email">Email</a></p>'); ?>

		</div>
	</div> <!-- end line -->

	<div class="line break">
		<div class="span1 unit left">

			<?php 

				$result = $db->query("SELECT * FROM systems ORDER BY system_category ASC, system_name ASC");
				$now = time();

				while($row = $result->fetch_assoc())
				{

					$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id
													FROM issue_entries i
													WHERE i.system_id = {$row['system_id']}
													AND i.start_time < '$now'");

					$status = '<div class="lib-success" style="margin: 0;">
						<p>All systems are online.</p>
						</div>';

					while ($rw = $system_result->fetch_assoc()) {

						// Check if there is a resolution or if a scheduled resolution has not happened yet
						if (($rw['end_time'] == 0) || ($rw['end_time'] > $now)) { 
							if ($rw['status_type_id'] == 2) { 
								$status = '<div class="lib-error" style="margin: 0;">
									<p>Uh-oh, we have a system down. You can bet that we&#8217;re working on it!</p>
									</div>';
								break 2;
							}
						}
					} // end while

				} // end system while

				echo $status;

			?>

		</div> <!-- end span -->
	</div> <!-- end line -->

	<!--div class="line break">
		<div class="span2 unit left" style="margin-bottom: 1em;">
			<h3>Get Help</h3>

			<p>We&#8217;re available to help even if you&#8217;re off campus. Stop in at <a href="http://gvsu.edu/library/directions">any location</a>, or contact us during <a href="http://gvsu.edu/library/hours">The Mary Idema Pew Library's regular hours</a>.</p>

			<style type="text/css">
.chat { display: inline-block; padding-left: 1.45em; }
.chat-online { background-image: url(http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/chat-online.png); background-repeat:no-repeat;background-position:middle left;  }
.chat-offline { background-image: url(http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/chat-offline.png); background-repeat:no-repeat;background-position:middle left; color: #575757 !important;}</style>
<div class="needs-js">
	&nbsp;</div>
<div class="libraryh3lp" jid="gvsulibs-queue@chat.libraryh3lp.com" style="display: none;">
	<a class="status-button" href="#" onclick="window.open('https://libraryh3lp.com/chat/gvsulibs-queue@chat.libraryh3lp.com?skin=16489',
   'chat', 'resizable=1,width=320,height=200'); return false;" style="margin-top:.5em; text-decoration: none; margin-right: 1em;"><span class="chat chat-online">Chat now </span> </a></div>
<div class="libraryh3lp" style="display: none;">
	<a class="status-button" href="http://gvsu.edu/chat" style="margin-top:.5em; text-decoration: none; margin-right: 1em;"><span class="chat chat-offline">Chat is Offline</span></a></div>

			<a class="status-button" style="text-decoration: none; margin-top: .5em" href="mailto:library@gvsu.edu">Email</a>

		</div>

		<div class="span2 unit right">
			<h3>Report a Problem</h3>
			<p>Having trouble with any of the University Library's online systems? Drop us a line and let us know. We&#8217;ll do our best to sort it out.</p>

			<a href="feedback.php" class="status-button" style="margin-top: .5em" id="feedback-trigger">Report a Problem</a>

		</div> <!-- end span -->
	<!--/div> <!-- end line -->
	
	<style>
	.status-table .heading { font-weight: bold; }
	.status-heading dl dt { text-align: left !important;}
	.status-table dt,
	.status-table dd { display: inline-block; width:48%; padding: .7em 0;}
	.status-table dl { border-bottom: 1px solid #ddd; width: 95%; }
	.status-table dd { text-align: right;}
	.issue-box { margin-bottom: 1.5em; padding-bottom: 1.5em; padding-top: 0; border-bottom: 2px solid #bbb;}
	.issue-box h4 { margin-bottom: .4em; margin-top: 0; font-weight: bold;}
	.issue-box .tagline { font-size: 90%; color: #575757;margin-top: 1em;clear:both;}
	.comment-list { margin-top: .7em;padding-top: .7em;  border-top: 1px solid #ddd; }
	.timestamp {display: inline-block; float: left; margin-right: .5em;line-height: 1.5em;}
	h4 span { font-size: 14px; display: inline-block; padding: .4em; }
	.tag-unresolved { background: #cb0000; color: white; }
	.tag-resolved { background: green; color: white; }
	</style>

	<div class="line break status-table">
		<div class="span2 unit left">
						
						<!-- load system names -->
						<?php

							$result = $db->query("SELECT * FROM systems ORDER BY system_category ASC, system_name ASC");
							$now = time();
							$i = 0;
							$system_count = $result->num_rows;
							
							// Calculate where to drop in the code for a second column
							$half = round($system_count/2);

							// loop through each system
							while($row = $result->fetch_assoc())
							{
								
								if($i == $half) {
									echo '</div><div class="span2 unit right lastUnit">';
								}

								echo '<dl class="system">';
								echo '<dt>' . $row["system_name"] . '</dt> ';
								echo '<dd class = "col2 name"';

								$system_result = $db->query ("SELECT i.start_time, i.end_time, i.status_type_id, s.status_type_text, i.issue_id
								FROM issue_entries i, status_type s
								WHERE i.system_id = {$row['system_id']} AND i.status_type_id = s.status_type_id AND i.start_time < '$now'");
	
								$currently = ' style="color: #147D11">Online'; // currently displayed. Color difference is WCAG2 AA compliant

								// Display Day
								while ($rw = $system_result->fetch_assoc()) {

									// Check if there is no resolution or a scheduled resolution is still in the future
									if (($rw['end_time'] == 0) || ($rw['end_time'] > $now) || ($rw['start_time'] > $now)) { 
                                                                                $day = date('Ymd',$now);

										//echo '<p>color</p>';

										if ($rw['status_type_id'] == 2) { 
											// Color difference is WCAG2 AA compliant
											// Changed GET requests to just grab issue ID, not day and system.
											$currently = '><a href="detail.php?id='. $row['issue_id'] .'" style = "text-decoration: none; color: #cb0000;">'.$rw['status_type_text'].'</a>';
										}
										else {
											$currently = '><a href="detail.php?id='. $row['issue_id'] .'" style = "text-decoration: none; color: #cb0000;">'.$rw['status_type_text'].'</a>';
										}
									}
								}

								echo $currently . '</dd>'; // close currently displayed

								echo '</dl>'; // close row
								$i++;
							}

						?>

			</div>
	</div> <!-- end line -->

	<!--
	<div class="line break">
		<div class="prevNext">
			<a href="#" class="prev ">&#8249; Earlier</a>
			<a href="#" class="next">Next &#8250;</a>
		</div>
	</div>
	-->
	
	<!-- Add blog-like view of incidents -->
	<div style="clear: both; margin-top: 2em;"></div>	
	<?php
	
		$issue_query = "SELECT issue_entries.issue_id, systems.system_name, issue_entries.end_time FROM issue_entries, systems WHERE issue_entries.system_id = systems.system_id ORDER BY issue_entries.issue_id DESC LIMIT 10";
		
			$issue_result = $db->query($issue_query);
			$n_rows = $issue_result->num_rows;

			if ($n_rows > 0) {

				while ($issue_entries = $issue_result->fetch_assoc()) {

					$result = $db->query("SELECT s.status_id, s.issue_id, s.status_timestamp, s.status_public, s.status_user_id, s.status_text, s.status_delete, u.user_id, u.user_fn, u.user_ln, st.status_type_id, st.status_type_text
						FROM status_entries s, user u, status_type st
						WHERE s.issue_id = '{$issue_entries['issue_id']}' AND s.status_user_id = u.user_id AND s.status_type_id = st.status_type_id
						ORDER BY s.status_timestamp ASC");

					$num_rows = $result->num_rows;
					$issue_id = $status_entries['issue_id'];

					$rc = 0;

					// display issues and check for comments
					while ($status_entries = $result->fetch_assoc()) {

						$rc++;

						if ($rc == 1) {
							
							if($issue_entries['end_time'] > 0) {
								$current_status = '<span class="tag-resolved">Resolved</span>';
							} else {
								$current_status = '<span class="tag-unresolved">Unresolved</span>';
							}

							echo '
							<!-- Issue -->
							<div class = "line issue-box span1">
								<h4>' . $status_entries['status_type_text'] . ' for ' . $issue_entries['system_name'] . ' ' . $current_status .'</h4>
								<div class="comment-text"><strong class="timestamp">[' . date("g:i a", $status_entries['status_timestamp']) . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>';
								
								$attribution = '<p class="tagline">This issue was reported at ' . date("g:i a n/j/y", $status_entries['status_timestamp']) . ' by ' . $status_entries['user_fn'] . '</p>';
						
						} else {
									
							// Comment wrapper
							echo '<div class="comment-list">
									<div class ="comment-text"><strong class="timestamp">[' . date("g:i a", $status_entries['status_timestamp']) . ' - ' .$status_entries['user_fn'] . ']</strong> ' . Markdown($status_entries['status_text']) . '</div>
								</div> <!-- end comment-list --> ';

}

	
}echo $attribution . ' </div><!-- End .line -->';
					} // close status loop
				}
			
	
	?>
	

	<div class="line break footer">
		<div class="span1 unit break">
			<p>Written by <a href="http://jonearley.net/">Jon Earley</a> for <a href="http://gvsu.edu/library">Grand Valley State University Libraries</a>. Code is <a href="https://github.com/gvsulib/library-Status">available on Github</a>.</p>
		</div> <!-- end span -->
	</div> <!-- end line -->
</div>
<script> 
  (function() {
    var x = document.createElement("script"); x.type = "text/javascript"; x.async = true;
    x.src = (document.location.protocol === "https:" ? "https://" : "http://") + "libraryh3lp.com/js/libraryh3lp.js?multi,poll";
    var y = document.getElementsByTagName("script")[0]; y.parentNode.insertBefore(x, y);
  })();
</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>

$(document).ready(function() {

	$("body").append('<div class="feedback lib-form"> <form method="post" action="http://www.gvsu.edu/library/customemail-post.htm?keyId=9D7CB431-E6EB-A2DB-48089384265083C9"> <label for="name">Your Name:</label> <input type="text" name="name" id="name" placeholder="Optional" /> <label for="feedback">Have an idea? See a problem?</label> <textarea name="feedback"></textarea> <div class="right"> <a href="index.php" style="display: inline-block; margin-right: 2em;">Cancel</a> <input class="lib-button" type="submit" value="Send Feedback" style="margin-top: 1em;" /> </div> </form> </div>');

	$(".feedback").hide();

	$("#feedback-trigger").click(function(e) {

		e.preventDefault();

		$(".feedback").slideToggle(400);

	});
});

</script>
</body>

</html>
