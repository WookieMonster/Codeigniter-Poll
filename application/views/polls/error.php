<?php echo doctype('html5'); ?>

<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Poll Listing</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<?php echo link_tag($base_styles); ?>
</head>
<body>
<div id="container">
	<div id="content" role="main">
		<h2><?php echo $title; ?></h2>
		<p><?php echo anchor('', 'Back to poll listing'); ?></p>
		<p><?php echo $error_message; ?></p>
	</div>
</div>
</body>
</html>