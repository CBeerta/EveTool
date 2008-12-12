<div>
<?php foreach ($groupIDList as $item): ?>
&#149; <a href="<?php echo site_url('materials/index/'.$item['groupID'].'/'.$character);?>"><?php echo $item['groupName']; ?></a>
<?php endforeach; ?>
<!-- &#149; <a href="<?php echo site_url('materials/index/'.$character);?>?categoryID=25">Raw Minerals</a>  -->
</div>
<hr />
<table width="100%">
<caption><?php echo $caption; ?></caption>
<tr>
	<th colspan="3">Name</th>
    <th>Amount</th>
    <th>Volume</th>
    <th colspan="2">Sell Price</th>
    <th colspan="2">Buy Price</th>
</tr>
<?php foreach ($data as $k => $v): ?>
<tr>
	<td style="text-align: left;" colspan="2"><img src="<?php echo getIconUrl($k,32);?>"></td>
    <td style="text-align: left;"><?php echo $v['typeName'];?></td>
    <td><?php echo number_format($v['quantity']); ?></td>
    <td><?php echo number_format($v['volume']*$v['quantity']); ?> m&sup3;</td>
    <td width="5"><i><?php echo number_format($prices[$k]['sell']['median'], 2); ?></i></td>
    <td><?php echo number_format($prices[$k]['sell']['avg']*$v['quantity'], 2); ?> ISK</td>
    <td width="5"><i><?php echo number_format($prices[$k]['buy']['median'], 2); ?></i></td>
    <td><?php echo number_format($prices[$k]['buy']['avg']*$v['quantity'], 2); ?> ISK</td>
<tr>
<?php endforeach; ?>
<th colspan="4">Sum:</td>
    <td><?php echo number_format($sums['volume']); ?> m&sup3;</td>
    <td colspan="2"><?php echo number_format($sums['sellprice'], 2); ?> ISK</td>
    <td colspan="2"><?php echo number_format($sums['buyprice'], 2); ?> ISK</td>
</tr>
</table>
<span style="font-size: 77%">Assets are not updated on this Page</span>
