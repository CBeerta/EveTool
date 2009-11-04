<table width="100%" id="ships">
<caption>Ship Blueprints</caption>
<tr>
<?php foreach ($blueprints[$tl][6] as $k => $v): ?>
    <tr>
        <?php echo form_open("manufacturing/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>

<?php if (!empty($blueprints[$tl][7])): ?>
<table width="100%" id="modules">
<caption>Modules</caption>
<tr>
<?php foreach ($blueprints[$tl][7] as $k => $v): ?>
    <tr>
        <?php echo form_open("manufacturing/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>
<?php endif; ?>

<?php if (!empty($blueprints[$tl][8])): ?>
<table width="100%" id="charges">
<caption>Ammunition and Charges</caption>
<tr>
<?php foreach ($blueprints[$tl][8] as $k => $v): ?>
    <tr>
        <?php echo form_open("manufacturing/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>
<?php endif; ?>

<?php if (!empty($blueprints[$tl][18])): ?>
<table width="100%" id="drones">
<caption>Drones</caption>
<tr>
<?php foreach ($blueprints[$tl][18] as $k => $v): ?>
    <tr>
        <?php echo form_open("manufacturing/redirect/", array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>
<?php endif; ?>

