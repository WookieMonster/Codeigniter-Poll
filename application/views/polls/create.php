<?php echo doctype('html5'); ?>

<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Poll Listing</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<?php echo link_tag($base_styles); ?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="<?php echo base_url('res/js/jQuery.WMAdaptiveInputs.js'); ?>"></script>
	<script type="text/javascript" charset="utf-8">
		$(function(){
			$('#poll_options').WMAdaptiveInputs({
				minOptions: '<?php echo $min_options; ?>',
				maxOptions: '<?php echo $max_options; ?>',
				inputNameAttr: 'options[]',
				inputClassAttr: 'btn_remove'
			});
			$('form.adpt_inputs_form').each(function(){
				$this = $(this);
				$this.find('button[name="adpt_submit"]').on('click', function(event){
					event.preventDefault();
					var str = $this.serialize();
					$.post('<?php echo site_url('poll/create'); ?>', str, function(response){
						var jsonObj = $.parseJSON(response);
						if (jsonObj.fail == false){
							window.location.replace("<?php echo site_url('poll'); ?>");
						}else{
							$this.find('.adpt_errors').html(jsonObj.error_messages).hide().slideDown();
						}
					});
				});
			});
		});
	</script>
</head>
<body>
<div id="container">
	<div id="content" role="main">
		<h2><?php echo $title; ?></h2>
		<p><?php echo anchor('', 'Back to poll listing'); ?></p>
		<?php echo form_open('poll/create', array('class' => 'adpt_inputs_form')); ?>
		<ul class="adpt_errors"></ul>
		<dl>
			<dt>Title:</dt>
			<dd><?php echo form_input(array('name' => 'title', 'id' => 'title', 'class' => 'txt_input', 'value' => set_value('title'))); ?></dd>
			<?php echo form_error('title'); ?>
		</dl>
		<div id="poll_options" class="adpt_inputs">
			<p>Options:</p>
			<ol class="adpt_inputs_list"></ol>
			<p><a href="#" class="adpt_add_option btn_add">Add option</a></p>
		</div>
		<?php echo form_error('options[]'); ?>
		<p><button type="submit" name="adpt_submit">Create New Poll</button></p>
		<?php echo form_close(); ?>
	</div>
</div>
</body>
</html>