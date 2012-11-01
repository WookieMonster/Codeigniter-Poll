/**
 * Adaptive Inputs
 * 
 * Version: 1.0
 * Author: WookieMonster
 */
(function($){
	$.fn.WMAdaptiveInputs = function(options){
		var settings = $.extend({
			minOptions: 2,
			maxOptions: 5,
			inputNameAttr: 'options[]',
			placeholder: 'Option title...'
		}, options);
		
		return this.each(function(){
			var $this = $(this);
			for (var i = 0; i < settings.minOptions; i++){
				$this.find('.adpt_inputs_list').append('<li><input type="text" name="'+settings.inputNameAttr+'" value="" class="txt_input options" placeholder="'+settings.placeholder+'" /></li>');
			}
			$this.find('.adpt_add_option').on('click', function(event){ 
				event.preventDefault();
				if ($this.find('.adpt_inputs_list li').length < settings.maxOptions){
					$this.find('.adpt_inputs_list').append('<li><input type="text" name="'+settings.inputNameAttr+'" value="" class="txt_input" placeholder="'+settings.placeholder+'" />&nbsp;<a href="#" class="adpt_remove_option btn_delete">Remove option</a></li>');
				}
			});
			$(document).on('click', '.adpt_remove_option', function(event){
				event.preventDefault();
				$(this).parent().remove();
			});
		});
	};
})(jQuery);