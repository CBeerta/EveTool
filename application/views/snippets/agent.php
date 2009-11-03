<table style="min-width: 600px;">
<tr>
    <?php if ($chars_standing !== False): ?>
    <td bgcolor="<?php echo ($agent->required_standing <= $chars_standing) ? 'green' : 'red'; ?>">
        &nbsp;
    </td>
    <?php endif; ?>
    <td width="50" valign="top" align="center">
        <div style="font-size: 300%;"><?php echo $agent->level; ?></div>
	    <div style="padding-left:3px"><b>Q <?echo $agent->quality; ?></b></div>
	</td>
	<td valign="top" style="padding-left:5px" id="left">
	    <b>Corporation</b>: <?php echo $agent->corpName; ?> / <?php echo $agent->division; ?> <br>
	    <b>Faction</b>: <?php echo $agent->faction; ?> -<b> Region</b>: <?php echo $agent->region; ?> - <b>System</b>: <a target="_blank" href="http://evemaps.dotlan.net/system/<?php echo $agent->systemName; ?>"><img title="Open with Dotlan" src="<?php echo site_url("files/images/map.png"); ?>"></a> <?php echo $agent->systemName; ?> (<font color="<?php echo $agent->security_color; ?>"><?php echo $agent->security;?></font>)<br>

	    <b>Station</b>: <?php echo $agent->station; ?><br>
	    <b>Required Standing</b>: <?php echo $agent->required_standing; ?> - <b>Type</b>: <?php print_r(preg_replace("|(\p{Lu})|", ' $1', $agent->agentType));?><br>
	</td>
	<td>
	    <img src="<?php echo "/files/cache/char/{$agent->itemID}/64/char.jpg"; ?>">
	</td>
</tr>
</table>
