<div class="feedback lib-form row">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		<div class="span2 unit left">
			<label for="name">Your Name:</label>
			<input type="text" name="name" id="name" placeholder="Optional" />
		</div>
		<div class="span1 unit left lastUnit">
			<label for="email">Your Email:</label>
			<input type="email" name="email" id="email" required placeholder="Required" />
		<input type="hidden" name="url" value="<?php echo urldecode($_GET["url"]); ?>">
		</div>
		<label for="feedback">Have an idea? See a problem?</label>
		<textarea name="feedback" required></textarea>
		
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
			
		</form>
	</div>