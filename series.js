jQuery(document).ready(function(){
    $btns = jQuery('.show-all');
    $btns.parent().nextAll().css('display','none');
    $btns.toggle(function(){
        var $btn = jQuery(this);
        $btn.parent().nextAll().show();
    },function(){
        var $btn = jQuery(this);
        $btn.parent().nextAll().hide();
    });
});