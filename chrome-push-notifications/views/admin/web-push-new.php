<?php
if($_POST) {
	if(isset($_POST['title']) && !empty($_POST['title']) && isset($_POST['message']) && !empty($_POST['message']) && isset($_POST['wp_chrome_new_notification']) && wp_verify_nonce($_POST['wp_chrome_new_notification'], 'wp_chrome_new_notification' )) {

		$data = array(
			'title' => sanitize_text_field($_POST['title']),
			'message' => sanitize_text_field($_POST['message']),
			'url' => sanitize_text_field($_POST['url']),
			);

		$result = WPChromePush::init()->sendGCM($data, 'message');
		$answer = json_decode($result);

	    if($answer) {
			$suc    = $answer->{'success'};
			$fail   = $answer->{'failure'};
			
	    	//if debug
		    if(get_option('web_push_debuger')) {
				$gcm_output= "<div id='message' class='updated'><p><b>Push Notification Sent</b><i>&nbsp;&nbsp;</i></p><p>$result</p></div>";
			} else {
		    	$gcm_output= "<div id='message' class='updated'><p><b>Push Notification Sent</b><i>&nbsp;&nbsp;</i></p><p>".__('Success:','px_gcm')." $suc  &nbsp;&nbsp;".__('Failed:','px_gcm')." $fail </p></div>";
		    }
	    	
	    } else {
	    	$error_message = "<div id='message' class='error'><p><b>Error: </b>$result</p></div>";
	    }
	} else {
		$error_message = "<div id='message' class='error'><p><b>Error: </b>Please complete all the fields.</p></div>";
	}
}
?>

<div class="wrap">
<h2>New Push Notification</h2>
<p><?php echo isset($gcm_output) ? $gcm_output : '';?></p>
<p><?php echo isset($error_message) ? $error_message : '';?></p>
<h3>New Message</h3>

<form method="post" action="">
	<?php wp_nonce_field('wp_chrome_new_notification', 'wp_chrome_new_notification'); ?>
	<p><input id="url" name="url" type="text" placeholder="Url"/></p>
	<p><input id="title" name="title" type="text" placeholder="Title"/></p>
	<textarea id="message" name="message" type="text" cols="20" rows="5" placeholder="Message"></textarea>
	<?php submit_button('Send'); ?>
	</form>
</div> 