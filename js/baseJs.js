/**
 * funkce zobrazi a schova flash message
 */
function flashMessage (id) {
    if(!id) {
        id = 'flash';
    }
    $flash = $('#'+id);
    $('div', $flash).click(function(){
        $(this).fadeOut('fast');
    });

    $flash.delay(7000).fadeOut('slow');
}


//mazani s potvrzenim
function confirmDelete(selector) {
    $(selector).click(function(){
        $this = $(this);
        message = $this.attr('title');
        if(!message) {
            message = 'Opravdu si přejete mazat?';
        }

        if (confirm(message)) {
            $this.attr('href', $this.attr('delete'));
            return true;
        }

        return false;
    });
}
/**
 * funkce spouštěné po načtení prohlížeče
 */
$(document).ready(function(){

    flashMessage();

    //vlozeni kurzoru
    if(!$(':focus').length) {
        $('.cursor:eq(0)').focus();
    }

    //mazani s potvrzenim
    confirmDelete('a[delete]');

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
});
