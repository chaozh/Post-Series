<?php
/**
 * TinyMCE plugin dialog markup
 * 
 * @package Series
 * @subpackage Series for WordPress
 * 
 * @author chaozh
 * @version 1.4.1
 * 
 * @uses series_show_future()
 */
?>
<div id="series_tinymce_dialog">
	<p><?php _e('Choose a Series from the list below to embed in your post:',SERIES_BASE);?></p>
    <?php if( isset( $series ) && !empty( $series ) ): ?>
    <table id="series_table" class="serie-table widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th class="manage-column" scope="col"><?php _e('Name:',SERIES_BASE);?></th>
			<th class="manage-column" scope="col"><?php _e('Count:',SERIES_BASE);?></th>
		</tr>
	</thead>
	<tbody>
	<?php $alternate = 0; ?>
	<?php foreach( $series as $serie ): ?>
    <tr id="series_id_<?php echo $serie->term_id; ?>" class="author-self status-publish iedit<?php echo ( $alternate & 1 ) ? ' alternate' : ''; ?>" valign="top">
			<td class="column-title">
				<?php echo $serie->name; ?>
			</td>
			<td class="column-count"><?php echo $serie->count; ?></td>
		</tr>
		<?php $alternate++; ?>
	<?php endforeach; ?>
    </tbody>
  </table> 
  <?php endif; ?>
	<table class="serie-form widefat fixed">
    <tbody>
		<tr class="series-title">
			<th scope="row" class="label">
				<span class="alignleft"><label for="series_title"><?php _e('Title:',SERIES_BASE);?></label></span>
			</th>
			<td class="field"><input id="series_title" name="series-title" value="" type="text"/></td>
		</tr>
		
		<tr class="series-limit">
			<th scope="row" class="label">
                <span class="alignleft"><label for="series_limit"><?php _e('Limit show:',SERIES_BASE);?></label></span>
            </th>
			<td class="field"><input id="series_limit" name="series-limit" value="" type="text"/></td>
		</tr>
        
        <tr class="series-future">
			<th scope="row" class="label">
                <span class="alignleft"><label for="series_future"><?php _e('Future show:',SERIES_BASE);?></label></span>
            </th>
			<td class="field">
                <?php series_show_future(); ?>
			</td>
		</tr>
        
    </tbody>
    </table>
    
    <div class="ui-dialog-buttonset">
         <button type="button" id="series_insert_btn" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php _e('Insert', SERIES_BASE); ?></span>
        </button>
        <button type="button" id="series_cancel_btn" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php _e('Cancel', SERIES_BASE); ?></span>
        </button>
    </div>

</div>