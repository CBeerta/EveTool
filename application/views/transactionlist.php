<div id="content">
<table width="100%">
    <caption>Transactions</caption>
    <tr>
    	<th>By</th>
        <th>Date</th>
        <th>Units</th>
        <th colspan="2">Name</th>
        <th>Unit</th>
        <th>Total</th>
        <th>Station</th>
    </tr>
    <?php foreach($translist as $trans):?>
    <tr>
	    <td>
	        <a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$trans['char']->characterID); ?>">       
	            <img src="<?php echo site_url("files/cache/char/{$trans['char']->characterID}/64/char.jpg"); ?>" width="32" height="32" title="<?php echo $trans['char']->name; ?>">
	        </a>
	    </td>
        <td><?php print api_time_print($trans['transactionDateTime'], 'Y.m.d H:i'); ?></td>
        <td><?php print number_format($trans['quantity']); ?></td>
        <td>
            <a id="fb_item" style="color: black;" href="<?php echo site_url('/fancybox/item/'.$trans['typeID']); ?>">
                <?php echo icon_url(get_inv_type($trans['typeID']),32);?>
            </a>
        </td>
        <td>
            <?php print $trans['typeName']; ?>
        </td>
        <?php if ($trans['transactionType'] == 'buy'): ?>
        <td><font class="expense"><?php print number_format($trans['price'],2); ?></font></td>
        <td><font class="expense"><?php print number_format($trans['price']*$trans['quantity']); ?></font></td>
        <?php else: ?>
        <td><font class="income"><?php print number_format($trans['price'],2); ?></font></td>
        <td><font class="income"><?php print number_format($trans['price']*$trans['quantity']); ?></font></td>
        <?php endif; ?>
        <td>
    		<a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$trans['stationID']); ?>"><?php echo $trans['stationName'];?></a>
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