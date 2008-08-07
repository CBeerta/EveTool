<table width="100%">
<caption>Player Owned T1 Blueprints</caption>
<tr>
    <th colspan="2">Name</th>
    <th>Location</th>
</tr>
<?php foreach($blueprints as $bp): ?>
<tr>
    <td><img width="32" height="32" src="<?php echo getIconUrl($bp['typeID'],32);?>"></td>
    <td style="text-align: left;">
        <a href="<?php echo site_url('/production/t'.$bp['techLevel'].'/detail/'.$character.'/'.$bp['typeID']);?>"><?php echo $bp['typeName']; ?></a>
    </td>
    <td><?php echo locationIDToName($bp['locationID']); ?></td>
</tr>
<?php endforeach; ?>
</table>
