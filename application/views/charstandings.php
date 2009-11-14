<table cellspacing="5" cellpadding="5" width="100%">
<caption>your Standings</caption>
<?php foreach ( $standings as $k => $v ): ?>
<tr>    
    <th colspan="3">
        <?php echo $k; ?>
    </th>
</tr>
<tr>
    <th>Name</th>
    <th>Standing</th>
    <th width="16">&nbsp;</th>
</tr>
<?php foreach ( $v as $row ): ?>
<tr>
    <td style="text-align: left;">
        <a href="<?php echo site_url("/agents/by_id/{$row['id']}"); ?>"><?php echo $row['name']; ?></a>
        <?php echo $row['agent_info']; ?>
    </td>
    <td><?php echo $row['standing']; ?></td>
    <td>
        <img src="<?php echo site_url('/files/images/'.$row['sta_icon']); ?>">
    </td>
</tr>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
