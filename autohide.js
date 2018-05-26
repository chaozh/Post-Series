jQuery(document).ready(function($) {
    var $autolink = $('a.autohide');
    var $series = $autolink.parents('.post-series-title').next();
    $series.hide();
    $autolink.click(function() {
        $series.toggle();
        $(this).children().first().toggleClass('icon-plus-square-o icon-minus-square-o');
    });
});