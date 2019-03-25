



<div class="feedback lib-form row">
<a name="problem"><h3>Report an Issue</h3></a>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		<input type="hidden" name="token" value="<? echo $token; ?>">
		

		

		<?
		//capture users IP address
		$ip = $_SERVER['REMOTE_ADDR'];

		$campusRegex = "/^148\.61\..*|^35\.40\..*|^35\.39\..*|^35\.41\..*|^35\.38\..*|^10\.10\..*|^155\.130\..*/";

		if (preg_match($campusRegex, $ip) === 1) {
			$campusString = "On Campus or using VPN";

		} else {
			$campusString = "Off Campus";
		}



		?>

		<input type="hidden" name="onOffCampus" value="<? echo $campusString; ?>">
		<input type="hidden" name="browser" value="<? echo $_SERVER['HTTP_USER_AGENT']; ?>">

		
		<div class="span2 unit left">
			<label for="name">Your Name:</label>
			<input type="text" name="name" id="name" placeholder="Optional" <? if (isset($_POST["name"])) {echo "value='" . $_POST["name"] . "'";} ?>/>
		</div>
		<div class="span1 unit left lastUnit">
			<label for="email">Your Email:</label>
			<input type="email" name="email" id="email" placeholder="email address" <? if (isset($_POST["email"])) {echo "value='" . $_POST["email"] . "'";} ?>/>


		
		<?php if (isset($_GET["url"])) {
			$url = $_GET["url"];
		} elseif (isset($_POST["url"])) {
			$url = $_POST["url"];
		} else {
			$url="";
		}
		echo '<input type="hidden" name="url" value="' . $url . '">';
		?>
		</div>

		<div id="subjectContainer">
		<label for="description">Subject</label>
		<input type="text" name="description" id="description" maxlength="80" placeholder="Describe your issue in a few words (required)" required <? if (isset($_POST["description"])) {echo "value='" . $_POST["description"] . "'";} ?>/>
		<ul id="results" aria-live="polite" role="listbox">
		</ul>
		</div>

		
		
		

		<label for="feedback">Full Description</label>
		<textarea name="feedback" required><? if (isset($_POST["feedback"])) {echo $_POST["feedback"];} ?></textarea>
		<label id="splabel" for="comments">Do not fill this field out</label>
								<textarea ID="spcomments" name="comments" placeholder="Do not fill out this field"></textarea>
		
					<!--captcha code container-->
					<div class="g-recaptcha" data-sitekey="<?php echo $recaptchaSiteKey; ?>"></div>

					<!--alternate captcha code for browsers without javascript-->
					<noscript>
						<div>
							<div style="width: 302px; height: 422px; position: relative;">
								<div style="width: 302px; height: 422px; position: absolute;">
									<iframe src="https://www.google.com/recaptcha/api/fallback?k=your_site_key"
													frameborder="0" scrolling="no"
													style="width: 302px; height:422px; border-style: none;">
									</iframe>
								</div>
							</div>
							<div style="width: 300px; height: 60px; border-style: none;
														bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
														background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
								<textarea id="g-recaptcha-response" name="g-recaptcha-response"
														class="g-recaptcha-response"
														style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
																		margin: 10px 25px; padding: 0px; resize: none;" >
								</textarea>
								
							</div>
						</div>
					</noscript>
		     
					
			
				<input class="btn btn-primary" type="submit" value="Report a Problem" name="email-asana" style="margin-top: 1em;" />
				<script>
   // Search LA API when question is asked  
   // set variables
   var chatOnline, submitUrl;

   $("input#description").blur(function() {
			$("results").hide();
			console.log('Hide the autocomplete');
		});  

	$(function () {
	    var minlength = 3;
	    $("#description").keyup(function () {
	        var that = this,
	        value = $(this).val();
	        if (value.length >= minlength ) {
	            $.ajax({
	                type: "GET",
	                url: "https://api2.libanswers.com/1.0/search/" + value,
	                data: {
	                    'iid' : 1050,
	                    'callback' : 'localJsonpCallback',
						'limit' : 5
	                },
	                dataType: "jsonp"
	                
	            });
	        }
	    });
	}); 
	
	function localJsonpCallback(json) {
		q = json.data.search.results;
		if(q.length > 0) {
			$('ul#results').html('<li><h4 tabindex="0">Are any of these links helpful?</h4></li>');
			$.each(q, function () {
			$('ul#results').append('<li id="answer-' + this.id + '"><a href="' + this.url + '" role="option">' + this.question + '</a></li>');
			$('ul#results').show();
			// Show me all the responses
       		console.log(this.question);
       		console.log(this.url);
       		console.log(this.topics[0]);
    	});

		var blank = $("#description");
		var position = blank.position();

		}
		
		
	}  

		          
   
</script>
		</form>
	</div>