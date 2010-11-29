<div id="content">

    <table width="100%">
    <caption>
        <div>Past Orders</div>
    </caption>
    <tr>
        <th width="32">By</th>
	    <th colspan="2">Item</th>
	    <th>Type</th>
        <th>Price per Unit</th>
        <th>Amount</th>
        <th>Total</th>
        <th>Station</th>
    </tr>
    <?php foreach ($past_orders as $row): ?>
    <tr>
        <td>
            <a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$row['charID']); ?>">       
                <?php echo get_character_portrait($row['owner'], 32, 'entry'); ?>
            </a>
        </td>
	    <td style="text-align: left;">
            <a id="fb_item" href="<?php echo site_url('/fancybox/item/'.$row['typeID']); ?>">       
        	    <?php echo icon_url($row,32);?>
	        </a>
        </td>
        <td style="text-align: left;"><?php echo $row['typeName'];?></td>
        <td><?php echo ucfirst($row['type']); ?></td>
	    <td><?php echo number_format($row['price'], 2);?> ISK</td>
        <td width="5"><?php echo number_format($row['total']); ?></td>
	    <td><?php echo number_format($row['total']*$row['price']);?> ISK</td>
	    <td>
            <a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$row['stationID']); ?>"><?php echo locationid_to_name($row['stationID']);?></a>
        </td>
    </tr>
    <?php endforeach;?>
    <tr>
        <td colspan="7" style="text-align: center;">
            <?php echo $this->pagination->create_links(); ?>
        </td>
    </tr>
    </table>

</div>
