<table width="100%">
<tr>
	<th width="32">Owner</th>
    <th colspan="3">Name</th>
    <th>Quantity</th>
    <th>Volume</th>
	<th colspan="2">Location</th>
</td>
<?php foreach ($found as $row): ?>
<tr>
    <td><?php echo get_character_portrait($row['characterID'], 32); ?></td>
    <td><img width="32" height="32" src="<?php echo getIconUrl($row,32);?>"></td>
    <td colspan="2" style="text-align: left;"><?php echo $row['typeName']; ?></td>
    <td><?php echo number_format($row['quantity']); ?></td>
    <td><?php echo number_format($row['volume']*$row['quantity']);?> m&sup3;</td>
	
	<?php if (isset($row['containedIn'])): ?>
	<td>
		<?php echo locationIDToName($row['locationID']); ?>
	</td>
	<td>
		<img width="32" height="32" src="<?php echo getIconUrl(getInvType($row['containedIn']), 32); ?>">
	</td>
	<?php else: ?>
	<td colspan="2">
		<?php echo locationIDToName($row['locationID']); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>