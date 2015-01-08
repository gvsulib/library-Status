jQuery.fn.slideToggleBoolean = function(show){
	if(show){
		$(this).slideDown();
	}
	else{
		$(this).slideUp();
	}
}

function updateLoginForm(){
	var chk = jQuery("input:radio:checked");
	if (chk.length > 0){
		chk = chk.val() == 'true';
		jQuery("#login-native-login-wrapper").slideToggleBoolean(chk);
		jQuery("#login-non-native-login-wrapper").slideToggleBoolean(!chk);
	}
}

jQuery(document).ready(function(){
	jQuery('abbr').on('mouseenter mouseout', 
		function(e){
			e.preventDefault();
			var text = "Hover over a (?) for a description of the field.";
			if (e.type == 'mouseenter'){
				text = jQuery(this).data('text');
			}
			jQuery('#help').text(text);
		}
		);
	if (jQuery('form')){
		var form = jQuery('form');
		form.find('input').not('#show').addClass('required');
		form.validate();
	}
	jQuery('button').click(function(){
		if (this.value == -1){
			jQuery('.required').removeClass('required');
		}
		jQuery('#go').val(this.value).parent().submit();
	});
	jQuery("#show").click(function(){
		jQuery(".pw").prop('type', this.checked ? 'text' : 'password');
	});
});