<table width="100%">
    <caption>Wallet Journal</caption>
    <tr>
        <td colspan="5" style="text-align:center;">
			<img src="<?php echo site_url("wallet/chart");?>">
		</td>
    </tr>
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Balance</th>
        <th>Source/Target</th>
    </tr>
    <?php foreach($wallet as $trans):?>
    <tr>
        <td><?php print api_time_print($trans['date'], 'D, j F H:i'); ?></td>
        <td><?php print $reftypes[$trans['refTypeID']]; ?></td>
        <?php if ($trans['amount'] > 0): ?>
        <td><font id="income"><?php print number_format($trans['amount']); ?></font></td>
        <td><?php print number_format($trans['balance']); ?></td>
        <td><?php print $trans['ownerName1']; ?></td>
        <?php else:?>
        <td><font id="expense"><?php print number_format($trans['amount']); ?></font></td>
        <td><?php print number_format($trans['balance']); ?></td>
        <td><?php print $trans['ownerName2']; ?></td>
        <?php endif;?>
    </tr>
    <?php endforeach;?>
</table>
