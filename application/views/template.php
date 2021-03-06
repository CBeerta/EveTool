<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Perfect Blemish     
Description: A two-column, fixed-width design with dark color scheme.
Version    : 1.0
Released   : 20100729

-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>evetool&sup2; - <?php echo $page_title; echo isset($page_sub_title) ? ' - '.$page_sub_title : ''?></title>
<link href="/files/eve.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo site_url('/files/jquery.js');?>"></script>
<script type="text/javascript" src="<?php echo site_url('/files/fancybox/jquery.fancybox-1.2.5.pack.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo site_url('/files/fancybox/jquery.fancybox-1.2.5.css');?>">
<script type="text/javascript" src="<?php echo site_url('/files/eve.js');?>"></script>
<script type="text/javascript" src="<?php echo site_url('/files/sorttable.js');?>"></script>

</head>
<body>
<div id="wrapper">
	<div id="header-wrapper">
		<div id="header">
			<div id="logo">
				<h1><a href="#"><span>eve</span>tool&sup2;</a></h1>
			</div>
			<div id="menu">
				<ul>
					<?php foreach (array('Social', 'Characters', 'Market', 'Industry', 'Assets') as $v):?>
					<li<?php if ($page_title == $v): ?> class="current_page_item"<?php endif;?>><a href="<?php echo site_url(strtolower("{$v}/index"));?>"><?php echo $v;?></a></li>
					<?php endforeach;?>
				</ul>
			</div>
		</div>
	</div>
	<!-- end #header -->
	<div id="page">
		<!--div id="content"-->
			<?php echo $content;?>
		<!--/div-->
		<!-- end #content -->
		<div id="sidebar">
			<?php echo isset($sidebar) ? $sidebar : '';	?>
			
			<?php if (!empty($submenu)):?>
			<ul>
			    <?php if (isset($search)): ?>
				<li>
					<h2><?php echo $search->header;?></h2>
					<div id="search" >
						<?php echo form_open($search->url); ?>
							<div>
								<input type="text" name="s" id="search-text" value="" />
								<input type="submit" id="search-submit" value="GO" />
							</div>
						<?php echo form_close(); ?>
					</div>
					<div style="clear: both;">&nbsp;</div>
				</li>
				<?php endif; ?>
			<?php foreach ($submenu as $title => $options):?>
				<li>
					<h2><?php echo $title;?></h2>
					<ul>
						<?php foreach ($options as $k => $v):?>
						<li><a href="<?php echo site_url(strtolower("{$page_title}")."/{$k}");?>"><?php echo $v;?></a></li>
						<?php endforeach;?>
					</ul>
				</li>	
			<?php endforeach; ?>			
			</ul>
			<?php endif;?>
		</div>
		<!-- end #sidebar -->
		<div style="clear: both;">&nbsp;</div>
	</div>
	<!-- end #page -->
</div>
<div id="footer">
	<p>Copyright (c) 2010 Claus Beerta. All rights reserved. Design by <a href="http://www.freecsstemplates.org/"> CSS Templates</a>.</p>
</div>
<!-- end #footer -->
</body>
</html>
