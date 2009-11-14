<table width="99%" id="location_snippet">

	<tr>
		<th colspan="2">
			<img align="right" src="<?php echo get_icon_url($loc, 64);?>">
			<h1><?php echo $loc['stationName']; ?></h1>
		</th>
	</tr>
	<tr>
		<td><b>Solar System:</b></td>
		<td>
			<?php echo $loc['solarSystemName'];?>
		</td>
	</tr>
	<tr>
		<td><b>Region:</b></td>
		<td>
			<?php echo $loc['regionName'];?>
		</td>
	</tr>
	
	<!--
	<tr>
		<td>
			<a target="_blank" href="http://evemaps.dotlan.net/system/">
				<img title="Open System with Dotlan" src="<?php echo site_url("files/images/map.png"); ?>">
			</a>
		</td>
		<td></td>
	</tr>
	-->

</table>