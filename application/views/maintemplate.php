    <div id="bd">
        <div id="yui-main">
            <div class="yui-b" id="content"><?php print $content; ?></div>
        </div>
        <div class="yui-b" id="nav">
        <?php if(!empty($character)): ?>
        <h2>Information</h2>
        <ul>
            <li><a id="nav" href="<?php echo site_url('/transactions/index/'.$character);?>">Transaction List</a>
            <li><a id="nav" href="<?php echo site_url('/wallet/journal/'.$character);?>">Wallet Journal</a>
            <li><a id="nav" href="<?php echo site_url('/assets/index/'.$character);?>">Assets</a>
            <li><a id="nav" href="<?php echo site_url('/materials/index/'.$character);?>">Materials</a>
            <li><a id="nav" href="<?php echo site_url('/industry/jobs/'.$character);?>">Industry Jobs</a>
            <li><a id="nav" href="<?php echo site_url('/market/orders/'.$character);?>">Market Orders</a>
        </ul>
        <h2>Tools</h2>
        <ul>
            <li><a id="nav" href="<?php echo site_url('/production/index/'.$character);?>">Production</a>
        </ul>
        <br />
        <?php else: ?>&nbsp;<?php endif; ?>
        <br />
        <h2>Configuration</h2>
        <ul>
            <li><?php echo anchor('overview/config', 'Options', array('id' => 'nav')); ?>
            <li><?php echo anchor('apikeys/add', 'Add Character', array('id' => 'nav')); ?>
            <li><?php echo anchor('user/logout', 'Logout', array('id' => 'nav')); ?>
        </ul>
        </div>
    </div>
</div>
<div id="ft"><a id="ft" href="mailto:claus@beerta.de">claus@beerta.de</a> &bull; Copyright &copy; 2008 Claus Beerta &bull; <?php echo $this->benchmark->elapsed_time(); ?>s</div>
</body>
</html>
