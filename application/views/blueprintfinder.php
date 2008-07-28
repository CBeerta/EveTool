<table width="100%">
<caption>Blueprints</caption>
<tr>
    <th colspan="2">Name</th>
    <th>Location</th>
</tr>
<?php foreach($blueprints as $bp): ?>
<tr>
    <td><img width="32" height="32" src="<?php echo getIconUrl($bp['typeID'],32);?>"></td>
    <td style="text-align: left;">
        <a href="<?php echo site_url('/production/t'.$bp['techLevel'].'/'.$character);?>?blueprintID=<?php echo $bp['typeID'];?>"><?php echo $bp['typeName']; ?></a>
    </td>
    <td><?php echo locationIDToName($bp['locationID']); ?></td>
</tr>
<?php endforeach; ?>
</table>
