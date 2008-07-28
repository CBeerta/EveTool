<table width="100%">
    <caption><?php echo $caption;?></caption>
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
<b>Skill Requirements:</b>
<pre>
<?php print_r($skillreq); ?>
</pre>
<?php endif; ?>
