<table width="100%">
<caption>Members of the Corporation: <?php echo $corpname;?></caption>
<tr>
    <th colspan="2" width="20%">Name</th>
    <th width="20%">Joined</th>
    <th>Title</th>
	<th>Base</th>
</tr>
<?php foreach ($memberlist as $row): ?>
<tr>
	<td><?php echo get_character_portrait($row['characterID'], 32); ?></td>
	<td style="text-align: left;">
		<a href="<?php echo site_url("/corp/member_detail/".$row["characterID"]);?>"><?php echo $row['name'];?></a>
	</td>
	<td><?php echo apiTimePrettyPrint($row['startDateTime']);?></td>
	<td><?php echo $row['title'];?></td>
	<td><?php echo $row['base'];?></td>
</tr
<?php endforeach; ?>
</table>