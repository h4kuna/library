$(function () {
	// vhodně nastylovaný div vložím po načtení stránky
	$('<div id="ajax-spinner"></div>').appendTo("body").ajaxStop(function () {
		$(this).hide(500);
	}).hide();
});


$("a.ajax").live("click", function (event) {
	event.preventDefault();
	$.get(this.href);
	$("#ajax-spinner").show();
});