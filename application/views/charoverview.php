<div id="content">
<?php foreach ($data as $i):?>
			<div class="post">
				<h2 class="title"><a href="#"><?php echo $i['name']; ?></a></h2>
				<p class="meta"><?php echo $i['name']; ?> From <span class="author"><?php echo $i['corporationName']; ?> <?php if (!empty($i['allianceName'])): echo '/ '.$i['allianceName']; endif;?></span></p>
				<div class="entry">
					<p>
					<img src="<?php echo site_url("files/cache/char/{$i['characterID']}/64/char.jpg"); ?>" width="64" height="64" align="left" hspace="5">
					<?php echo $i['sex'];?> 
					<?php if (!empty($i['trainingTypeID'])):?>
					is currently Training <b><?php echo $i['trainingTypeID']; ?></b> to Level <b><?php echo $i['trainingToLevel']; ?></b>. 
					<?php echo $i['sex']; ?> started Training <?php echo $i['trainingStartTime'];?> and will finish <?php echo $i['trainingEndTime'];?> and
					<?php endif; ?>
					currently has <b><?php echo number_format($i['balance']); ?></b> ISK.
					</p>
				</div>
			</div>
<?php endforeach; ?>
</div>