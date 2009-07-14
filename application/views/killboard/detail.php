<?php //debug_popup($k); ?>
<div>
<div style="position: relative; float: right; width: 420px; top: 0px;">

<table style="width: 100%;">
    <caption>Details:</caption>
    <tr>
        <td>Location:</td>
        <td><?php echo $k->system;?> (<?php echo $k->security; ?>)</td>
    </tr>
    <tr>
        <td>Date:</td>
        <td><?php echo gmdate('r', $k->when);?></td>
    </tr>
    <tr>
        <td>Total Damage Taken:</td>
        <td><?php echo number_format($k->damage_taken);?></td>
    </tr>
</table>
<?php if (!empty($k->items['dropped_items']) || !empty($k->items['destroyed_items'])) echo Ship_Fitting::get($k->destroyed, Ship_Fitting::items_from_killmail($k->filename)); ?>
<table style="width: 100%;">
    <caption>Items:</caption>
    <?php if (!empty($k->items['dropped_items'])): ?>
    <?php foreach ($k->items['dropped_items'] as $item): ?>
    <tr>
        <td style="width:2px;" bgcolor="green"></td>
        <td width="32" height="32"><img src="<?php echo getIconUrl($k->items_to_id[$item->name], 32);?>"></td>
        <td width="64"><?php echo number_format($item->qty);?></td>
        <td><?php echo $item->name;?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif;?>
    <?php if (!empty($k->items['destroyed_items'])): ?>
    <?php foreach ($k->items['destroyed_items'] as $item): ?>
    <tr>
        <td style="width:2px;" bgcolor="red"></td>
        <td width="32" height="32"><img src="<?php echo getIconUrl($k->items_to_id[$item->name], 32);?>"></td>
        <td width="64"><?php echo number_format($item->qty);?></td>
        <td><?php echo $item->name;?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif;?>
</table>

</div>

<div style="width: 400px; padding-right: 5px;">
    <table style="width: 100%;">
        <caption>Victim:</caption>
        <tr>
            <th colspan="4"><?php echo $k->victim;?></th>
        </tr>
        <tr>
            <th rowspan="4" style="width: 64px;">
                <a href="<?php echo site_url("killboard/char/{$k->victim}");?>">
                    <img src="<?php echo site_url("files/cache/char/{$k->characterID}/64/char.jpg"); ?>">
                </a>
            </th>
            <th rowspan="4" style="width: 64px;"><img src="<?php echo getIconUrl($k->items_to_id[$k->destroyed], 64); ?>"></th>
        </tr>
        <tr>
            <td>Corp:</td>
            <td><?php echo anchor("/killboard/corp/{$k->corp}", $k->corp);?></td>
        </tr>
        <tr>
            <td>Alliance:</td>
            <td><?php echo $k->alliance;?></td>
        </tr>
        <tr>
            <td>Ship:</td>
            <td>
                <?php echo $k->destroyed;?> 
                (<?php echo $k->items_to_id[$k->destroyed]->groupName;?>)
            </td>
        </tr>
    </table>
    <table style="width: 100%">
        <caption>Involved Parties:</caption>
    <?php foreach ($k->involved_parties as $p): ?>
        <tr>
            <th colspan="4">
            <?php echo $p->name;?>
            <?php if ($k->involved_parties[$k->final_blow]->name == $p->name) echo '(Final Blow)';?>
            </th>
        </tr>
        <tr>
            <th rowspan="5" style="width: 64px;">
                <a href="<?php echo site_url("killboard/char/{$p->name}");?>">
                    <img src="<?php echo site_url("files/cache/char/{$p->characterID}/64/char.jpg"); ?>">
                </a>                
            </th>
            <th rowspan="5" style="width: 64px;"><img src="<?php echo getIconUrl($k->items_to_id[$p->ship], 64); ?>"></th>
        </tr>
        <tr>
            <td><?php echo anchor("/killboard/corp/{$p->corp}", $p->corp);?></td>
        </tr>
        <tr>
            <td><?php echo $p->alliance;?></td>
        </tr>
        <tr>
            <td>
            <?php echo $p->ship;?> 
            (<?php echo $k->items_to_id[$p->ship]->groupName;?>)
            </td>
        </tr>
        <tr>
            <td>
            <img src="<?php echo getIconUrl($k->items_to_id[$p->weapon], 32);?>" width="16" height="16">
            <?php echo $p->weapon; ?>
            </td>
        </tr>
        <tr>
            <th colspan="2">Damage done:</th><td colspan="2"><?php echo number_format($p->damage_done);?><?php if ($k->damage_taken > 0) echo "(".round(100/$k->damage_taken*$p->damage_done)."%)";?></td>
        </tr>
    <?php endforeach; ?>
    </table>
</div>

</div>
