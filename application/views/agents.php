<div>
    <div style="top: 0px; right: 0px; float: right;">
        <table>
        <caption>Filter Options</caption>
        <?php echo form_open("/agents/npccorp/{$corpid}"); ?>
        <tr>
            <td id="left">
                Only Usable:<br />
            <?php if (isset($corpstanding)): ?>
                <?php echo form_checkbox('show_available', 'show_only', $show_available); ?>
            <?php else:?>
                <i>None Available</i>
            <?php endif; ?>
            </td>
            <td id="left">
                Only in Hi-Sec:<br />
                <?php echo form_checkbox('show_hisec', 'hisec_only', $show_hisec); ?>
            </td>
        </tr>
        <tr>
            <td id="left">
                Level: <br />
                <?php echo form_dropdown('level', array(0 => '-', 1 => 1, 2 => 2,3 => 3,4 => 4, 5 => 5), $selected_level, 'style="width: 110px;"'); ?>
            </td>
            <td id="left">
                Division:<br />
                <?php echo form_dropdown('division', $divisions, $selected_division, 'style="width: 110px;"'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" id="left">
                Region: <br />
                <?php echo form_dropdown('region', $regions, $selected_region, 'style="width: 220px;"'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" id="left">
                Corporation: <br />
                <?php echo form_dropdown('corp', $corps, $corpid, 'style="width: 220px;"'); ?>
            </td>    
        </tr>
        <tr>
            <td><a href="<?php echo site_url("/agents/npccorp/{$corpid}"); ?>">Reset Filter</a></td>
            <td><?php echo form_submit('submit', 'Filter'); ?></td>
        </table>
        <?php echo form_close(); ?>
    </div>
        
    <div style="float: left;">
        <?php if ($agents): ?>
        <?php foreach ($agents as $row): ?>
            <h3><?php echo $row->itemName;?></h3>
            <?php echo Agent_Info::agent_snippet($row->itemID, isset($corpstanding) ? $corpstanding : False); ?>
        <?php endforeach; ?>    
        <?php else: ?>
        <br/><h1>No Agents found!</h1>
        <?php endif; ?>
    </div>
</div>
