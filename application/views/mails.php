<!--
<pre>
<?php #print_r($mails); ?>
</pre>
-->
<div id="content">
<?php foreach ($mails as $item):?>
<?php if ($item['senderID'] == $item['forID']) continue; # Skip mails that we sent?>
			<div class="post">
				<h2 class="title">
                    <?php echo get_character_portrait($item['forID'], 64, 'left'); ?>
			        <a href="#"><?php echo $item['title']; ?></a>
                </h2>
				<p class="meta">To <span class="author"><a href="#"><?php echo $item['for']?></a><?php echo !empty($item['toList']) ? " ({$item['toList']})" : '';?></span> <span class="date"><?php print api_time_print($item['sentDate']); ?></span>&nbsp;</p>
				<div class="entry">
					<?php echo strip_tags($item['body'], '<p><a><br>'); ?>
				</div>
			</div>
<?php endforeach; ?>
</div>
