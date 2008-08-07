<table width="100%">
<caption>T1 Ship Blueprints</caption>
<tr>
<?php foreach ($t1 as $k => $v): ?>
    <tr>
        <?php echo form_open('production/t1/detail/'.$character, array('name' => 'form'.str_replace(' ', '_', $k))); ?>
        <th><?php echo $k; ?></th>
        <td style="text-align: left;"><?php echo form_dropdown($k, $v); ?></td>
        <td><?php echo form_submit('submit', 'Go'); ?></td>
        <?php echo form_close(); ?>
    </tr>
<?php endforeach;?>
</tr>
</table>

