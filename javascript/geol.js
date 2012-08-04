(function($){
	$(function(){
		$('#formulaire_recherche label').css({'position':'absolute','left':'5px','top':'0'});
		if ($('input#recherche').val().length == 0) {
			$('input#recherche').focusin(function(){
				$('#formulaire_recherche label').fadeOut(1000);
			});
			$('input#recherche').focusout(function(){
				$('#formulaire_recherche label').fadeIn(1000);
			});
		} else {
			$('#formulaire_recherche label').hide();
		}

		// Menu depliant de navigation de programmer.spip.org
		
		$('.categories li:not(.on) ul').hide();
		$('.categories li a').hover(function(){
			var me=this;
			var time=400;
			// un temps plus long pour refermer !
			if ($(me).parent().find('>ul').is(':visible')) {
				time=1200;
			}
			
			$(me).addClass('hop');
			setTimeout(function(){
				// verifier que la souris n'est pas deja partie !
				if ($(me).hasClass('hop')) {
					var parent = $(me).parent();
					// verifier que ce n'est pas une liste exposee
					if (!$(parent).hasClass('on')) {
						// fermer les ul
						var ul = $(parent).find('>ul');
						if ($(ul).is(':visible')) {
							$(ul).find('li:not(.on) ul').hide();
							$(ul).slideUp('fast').parent('li').removeClass('deplie');
							$(ul).children('li.deplie').removeClass('deplie');
						// ou ouvrir le premier
						} else {
							$(ul).slideDown('fast').parent('li').addClass('deplie');
						}
					}
				}
			}, time);
		},function(){
			$(this).removeClass('hop');
		});

	});
})(jQuery);