<?php //debug_popup($types); ?>
<div style="position: static; top: 0px; right: 0px; float: right; margin-right: 5px; background: #444; text-align: right;">
    <h3>Put Items or EFT Fitting in:</h3>
    <?php echo form_open("/market/fitting_helper"); ?>
        <?php echo form_textarea(array('name' => 'items', 'id' => 'id', 'cols' => 35, 'rows' => 20, 'value' => $posted )); ?> <br />
        <?php echo form_submit('submit', 'Submit'); ?>
    <?php echo form_close(); ?>
</div>
<pre>
Not entirely sure how to make this page more usefull.

Ideas are:

 * Have user select a Location, and than let the app tell what items are missing to complete a fit?
 * ability to save fittings? 
 * Ability to import an arbitary directory with eft fittings in it??
 * Is this "Price Checks" or a "Fitting Helper"?

</pre>
<?php if (!empty($types)): ?>
<div>
<table border="0">
<tr>
    <th colspan="2">Item</th>
    <th>Amount</th>
    <th>Value</th>
    <th>Volume</th>
    <th id="availability" style="display: none;">Availability</th>
</tr>
<?php foreach ($types as $type): ?>
    <tr>
        <td>
            <a id="fb_item" href="<?php echo site_url("/fancybox/item/{$type->typeID}"); ?>">
		        <?php echo icon_url($type,32);?>
            </a>
        </td>        
        <td>
            <?php echo $type->typeName; ?>
        </td>
        <td><?php echo $type->amount; ?></td>
        <td><?php echo number_format($prices[$type->typeID]['sell']['median'] * $type->amount);?> ISK</td>
        <td><?php echo number_format($type->volume * $type->amount);?> m&sup3;</td>
        <td id="availability" style="display: none;">
            <div id="availability"></div>
        </td>
    </tr>
<?php endforeach; ?>
    <tr>
        <th colspan="3">Total:</th>
        <td><?php echo number_format($total_sell);?> ISK</td>
        <td><?php echo number_format($total_volume);?> m&sup3;</td>
    </tr>
</table>
</div>
<?php endif; ?>
<?php if (isset($errors)): ?>
<pre>
<?php print_r($errors); ?>
</pre>
<?php endif; ?>
<div style="clear: both;">&nbsp;</div>
