<table>
<caption>You have to login to view this page</caption>
<?php echo form_open('user/login'); ?>
<tr><td>Username:</td><td><?php echo form_input('username'); ?></td></tr>
<tr><td><li>Password:</td><td><?php echo form_password('password'); ?></td></tr>
<tr><td colspan="2"><?php echo form_submit('submit', 'Login'); ?></td></tr>
<?php echo form_close(); ?>
</table>
<p>If you do not have an account yet, please <?php echo anchor('user/register', 'register'); ?>.</p>
