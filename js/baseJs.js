function confirmDelete(delUrl, message) {
	if(!message) {
		message = 'Opravdu si přejete mazat?';
	}

	if (confirm(message)) {
		document.location = delUrl;
		return true;
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


function confirmDialog(delUrl, message) {
	if(!message)
		message = 'Opravdu to chcete smazat?';
	if (confirm(message)) {
		document.location = delUrl;
	}
}


/**
 * funkce spouštěné po načtení prohlížeče
 */
$(document).ready(function(){
	//zpravicky
	flashMessage();

	//vlozeni kurzoru
	$('.cursor').focus();

	$.datepicker.setDefaults($.datepicker.regional['cs']);
	$.datepicker.setDefaults({'firstDay': 1, 'duration': 'fast', 'minDate': 0, 'dateFormat': 'yy-mm-dd'})
})
