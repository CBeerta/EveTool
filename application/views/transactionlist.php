<table width="100%">
    <caption>Transactions</caption>
    <tr>
        <th>Date</th>
        <th>Units</th>
        <th colspan="2">Name</th>
        <th>Unit</th>
        <th>Total</th>
        <th>Station</th>
    </tr>
    <?php foreach($translist as $trans):?>
    <tr>
        <td><?php print api_time_print($trans['transactionDateTime'], 'Y.m.d H:i'); ?></td>
        <td><?php print number_format($trans['quantity']); ?></td>
        <td>
            <a id="fb_item" style="color: black;" href="<?php echo site_url('/fancybox/item/'.$trans['typeID']); ?>">
                <img src="<?php echo get_icon_url(get_inv_type($trans['typeID']),32);?>" width="32" height="32">
            </a>
        </td>
        <td>
            <?php print $trans['typeName']; ?>
        </td>
        <?php if ($trans['transactionType'] == 'buy'): ?>
        <td><font id="expense"><?php print number_format($trans['price'],2); ?></font></td>
        <td><font id="expense"><?php print number_format($trans['price']*$trans['quantity']); ?></font></td>
        <?php else: ?>
        <td><font id="income"><?php print number_format($trans['price'],2); ?></font></td>
        <td><font id="income"><?php print number_format($trans['price']*$trans['quantity']); ?></font></td>
        <?php endif; ?>
        <td>
    		<a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$trans['stationID']); ?>"><?php echo $trans['stationName'];?></a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
