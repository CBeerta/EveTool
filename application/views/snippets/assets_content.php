<table>
<?php foreach ($contents as $v): ?>
    <tr>
    <td>
        <?php echo get_character_portrait($v['owner'], 64, 'entry'); ?>
    </td>
	<td style="text-align: left;">
	    <img src="<?php echo get_icon_url($v,64);?>">
    </td>
    <td><?php echo $v['typeName']; ?></td>
    <td><?php echo number_format($v['quantity']); ?></td>
    </tr>
<?php endforeach; ?>
</table>
