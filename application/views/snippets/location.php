<table width="99%" id="location_snippet">

		<th colspan="2">
			<img align="right" src="<?php echo get_icon_url($loc, 64);?>">
			<h1><?php echo $loc['stationName']; ?></h1>
		</th>
	</tr>
	<tr>
		<td><b>Solar System:</b></td>
		<td>
			<a target="_blank" href="<?php echo dotlan_url("{$loc['regionName']}/{$loc['solarSystemName']}");?>">
				<img title="Dotlan" src="<?php echo site_url("files/images/map.png"); ?>">
			</a>
			<?php echo $loc['solarSystemName'];?>
		</td>
	</tr>
	<tr>
		<td><b>Region:</b></td>
		<td>
			<a target="_blank" href="<?php echo dotlan_url($loc['regionName']);?>">
				<img title="Dotlan" src="<?php echo site_url("files/images/map.png"); ?>">
			</a>
			<?php echo $loc['regionName'];?>
		</td>
	</tr>
</table>