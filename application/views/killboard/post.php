<?php echo form_open("killboard/post"); ?>
<table>
<caption>Post your Killmail:</caption>
<tr>
    <td>
        <?php echo form_textarea(array('name' => 'killmail', 'rows' => 20, 'cols' => 40)); ?>
    </td>
</tr>
<tr><td><?php echo form_submit('Submit', 'submit'); ?></td></tr>
</table>
<?php echo form_close(); ?>
