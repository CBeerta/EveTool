<table width="100%">
<caption>Industry Jobs<!-- for the last <?php echo $max_days; ?> days--></caption>
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
        <a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$row['installerID']); ?>">
            <?php echo get_character_portrait($row['installer'], 32); ?>
        </a>
    </td>
	<td style="text-align: left;">
        <a id="fb_item" href="<?php echo site_url('/fancybox/item/'.$row['typeID']); ?>">       
    	    <?php echo icon_url($row,32);?>
        </a>
    </td>
    <td style="text-align: left;"><?php echo $row['typeName']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['activity']; ?></td>
    <td><?php echo $row['amount']; ?></td>
    <td><?php echo $row['ends']; ?></td>
    <td>
		<a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$row['outputLocationID']); ?>"><?php echo locationid_to_name($row['outputLocationID']);?></a>
    </td>
<tr>
<?php endforeach; ?>
<tr>
    <td colspan="8" style="text-align: center;">
        <?php echo $this->pagination->create_links(); ?>
    </td>
</tr>
</table>
