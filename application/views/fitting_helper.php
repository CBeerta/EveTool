<div style="position: static; top: 0px; right: 0px; float: right; margin-right: 5px; background: #444;">
    <h2>Put Items or EFT Fitting in:</h2>
    <?php echo form_open("/market/fitting_helper"); ?>
        <?php echo form_textarea(array('name' => 'items', 'id' => 'id', 'cols' => 35, 'rows' => 20 )); ?> <br />
        <?php echo form_submit('submit', 'Submit'); ?>
    <?php echo form_close(); ?>
</div>
<?php if (!empty($types)): ?>
<div>
<table border="0">
<tr>
    <th colspan="2">Item</th>
    <th>Need</th>
    <th>Value</th>
</tr>
<?php foreach ($types as $type): ?>
    <tr>
        <td>
            <a id="fb_item" href="<?php echo site_url("/fancybox/item/{$type->typeID}"); ?>">
		        <img align="left" width="32" height="32" src="<?php echo get_icon_url($type,32);?>">
            </a>
        </td>        
        <td>
            <?php echo $type->typeName; ?>
        </td>
        <td><?php echo $type->amount; ?></td>
        <td><?php echo number_format($prices[$type->typeID]['sell']['median'] * $type->amount);?> ISK</td>
    </tr>
<?php endforeach; ?>
    <tr>
        <th colspan="3">Total:</th>
        <td><?php echo number_format($total_sell);?> ISK</td>
    </tr>
</table>
</div>
<?php endif; ?>
