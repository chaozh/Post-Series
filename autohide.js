jQuery(document).ready(function($) {
    $('a.autohide').click(function() {
		$(this).parent().next().toggle();
		$(this).children().first().toggleClass('icon-plus-square-o icon-minus-square-o');
	});
});