<div id="content">

<!-- TABLE TERROR INCOMING !!! WORST HTML EEEEEVAAAAAAAAAAAR RRR -->

<table summary="Character Info">
<tbody>
  <tr>
    <td valign="top" width="258">
        <?php echo get_character_portrait($characterID, 256); ?>
    </td>
    <td valign="top">
	<table class="dataTable" summary="Character Info">
      <tbody>
        <tr>
          <th colspan="2">Info</th>
          <th colspan="2">Attributes</th>
          <th colspan="2">Skills At Level</th>
        </tr>
        <tr>
          <td class="dataTableCell">Charactername:</td>
          <td class="left"><?php echo $name; ?></td>
          <td class="dataTableCell">Intelligence:</td>
          <td class="left"><?php echo $attributes['intelligence']; ?></td>
          <td class="dataTableCell">1:</td><td><?php echo $skills_at_level[1]; ?> (<?php echo number_format(100.0 / $skills_total * $skills_at_level[1], 1); ?>%)</td>
        </tr>
        <tr>
          <td class="dataTableCell">Corporation:</td>
          <td class="left"><?php echo $corporationName; ?></td>
          <td class="dataTableCell">Perception:</td>
          <td class="left"><?php echo $attributes['perception']; ?></td>
          <td class="dataTableCell">2:</td><td><?php echo $skills_at_level[2]; ?> (<?php echo number_format(100.0 / $skills_total * $skills_at_level[2], 1); ?>%)</td>
        </tr>
        <tr>
          <td class="dataTableCell">Race / Blood line:</td>
          <td class="left"><?php echo $race.' / '.$bloodLine;?></td>
          <td class="dataTableCell">Memory:</td>
          <td class="left"><?php echo $attributes['memory']?></td>
          <td class="dataTableCell">3:</td><td><?php echo $skills_at_level[3]; ?> (<?php echo number_format(100.0 / $skills_total * $skills_at_level[3], 1); ?>%)</td>
        </tr>
        <tr>
          <td class="dataTableCell">Total Cash:</td>
          <td class="left"><?php echo number_format($balance);?> ISK</td>
          <td class="dataTableCell">Charisma</td>
          <td class="left"><?php echo $attributes['charisma']?></td>
          <td class="dataTableCell">4:</td><td><?php echo $skills_at_level[4]; ?> (<?php echo number_format(100.0 / $skills_total * $skills_at_level[4], 1); ?>%)</td>
        </tr>
        <tr>
          <td class="dataTableCell">Skill Points:</td>
          <td class="left"><?php echo number_format($skillpoints_total);?></td>
          <td class="dataTableCell">Willpower:</td>
          <td class="left"><?php echo $attributes['willpower'];?></td>
          <td class="dataTableCell">5:</td><td><?php echo $skills_at_level[5]; ?> (<?php echo number_format(100.0 / $skills_total * $skills_at_level[5], 1); ?>%)</td>
        </tr>
        <tr>
          <td class="dataTableCell">Skills Total:</td>
          <td colspan="5" class="left"><?php echo $skills_total; ?></td>
        </tr>
        <tr>
          <td colspan="6" class="dataTableCell">&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableCell">Clone:</td>
          <?php if ($skillpoints_total > $cloneSkillPoints): ?>
          <td colspan="5" id="expense"><?php echo $cloneName.' ('.number_format($cloneSkillPoints).')';?></td>
          <?php else: ?>
          <td colspan="5" class="left"><?php echo $cloneName.' ('.number_format($cloneSkillPoints).')';?></td>
          <?php endif; ?>
        </tr>
        <?php if (!empty($corporationTitles)): ?>
        <tr>
          <td class="dataTableCell">Roles:</td>
          <td colspan="5" class="left">
          <?php foreach ($corporationTitles as $v): ?>
          <?php echo $v['titleName'].', '; ?>  
          <?php endforeach; ?>
          </td>
        </tr>
        <?php endif; ?>
        <?php if ($isTraining): ?>
        <tr>
          <td class="dataTableCell">Currently training:</td>
          <td class="left" colspan="5"><?php echo $trainingTypeName; ?>
            <?php echo roman($trainingToLevel); ?>
          </td>
        </tr>
        <tr>
          <td class="dataTableCell">Finishes in:</td>
          <td colspan="5" class="left"><?php echo api_time_to_complete((string) $trainingEndTime); ?></td>
        </tr>
        <?php endif; ?>
      </tbody>
      </table>
    </td>
  </tr>
  <?php if (count($queue) > 0): ?>
  <tr>
	<td style="text-align: left;" valign="top" colspan="2">
		<h4>Training Queue:</h4>
		<?php foreach ($queue as $entry): ?>
		<div>
			<div style="float: right;margin-top:4px;"><img alt="level<?php echo $entry['level']; ?>" src="/files/images/level<?php echo $entry['level'];?>_q.gif" /></div>
			<div style="line-height: 1.45em; font-size: 11px;">
			  <?php echo $entry['typeName']; ?> / Rank <?php echo $entry['rank']; ?>
                <div>
                  <div style="line-height: 1.5em;margin-left:12px;font-size:11px">
                    <div>
                      <span class="navdot">&#xB7;</span><span>Training to: </span>
                      <strong>Level <?php echo $entry['level']; ?></strong> - 
					  <span>Started: </span>
                      <?php echo api_time_print($entry['startTime']); ?> - 
					  <span>Ending: </span>
                      <?php echo api_time_print($entry['endTime']); ?> - 
					  <span>Time left: </span>
                      <?php echo api_time_to_complete($entry['endTime']); ?>
                    </div>
                  </div>
                </div>
			</div>
		</div>
		<?php endforeach; ?>
	</td>
  </tr>
  <?php endif; ?>
</tbody>
</table>

<?php foreach ($groups as $k => $v): ?>
<div style="margin-top: 50px; margin-bottom: -24px; margin-right: 10px;">
    <div style="margin-top: 10px;">
        <div style="border-top: 1px solid rgb(67, 67, 67); border-bottom: 1px solid rgb(67, 67, 67); background: rgb(44, 44, 56) url(/files/images/<?php echo $k;?>.jpg) no-repeat scroll 74px 5px; margin-bottom: 10px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; height: 21px;"></div>
        <img alt="<?php echo $k;?>" src="/files/images/<?php echo $k; ?>.png" style="border: 0px none ; width: 64px; height: 64px; top: -52px;" class="newsTitleImage" />
        <div style="margin-left: 82px;">
        <?php foreach ($v['skills'] as $skillID): ?>
            <div style="border-top: 1px dotted rgb(34, 85, 85); padding-right: 10px;">
                <div style="float: right;margin-top:4px;">
                    <img align="right" src="/files/images/level<?php if ($skillID == $trainingTypeID) { echo ($skills[$skillID]->level+1).'_act'; } else { echo $skills[$skillID]->level; }?>.gif" />
                </div>
                <div style="line-height: 1.45em; font-size: 11px;">
                  <?php echo $skills[$skillID]->typeName; ?> / <i>Rank <?php echo $skills[$skillID]->rank; ?></i> / <i>SP: <?php echo number_format($skills[$skillID]->skillpoints); ?><!--  of <?php #echo number_format(250000*$skill['rank']); ?>--></i>
                </div>
                <?php if ($skillID == $trainingTypeID): ?>
                <div>
                  <div style="line-height: 1.5em;margin-left:12px;font-size:11px">
                    <div>
                      <span class="navdot">&#xB7;</span><span>Currently training to: </span>
                      <strong>Level <?php echo $trainingToLevel; ?></strong>
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span>Started: </span>
                      <?php echo api_time_print($trainingStartTime); ?>
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span>Ending: </span>
                      <?php echo api_time_print($trainingEndTime); ?>
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span>Time left: </span>
                      <?php echo api_time_print($trainingEndTime); ?>
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

</div>
