<!--
<pre>
<?php print_r($mails); ?>
</pre>
-->
<div id="content">
<?php foreach ($mails as $item):?>
<?php if ($item['senderID'] == $item['forID']) continue; # Skip mails that we sent?>
			<div class="post">
				<h2 class="title"><?php echo $item['title']; ?></h2>
				<p class="meta">To <span class="author"><a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$item['forID']); ?>"><?php echo $item['for']?></a><?php echo !empty($item['toList']) ? " ({$item['toList']})" : '';?></span> <span class="date">
				from <a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$item['senderID']); ?>"><?php echo get_character_info($item['senderID'])->characterName; ?></a>
			 	on <?php print api_time_print($item['sentDate']); ?></span>&nbsp;
				</p>
				<div class="entry">
					<?php echo strip_tags($item['body'], '<p><a><br>'); ?>
				</div>
			</div>
<?php endforeach; ?>
</div>
