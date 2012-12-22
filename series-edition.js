jQuery(document).ready( function() {

	jQuery("#posts-list").sortable({
		connectWith: '.series-sortable',
		placeholder: 'series-placeholder',
		revert: true
	}).disableSelection();
    
    jQuery(".series").sortable({
		connectWith: '#posts-list',
		revert: true,
		placeholder: 'series-placeholder',
		update: function () {
			var serieID = jQuery(this).attr('id');
			var orderList = jQuery('ul#' + serieID).sortable('toArray');
			jQuery("div#ajax-wait").show(0);
			jQuery("div#ajax-response").hide(0);

			jQuery.ajax({
				type: "POST",
				url: seriesEdition.ajax_url,
				cache: false,
				data: {
					action: seriesEdition.UpdateSeries,
					serie: serieID,
					post: orderList,
					series_nonce: seriesEdition.nonce
				},
				success: function(msg) {
					jQuery("div#ajax-wait").hide(0);
					code_msg = msg.split('|');
					jQuery("div#ajax-response").removeClass().addClass((code_msg[0]>0) ? 'error' : 'updated').show(0).html('<p>' + code_msg[1] + '</p>');
					if (code_msg[0] > 0) {
						jQuery('#posts-list').sortable('cancel');
						jQuery(this).sortable('cancel');
					}
				} // End of success
			}); // End of jQuery Index
			// return (false);
		} // End of update
	}).disableSelection();
    
    var id = "#" + seriesEdition.class_prefix + "-add-submit";
    jQuery(id).click(function(){
        jQuery.ajax({
			type: "POST",
			url: seriesEdition.ajax_url,
			cache: false,
			data: {
				action: seriesEdition.AddSerie,
                name: jQuery("#tag-name").val(),
				slug: jQuery("#tag-slug").val(),
                parent : jQuery("#parent").val(),
				description: jQuery("#tag-description").val(),
				series_nonce: seriesEdition.nonce
			},
			success: function(msg) {
				jQuery("div#ajax-wait").hide(0);
				code_msg = msg.split('|');
				jQuery("div#ajax-response").removeClass().addClass((code_msg[0]>0) ? 'error' : 'updated').show(0).html('<p>' + code_msg[1] + '</p>');
				if (code_msg[0] > 0) {
					jQuery('#posts-list').sortable('cancel');
					jQuery(this).sortable('cancel');
				}else{ //add serie success
                    window.location.reload();//refresh page
				}
			} // End of success
		}); // End of jQuery Index
    });
    
    jQuery(".delete").click(function(){
        var r = true;
        if ( 'undefined' != showNotice )
             r = showNotice.warn();
        if ( r ){
            var tagID = jQuery(this).attr("id");
            jQuery.ajax({
    			type: "POST",
    			url: seriesEdition.ajax_url,
    			cache: false,
    			data: {
    				action: seriesEdition.DeleteSerie,
                    tag_ID: tagID,
    				series_nonce: seriesEdition.nonce
    			},
    			success: function(msg) {
    				jQuery("div#ajax-wait").hide(0);
    				code_msg = msg.split('|');
    				jQuery("div#ajax-response").removeClass().addClass((code_msg[0]>0) ? 'error' : 'updated').show(0).html('<p>' + code_msg[1] + '</p>');
    				if (code_msg[0] > 0) {
    					jQuery('#posts-list').sortable('cancel');
    					jQuery(this).sortable('cancel');
    				}else{ //add serie success
                        window.location.reload();//refresh page
    				}
    			} // End of success
    		}); // End of jQuery Index
        }
    });
    
});