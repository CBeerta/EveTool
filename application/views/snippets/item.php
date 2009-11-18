<table width="99%" id="location_snippet">

	<tr>
		<th colspan="2">
			<img align="right" src="<?php echo get_icon_url($item, 64);?>">
			<h1><?php echo $item['typeName']; ?>
                <?php if (igb_trusted()): ?>
                <?php echo igb_show_info($item['typeID']); ?>
                <?php endif; ?>
			</h1>
		</th>
	</tr>
	<tr>
		<td><b>Description:</b></td>
		<td>
			<?php echo $item['description'];?>
		</td>
	</tr>
	<tr>
		<td><b>Group:</b></td>
		<td>
			<?php echo $item['categoryName'].', '.$item['groupName'];?>
		</td>
	</tr>
	<tr>
		<td><b>Volume:</b></td>
		<td>
			<?php echo number_format($item['volume'], 2); ?> m&sup3
		</td>
	</tr>
	<tr>
		<td><b>Mass:</b></td>
		<td>
			<?php echo number_format($item['mass'], 2); ?> m&sup3
		</td>
	</tr>

</table>
<!--
<pre>
	<?php print_r($item); ?>
</pre>
-->
