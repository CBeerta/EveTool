<!--
<?php if (!isset($corpname)):?>
<div id="buttan">
<a id="buttan" href="<? echo site_url("/wallet/journal");?>">Wallet Journal</a>
<a id="buttan" href="<? echo site_url("/wallet/dailyjournal");?>">Group by Day</a>
</div>
<?php endif;?>
-->
<table width="100%">
    <caption>Wallet Journal, grouped by Day</caption>
    <tr>
        <th>Type</th>
        <th>Expense</th>
        <th>Income</th>
    </tr>
    <?php foreach($daily as $k => $v):?>
    <tr>
        <td class="light" style="text-align: left;"><?php echo $total[$k]['prettydate']; ?></td>
        <td style="text-align: right;" class="light"><font id="expense"><?php echo number_format($total[$k]['expense'], 2); ?></font></td>
        <td style="text-align: right;" class="light"><font id="income"><?php echo number_format($total[$k]['income'], 2); ?></font></td>
    </tr>
    <?php foreach($v as $type): ?>
    <tr>
        <td><?php echo $type['refTypeName']; ?></td>
        <td style="text-align: right;"><font id="expense"><?php echo number_format($type['expense'], 2); ?></font></td>
        <td style="text-align: right;"><font id="income"><?php echo number_format($type['income'], 2); ?></font></td>
    </tr>
    <?php endforeach;?>
	<tr>
		<td class="light" colspan="2" align="right">Balance:</td>
		<td style="text-align: right;" class="light"><?php echo number_format($balance[$k], 2); ?></td>
	</tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    <?php endforeach;?>
</table>
