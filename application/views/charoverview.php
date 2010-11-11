
<div id="content">

	<div class="post">
		<h2>Totals</h2>
		<p>
		<ul>
		<li>You currently have <b><?php echo number_format($global['totalisk']);?></b> ISK total on all Characters.
		<li>All your Characters Combined have <b><?php echo number_format($global['totalsp']);?></b> Skillpoints.
		</ul>
		</p>
	</div>

<?php foreach ($data as $i):?>
			<div class="post">
				<h2 class="title"><a href="<?php echo site_url('skillsheet/'.$i['name']);?>"><?php echo $i['name']; ?></a></h2>
				<div class="entry">
					<p>
					<!-- there is WAY to much php code right here. Not pretty -->
					<img src="<?php echo site_url("files/cache/char/{$i['characterID']}/64/char.jpg"); ?>" width="64" height="64" align="left" hspace="5">
					<?php echo $i['name']; ?> (<i><?php echo $i['corporationName']; ?><?php if (!empty($i['allianceName'])): echo ' / '.$i['allianceName']; endif;?></i>)
					<?php if (!empty($i['trainingTypeID'])):?>
					is currently Training <b><?php echo $i['trainingTypeName']['typeName']; ?></b> to Level <b><?php echo $i['trainingToLevel']; ?></b>. 
					<?php echo $i['sex']; ?> started Training <?php echo api_time_print($i['trainingStartTime']);?> and will finish <?php echo api_time_print($i['trainingEndTime']);?> (<b><?php echo api_time_to_complete($i['trainingEndTime']);?></b>).
					<?php else: ?>
					is currently <b>not</b> Training a Skill.
					<?php endif; ?>
					At <b><?php echo number_format($i['extra_info']['skillPointsTotal']);?></b> Skillpoints <?php echo $i['sex']; ?> has a total of <b><?php echo $i['extra_info']['skillsTotal'];?></b> Skills Trained, <b><?php echo $i['extra_info']['skillsAtLevel'][5];?></b> of them at Level <b>5</b>.
					<?php echo $i['sex2']; ?> Wallet currently sits at <b><?php echo number_format($i['balance']); ?></b> ISK.
					</p>
				</div>
			</div>
<?php endforeach; ?>
</div>
