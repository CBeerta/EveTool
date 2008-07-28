<table width="100%">
<caption>Jobs for the last 7 days</caption>
<tr>
	<th colspan="2">Item</th>
    <th>Status</th>
    <th>Activity</th>
    <th>Amount</th>
    <th>End</th>
    <th>Station</th>
</tr>
<?php foreach ($data as $row): ?>
<tr>
	<td style="text-align: left;"><img src="<?php echo getIconUrl($row['typeID'],32);?>"></td>
    <td style="text-align: left;"><?php echo $row['typeName']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['activity']; ?></td>
    <td><?php echo $row['amount']; ?></td>
    <td><?php echo $row['ends']; ?></td>
    <td><?php echo $row['location']; ?></td>
<tr>
<?php endforeach; ?>
</table>
