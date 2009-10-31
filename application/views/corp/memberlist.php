<?php //debug_popup($memberlist);?>
<table width="100%">
<caption>Members of the Corporation: <?php echo $corpname;?></caption>
<tr>
    <th colspan="2">Name</th>
    <th>Joined</th>
    <th>Title</th>
	<th>Base</th>
</tr>
<?php foreach ($memberlist as $row): ?>
<tr>
	<td><?php echo get_character_portrait($row['characterID'], 32); ?></td>
	<td style="text-align: left;"><?php echo $row['name'];?></td>
	<td><?php echo $row['startDateTime'];?></td>
	<td><?php echo $row['title'];?></td>
	<td><?php echo $row['base'];?></td>
</tr
<?php endforeach; ?>
</table>