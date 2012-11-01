jQuery(document).ready(function(){
    var $series_tinymce_dialog = jQuery('#series_tinymce_dialog');
    $series_tinymce_dialog.dialog({
        autoOpen: false,
        open: function(){
            if( jQuery('#series_table tbody tr.selected').length == 0  ){
                var $defultChosen = jQuery('#series_table tbody tr:first');
                $defultChosen.addClass('selected');
                jQuery('#series_title').val($defultChosen.find('.column-title').text().trim());
                jQuery('#series_limit').val($defultChosen.find('.column-count').text().trim());
            }
        },
        width: 450,
        height: 'auto',
        draggable: true,
        resizable: false,
        title: trans.title,
        dialogClass: parseInt(jQuery().jquery.split(".")[1]) === 2 ? 'ui-series-2' : 'ui-series'
    }).find('#series_table tbody tr').click(function(event){
        var $chosen = jQuery(this);
        event.preventDefault();
        jQuery('#series_table tbody tr').removeClass('selected');
        jQuery(this).addClass('selected');
        jQuery('#series_title').val($chosen.find('.column-title').text().trim());
        jQuery('#series_limit').val($chosen.find('.column-count').text().trim());
    });
    
    jQuery("#series_insert_btn").click(function(){
        insertSelectedSeries();
    });
    
    jQuery("#series_cancel_btn").click(function(){
        $series_tinymce_dialog.dialog('close');
    });
    
    function insertSelectedSeries(){
        var seriesID = jQuery('#series_table tbody tr.selected')[0].id.split("_")[2];
        var title = jQuery('#series_title').val();
        var limit = jQuery('#series_limit').val();
    
        var shortCodeString = " [series id='" + seriesID + "'";
        if(title.replace(/^\s+|\s+$/g,"") != ""){
            shortCodeString += " title='" + title + "'";
        }
        if(limit.replace(/^\s+|\s+$/g,"") != ""){
            shortCodeString += " limit='" + limit + "'";
        }
        shortCodeString += "] ";
        
        if (typeof(tinyMCE) != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
            ed.focus();
            if (tinymce.isIE) {
                ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
            }
            ed.execCommand('mceInsertContent', false, shortCodeString);
        } else {
            edInsertContent(edCanvas, shortCodeString);
        }
        
        $series_tinymce_dialog.dialog('close');
    }
    
});