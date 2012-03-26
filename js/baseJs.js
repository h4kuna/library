
/**
 * @deprecated
 * vlkadat do onclick pac to jinak nemaka ve firefoxu
 */
function confirmDelete(delUrl, message) {
	if(!message) {
		message = 'Opravdu si přejete mazat?';
	}
	if (confirm(message)) {
		document.location = delUrl;
	}

	return false;
}

/**
 * funkce zobrazi a schova flash message
 */
function flashMessage ()
{
	var flash = $('#flash');

	flash.css({
		'position': 'fixed',
		'left': '20%',
		'right': '20%',
		'top': '6%',
		'height': 'auto'
	}).animate({
		opacity: 0.9
	});

	$('a#close').attr('href', '#').click(function() {
		flash.stop().fadeOut('fast');
	});

	flash.delay(10000).fadeOut('slow');
}


/**
 * funkce spouštěné po načtení prohlížeče
 */
$(document).ready(function(){
	//zpravicky
	flashMessage();

	//vlozeni kurzoru
	if(!$(':focus').length) {
		$('.cursor').focus();
	}

	//mazani s potvrzenim
	$('a[delete]').click(function(){
		$this = $(this);
		message = $this.attr('message');
		if(!message) {
			message = 'Opravdu si přejete mazat?';
		}

		if (confirm(message)) {
			$this.attr('href', $this.attr('delete'));
			return true;
		}

		return false;
	});

	//placholder
	jQuery.support.placeholder = false;
	test = document.createElement('input');
	if('placeholder' in test) {
		jQuery.support.placeholder = true;
	}

	if(!$.support.placeholder) {
		$('[placeholder]').focus(function () {
			$this = $(this);
			placeholder = $this.attr('placeholder');

			if($this.attr('type') == 'text') {
				if (placeholder != '' && $this.val() == placeholder) {
					$this.val('').removeClass('hasPlaceholder');
				}
			}

		}).blur(function () {
			$this = $(this);
			placeholder = $this.attr('placeholder');

			switch($this.attr('type')) {
				case 'password':
					if(placeholder != '' && $this.val() == '')
					{
						$this.after( $('<input type="text">').val($this.attr('placeholder')).attr('_id', $this.attr('id')).bind('focus', function(){
							$('#'+$(this).attr('_id')).css({'display': 'inline-block'}).focus();
							$(this).remove();
						})).css({
							'display': 'none'
						});
					}
					break;
				default:
					if (placeholder != '' && ($this.val() == '' || $this.val() == placeholder)) {
						$this.val($this.attr('placeholder')).addClass('hasPlaceholder');
					}
					break;
			}
		}).blur();
	}

	//chrome autofill
	if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0) {
		$('input:-webkit-autofill').each(function(k, v){
			$(v).css({
				'background-color': 'transparent'
			});
		});
	}

})
