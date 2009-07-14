<script language="javascript">
function toggle_it(itemID){
    // Toggle visibility between none and inline
    if ((document.getElementById(itemID).style.display == 'none'))
    {
        document.getElementById(itemID).style.display = '';
    } else {
        document.getElementById(itemID).style.display = 'none';
    }
}
</script> 
<table width="100%">
<?php foreach ($assets as $location): ?>
<tr>
    <th colspan="5" onClick="toggle_it(<?php echo $location[0]['locationID']; ?>)"><div style="position: absolute; left: 30px;">&darr;</div>Assets located in: <?php echo locationIDToName($location[0]['locationID']); ?></th>
</tr>
<tbody style="display: none;" id="<?php echo $location[0]['locationID']; ?>">
<tr>
    <th colspan="3">Name</th>
    <th>Quantity</th>
    <th>Volume</th>
</td>
<?php foreach($location as $asset): ?>
<tr>
    <td><img width="32" height="32" src="<?php echo getIconUrl($asset,32);?>"></td>
    <td colspan="2" style="text-align: left;"><?php echo $asset['typeName']; ?></td>
    <td><?php echo number_format($asset['quantity']); ?></td>
    <td><?php echo number_format($asset['volume']*$asset['quantity']);?> m&sup3;</td>
</tr>
<?php if ($asset['contentAmount'] > 0) foreach($asset['contents'] as $content): ?>
<?php list($sloticon, $slottitle) = slotIcon($content['flag']); ?>
<tr id="contents">
    <td><img width="24" height="24" title="<?php echo $slottitle; ?>" src="<?php echo $sloticon; ?>"></td>
    <td><img width="16" height="16" src="<?php echo getIconUrl($content,32);?>"></td>
    <td style="text-align: left;"><?php echo $content['typeName']; ?></td>
    <td><?php echo number_format($content['quantity']); ?></td>
    <td><?php echo number_format($content['volume']*$content['quantity']);?> m&sup3;</td>
</tr>
<?php endforeach; // content?>
<?php endforeach; // asset?>
<tr>
    <td colspan="5">&nbsp;</td>
</tr>
</tbody>
<?php endforeach; // location?>
</table>
<br />
<span style="font-size: 77%;">Cached Until: <?php echo apiTimePrettyPrint($cachedUntil);?></span>
