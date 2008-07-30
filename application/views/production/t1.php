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
        <th colspan="2">Type</th>
        <th>Requires</th>
        <th colspan="2">Available</th>
    </tr>
<?php foreach($data as $r): ?>
    <tr>
        <td width="32"><img src="<?php echo getIconUrl($r['typeID'], 32); ?>"></td>
        <td style="text-align: left"><?php echo $r['typeName']; ?></td>
        <td><?php echo number_format($r['requires']); ?></td>
        <td><?php 
            if ($r['requires'] > $r['available'])
                echo '<font color="red">'.number_format($r['available']).'</font>'; 
            else 
                echo number_format($r['available']);
            ?></td>
        <td width="16">(<?php print floor($r['available']/$r['requires']); ?>)</td>
    </tr>
<?php endforeach; ?>
</table>
<?php if (isset($skillreq)): ?>
<br />
<p><b>Skill Requirements:</b></p>
<ul>
<?php foreach ($skillreq as $skill): ?>
    <li><?php echo $skill->typeName; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<br />
