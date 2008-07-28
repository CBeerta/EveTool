<table>
<caption>Register a new Account</caption>
<?php echo form_open('user/register'); ?></td></tr>
<tr><td>Username:</td><td><?php echo form_input('username'); ?></td></tr>
<tr><td>Password:</td><td><?php echo form_password('password'); ?></td></tr>
<tr><td>Email:</td><td><?php echo form_input('email'); ?></td></tr>
<tr><td colspan="2"><?php echo form_submit('submit', 'Register'); ?></td></tr>
<?php echo form_close(); ?>
</table>
