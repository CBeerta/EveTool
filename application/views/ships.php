<table width="100%">
<caption>Ships <?php echo $character; ?> is Skilled to Fly.</caption>
<?php foreach(array_keys($canfly) as $groupName): ?>
    <tr>
        <th class="left" colspan="2">
            <?php echo $groupName; ?>
        </th>
    </tr>
    <?php foreach(array_keys($canfly[$groupName]) as $raceName): ?>
    <tr>
        <th class="left" width="1%"><img title="<?php echo $raceName; ?>" src="<?php echo site_url("/files/images/".strtolower($raceName).".png"); ?>"></th>
        <td>
        <table>
            <tr>
            <?php foreach($canfly[$groupName][$raceName] as $ship): ?>
            <td width="70" style="text-align: center; vertical-align: top;">
                <a id="fb_item" style="color: black;" href="<?php echo site_url('/fancybox/item/'.$ship['typeID']); ?>">
                    <?php echo icon_url($ship, 64); ?>
                </a>
                <?php echo $ship['typeName']; ?>
            </td>
            <?php endforeach; ?>
            </tr>
        </table>
        </td>
    </tr>
    <?php endforeach;?>
<?php endforeach; ?>
</table>

