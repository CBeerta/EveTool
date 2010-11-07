<?php foreach ($items as $item):?>
<div id="content">
			<div class="post">
				<h2 class="title"><a href="#"><?php echo $item['title']; ?></a></h2>
				<p class="meta"><span class="author"><a href="#"><?php echo $item['from']?></a></span> <span class="date">July 07, 2010</span>&nbsp;</p>
				<div class="entry">
					<?php echo $item['body']; ?>
				</div>
			</div>
</div>
<?php endforeach; ?>