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
		<p><?php echo anchor('poll/create', 'Create new poll'); ?> | <?php echo anchor('poll/data', 'View data'); ?></p>
		<?php if ($results === FALSE) { ?>
			<div class="poll">
				<p>There are no polls.</p>
			</div>
		<?php } else { ?>
		<?php foreach ($results as $row) { ?>
			<div class="poll">
				<h3><?php echo anchor("poll/view/{$row['poll_id']}", $row['title']); ?></h3>
				<dl class="options">
					<?php foreach ($row['options'] as $option) { ?>
						<dt><?php echo $option['title']; ?>  <span class="vote_count">(<?php echo $option['votes']; ?>)</span></dt>
						<dd><span class="poll_bg"><span class="poll_bar" style="width: <?php echo $option['percentage']; ?>%"></span></span></dd>
						<dd><?php echo anchor("poll/vote/{$row['poll_id']}/{$option['option_id']}", 'Vote', array('class' => 'btn_add')); ?></dd>
					<?php } ?>
				</dl>
				<p><?php echo anchor("poll/delete/{$row['poll_id']}", 'Delete this poll', array('class' => 'btn_delete')); ?>
				<?php if ($row['closed']) { ?>
				<?php echo anchor("poll/open/{$row['poll_id']}", 'Open poll', array('class' => 'btn_normal')); ?>
				<?php } else { ?>
				<?php echo anchor("poll/close/{$row['poll_id']}", 'Close poll', array('class' => 'btn_normal')); ?>
				<?php } ?>
				</p>
			</div>
		<?php } ?>
		<?php } ?>
		<p><?php echo $paging_links; ?></p>
	</div>
</div>
</body>
</html>