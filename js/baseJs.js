
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

})
