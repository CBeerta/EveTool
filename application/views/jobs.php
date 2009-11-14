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
    <td>
        <a id="fb_character" style="color: black;" href="<?php echo site_url('/fancybox/character/'.$row['installerID']); ?>">       
            <?php echo get_character_portrait($row['installerID'], 32); ?>
        </a>
    </td>
	<td style="text-align: left;">
        <a id="fb_character" style="color: black;" href="<?php echo site_url('/fancybox/item/'.$row['typeID']); ?>">       
    	    <img src="<?php echo get_icon_url($row,32);?>" width="32" height="32">
        </a>
    </td>
    <td style="text-align: left;"><?php echo $row['typeName']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['activity']; ?></td>
    <td><?php echo $row['amount']; ?></td>
    <td><?php echo $row['ends']; ?></td>
    <td>
		<a id="fb_location" style="color: black;" href="<?php echo site_url('/fancybox/location/'.$row['location']); ?>"><?php echo locationid_to_name($row['location']);?></a>
    </td>
<tr>
<?php endforeach; ?>
</table>
