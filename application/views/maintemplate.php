    <div id="bd">
        <div id="yui-main">
            <div class="yui-b" id="content"><?php print $content; ?></div>
        </div>
        <div class="yui-b" id="nav">
		<h2><a href="<?php echo site_url();?>">Home</a></h2>
        <?php if(!empty($character)): ?>
        <h2 class="information menu_slider">Information</h2>
        <ul id="information">
            <li><a href="<?php echo site_url("/character/skilltree/");?>">Skilltree</a>
            <li><a href="<?php echo site_url("/character/ships/");?>">Ship Capabilities</a>
            <li><a href="<?php echo site_url("/transactions/index/");?>">Transaction List</a>
            <li><a href="<?php echo site_url("/wallet/journal/");?>">Wallet Journal</a>
            <ul class="submenu">
                <li><a href="<?php echo site_url("/wallet/dailyjournal/");?>">grouped by Day</a>
            </ul>
            <li><a href="<?php echo site_url("/assets/index");?>">Assets</a>
            <ul class="submenu">
				<form method="post" action="<?php echo site_url("/assets/search");?>" id="assetSearch">
					<li><input type="text" name="search" id="search" id="search" value="Search.." size="14" onfocus="if(this.value == 'Search..') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Search..';}"/>
				</form>
                <li><a href="<?php echo site_url("/materials/index/group/18");?>">Materials</a>
                <li><a href="<?php echo site_url("/assets/blueprints/");?>">Blueprints</a>
                <li><a href="<?php echo site_url("/assets/ships/");?>">Ships</a>
            </ul>
            <li><a href="<?php echo site_url("/industry/jobs/");?>">Industry Jobs</a>
            <li><a href="<?php echo site_url("/market/orders/");?>">Market Orders</a>
            <li><a href="<?php echo site_url("/market/fitting_helper/");?>">Price Checks</a>
            <!-- <li><a href="<?php echo site_url("/market/margintrader/");?>">Margin Trader</a> -->
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
        <h2 class="t1production menu_slider">T1 Production</h2>
        <ul id="t1production"  style="display: none;">
            <li><a href="<?php echo site_url("/manufacturing/index/1");?>#ships">Ships</a>
            <li><a href="<?php echo site_url("/manufacturing/index/1");?>#modules">Modules</a>
            <li><a href="<?php echo site_url("/manufacturing/index/1");?>#charges">Ammunition</a>
            <li><a href="<?php echo site_url("/manufacturing/index/1");?>#drones">Drones</a>
        </ul>
        <h2 class="t2production menu_slider">T2 Production</h2>
        <ul id="t2production" style="display: none;">
            <li><a href="<?php echo site_url("/manufacturing/index/2");?>#ships">Ships</a>
            <li><a href="<?php echo site_url("/manufacturing/index/2");?>#modules">Modules</a>
            <li><a href="<?php echo site_url("/manufacturing/index/2");?>#charges">Ammunition</a>
            <li><a href="<?php echo site_url("/manufacturing/index/2");?>#drones">Drones</a>
        </ul>
        <?php endif; ?>
        <h2 class="configuration menu_slider">Configuration</h2>
        <ul id="configuration" style="display: none;">
            <li><?php echo anchor('overview/config', 'Options'); ?>
            <li><?php echo anchor('apikeys/add', 'Add Character'); ?>
            <li><?php echo anchor('user/logout', 'Logout'); ?>
        </ul>
        </div>
    </div>
</div>
<div id="ft">
    <a id="ft" href="mailto:claus@beerta.de">claus@beerta.de</a> &bull; Copyright &copy; 2008-2009 Claus Beerta &bull; <?php echo $this->benchmark->elapsed_time(); ?>s &bull; <?php echo date('r'); ?><br />
    Design by <a href="http://freecsstemplates.org/">http://freecsstemplates.org/</a>
</div>
<!--
    $Id$
-->
</body>
</html>
