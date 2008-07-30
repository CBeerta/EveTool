<table>
<caption>Recover your Password</caption>
<?php echo form_open('user/recover'); ?></td></tr>
<tr><td>Email:</td><td><?php echo form_input('email'); ?></td></tr>
<tr><td colspan="2"><?php echo form_submit('submit', 'Recover'); ?></td></tr>
<?php echo form_close(); ?>
</table>
