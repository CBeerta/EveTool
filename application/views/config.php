<table>
<caption>Configuration Settings</caption>
<?php echo form_open('overview/config'); ?>

<tr>
    <td>Region for Price Information</td>
    <td><?php echo form_dropdown('regions',$regions, $config_region); ?></td>
</tr>


<tr>
    <td colspan="2"><?php echo form_submit('submit', 'Update'); ?></td>
</tr>

<?php echo form_close(); ?>
</table>