<?php
session_start();

date_default_timezone_set('America/Detroit');
// Include additional libraries that make this work
require 'resources/secret/config.php';
require 'resources/php/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?php echo $library_name; ?> Status</title>

	
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" type="text/css" href="resources/css/styles.css" />

	<script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>

<div id="gvsu-cf_header" class="responsive">
		<div id="gvsu-cf_header-inner">
			<div id="gvsu-cf_header-logo">
				<a href="http://gvsu.edu/">
					<img src="//gvsu.edu/includes/topbanner/3/gvsu_logo.png" alt="Grand Valley State University">
				</a>
			</div><!-- End #gvsu-cf_header-logo -->
		</div><!-- End #gvsu-cf_header-inner -->
	</div><!-- End #gvsu-cf_header -->

		<div id="cms-header-wrapper">
		<div id="cms-header">
			<div id="cms-header-inner">
				<a id="cms-navigation-toggle" href="cms-siteindex-index.htm" onclick="return cmsToggleMenu(document.getElementById('cms-navigation'))">
					<img src=" //gvsu.edu/cms4/skeleton/0/files/img/transparent.png" alt="Menu">
				</a>
				<h1>
					<a href="http://gvsu.edu/library">University Libraries</a>
				</h1>
				<div id="library-search">
					<form action="//gvsu.summon.serialssolutions.com/search">
						<input type="hidden" name="spellcheck" value="true">
						<p>
							<input type="text" name="s.q" placeholder="Find articles, books, &amp; more" size="35">
							<input type="submit" value="Find It!">
						</p>
					</form>
				</div><!-- End #library-search -->
					
			<div class="cms-navigation" id="cms-navigation">
				<ul>
					<li><a href="http://gvsu.edu/library/find">Find Materials</a></li>
					<li><a href="http://gvsu.edu/library/allservices">Services</a></li>
					<li><a href="http://gvsu.edu/library/about">About Us</a></li>
					<li><a href="http://help.library.gvsu.edu">Help</a></li>
				</ul>
			</div><!-- End #cms-navigation -->

			<div class="cms-clear"></div>
		
			</div> <!-- End #cms-header-inner -->
		</div><!-- End #cms-header -->
	</div><!-- End #cms-header-wrapper -->


	<div id="cms-body-wrapper">
		<div id="cms-body">
			<div id="cms-body-inner">
				<div id="cms-body-table">
					<div id="cms-content" class="feedback">

	<div class="row break">
		<div class="span2 unit left">
			<h2><a href="index.php"><?php echo $library_name; ?> Status</a></h2>
		</div> <!-- end span -->
	</div> <!-- end line -->
<div clas="cms-clear"></div>
	<div class="lib-form" style="margin-top: 1em; max-width: 90%; margin: 0 auto;">
		<form method="post" action="index.php">
		<div class="row">
			<div class="span2 unit left">
				<label for="name">Your Name:</label>
				<input type="text" name="name" id="name" placeholder="Optional" />
			</div>
			
			<div class="span1 unit left">
				<label for="name">Your Email:</label>
				<input type="text" name="email" id="email" placeholder="Optional" />
			</div>

		</div>
		<div class="row">
		<div class="span3">
			<label for="feedback">Have an idea? See a problem?</label>
			<textarea name="feedback"></textarea>
			<input type="text" name="bot_check" style="display:none;" />
			<div class="g-recaptcha" data-sitekey="<?php echo $recaptchaSiteKey; ?>" style="padding: 10px; display:inline-block"></div>
			<noscript>
				<div style="width: 302px; height: 352px;">
					<div style="width: 302px; height: 352px; position: relative;">
						<div style="width: 302px; height: 352px; position: absolute;">
							<iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo $recaptchaSiteKey; ?>"
							frameborder="0" scrolling="no"
							style="width: 302px; height:352px; border-style: none;">
							</iframe>
						</div>
						<div style="width: 250px; height: 80px; position: absolute; border-style: none;
						bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
						<textarea id="g-recaptcha-response" name="g-recaptcha-response"
						class="g-recaptcha-response"
						style="width: 250px; height: 80px; border: 1px solid #c1c1c1;
						margin: 0px; padding: 0px; resize: none;" value="">
						</textarea>
						</div>
					</div>
				</div>
			</noscript>
			<div class="right">
			
				<a href="index.php" style="display: inline-block; margin-right: 2em;position:relative;top:.35em">Cancel</a>
				<input class="lib-button" type="submit" name="problem-report" value="Send Feedback" style="margin-top: 1em;" />
			</div>
			</div>
			</div>

		</form>
	</div>

	</div><!-- End #cms-content -->
				</div><!-- End #cms-body-table -->
			</div><!-- End #cms-body-inner -->
		</div><!-- end #cms-body -->
	</div><!-- end #cms-body-wrapper -->

	<div id="cms-footer-wrapper">
		<div id="cms-footer">
			<div id="cms-footer-inner">
				<ul>
					<li><h4>Contact</h4>
						<p class="vcard">
							<span class="tel"> 
								<span class="type">Phone</span>:
								<span class="value">(616) 331-3500</span>
							</span>
							<br />
							<a href="mailto:library@gvsu.edu" class="email" target="_blank">library@gvsu.edu</a>
							<br />
						</p>
					</li>
					<li><h4>Social Media</h4>
						<p>
							<a href="http://twitter.com/gvsulib" title="http://twitter.com/gvsulib" class="socialmedia-icon socialmedia-icon-twitter">
								<span class="cms-screen-reader">http://twitter.com/gvsulib</span>
							</a>
							<a href="http://youtube.com/user/gvsulib" title="http://youtube.com/user/gvsulib" class="socialmedia-icon socialmedia-icon-youtube">
								<span class="cms-screen-reader">http://youtube.com/user/gvsulib</span>
							</a>
						</p>
					</li>
					<li id="library-fdlp">
								<p>
									<a href="http://gvsu.edu/library/govdoc" target="_blank">
										<img src="//gvsu.edu/cms4/asset/0862059E-9024-5893-1B5AAAC2F83BDDD8/fdlp-new.png" alt="Federal Depository Library Program Logo">
									</a>
									<br>
									Federal Depository<br>
									Library Program
								</p>
							</li>
				</ul>
			</div><!-- End #cms-footer-inner -->
		</div><!-- End #cms-footer -->
	</div><!-- End #cms-footer-wrapper -->

	<div id="cms-copyright-wrapper">
		<div id="cms-copyright">
			<div id="cms-copyright-inner">
				<ul>
					<li><a href="http://gvsu.edu/affirmativeactionstatement.htm">GVSU is an EO/AA Institutio</a></li>
					<li><a href="http://gvsu.edu/privacystatement.htm">Privacy Policy</a></li>
					<li><a href="http://gvsu.edu/disclosures">Disclosures</a></li>
					<li>Copyright © 1995-2015 GVSU</li>
				</ul>
			</div><!-- End #cms-copyright-inner -->
		</div><!-- End #cms-copyright -->
	</div><!-- End #cms-copyright-wrapper -->


</body>

</html>