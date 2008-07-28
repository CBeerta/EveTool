<?php 
/*
This is a ROYAL MESS. Would actually be more readable as PHP Code with html in it, instead of this 

Also, EVEN MOAR A MESS NOW

FIXME: FFS!!

2008-07-23: Everytime i look at this, i want to punch myself in the face

*/
?>
<table width="95%">
<?php if (isset($caption)): ?>
<caption><?php echo $caption; ?></caption>
<?php endif; ?>
<tr>
	<th colspan="3"><?php echo $cols[0]; ?></th>
	<?php for($i=1;$i<count($cols);$i++): ?>
	<th><?php echo $cols[$i]; ?></th>
	<?php endfor; ?>
</tr>
<?php if (isset($data) && count($data) > 0): ?>
<?php for ($y=0;$y<count($data);$y++): ?>
<tr>
	<?php if (isset($flags) && !empty($flags[$y])): ?>
    <?php list($sloticon, $slottitle) = slotIcon($flags[$y]); ?>
	<td style="text-align: left;"><img width="24" height="24" title="<?php echo $slottitle; ?>" src="<?php echo $sloticon; ?>"></td>
	<td style="text-align: left;">
	<?php else: ?>
	<td style="text-align: left;" colspan="2">
	<?php endif; ?>
	<?php if (isset($icons)):?>
	<img src="<?php echo getIconUrl($icons[$y],16);?>">
	<?php else:?>
	&nbsp;
	<?php endif;?>
	</td>
    <td style="text-align: left;"><?php echo $data[$y][0];?></td>
	<?php for($i=1;$i<count($cols);$i++): ?>
	<td><?php echo $data[$y][$i];?></td>
	<?php endfor;?>
<?php endfor;?>
<?php endif;?>
<?php if (isset($sums)): ?>
<tr>
<th colspan="3">Sum:</td>
<?php foreach($sums as $sum): ?>
<?php if($sum !== False): ?>
    <td><?php echo $sum; ?></td>
<?php else: ?>
    <td>&nbsp;</td>
<?php endif; ?>
<?php endforeach; ?>
</tr>
<?php endif; ?>
</table>
