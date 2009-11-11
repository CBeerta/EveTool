<div id="buttan">
Days to show: 
<?php echo anchor("industry/jobs/7", "7");?>, 
<?php echo anchor("industry/jobs/14", "14");?>, 
<?php echo anchor("industry/jobs/30", "30");?>,
<?php echo anchor("industry/jobs/999999", "all");?>
</div>
<table width="100%">
<caption>Jobs for the last <?php echo $maxDays; ?> days</caption>
<tr>
	<th width="32">By</th>
    <th colspan="2">Item</th>
    <th>Status</th>
    <th>Activity</th>
    <th>Amount</th>
    <th>End</th>
    <th>Station</th>
</tr>
<?php foreach ($data as $row): ?>
<tr>
    <?php if (isset($corpmates[$row['installerID']])): ?>
    <td><?php echo get_character_portrait($row['installerID'], 32); ?></td>
    <?php else: ?>
    <td><?php echo get_character_portrait($row['installerID'], 32); ?></td>
    <?php endif; ?>
	<td style="text-align: left;"><img src="<?php echo get_icon_url($row,32);?>" width="32" height="32"></td>
    <td style="text-align: left;"><?php echo $row['typeName']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['activity']; ?></td>
    <td><?php echo $row['amount']; ?></td>
    <td><?php echo $row['ends']; ?></td>
    <td><?php echo $row['location']; ?></td>
<tr>
<?php endforeach; ?>
</table>
