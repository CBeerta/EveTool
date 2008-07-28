<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Eve Tool</title>
    <!-- css --> 
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/container/assets/skins/sam/container.css"> 
    <link rel="stylesheet" type="text/css" href="/files/eve.css">
    <!-- js --> 
    <script type="text/javascript" src="http://yui.yahooapis.com/combo?2.5.2/build/yahoo-dom-event/yahoo-dom-event.js&2.5.2/build/container/container-min.js"></script>
    
</head>
<body>
<div id="doc4" class="yui-t4">
    <div id="hd">
        <div class="yui-g">
            <div class="yui-u first"><?php //echo $character;?></div>
            <div class="yui-u">
                <p>
                    <?php foreach (array_keys($chars) as $char): ?>
					<!-- <a href="/eve/<?php echo $tool.'/'.$subtool; ?><?php echo '/'.$char;?>"><img title="<?php echo $char; ?>" src="/files/cache/char/<?php echo $chars[$char]['charid'];?>/64/char.jpg"></a> -->
					<a href="/eve/overview/index/<?php echo $char;?>"><img title="<?php echo $char; ?>" src="/files/cache/char/<?php echo $chars[$char]['charid'];?>/64/char.jpg"></a>
                    <?php endforeach; ?>
                </p>
            </div>
        </div>
    </div>
