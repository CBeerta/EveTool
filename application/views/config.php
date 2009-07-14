<table>
<caption>Configuration Settings</caption>
<?php echo form_open('overview/config'); ?>

<tr>
    <td>Region for Price Information</td>
    <td><?php echo form_dropdown('regions',$regions, $config_region); ?></td>
</tr>
<tr>
    <td>Use Perfect Skills instead of Character Skills for Production</td>
    <td><?php echo form_checkbox('use_perfect', True, $use_perfect); ?></td>
</tr>
<tr>
    <td>Pull Corporation Data, and merge with Character data where applicable</td>
    <td><?php echo form_checkbox('pull_corp', True, $pull_corp); ?></td>
</tr>
<tr>
    <td>Your Timezone</td>
    <td><?php echo form_dropdown('user_timezone', $timezone_list, $selected_tz); ?>
</tr>


<tr><th colspan="2">Custom Mineral Prices</th></tr>
<?php foreach ($mineral_prices as $k => $v): ?>
<tr>
    <td><?php print getInvType($k)->typeName; ?></td>
    <td><?php print form_input("mineral_prices[{$k}]", $v);?></td>
</tr>
<?php endforeach; ?>



<tr>
    <td colspan="2"><?php echo form_submit('submit', 'Update'); ?></td>
</tr>

<?php echo form_close(); ?>
</table>
