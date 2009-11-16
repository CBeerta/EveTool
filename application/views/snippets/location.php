<?php //print_r($loc); ?>
<table width="99%" id="location_snippet">

		<th colspan="2">
			<img align="right" src="<?php echo get_icon_url($loc, 64);?>">
			<h1>
			    <?php echo $loc['stationName']; ?>
			    <?php if (igb_trusted()): ?>
			    <?php echo igb_show_info($loc['stationTypeID']); ?>
			    <?php endif; ?>
		    </h1>
		</th>
	</tr>
	<tr>
		<td><b>Solar System:</b></td>
		<td>
			<a target="_blank" href="<?php echo dotlan_url("{$loc['regionName']}/{$loc['solarSystemName']}");?>">
				<img title="Dotlan" src="<?php echo site_url("files/images/map.png"); ?>">
			</a>
			<?php echo $loc['solarSystemName'];?>
			<?php if (igb_trusted()): ?>
			<?php echo igb_show_info(5, $loc['solarSystemID']); ?>
			&#8226;	<?php echo igb_set_destination($loc['solarSystemID']); ?>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td><b>Region:</b></td>
		<td>
			<a target="_blank" href="<?php echo dotlan_url($loc['regionName']);?>">
				<img title="Dotlan" src="<?php echo site_url("files/images/map.png"); ?>">
			</a>
			<?php echo $loc['regionName'];?>
			<?php if (igb_trusted()): ?>
		    <?php echo igb_show_info(3, $loc['regionID']); ?>
			<?php endif; ?>
		</td>
	</tr>
</table>
