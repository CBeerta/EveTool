<table width="100%">
<caption>Market Orders</caption>
<tr>
	<th colspan="2">Type</th>
    <th>Price</th>
    <th colspan="2">Remaining</th>
    <th>Total</th>
    <th>Ends</th>
    <th>Station</th>
</tr>
<?php foreach ($data as $row): ?>
<tr>
	<td style="text-align: left;"><img src="<?php echo getIconUrl($row['typeID'],32);?>"></td>
    <td style="text-align: left;"><?php echo $row['typeName'];?></td>
	<td><?php echo number_format($row['price'], 2);?> ISK</td>
    <td width="5"><?php echo $row['remaining'].'/'.$row['total']; ?></td>
	<td><?php echo number_format($row['remaining']*$row['price']);?> ISK</td>
	<td><?php echo number_format($row['total']*$row['price']);?> ISK</td>
	<td><?php echo $row['ends'];?></td>
	<td><?php echo $row['location'];?></td>
</tr>
<?php endforeach;?>
<tr>
    <th colspan="3">Sum:</td>
    <td><?php echo $remaining.'/'.$total; ?></td>
    <td><?php echo number_format($remainingPrice); ?> ISK</td>
    <td><?php echo number_format($totalPrice); ?> ISK</td>
    <td colspan="2">&nbsp;</td>
</tr>
</table>
