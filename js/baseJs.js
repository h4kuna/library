
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

	$('a', flash).attr('href', '#').click(function() {
		flash.stop().fadeOut('fast');
		return false;
	});

	flash.delay(20000).fadeOut('slow');
}


/**
 * funkce spouštěné po načtení prohlížeče
 */
$(document).ready(function(){
	//vlozeni kurzoru
	if(!$(':focus').length) {
		$('.cursor:eq(0)').focus();
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
	if(!('placeholder' in document.createElement('input'))) {
		$('[placeholder]').blur(function () {
			$this = $(this);
			placeholder = $this.attr('placeholder');

			if(placeholder != '' && $this.val() == '')
			{
				$this.after( $('<input class="hasPlaceholder" type="text">')
					.val( $this.attr('placeholder')).attr('_id', $this.attr('id') )
					.bind('focus', function(){
						$this = $(this);
						$('#' + $this.attr('_id')).css({
							'display': 'inline-block'
						}).focus();
						$this.remove();
					})).css( {
					'display': 'none'
				} );
			}
		}).blur();
	}
})
