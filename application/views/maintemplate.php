    <div id="bd">
        <div id="yui-main">
            <div class="yui-b" id="content"><?php print $content; ?></div>
        </div>
        <div class="yui-b" id="nav">
		<h2><a href="<?php echo site_url();?>">Home</a></h2>
        <?php if(!empty($character)): ?>
        <h2>Information</h2>
        <ul>
            <li><a href="<?php echo site_url("/overview/skilltree/");?>">Skilltree</a>
            <li><a href="<?php echo site_url("/transactions/index/");?>">Transaction List</a>
            <li><a href="<?php echo site_url("/wallet/dailyjournal/");?>">Wallet Journal</a>
            <li><a href="<?php echo site_url("/assets/index");?>">Assets</a>
            <ul class="submenu">
                <li><a href="<?php echo site_url("/materials/index/18");?>">Materials</a>
                <li><a href="<?php echo site_url("/assets/blueprints/");?>">Blueprints</a>
                <li><a href="<?php echo site_url("/assets/ships/");?>">Ships</a>
            </ul>
            <li><a href="<?php echo site_url("/industry/jobs/");?>">Industry Jobs</a>
            <li><a href="<?php echo site_url("/market/orders/");?>">Market Orders</a>
            <li><a href="<?php echo site_url("/charstandings/agents/");?>">Agents</a>
        </ul>
		<?php if ($has_corpapi_access): ?>
        <h2>Corporation</h2>
        <ul>
            <li><a href="<?php echo site_url("/corp/memberlist/");?>">Memberlist</a>
	        <li><a href="<?php echo site_url("/corp/wallet/");?>">Wallet Journal</a>
	        <li><a href="<?php echo site_url("/corp/transactions/");?>">Transaction List</a>
        </ul>
		<?php endif;?>
        <h2>T1 Production</h2>
        <ul>
            <li><a href="<?php echo site_url("/production/t1/index/");?>#ships">Ships</a>
            <li><a href="<?php echo site_url("/production/t1/index/");?>#modules">Modules</a>
            <li><a href="<?php echo site_url("/production/t1/index/");?>#charges">Ammunition</a>
            <li><a href="<?php echo site_url("/production/t1/index/");?>#drones">Drones</a>
        </ul>
        <?php endif; ?>
        <br />
        <h2>Configuration</h2>
        <ul>
            <li><?php echo anchor('overview/config', 'Options'); ?>
            <li><?php echo anchor('apikeys/add', 'Add Character'); ?>
            <li><?php echo anchor('user/logout', 'Logout'); ?>
        </ul>
        </div>
    </div>
</div>
<div id="ft"><a id="ft" href="mailto:claus@beerta.de">claus@beerta.de</a> &bull; Copyright &copy; 2008 Claus Beerta &bull; <?php echo $this->benchmark->elapsed_time(); ?>s &bull; <?php echo date('r'); ?></div>
<!--
    $Id$
-->
</body>
</html>