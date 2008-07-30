<table width="100%">
<caption>Published Blueprints</caption>
<tr>
<?php foreach ($t1 as $k => $v): ?>
    <tr>
        <?php echo form_open('production/t1/'.$character, array('name' => 'form'.$k)); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>
<table width="100%">
<caption>Player Owned Blueprints</caption>
<tr>
    <th colspan="2">Name</th>
    <th>Location</th>
</tr>
<?php foreach($blueprints as $bp): ?>
<tr>
    <td><img width="32" height="32" src="<?php echo getIconUrl($bp['typeID'],32);?>"></td>
    <td style="text-align: left;">
        <a href="<?php echo site_url('/production/t'.$bp['techLevel'].'/'.$character.'/'.$bp['typeID']);?>"><?php echo $bp['typeName']; ?></a>
    </td>
    <td><?php echo locationIDToName($bp['locationID']); ?></td>
</tr>
<?php endforeach; ?>
</table>
