<table width="100%">
<?php foreach ($chars as $k => $v): ?>
<tr>
    <td class="light" colspan="3"><img title="<?php echo $k; ?>" src="/files/cache/char/<?php echo $v['charid'];?>/64/char.jpg" align="left"><?php echo $k;?></td>
</tr>
<tr>
    <td class="light">Balance:</td><td><?php echo number_format($v['balance'],2); ?> ISK</td>
</tr>
<?php if (!empty($v['training']['skillInTraining'])): ?>
<tr>
    <td class="light">Currently Training:</td>
    <td style="text-align: right;" class="dataTableCell"><img alt="<?php echo $v['training']['trainingToLevel']; ?>" src="/files/images/level<?php echo $v['training']['trainingToLevel']; ?>_act.gif" align="right"/><?php echo $v['training']['trainingTypeName']; ?></td>
</tr>
<tr>
  <td class="light" class="dataTableCell">Finishes in</td>
  <td><?php echo timeToComplete($v['training']['trainingEndTime']); ?></td>
</tr>
<?php endif;?>
<? endforeach; ?>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
    <td class="light">Total Isk:</td><td><?php echo number_format($total,2);?> ISK</td>
</tr>
</table>
