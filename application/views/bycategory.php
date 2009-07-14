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
<caption><?php echo $title;?></caption>
<tr>
    <th colspan="2">Name</th>
    <th>Location</th>
</tr>
<?php foreach($assets as $asset): ?>
<tr onClick="toggle_it(<?php echo $asset['assetItemID']; ?>)">
    <th><img align="left" width="64" height="64" src="<?php echo getIconUrl($asset['typeID'],64);?>"></th>
    <th style="text-align: left;">
    <?php if (isset($asset['techLevel'])): ?>
        <a href="<?php echo site_url('/production/t'.$asset['techLevel'].'/detail/'.$asset['typeID'].'/'.$character);?>"><?php echo $asset['typeName']; ?></a>
    <?php else: ?>
        <?php echo $asset['typeName']; ?>
    <?php endif; ?>
    <br />
    <?php echo getInvType($asset['typeID'])->groupName; ?>
    </th>
    <th><?php echo locationIDToName($asset['locationID']); ?></th>
</tr>
<?php if ( getInvType($asset['typeID'])->categoryID == 6 && isset($asset['assetItemID']) ): //Ship?>
<tbody style="display: none;" id="<?php echo $asset['assetItemID']; ?>">
<tr>
    <td colspan="3">
        <?php echo shipFitting($asset['assetItemID'], $asset['typeID']); ?>
    </td>
</tr>
</tbody>
<?php endif; ?>
<?php endforeach; ?>
</table>
