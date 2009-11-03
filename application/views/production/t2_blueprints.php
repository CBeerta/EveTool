<table width="100%" id="ships">
<caption>T2 Ship Blueprints</caption>
<tr>
<?php foreach ($t2[6] as $k => $v): ?>
    <tr>
        <?php echo form_open("production/t1/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>

<table width="100%" id="modules">
<caption>T2 Modules</caption>
<tr>
<?php foreach ($t2[7] as $k => $v): ?>
    <tr>
        <?php echo form_open("production/t1/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>

<table width="100%" id="charges">
<caption>Ammunition and Charges</caption>
<tr>
<?php foreach ($t2[8] as $k => $v): ?>
    <tr>
        <?php echo form_open("production/t1/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>

<table width="100%" id="drones">
<caption>Drones</caption>
<tr>
<?php foreach ($t2[18] as $k => $v): ?>
    <tr>
        <?php echo form_open("production/t1/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>