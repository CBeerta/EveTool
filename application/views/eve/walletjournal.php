<table width="100%">
    <caption>Wallet Journal</caption>
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Balance</th>
        <th>Source/Target</th>
    </tr>
    <?php foreach($wallet as $trans):?>
    <tr>
        <td><?php print apiTimePrettyPrint($trans['date'], 'D, j F H:i'); ?></td>
        <td><?php print $reftypes[$trans['refTypeID']]; ?></td>
        <?php if ($trans['amount'] > 0): ?>
        <td><font color="green"><?php print number_format($trans['amount']); ?></font></td>
        <td><?php print number_format($trans['balance']); ?></td>
        <td><?php print $trans['ownerName1']; ?></td>
        <?php else:?>
        <td><font color="red"><?php print number_format($trans['amount']); ?></font></td>
        <td><?php print number_format($trans['balance']); ?></td>
        <td><?php print $trans['ownerName2']; ?></td>
        <?php endif;?>
    </tr>
    <?php endforeach;?>
</table>
