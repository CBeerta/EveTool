<div style="position: static; top: 0px; right: 0px; float: right; margin-right: 5px; background: #444;">
    <table>
    <?php foreach ($chars as $k => $v): ?>
        <tr>
            <th colspan="2"><?php echo $k;?></th>
        </tr>
        <tr>
            <td class="light">Balance:</td><td><?php echo number_format($v['balance'],2); ?> ISK</td>
        </tr>
        <?php if (!empty($v['training']['skillInTraining'])): ?>
        <tr>
            <td class="light">Currently Training:</td>
            <td class="left">
                <!-- <img alt="<?php echo $v['training']['trainingToLevel']; ?>" src="/files/images/level<?php echo $v['training']['trainingToLevel']; ?>_act.gif" align="right"/> -->
                <?php echo "{$v['training']['trainingTypeName']} ".roman($v['training']['trainingToLevel']); ?>
            </td>
        </tr>
        <tr>
          <td class="light">Finishes in:</td>
          <td><?php echo api_time_to_complete($v['training']['trainingEndTime']); ?></td>
        </tr>
        <?php endif;?>
        <?php if (isset($v['queue'])): ?>
        <tr>
          <td class="light">Queue:</td>
          <td><?php echo api_time_to_complete($v['queue']); ?></td>
        </tr>
        <?php endif; ?>
    <? endforeach; ?>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
            <th class="light">Total Isk:</th><td><?php echo number_format($total,2);?> ISK</td>
        </tr>
    </table>
</div>
<div style="position: static;top: 0px;">
    <?php #if (igb_trusted()): echo '<pre>'; print_r($_SERVER); print '</pre>'; endif; ?>
    <?php /* foreach ($feed as $item): ?>
    <h4><?php echo $item->get_title();?></h4>
    <p><?php echo $item->get_description();?></p>
    <br />
    <?php endforeach; */?>
</div>

