<table width="100%">
    <caption>
        <a id="fb_image" href="<?php echo site_url("wallet/chart/{$character}.jpg");?>">
            <img title="Chart" src="<?php echo site_url("/files/images/chart_curve_go.png");?>" style="background-color: white; padding: 1px; margin-right:10px;">
        </a>
        Wallet Journal
    </caption>
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Balance</th>
        <th>Source/Target</th>
        <th>Location</th>
    </tr>
    <?php foreach($wallet as $trans):?>
    <tr>
        <td><?php print api_time_print($trans['date'], 'Y.m.d H:i'); ?></td>
        <td><?php print $reftypes[$trans['refTypeID']]; ?></td>
        <?php if ($trans['amount'] > 0): ?>
        <td><font class="income"><?php print number_format($trans['amount']); ?></font></td>
        <td><?php print number_format($trans['balance']); ?></td>
        <td><?php print $trans['ownerName1']; ?></td>
        <?php else:?>
        <td><font class="expense"><?php print number_format($trans['amount']); ?></font></td>
        <td><?php print number_format($trans['balance']); ?></td>
        <td><?php print $trans['ownerName2']; ?></td>
        <?php endif;?>
        <?php if ($trans['argID1'] != 0): ?>
        <td>
	        <a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$trans['argID1']); ?>"><?php echo $trans['argName1'];?></a>
        </td>
        <?php else: ?>
        <td></td>
        <?php endif; ?>
        
    </tr>
    <?php endforeach;?>
    <tr>
        <td colspan="6" style="text-align: center;">
            <?php echo $this->pagination->create_links(); ?>
        </td>
    </tr>
</table>
