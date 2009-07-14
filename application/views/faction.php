<p>
<?php echo nl2br($faction->description);?>
</p>
<br />
<table cellspacing="1" cellpadding="1" width="100%">
<caption>Corporations belonging to the "<?php echo $faction->factionName; ?>" Faction.</caption>
<?php foreach ( $corps as $row):?>
    <tr>
        <td>
            <?php echo anchor("/agents/npccorp/{$row->corporationID}", $row->itemName);?>
        </td>
    </tr>
<?php endforeach;?>    
</table>