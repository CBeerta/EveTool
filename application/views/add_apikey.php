<table>
<caption>Provide <b>Full</b> Api Keys</caption>
<?php echo form_open('eve/apikeys/add'); ?>
<tr><td>Api User:</td><td><?php echo form_input('apiuser'); ?></td></tr>
<tr><td>Full Api Key:</td><td><?php echo form_input('apikey'); ?></td></tr>
<tr><td colspan="2"><?php echo form_submit('submit', 'Add'); ?></td></tr>
<?php echo form_close(); ?>
</table>
