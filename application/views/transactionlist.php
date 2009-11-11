<table width="100%">
    <caption>Transactions</caption>
    <tr>
        <th>Date</th>
        <th>Units</th>
        <th>Name</th>
        <th>Unit</th>
        <th>Total</th>
        <th>Station</th>
    </tr>
    <?php foreach($translist as $trans):?>
    <tr>
        <td><?php print api_time_print($trans['transactionDateTime'], 'Y.m.d H:i'); ?></td>
        <td><?php print number_format($trans['quantity']); ?></td>
        <td><?php print $trans['typeName']; ?></td>
        <?php if ($trans['transactionType'] == 'buy'): ?>
        <td><font color="red"><?php print number_format($trans['price']); ?></font></td>
        <td><font color="red"><?php print number_format($trans['price']*$trans['quantity']); ?></font></td>
        <?php else: ?>
        <td><font color="green"><?php print number_format($trans['price']); ?></font></td>
        <td><font color="green"><?php print number_format($trans['price']*$trans['quantity']); ?></font></td>
        <?php endif; ?>
        <td><?php print $trans['stationName']; ?></td>
    </tr>
    <?php endforeach;?>
</table>
