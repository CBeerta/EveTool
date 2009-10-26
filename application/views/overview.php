<div style="position: static; top: 0px; right: 0px; float: right; margin-right: 5px;">
    <table style="min-width:60%;">
    <?php foreach ($chars as $k => $v): ?>
        <tr>
            <td class="light" rowspan="4" width="70" style="text-align: left;">
                <img title="<?php echo $k; ?>" src="<?php echo site_url("/files/cache/char/{$v['charid']}/64/char.jpg"); ?>">
            </td>
        </tr>
        <tr>
            <td class="light">Balance:</td><td><?php echo number_format($v['balance'],2); ?> ISK</td>
        </tr>
        <?php if (!empty($v['training']['skillInTraining'])): ?>
        <tr>
            <td class="light">Currently Training:</td>
            <td style="text-align: right;" class="dataTableCell">
                <img alt="<?php echo $v['training']['trainingToLevel']; ?>" src="/files/images/level<?php echo $v['training']['trainingToLevel']; ?>_act.gif" align="right"/><?php echo $v['training']['trainingTypeName']; ?>
            </td>
        </tr>
        <tr>
          <td class="light" class="dataTableCell">Finishes in</td>
          <td><?php echo timeToComplete($v['training']['trainingEndTime']); ?></td>
        </tr>
        <?php else: ?>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <?php endif;?>
    <? endforeach; ?>
    <tr><td colspan="3">&nbsp;</td></tr>
    <tr>
        <td colspan="2" class="light">Total Isk:</td><td><?php echo number_format($total,2);?> ISK</td>
    </tr>
    </table>
</div>
<div style="position: static;top: 0px;">
    <?php  foreach ($feed as $item): ?>
    <h4><?php echo $item->get_title();?></h4>
    <p><?php echo $item->get_description();?></p>
    <br />
    <?php endforeach; ?>
</div>    
