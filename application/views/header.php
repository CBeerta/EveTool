<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Eve Tool - <?php echo ucfirst($tool).' - '.$character; ?></title>
    <!-- css --> 
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css"> 
    <!-- <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/container/assets/skins/sam/container.css">  -->
    <link rel="stylesheet" type="text/css" href="<?php echo site_url('/files/eve.css');?>">
    <!-- js --> 
    <!-- <script type="text/javascript" src="http://yui.yahooapis.com/combo?2.5.2/build/yahoo-dom-event/yahoo-dom-event.js&2.5.2/build/container/container-min.js"></script> -->
    <script type="text/javascript" src="<?php echo site_url('/files/jquery.js');?>"></script>
    <!-- fancybox -->
    <script type="text/javascript" src="<?php echo site_url('/files/fancybox/jquery.fancybox-1.2.5.pack.js');?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo site_url('/files/fancybox/jquery.fancybox-1.2.5.css');?>">
    
    <script type="text/javascript" src="<?php echo site_url('/files/eve.js');?>"></script>
    <script type="text/javascript" src="<?php echo site_url('/files/sorttable.js');?>"></script>
</head>
<?php if (!igb_trusted()): ?>
<body onload="CCPEVE.requestTrust('<?php echo site_url();?>')">
<?php else: ?>
<body>
<?php endif; ?>
<div id="doc3" class="yui-t4">
    <div id="hd">
        <div class="yui-g">
            <div class="yui-u first"><h1 id="hd" title="This Thingy needs a better Name!"><img src="<?php echo site_url("/files/images/logo.png");?>"></h1></div>
            <div class="yui-u" align="right">
                <p>
                    <?php foreach (array_keys($chars) as $char): ?>
					<a href="<?php echo "{$base_url}/{$char}"; ?>">
					<img title="<?php echo $char; ?>" id="hd" style=" <?php if ($character == $char) { echo 'border: 2px solid #a1a;'; } ?> background: url(<?php echo site_url("/files/cache/char/{$chars[$char]['charid']}/64/char.jpg");?>" src="<?php echo site_url("/files/images/overlay-64.png"); ?>">
                    </a>
                    <?php endforeach; ?>
                </p>
            </div>
        </div>
    </div>
    <div id="ajax_loading_thingy" style="display: none;">Loading ...</div>
