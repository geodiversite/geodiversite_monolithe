(function($){
	$(function(){
		
		// Faux placeholder pour le formulaire de recherche
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
		
		// Bloc dépliable pour code_embed, pompé sur mediaspip http://zone.spip.org/trac/spip-zone/browser/_squelettes_/mediaspip/mediaspip_core/trunk/javascript/mediaspip_base.js#L9
		$('.code_embed').each(function(){
			var parent = $(this).parents('div.embed');
			if(parent.find('span.title').size() > 0){
				var id_code = parent.find('textarea').attr('id');
				parent.find('.formulaire_embed_code').hide();
				parent.find('span.title').append('<span class="show_code show_code_plus">&nbsp;+</span>').wrapInner('<a class="show_code" href="#'+id_code+'"></a></span>');
			}
			$('.show_code').click(function(){
				var parent = $(this).parents('div.embed');
				if(parent.find('.formulaire_embed_code').is(':hidden')){
					parent.find('.formulaire_embed_code').slideDown("normal");
					parent.find('span.show_code').html('&nbsp;-').removeClass('show_code_plus').addClass('show_code_moins');
				}else{
					parent.find('.formulaire_embed_code').slideUp("normal");
					parent.find('span.show_code').removeClass('show_code_moins').addClass('show_code_plus').html('&nbsp;+');
				}
				return false;
			});
		});
		$('.copypaste').click(function(){
			$(this).focus().select();
		});

	});
})(jQuery);