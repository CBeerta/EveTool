<table width="99%" id="location_snippet">
		<th colspan="2">
			<?php echo get_character_portrait($char, 64, 'left'); ?>
			<h1>
			    <?php echo $char->characterName; ?>
		    </h1>
		</th>
	</tr>
	<?php foreach ($char->children() as $v): ?>
    <?php if (in_array($v->getName(), array('characterID', 'corporationID', 'allianceID'))) continue; ?>
    <tr>
        <td><?php echo ucfirst(strtolower($v->getName()));?></td>
        <td><?php echo $v; ?>
    </tr>
	<?php endforeach;?>

</table>


