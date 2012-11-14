(function($){
	$(function(){
		$('#formulaire_recherche label').css({'position':'absolute','left':'5px','top':'0'});
		if ($('input#recherche').length > 0 && $('input#recherche').val().length == 0) {
			$('input#recherche').focusin(function(){
				$('#formulaire_recherche label').fadeOut(1000);
			});
			$('input#recherche').focusout(function(){
				$('#formulaire_recherche label').fadeIn(1000);
			});
		} else {
			$('#formulaire_recherche label').hide();
		}
	});
})(jQuery);