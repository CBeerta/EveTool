<div id="content">
<?php foreach ($mails as $item):?>
			<div class="post">
				<h2 class="title"><a href="#"><?php echo $item['title']; ?></a></h2>
				<p class="meta">To <span class="author"><a href="#"><?php echo $item['for']?></a></span> <span class="date">July 07, 2010</span>&nbsp;</p>
				<div class="entry">
					<?php echo strip_tags($item['body'], '<p><a><br>'); ?>
				</div>
			</div>
<?php endforeach; ?>
</div>
