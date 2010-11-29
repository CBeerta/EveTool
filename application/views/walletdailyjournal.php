<!--
<?php #print_r($daily); ?>
-->
<div id="content">
<?php if (!empty($error)): ?>
    <div class="error">Unable to load Wallet Journal for : <?php echo implode(', ', $error);?></div>
<?php endif ; ?>
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
        <td style="text-align: right;"><font class="expense"><?php echo number_format($total[$k]['expense'], 2); ?></font></td>
        <td style="text-align: right;"><font class="income"><?php echo number_format($total[$k]['income'], 2); ?></font></td>
    </tr>
        <?php foreach($v as $type): ?>
        <tr>
            <td style="text-align: right;"><?php echo $type['refTypeName']; ?></td>
            <td style="text-align: right;"><font class="expense"><?php echo number_format($type['expense'], 2); ?></font></td>
            <td style="text-align: right;"><font class="income"><?php echo number_format($type['income'], 2); ?></font></td>
        </tr>
        <?php endforeach;?>
    <tr><td colspan="3">&nbsp;</td></tr>
    <?php endforeach;?>
</table>
</div>
