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
    <td>
        <a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$row['characterID']); ?>">
            <?php echo get_character_portrait($row['characterID'], 32); ?>
        </a>
    </td>
    <td>
        <a id="fb_item" href="<?php echo site_url('/fancybox/item/'.$row['typeID']); ?>">
            <img width="32" height="32" src="<?php echo get_icon_url($row,32);?>">
        </a>
    </td>
    <td colspan="2" style="text-align: left;"><?php echo $row['typeName']; ?></td>
    <td><?php echo number_format($row['quantity']); ?></td>
    <td><?php echo number_format($row['volume']*$row['quantity']);?> m&sup3;</td>
	
	<?php if (isset($row['containedIn'])): ?>
	<td>
        <a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$row['locationID']); ?>">
    		<?php echo locationid_to_name($row['locationID']); ?>
        </a>
	</td>
	<td>
        <a id="fb_item" href="<?php echo site_url('/fancybox/item/'.$row['containedIn']); ?>">
    		<img width="32" height="32" src="<?php echo get_icon_url(get_inv_type($row['containedIn']), 32); ?>">
		</a>
	</td>
	<?php else: ?>
	<td colspan="2">
        <a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$row['locationID']); ?>">
    		<?php echo locationid_to_name($row['locationID']); ?>
        </a>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
