<div style="position: relative; top: 0px; right: 0px; float: right;">
    <?php echo anchor("killboard/post", "Post a Killmail"); ?>
</div>
<?php if (empty($killmails)): ?>
<h1>No Killmails found for <?php echo $this->character;?></h1>
<?php else:?>
<?php if (isset($totals)): ?>
<h1 style="text-align: center"><?php echo $totals->kills;?> Ships killed, <span style="color: red"><?php echo $totals->losses;?> Ships lost</span>.</h1>
<br/>
<?php endif; ?>

<div style="text-align: center;">Page: <?php echo $pagination;?></div>

<table width="100%">
<caption>Recent Activity for "<?php echo empty($corp) ? $character : $corp; ?>"</caption>
<tr>
    <th colspan="3">Shiptype</th>
    <th>Victim</th>
    <th>Final Blow</th>
    <th>System</th>
    <th>Inv.</th>
    <th>Time</th>
</tr>
<?php foreach (array_keys($killmails) as $k): ?>
<tr>
    <th colspan="8"><?php echo date('l, F j Y', $killmails[$k][0]->when); ?></th>
</tr>
<?php foreach ($killmails[$k] as $v): ?>
<?php $invType = getInvType($v->destroyed); ?>
<tr>
    <?php if ( $v->victim == $character || $v->corp == $corp /* || $v->alliance == $alliance */ ): ?>
    <td width="2" bgcolor="red"></td>
    <?php else: ?>
    <td width="2" bgcolor="green"></td>
    <?php endif; ?>
    <td width="32">
        <a href="<?php echo site_url("/killboard/detail/{$v->filename}");?>"><img src="<?php echo getIconUrl($invType, 32);?>" border="0"></a>
    </td>
    <td>
        <b><?php echo $invType->typeName; ?></b><br />
        <?php echo $invType->groupName; ?>
    </td>
    <td>
        <a href="<?php echo site_url("killboard/char/{$v->victim}");?>">
            <img src="<?php echo site_url("files/cache/char/".get_character_id($v->victim)."/64/char.jpg"); ?>" width="32" height="32" align="right">
        </a>
        <b><?php echo $v->victim; ?></b><br/>
        <?php echo anchor("killboard/corp/{$v->corp}/{$character}", $v->corp); ?>
     </td>
     <td>
        <a href="<?php echo site_url("killboard/char/{$v->involved_parties[$v->final_blow]->name}");?>">
                <img src="<?php echo site_url("files/cache/char/".get_character_id($v->involved_parties[$v->final_blow]->name)."/64/char.jpg"); ?>" width="32" height="32" align="right">
        </a>
        <b><?php echo $v->involved_parties[$v->final_blow]->name; ?></b><br />
        <?php echo anchor ("killboard/corp/{$v->involved_parties[$v->final_blow]->corp}", $v->involved_parties[$v->final_blow]->corp); ?><br />
     </td>
    <td>
        <b><?php echo $v->system; ?></b><br />
        (<?php echo $v->security; ?>)
     </td>
    <td><?php echo count($v->involved_parties); ?></td>
    <td><?php echo gmdate('H:i', $v->when); ?></td>
</tr>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
<?php endif; ?>

