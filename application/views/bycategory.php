<table width="100%">
<caption><?php echo $title;?></caption>
<tr>
    <th colspan="2">Name</th>
    <th>Location</th>
</tr>
<?php foreach($assets as $asset): ?>
<tr >
    <th>
    <?php if ( $asset['categoryID'] == 6 && isset($asset['assetItemID']) ): //Ship?>
    <a id="fb_fitting" href="<?php echo site_url("/fancybox/fitting_from_db/{$asset['typeName']}/{$asset['assetItemID']}"); ?>">
        <img align="left" width="64" height="64" src="<?php echo get_icon_url($asset,64);?>">
    </a>
    <?php else: ?>
    <a id="fb_item" href="<?php echo site_url("/fancybox/item/{$asset['typeID']}"); ?>">
		<img align="left" width="64" height="64" src="<?php echo get_icon_url($asset,64);?>">
    </a>
	<?php endif; ?>
	</th>
    <th style="text-align: left;">
    <?php if (isset($asset['techLevel'])): ?>
        <a href="<?php echo site_url('/manufacturing/detail/'.$asset['typeID']);?>"><?php echo $asset['typeName']; ?></a>
    <?php else: ?>
        <?php echo $asset['typeName']; ?>
    <?php endif; ?>
    <br />
    <small><?php echo $asset['groupName']; ?></small>
    </th>
    <th style="text-align: left;">
        <a id="fb_location" href="<?php echo site_url('/fancybox/location/'.$asset['locationID']); ?>"><?php echo locationid_to_name($asset['locationID']);?></a>
    </th>
</tr>
<?php endforeach; ?>
</table>
