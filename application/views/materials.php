<script type="text/javascript">
$(document).ready(function(){
    $("span#editable").each(function(i)
    {
        setClickable(this, i);
    })
    $.getJSON("<?php echo site_url('materials/load/'.$groupID); ?>", loadResults);
});

function setClickable(obj, i) 
{
    $(obj).click(function() 
    {
        var textarea = '<span><input type="text" class="editable" size="16" value="'+$(this).html()+'">';
        var button	 = '<span><input type="button" value="Save" class="saveButton" /><input type="button" value="Undo" class="cancelButton" /></span></span>';
        var revert = $(obj).parent().html();
        $(obj).after(textarea + button).remove();
        $('.saveButton').click(function(){saveChanges(this, revert, false, i);});
        $('.cancelButton').click(function(){saveChanges(this, revert, true, i);});
    })
}

function saveChanges(obj, revert, cancel, n) 
{
    if(!cancel) 
    {
        var t = $(obj).parent().siblings(0).val();
        $.post("<?php echo site_url('materials/load/'.$groupID); ?>", {content: t, n: n}, loadResults, "json");
    }
    if(t=='') t='(click to add text)';
    
    $(obj).parent().parent().replaceWith(revert).remove();
    setClickable($("span#editable").get(n), n);
}	

function loadResults(data) {
    $.each(data.data, function(i, item)
    {
        //$(".quantity_" + i).text(numberFormat(item.quantity));
        $(".quantity_" + i).text(item.quantity);
        $(".volume_" + i).text(numberFormat(item.quantity * item.volume));
        $(".sellprice_" + i).text(numberFormat(item.quantity * data.prices[i].sell.median));
        $(".buyprice_" + i).text(numberFormat(item.quantity * data.prices[i].buy.median));
    });
    $(".total_volume").text(numberFormat(data.sums.volume));
    $(".total_sellprice").text(numberFormat(data.sums.sellprice));
    $(".total_buyprice").text(numberFormat(data.sums.buyprice));
}
</script>
<table width="100%">
<caption><?php echo $caption; ?></caption>
<tr>
    <th colspan="9">
        <?php echo form_open("materials/index"); ?>
        <?php if ( $groupID == 18 ): ?>
        Custom Mineral Prices: <?php echo form_checkbox('custom_prices', 'accept', $custom_prices); ?>&nbsp;|&nbsp;
        <?php endif; ?>
        <?php echo form_dropdown('groupID', $groupIDList, $groupID); ?>
        <?php echo form_submit('submit', 'Select'); ?>
        <?php echo form_close(); ?>
    </th>
</tr>
<tr>
	<th colspan="3">Name</th>
    <th>Amount</th>
    <th>Volume</th>
    <th colspan="2">Sell Price</th>
    <th colspan="2">Buy Price</th>
</tr>
<?php foreach ($types as $r): ?>
<tr>
	<td style="text-align: left;" colspan="2">
		<a id="fb_item" style="color: black;" href="<?php echo site_url('/fancybox/item/'.$r['typeID']); ?>">
			<img src="<?php echo get_icon_url($r,32);?>">
		</a>
	</td>
    <td style="text-align: left;"><?php echo $r['typeName'];?></td>
    <td class="editable">
        <span class="quantity_<?php echo $r['typeID'];?>" id="editable">0</span>
    </td>
    <td>
        <span class="volume_<?php echo $r['typeID'];?>">0</span> m&sup3;
    </td>
    <td width="5"><i><?php echo number_format($prices[$r['typeID']]['sell']['median'], 2); ?></i></td>
    <td style="text-align: right;">
        <span class="sellprice_<?php echo $r['typeID'];?>">0</span> ISK
    </td>
    <td width="5"><i><?php echo number_format($prices[$r['typeID']]['buy']['median'], 2); ?></i></td>
    <td style="text-align: right;">
        <span class="buyprice_<?php echo $r['typeID'];?>">0</span> ISK
    </td>
<tr>
<?php endforeach; ?>
<th colspan="4">Sum:</td>
    <td><span class="total_volume" style="font-weight: bold;">0</span> m&sup3;</td>
    <td colspan="2" style="text-align: right;"><span class="total_sellprice" style="font-weight: bold;">0</span> ISK</td>
    <td colspan="2" style="text-align: right;"><span class="total_buyprice" style="font-weight: bold;">0</span> ISK</td>
</tr>
</table>
<span style="font-size: 77%">Assets are not updated on this Page</span>
