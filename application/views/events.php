<!--
<?php print_r($events); ?>
-->
<div id="content">
<?php foreach ($events as $item):?>
<?php #if ($item['ownerID'] == 1) continue; # Skip CCP Crap?>
			<div class="post">
				<h2 class="title">
                    <?php echo get_character_portrait($item['character']->characterID, 64, 'left'); ?>
			        <a href="#"><?php echo $item['eventTitle']; ?></a>
                </h2>
				<p class="meta">From <span class="author"><a href="#"><?php echo $item['ownerName']?></a></span> <span class="date"> on <?php print api_time_print($item['eventDate']); ?></span> for <?php echo $item['duration'];?> Minutes</p>
				<div class="entry">
					<?php echo strip_tags(nl2br($item['eventText']), '<p><a><br>'); ?>
				</div>
			</div>
<?php endforeach; ?>
</div>
