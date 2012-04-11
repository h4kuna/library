
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
 * @var input checkbox|radio
 */
function graphicInput(input, path) {
	var img = '<img src="' + path + input +'_0.png" />';
	var isRadio = input == 'radio';
	$('input[type='+ input +']').each(function(k, v){
		var $input = $(v);
		$input.bind('init change', function(event){
			if(event.isTrigger) {
				$input.hide();
			}
			else {
				if(isRadio && event.type != 'init') {
					var id = $input.attr('id');
					$('input[name=' + $input.attr('name') + '][id!='+ id +']', $input.parents('form')).each(function(k, v) {
						$v = $(v);
						$v.next().remove();
						$v.trigger('init');
					});
				}
				$input.next().remove();
			}
			$button = $(img.replace('0', !!$input.attr('checked') + 0)).click(function(){
				$input.click();
			});
			$input.after($button);
		}).trigger('init');
	});
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
	//zpravicky
	flashMessage();

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
