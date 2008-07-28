    <div id="bd">
        <div id="yui-main">
            <div class="yui-b" id="content"><?php print $content; ?></div>
        </div>
        <div class="yui-b" id="nav">
        <?php if(!empty($character)): ?>
        <h2>Information</h2>
        <ul>
            <li><a id="nav" href="/eve/transactions/index/<?php echo $character;?>">Transaction List</a>
            <li><a id="nav" href="/eve/wallet/journal/<?php echo $character;?>">Wallet Journal</a>
            <li><a id="nav" href="/eve/assets/index/<?php echo $character;?>">Assets</a>
            <li><a id="nav" href="/eve/materials/index/<?php echo $character;?>">Materials</a>
            <li><a id="nav" href="/eve/industry/jobs/<?php echo $character;?>">Industry Jobs</a>
            <li><a id="nav" href="/eve/market/orders/<?php echo $character;?>">Market Orders</a>
        </ul>
        <h2>Tools</h2>
        <ul>
            <li><a id="nav" href="/eve/production/index/<?php echo $character;?>">Production</a>
        </ul>
        <br />
        <?php else: ?>&nbsp;<?php endif; ?>
        <br />
        <h2>Configuration</h2>
        <ul>
            <li><?php echo anchor('eve/overview/config', 'Options', array('id' => 'nav')); ?>
            <li><?php echo anchor('eve/apikeys/add', 'Add Character', array('id' => 'nav')); ?>
            <li><?php echo anchor('eve/user/logout', 'Logout', array('id' => 'nav')); ?>
        </ul>
        </div>
    </div>
</div>
<div id="ft">
    <a id="ft" href="mailto:claus@beerta.de">claus@beerta.de</a> &bull; Copyright &copy; 2008 Claus Beerta &bull; <?php echo $this->benchmark->elapsed_time(); ?>s
</div>

</body>
</html>
