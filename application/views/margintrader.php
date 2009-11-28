<div style="top: 0px; right: 0px; float: right; background: #444;">
    <table>
    <caption>Filter Options</caption>
    <?php echo form_open("/market/margintrader"); ?>
    <tr>
        <td>Tech Level:</td>
        <td id="left">
            <?php echo form_dropdown('tech_level', array(1 => 1, 2 => 2), $tech_level); ?>
        </td>
    </tr>
    <tr>
        <td>Meta Level:</td>
        <td id="left">
            <?php echo form_dropdown('meta_level', array(2 => '>=2', 3 => '>=3', 4 => '>=4'), $meta_level); ?>
        </td>
    </tr>
    <tr>
        <td>Slot:</td>
        <td id="left">
            <?php echo form_multiselect('slot[]', array(11 => 'Low Power', 13 => 'Med Power', 12 => 'High Power', ), $slot); ?>
        </td>
    </tr>
    <tr>
        <td>Minimum Value:</td>
        <td id="left">
            <?php echo form_input(array('name' => 'min_value', 'value' => $min_value, 'size' => 10)); ?>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><?php echo form_submit('submit', 'Filter'); ?></td>
    </table>
    <?php echo form_close(); ?>
    </table>
</div>
<?php //debug_popup($prices); ?>    
<table>
    <caption>Items</caption>
        <tr>    
            <th colspan="2">Type</th>
            <th>Buy Price</th>
            <th>Sell Price</th>
            <th>Margin</th>
            <th>Volume</th>
        </tr>
    <?php foreach ($items as $v): ?>
    <?php if ($prices[$v->typeID]['buy']['median'] < $min_value) continue; ?>
    <tr>
	    <td style="text-align: left;">
            <a id="fb_item" href="<?php echo site_url('/fancybox/item/'.$v->typeID); ?>">
        	    <?php echo icon_url($v,32); ?>
	        </a>
        </td>
        <td><?php echo $v->typeName; ?></td>
        <td><?php echo number_format($prices[$v->typeID]['buy']['median']); ?> ISK</td>
        <td><?php echo number_format($prices[$v->typeID]['sell']['median']); ?> ISK</td>
        <td><?php echo @number_format(($prices[$v->typeID]['sell']['median'] - $prices[$v->typeID]['buy']['median']) / $prices[$v->typeID]['buy']['median'] * 100); ?> %</td>
        <td><?php echo number_format($prices[$v->typeID]['sell']['volume']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<div style="clear: both;">&nbsp;</div>
