<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="<?php print site_url();?>blog/wp-content/themes/hemingway/style.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php print site_url();?>blog/wp-content/themes/hemingway/styles/white.css" type="text/css" media="screen" />
    <title>Whitelist</title>
</head>
<body>
<div id="header">
    <div class="inside">
    <h2>Hello <?php print $address;?></h2>
    <p class="description">Request your Email to be Whitelisted</p>
    </div>
</div>
<div id="primary" class="twocol-stories">
		<div class="inside">
    		<div class="story first">
    		I Recieved a Message from you on <?php print $added;?>, and you where not yet on my Whitelist. The message you've sent was ignored, and you
    		here send to this page to be added to my Whitelist.<br /><br />
    		Please fill out the Captcha, and click on Confirm. After that you've been whitelisted, and may resend your message.
    		</div>
    		<div class="story">
    		<?php echo $image?>
    		<?php echo form_open('whitelist/add'); ?>
    		<?php echo form_input(array('name' => 'captcha','maxlength' => 8, 'size' => 8));?>
    		<?php echo form_submit('addme', 'Submit!');?>
    		<?php echo form_hidden('id', $id);?>
    		<?php echo form_close();?>
		    </div>
		</div>
		<div class="clear"></div>
 </div>		
</body>
</html>
