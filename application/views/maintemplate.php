    <div id="bd">
        <div id="yui-main">
            <div class="yui-b" id="content"><?php print $content; ?></div>
        </div>
        <div class="yui-b" id="nav">
        <ul>
            <li><a href="<?php echo site_url();?>">Home</a>
        </ul>
        <?php if(!empty($character)): ?>
        <h2>Information</h2>
        <ul>
            <li><a href="<?php echo site_url('/transactions/index/'.$character);?>">Transaction List</a>
            <li><a href="<?php echo site_url('/wallet/journal/'.$character);?>">Wallet Journal</a>
            <li><a href="<?php echo site_url('/assets/index/'.$character);?>">Assets</a>
            <ul>
                <li><a href="<?php echo site_url('/materials/index/18/'.$character);?>">Materials</a>
                <li><a href="<?php echo site_url('/materials/blueprints/'.$character);?>">Blueprints</a>
                <li><a href="<?php echo site_url('/materials/ships/'.$character);?>">Ships</a>
            </ul>
            <li><a href="<?php echo site_url('/industry/jobs/'.$character);?>">Industry Jobs</a>
            <li><a href="<?php echo site_url('/market/orders/'.$character);?>">Market Orders</a>
        </ul>
        <h2>T1 Production</h2>
        <ul>
                <li><a href="<?php echo site_url('/production/t1/index/'.$character);?>#ships">Ships</a>
                <li><a href="<?php echo site_url('/production/t1/index/'.$character);?>#modules">Modules</a>
                <li><a href="<?php echo site_url('/production/t1/index/'.$character);?>#charges">Ammunition</a>
                <li><a href="<?php echo site_url('/production/t1/index/'.$character);?>#drones">Drones</a>
        </ul>
        <br />
        <?php else: ?>&nbsp;<?php endif; ?>
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
<div id="ft"><a id="ft" href="mailto:claus@beerta.de">claus@beerta.de</a> &bull; Copyright &copy; 2008 Claus Beerta &bull; <?php echo $this->benchmark->elapsed_time(); ?>s</div>
<!--
    $Id$
-->
</body>
</html>
