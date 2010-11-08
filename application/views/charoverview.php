
<div id="content">

	<div class="post">
		<h2>Totals</h2>
		<p>
		<ul>
		<li>You currently have <b><?php echo number_format($global['totalisk']);?></b> ISK total on all Characters.
		<li>All you Characters Combined have <b><?php echo number_format($global['totalsp']);?></b> Skillpoints.
		</ul>
		</p>
	</div>

<?php foreach ($data as $i):?>
			<div class="post">
				<h2 class="title"><a href="#"><?php echo $i['name']; ?></a></h2>
				<p class="meta"><?php echo $i['name']; ?> From <span class="author"><?php echo $i['corporationName']; ?> <?php if (!empty($i['allianceName'])): echo '/ '.$i['allianceName']; endif;?></span></p>
				<div class="entry">
					<p>
					<img src="<?php echo site_url("files/cache/char/{$i['characterID']}/64/char.jpg"); ?>" width="64" height="64" align="left" hspace="5">
					<?php echo $i['sex'];?> 
					<?php if (!empty($i['trainingTypeID'])):?>
					is currently Training <b><?php echo $i['trainingTypeName']['typeName']; ?></b> to Level <b><?php echo $i['trainingToLevel']; ?></b>. 
					<?php echo $i['sex']; ?> started Training <?php echo $i['trainingStartTime'];?> and will finish <?php echo $i['trainingEndTime'];?>.
					<?php else: ?>
					is currently <b>not</b> Training a Skill.
					<?php endif; ?>
					
					</p>
					<p>
					At <b><?php echo number_format($i['extra_info']['skillPointsTotal']);?></b> Skillpoints <?php echo $i['sex']; ?> has a total of <b><?php echo $i['extra_info']['skillsTotal'];?></b> Skills Trained, <b><?php echo $i['extra_info']['skillsAtLevel'][5];?></b> of them at Level <b>5</b>.
					</p>
					<p>
					<?php echo $i['sex2']; ?> Wallet currently sits at <b><?php echo number_format($i['balance']); ?></b> ISK.
					</p>
				</div>
			</div>
<?php endforeach; ?>
</div>
