<table width="100%">
<?php foreach ($assets as $location): ?>
<tr>
    <th colspan="5">
		<h2 class="menu_slider" title="Collapse Assets in Location" onClick="toggle_it(<?php echo $location[0]['locationID']; ?>)" style="position: absolute; left: 30px; font-weight: bold;">&darr;&darr;&darr;</h2>
		<div style="margin-left: 50px; text-align: left;">
		<a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$location[0]['locationID']); ?>"><?php echo locationid_to_name($location[0]['locationID']);?></a>
	    </div>
	</th>
</tr>
<tbody style="display: none;" id="<?php echo $location[0]['locationID']; ?>">
<tr>
    <th colspan="3">Name</th>
    <th>Quantity</th>
    <th>Volume</th>
</td>
<?php foreach($location as $asset): ?>
<tr>
    <td><img width="32" height="32" src="<?php echo get_icon_url($asset,32);?>"></td>
    <td colspan="2" style="text-align: left;"><?php echo $asset['typeName']; ?></td>
    <td><?php echo number_format($asset['quantity']); ?></td>
    <td><?php echo number_format($asset['volume']*$asset['quantity']);?> m&sup3;</td>
</tr>
<?php 
if ($asset['contentAmount'] > 0): 
    foreach($asset['contents'] as $content): 
?>
<?php list($sloticon, $slottitle) = slot_icon($content['flag']); ?>
<tr id="contents">
    <td><img width="24" height="24" title="<?php echo $slottitle; ?>" src="<?php echo $sloticon; ?>"></td>
    <td><img width="16" height="16" src="<?php echo get_icon_url($content,32);?>"></td>
    <td style="text-align: left;"><?php echo $content['typeName']; ?></td>
    <td><?php echo number_format($content['quantity']); ?></td>
    <td><?php echo number_format($content['volume']*$content['quantity'], 2);?> m&sup3;</td>
</tr>
<?php endforeach; // content?>
<tr id="contents">
    <td colspan="4" style="text-align: right";>Total Volume in Container:</td>
    <td><?php echo number_format($asset['contentsTotalVolume'], 2);?> m&sup3;</td>
</tr>
<?php endif; ?>
<?php endforeach; // asset?>
<tr>
    <td colspan="5">&nbsp;</td>
</tr>
</tbody>
<?php endforeach; // location?>
</table>
<br />
<span style="font-size: 77%;">Cached Until: <?php echo api_time_print($cachedUntil);?></span>
