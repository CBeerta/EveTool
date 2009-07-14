<table width="100%">
<caption><?php echo $title;?></caption>
<tr>
    <th colspan="2">Name</th>
    <th>Location</th>
</tr>
<?php foreach($assets as $asset): ?>
<tr>
    <td><img width="64" height="64" src="<?php echo getIconUrl($asset['typeID'],64);?>"></td>
    <td style="text-align: left;">
    <?php if (isset($asset['techLevel'])): ?>
        <a href="<?php echo site_url('/production/t'.$asset['techLevel'].'/detail/'.$asset['typeID'].'/'.$character);?>"><?php echo $asset['typeName']; ?></a>
    <?php else: ?>
        <?php echo $asset['typeName']; ?>
    <?php endif; ?>
    </td>
    <td><?php echo locationIDToName($asset['locationID']); ?></td>
</tr>
<?php if ( getInvType($asset['typeID'])->categoryID == 6 && isset($asset['assetItemID']) ): //Ship?>
<tr>
    <td colspan="2">&nbsp;</td>
    <td>
        <?php echo shipFitting($asset['assetItemID'], $asset['typeID']); ?>
    </td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
