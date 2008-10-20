<table width="100%">
<?php foreach ($chars as $k => $v): ?>
<tr>
    <th colspan="3"><?php echo $k;?></th>
</tr>
<tr>
    <th>Balance:</th><td colspan="2"><?php echo number_format($v['balance'],2); ?> ISK</td>
</tr>
<?php if (!empty($v['training']['skillInTraining'])): ?>
<tr>
    <th>Currently Training:</th>
    <td style="font-weight: bold;" class="dataTableCell"><?php echo $v['training']['trainingTypeName']; ?></td>
    <td style="text-align: center;" class="dataTableCell"><img alt="<?php echo $v['training']['trainingToLevel']; ?>" src="/files/images/level<?php echo $v['training']['trainingToLevel']; ?>_act.gif" /></td>
</tr>
<tr>
  <th class="dataTableCell">Finishes in</th>
  <td colspan="2"><?php echo timeToComplete($v['training']['trainingEndTime']); ?></td>
</tr>
<?php endif;?>
<tr><td colspan="3">&nbsp;</td></tr>
<? endforeach; ?>
<tr>
    <th>Total Isk:</th><td colspan="2"><?php echo number_format($total,2);?> ISK</td>
</tr>
</table>
