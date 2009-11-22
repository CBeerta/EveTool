<script type="text/javascript">
$(document).ready(function(){
    $("#bpForm").ajaxComplete(function(request, settings){
      $("#ajax_loading_thingy").hide();
    });
    $("#bpForm").ajaxStart(function(request, settings){
      $("#ajax_loading_thingy").show();
    });
    $.getJSON("<?php echo site_url('manufacturing/update/'.$blueprintID); ?>", loadResults);
    $("#bpForm").submit(formProcess);
    
    function loadResults(data) {
        $("#me").val(data.me);
        $("#totalVolume").text(numberFormat(data.totalVolume));
 	    $("#totalValue").text(numberFormat(data.totalValue));
 	    $.each(data.req, function(i, item){
            $(".req" + i).text(numberFormat(item));
            $(".have" + i).text(numberFormat(data.have[i]))
            if ( item > data.have[i]) {
                $(".have" + i).attr("class", "expense");
            } else {
                $(".have" + i).attr("class", "income");
            }
            $(".value"+i).text(numberFormat(data.value[i])+ ' ISK');
        });
        <?php if (count($totalMineralUsage) > 0): ?>
        $("#totalMineralVolume").text(numberFormat(data.totalMineralVolume));
        $("#totalMineralVolumeValue").text(numberFormat(data.totalMineralVolumeValue));
        $.each(data.totalMineralUsage, function(i, item){
            $(".totalReq" + i).text(numberFormat(item));
            $(".totalHave" + i).text(numberFormat(data.have[i]))
            
            if ( item > data.have[i]) {
                $(".totalHave" + i).attr("class", "expense");
            } else {
                $(".totalHave" + i).attr("class", "income");
            }
	       $(".totalValue"+i).text(numberFormat(data.totalMineralValue[i]) + ' ISK');

        });
        <?php endif; ?>
    }
    
    function formProcess(event){
      event.preventDefault();
      me = $("#me").val();
      amount = $("#amount").val();
      custom_prices = $("#custom_prices").val();
      $.post("<?php echo site_url('manufacturing/update/'.$blueprintID);?>", {me: me, amount: amount, custom_prices: custom_prices},loadResults, "json");
    }
});
</script>
<table width="100%">
    <tr>
        <th colspan="5">
            <a id="fb_item" href="<?php echo site_url('/fancybox/item/'.$product->typeID); ?>">
                <img src="<?php echo get_icon_url($product, 64); ?>" align="left">
            </a>
            <?php echo $product->typeName; ?>
        </th>
    </tr>
    <tr>
        <th colspan="5">
        <span>
            <form action="<?php echo site_url('manufacturing/update/'.$blueprintID); ?>" method="post" id="bpForm">
            ME: <input type="text" name="me" id="me" value="0" size="1" />
            Amount: <input type="text" name="amount" id="amount" value="1" size="1">
            <!-- Use Custom Mineral Prices: <?php echo form_checkbox('custom_prices', 'accept', False); ?> -->
            <?php echo form_submit('Submit', 'Submit'); ?>
            </form>
        </span>
        </th>
    </tr>
    <tr>
        <th colspan="2">Type</th>
        <th>Requires</th>
        <th>Available</th>
	    <th>Purchase Value</th>
    </tr>
<?php foreach($data as $r): ?>
    <tr>
        <td width="32"><img src="<?php echo get_icon_url($r, 32); ?>"></td>
        <td style="text-align: left">
        <?php if(is_numeric($r['isPart'])): ?>
        <a href="<?php echo site_url('manufacturing/detail/'.$r['isPart']); ?>"><?php echo $r['typeName']; ?></a>        <?php else: ?>
        <?php echo $r['typeName']; ?></td>
        <?php endif; ?>
        <td><p class="req<?php echo $r['typeID'];?>"></p></td>
        <td><p class="have<?php echo $r['typeID'];?>"></p></td>
	    <td><p class="value<?php echo $r['typeID'];?>"></p></td>
    </tr>
<?php endforeach; ?>
	<tr>
		<th colspan="2">Total Volume:</th>
        <td><span id="totalVolume"></span> m&sup3;</td>
        <th>Total Value:</th>
	   <td><span id="totalValue"></span> ISK</td>
	</tr>
<?php if (count($totalMineralUsage) > 0): ?>
    <tr>
        <th colspan="5">Total Mineral Usage</th>
    </tr>
    <tr>
        <th colspan="2">Type</th>
        <th>Requires</th>
        <th>Have</th>
        <th>Purchase Value</th>
    </tr>
    <?php foreach ($totalMineralUsage as $k => $v):?>
    <tr>        
        <td width="32"><img src="<?php echo get_icon_url($v, 32); ?>"></td>
        <td style="text-align: left"><?php echo get_inv_type($k)->typeName; ?></td>
        <td><p class="totalReq<?php echo $k;?>"></p></td>
        <td><p class="totalHave<?php echo $k;?>"></p></td>
	    <td><p class="totalValue<?php echo $k;?>"></p></td>
    </tr>        
    <?php endforeach;?>
	<tr>
		<th colspan="2">Total Volume:</th>
        <td><span id="totalMineralVolume"></span> m&sup3;</td>
        <th>Total Value:</td>
        <td><span id="totalMineralVolumeValue"></span> ISK</td>
	</tr>
<?php endif;?>
</table>
<?php if (count($skillreq) > 0): ?>
<br />
<p><b>Skill Requirements:</b></p>
<ul>
<?php foreach ($skillreq as $skill): ?>
    <li><?php echo "{$skill['typeName']} level {$skill['level']}"; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<br />
