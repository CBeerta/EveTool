<script type="text/javascript">
$(document).ready(function(){
    $("#bpForm").ajaxComplete(function(request, settings){
      $("#bpSpinner").hide();
    });
    $("#bpForm").ajaxStart(function(request, settings){
      $("#bpSpinner").show();
    });
    $.getJSON("<?php echo site_url('production/t1/update/'.$character.'/'.$blueprintID); ?>", loadResults);
    $("#bpForm").submit(formProcess);
    
    function loadResults(data) {
        $("#me").val(data.me);
        $("#totalVolume").text(numberFormat(data.totalVolume));
 	$("#totalValue").text(numberFormat(data.totalValue));
 	$.each(data.req, function(i, item){
            $(".req" + i).text(numberFormat(item));
            $(".have" + i).text(numberFormat(data.have[i]))
            if ( item > data.have[i]) {
                $(".have" + i).css({color:"red"});
            } else {
                $(".have" + i).css({color: $("td").css("color")});
            }
	    $(".value"+i).text(numberFormat(data.value[i])+ ' (' + numberFormat(data.price[i]) +')');
            
        });
        <?php if (count($totalMineralUsage) > 0): ?>
        $("#totalMineralVolume").text(numberFormat(data.totalMineralVolume));
        $.each(data.totalMineralUsage, function(i, item){
            $(".totalReq" + i).text(numberFormat(item));
            $(".totalHave" + i).text(numberFormat(data.have[i]))
            if ( item > data.have[i]) {
                $(".totalHave" + i).css({color:"red"});
            } else {
                $(".totalHave" + i).css({color: $("td").css("color")});
            }
	     $(".value"+i).text(numberFormat(data.value[i]));

        });
        <?php endif; ?>
    }
    
    function formProcess(event){
      event.preventDefault();
      me = $("#me").val();
      amount = $("#amount").val();
      $.post("<?php echo site_url('production/t1/update/'.$character.'/'.$blueprintID);?>", {me: me, amount: amount},loadResults, "json");
    }
});
</script>
<table width="100%">
    <tr>
        <th colspan="5"><?php echo $product->typeName; ?></th>
    </tr>
    <tr>
        <td colspan="5" style="text-align: left;">
            <img src="<?php echo getIconUrl($product->typeID, 128); ?>" align="left">
            <p style="padding-left: 140px;"><?php echo nl2br($product->description); ?></p>
        </td>
    </tr>
    <tr>
        <th colspan="5">
        <span>
            <img style="padding-left: 20px;" id="bpSpinner" align="left" src="<?php echo site_url('/files/spinner-light.gif'); ?>">
            <form action="<?php echo site_url('production/t1/update/'.$blueprintID); ?>" method="post" id="bpForm">
            ME: <input type="text" name="me" id="me" value="0" size="1" />
            Amount: <input type="text" name="amount" id="amount" value="1" size="1">
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
        <td width="32"><img src="<?php echo getIconUrl($r['typeID'], 32); ?>"></td>
        <td style="text-align: left">
        <?php if(is_numeric($r['isPart'])): ?>
        <a href="<?php echo site_url('production/t1/detail/'.$character.'/'.$r['isPart']); ?>"><?php echo $r['typeName']; ?></a>        <?php else: ?>
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
        <th colspan="3">Type</th>
        <th>Requires</th>
        <th>Have</th>
    </tr>
    <?php foreach ($totalMineralUsage as $k => $v):?>
    <tr>        
        <td width="32"><img src="<?php echo getIconUrl($k, 32); ?>"></td>
        <td colspan="2" style="text-align: left"><?php echo getInvType($k)->typeName; ?></td>
        <td><p class="totalReq<?php echo $k;?>"></p></td>
        <td><p class="totalHave<?php echo $k;?>"></p></td>
	<td><p class="totalValue<?php echo $k;?>"></p></td>
    </tr>        
    <?php endforeach;?>
	<tr>
		<th colspan="3">Total Volume:</th>
        <td><span id="totalMineralVolume"></span> m&sup3;</td>
        <td>&nbsp;</td>
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
