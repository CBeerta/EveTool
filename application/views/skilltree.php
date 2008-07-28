<table summary="Character Info">
<tbody>
  <tr>
    <td valign="top">
      <img style="border: 1px solid gray;" alt="<?php echo $character; ?>" src="/files/cache/char/<?php echo $charinfo['characterID'];?>/256/char.jpg" />
    </td>
    <td valign="top"><table class="dataTable" border="0" cellpadding="2" cellspacing="0" width="460" summary="Character Info">
      <tbody>
        <tr>
          <th colspan="2">Info</th>
          <th colspan="2">Attributes</th>
        </tr>
        <tr>
          <td class="dataTableCell">Charactername</td>
          <td class="dataTableCell"><?php echo $character; ?></td>
          <td class="dataTableCell">Intelligence</td>
          <td class="dataTableCell" align="center"><?php echo $attributes['intelligence']; ?></td>
        </tr>
        <tr>
          <td class="dataTableCell">Corporation</td>
          <td class="dataTableCell"><?php echo $charinfo['corporationName']; ?></td>
          <td class="dataTableCell">Perception</td>
          <td class="dataTableCell" align="center"><?php echo $attributes['perception']; ?></td>
        </tr>
        <tr>
          <td class="dataTableCell">Total Cash</td>
          <td class="dataTableCell"><?php echo number_format($charinfo['balance']);?> ISK</td>
          <td class="dataTableCell">Charisma</td>
          <td class="dataTableCell" align="center"><?php echo $attributes['charisma']?></td>
        </tr>
        <tr>
          <td class="dataTableCell">Total skill points</td>
          <td class="dataTableCell"><?php echo number_format($skillPointsTotal);?></td>
          <td class="dataTableCell">Willpower</td>
          <td class="dataTableCell" align="center"><?php echo $attributes['willpower'];?></td>
        </tr>
        <tr>
          <td class="dataTableCell">Race / Blood line</td>
          <td class="dataTableCell"><?php echo $charinfo['race'].' / '.$charinfo['bloodLine'];?></td>
          <td class="dataTableCell">Memory</td>
          <td class="dataTableCell" align="center"><?php echo $attributes['memory']?></td>
        </tr>
        <tr>
          <td colspan="4" class="dataTableCell"></td>
        </tr>
        <?php if (!empty($training['skillInTraining'])): ?>
        <tr>
          <td colspan="2" class="dataTableCell">Currently training</td>
          <td style="font-weight: bold;" class="dataTableCell"><?php echo $training['trainingTypeName']; ?></td>
          <td style="text-align: center;" class="dataTableCell"><img alt="<?php echo $training['trainingToLevel']; ?>" src="/files/images/level<?php echo $training['trainingToLevel']; ?>_act.gif" /></td>
        </tr>
        <tr>
          <td colspan="2" class="dataTableCell">Finishes in</td>
          <td colspan="2"><?php echo timeToComplete($training['trainingEndTime']); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="4" class="dataTableCell"></td>
        </tr>
      </tbody>
      </table>
    </td>
  </tr>
</tbody>
</table>

<?php foreach ($skillTree as $k => $v): ?>
<div style="margin-top: 50px; margin-bottom: -24px; margin-right: 10px;">
    <div style="margin-top: 10px;">
        <div style="border-top: 1px solid rgb(67, 67, 67); border-bottom: 1px solid rgb(67, 67, 67); background: rgb(44, 44, 56) url(/files/images/<?php echo $k;?>.jpg) no-repeat scroll 74px 5px; margin-bottom: 10px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; height: 21px;"></div>
        <img alt="<?php echo $k;?>" src="/files/images/<?php echo $k; ?>.png" style="border: 0px none ; width: 64px; height: 64px; top: -52px;" class="newsTitleImage" />
        <div style="margin-left: 82px;">
        <?php foreach ($v['skills'] as $skill): ?>
            <div style="border-top: 1px dotted rgb(34, 85, 85); padding-right: 10px;">
                <div style="float: right;margin-top:4px;"><img alt="level<?php echo $skill['level']; ?>" src="/files/images/level<?php if ($skill['typeID'] == $training['trainingTypeID']) { echo ($skill['level']+1).'_act'; } else { echo $skill['level']; }?>.gif" /></div>
                <div style="line-height: 1.45em; font-size: 11px;">
                  <?php echo $skill['typeName']; ?> / <i>Rank <?php echo $skill['rank']; ?></i> / <i>SP: <?php echo number_format($skill['skillpoints']); ?><!--  of <?php echo number_format(250000*$skill['rank']); ?>--></i>
                </div>
                <?php if ($skill['typeID'] == $training['trainingTypeID']): ?>
                <div>
                  <div style="line-height: 1.5em;margin-left:12px;font-size:11px">
                    <div>
                      <span class="navdot">&#xB7;</span><span>Currently training to: </span>
                      <strong>Level <?php echo $training['trainingToLevel']; ?></strong>
                    </div>
                    <!--
                    <div>
                     <span class="navdot">&#xB7;</span><span>SP done: </span>
                      <strong>[$skill.skillpoints] of [$skill.skilllevel5]</strong>
                    </div>
                    -->
                    <div>
                      <span class="navdot">&#xB7;</span><span>Started: </span>
                      <?php echo apiTimePrettyPrint($training['trainingStartTime']); ?>
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span>Ending: </span>
                      <?php echo apiTimePrettyPrint($training['trainingEndTime']); ?>
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span>Time left: </span>
                      <?php echo timeToComplete($training['trainingEndTime']); ?>
                    </div>
                  </div>
                </div>
                <?php endif;?>
            </div>
        <?php endforeach; ?>
        </div>
        <div style="line-height: 1.45em; margin-left: 82px; font-size: 11px;">
          <br /><span><span class="navdot">&bull;</span> <strong><?php echo $v['skillCount']; ?></strong> skills trained, for a total of <strong><?php echo number_format($v['groupSP']); ?></strong> Skillpoints.</span>
        </div>
    </div>
</div>
<?php endforeach; //skillTree ?>