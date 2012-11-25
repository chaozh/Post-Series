<?php 

/**
 * Add the PostSeries TinyMCE plugin to the TinyMCE plugins list
 * 
 * @param object $plugin_array The TinyMCE options array
 * 
 * @return object $plugin_array The modified TinyMCE options array
 */
function  series_add_tinymce_plugin( $plugin_array ) {
    $plugin_array['series'] =  SERIES_URL .'/inc/editor-plugin.js';
    return $plugin_array;
}
/**
 * Add the Series button to the TinyMCE interface
 * 
 * @param object $buttons An array of buttons for the TinyMCE interface
 * 
 * @return object $buttons The modified array of TinyMCE buttons
 */
function series_register_button( $buttons ) {
    array_push( $buttons, "separator", SERIES );
    return $buttons;
}

/**
 * Create the modal window dialog box for the TinyMCE plugin
 * 
 * @uses series_load()
 * @uses series_dir()
 */
function  series_tinymce_plugin_dialog() {
    // Only load the necessary scripts and render the modal window dialog box if the user is on the post/page editing admin pages
    if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) {
        $series =  get_terms( SERIES );
        include( SERIES_ROOT. '/inc/tinymce-plugin-dialog.php'  );
    }
}
add_action( 'admin_footer', 'series_tinymce_plugin_dialog' );
/**
 * Setup TinyMCE button
 * 
 * @uses wp_register_style()
 * @uses current_user_can()
 * @uses get_user_option()
 * @uses wp_enqueue_script()
 * @uses wp_enqueue_style()
 */
function series_addbuttons() {
    // Setup the stylesheet to use for the modal window interaction
    wp_register_style( 'series-dialog-styles', SERIES_URL. '/inc/series-dialog.css');

    // Return false if the user does not have WYSIWYG editing privileges
    if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
        return false;
    }
    
    // Add buttons to TinyMCE editor if user can edit with WYSIWYG editor
    if ( 'true' == get_user_option( 'rich_editing' ) ) {
        add_filter( 'mce_external_plugins', 'series_add_tinymce_plugin' );
        add_filter( 'mce_buttons', 'series_register_button' );
    }

    // Only load the necessary scripts if the user is on the post/page editing admin pages
    if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) {
        wp_enqueue_style('jquery-ui-dialog', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_script( 'jquery-ui-dialog' );
        
        wp_enqueue_style( 'series-dialog-styles' );
        wp_enqueue_script( 'series-dialog', SERIES_URL . '/inc/series-dialog.js' , array('jquery-ui-dialog'), '0.2', true );
        $translation_array = array( 
            'title' => __( 'Insert Series' , SERIES_BASE) 
        );
        wp_localize_script( 'series-dialog', 'trans', $translation_array );
        
    }
}
add_action( 'admin_init', 'series_addbuttons' );

/**
* Display or retrieve the HTML dropdown list of series.
*
* This is directly taken from wp_dropdown_categories in WordPress.
* I am unable to just create a wrapper because wp_dropdown_categories, although custom taxonomy aware,
* it will only use the term_id as the VALUE for each option (as of WP3.0) 
* and they query_var WordPress expects for non-heirarchal taxonomies is the slug not the term_id.
* Hence the requirement to make sure the values are the slug for the series.
*
* All arguments descriptions can be obtained from wp_dropdown_categories
*
*/
function series_custom_manage_posts_filter() {
    global $typenow;
    $post_types = series_posttype_support();
	if ( in_array($typenow, $post_types) ) {
        $series_name = '';
    	if (isset($_GET[SERIES])) $series_name = $_GET[SERIES];
    	
        wp_dropdown_categories(array(
            'show_option_all' => __('View all series', SERIES_BASE),
            'taxonomy' => SERIES,
            'name' => SERIES,
            'orderby' => 'term_order',
            'selected' => $series_name,
            'hierarchical' => true,
            'show_count' => false,
            'hide_empty' => true
        ));
     }
}
add_action('restrict_manage_posts', 'series_custom_manage_posts_filter');

function series_custom_convert_restrict($query) {
    global $pagenow;
    if ($pagenow=='edit.php') {
        $var = &$query->query_vars[SERIES];
        if ( isset($var) ) {
            $term = get_term_by('id',$var,SERIES);
            $var = $term->slug;
        }
    }
    return $query;
}
add_filter('parse_query','series_custom_convert_restrict');

add_action('admin_init', 'series_load_custom_column_actions', 10);
add_action('admin_init', 'series_load_custom_column_filters', 10);

function series_load_custom_column_actions() {
	//support for custom post types
	$posttypes = series_posttype_support();
	foreach ( $posttypes as $posttype ) {
		$action_ref = ( $posttype == 'post' ) ? 'manage_posts_custom_column' : 'manage_' . $posttype . 'posts_custom_column';
		$action_ref = ( $posttype == 'page' ) ? 'manage_pages_custom_column' : $action_ref;
		add_action($action_ref,'series_custom_column_action', 12, 2);	
	}
}

function series_custom_column_action( $column_name,$post_id ) {
    if ($column_name == SERIES) {
        $series = get_the_terms($post_id, SERIES);
        if (is_array($series)) {
            foreach($series as $key => $series_term) {
                $edit_link = get_term_link($series_term, SERIES);
                $series[$key] = '<a href="'.$edit_link.'">' . $series_term->name . '('. $series_term->count .')'.'</a>';
            }
            //echo implode("<br/>",$businesses);
            echo join(__( ', ' ), $series);
        }
    }
}


function series_load_custom_column_filters() {
	//support for custom post types
	$posttypes = series_posttype_support();
	foreach ( $posttypes as $posttype ) {
		$filter_ref = "manage_{$posttype}s_columns";
		add_filter($filter_ref, 'series_custom_column_filter');
	}
}

function series_custom_column_filter($defaults) {
	$post_types = series_posttype_support();
	if ( isset($_REQUEST['post_type']) && !in_array($_REQUEST['post_type'], $post_types) )
		return $defaults; //get out we only want this showing up on post post types for now.*/
	$defaults[SERIES] = __('Series', SERIES_BASE);
	return $defaults;
}


//admin settings
	
/**
 * series_set_options_cap()
 * 
 * @param mixed $capability
 * @return manage_options
 */
function series_set_options_cap() {    
    return 'manage_options';
}

/**
 * Setup options menu page
 * menu slug as series_settings
 * 
 * @use add_options_page
 */
function series_menu() {
	add_options_page( __( 'Post Series Settings', SERIES_BASE ), __( 'Post Series', SERIES_BASE ), series_set_options_cap(),  SERIES.'_settings', 'series_settings_page' );	
}
add_action( 'admin_menu', 'series_menu' );

/**
 * Register options group name and database slug for Post Series
 * Register settings section on menu slug series_settings
 * Register settings fild on section id series
 * 
 * @use register_settting
 * @use add_settings_section
 * @use add_settings_field
 */
function series_register_settings() {
    register_setting( SERIES.'_options', SERIES.'_options', 'series_settings_validate' );
    add_settings_section(SERIES,  __( 'Configure Post Series', SERIES_BASE ), 'series_section_text', SERIES.'_settings');
    
    add_settings_field('title_format',  __( 'Title Format', SERIES_BASE), 'series_title_format', SERIES.'_settings', SERIES);
    add_settings_field('class_prefix',  __( 'CSS Class Prefix', SERIES_BASE), 'series_class_prefix', SERIES.'_settings', SERIES);
    add_settings_field('series_wrap',  __( 'Series Wrap Element', SERIES_BASE), 'series_series_wrap', SERIES.'_settings', SERIES);
    add_settings_field('title_wrap',  __( 'Title Wrap Element', SERIES_BASE), 'series_title_wrap', SERIES.'_settings', SERIES);
    add_settings_field('show_future',  __( 'Show Unpublished', SERIES_BASE), 'series_show_future', SERIES.'_settings', SERIES);
    add_settings_field('auto_display', __('Auto Show Series On Post', SERIES_BASE), 'series_auto_display', SERIES.'_settings', SERIES);
    //add_settings_field('custom_archives', __('Use Custom Template for Series Archives', SERIES_BASE), 'series_custom_archives', SERIES.'_settings', SERIES);
}
add_action('admin_init', 'series_register_settings');

/**
 * series_settings_validate()
 * 
 * @return
 */
function series_settings_validate($series_input) {
    $series_options = get_option( SERIES . '_options' );

	$series_options['title_format'] = trim( $series_input['title_format'] );
	
	$series_options['series_wrap'] = trim( $series_input['series_wrap'] );

	if ( !preg_match( '/^[a-z]{4,20}$/i', $series_options['series_wrap'] ) ) {

		$series_options['series_wrap'] = 'section';

	}
	
	$series_options['title_wrap'] = trim( $series_input['title_wrap'] );

	if ( !preg_match( '/^[a-z]{4,20}$/i', $series_options['title_wrap'] ) ) {

		$series_options['title_wrap'] = 'h3';

	}
	
	$series_options['class_prefix'] = trim( $series_input['class_prefix'] );

	if ( !preg_match( '/^[_a-zA-Z0-9-]{2,20}$/i', $series_options['class_prefix'] ) ) {

		$series_options['class_prefix'] = 'post-series';

	}
    
    $series_options['show_future'] = trim( $series_input['show_future'] );

	if ( !preg_match( '/^on|off$/i', $series_options['show_future'] ) ) {

		$series_options['show_future'] = 'on';

	}
    
    $series_options['auto_display'] = trim( $series_input['auto_display'] );

	if ( !preg_match( '/^on|off$/i', $series_options['auto_display'] ) ) {

		$series_options['auto_display'] = 'off';

	}
    /*
    $series_options['custom_archives'] = trim( $series_input['custom_archives'] );

	if ( !preg_match( '/^on|off$/i', $series_options['custom_archives'] ) ) {

		$series_options['custom_archives'] = 'off';

	}*/

	return $series_options;
}

/**
 * Render Post Series Settings page
 * Use settings fields series_options(option_group) and all sections series_settings(menu slug[page])
 * 
 * @use settings_fields
 * @use do_settings_sections
 */
function series_settings_page(){
?>        
<div class="wrap">
	
	<?php screen_icon(); ?>
	<h2><?php _e( 'Post Series Settings', SERIES_BASE ); ?></h2>

	<form action="options.php" method="post">
		<?php // Adds options to settings page				
		settings_fields( SERIES. '_options' );				
		do_settings_sections(  SERIES.'_settings' );
		?>
		
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php _e( 'Save Changes') ?>" />
		</p>
				
	</form>
	
	<h3><?php _e( 'Add Series to your Post or Page', SERIES_BASE ); ?></h3>	
	<p><?php printf( __ ( 'Use %1$s to add it to your Post or Page content, or use the Post Series Widget.', SERIES_BASE), "<code>[series]</code>" )?></p>
	
</div><!-- .wrap -->
<?php

}

function series_section_text() {
	echo "<p>". __( 'Set up your series using the options below.', SERIES_BASE ) ."</p>";

}
//render field
function series_title_format() {		
	$series_title_format = __( 'title format', SERIES_BASE );
	$series_options = get_option( SERIES . '_options' );

	echo "<input id='title_format' name='".SERIES."_options[title_format]' size='40' type='text' value='{$series_options['title_format']}' /> $series_title_format";
}

function series_class_prefix() {		
	$series_class_prefix = __( 'class prefix', SERIES_BASE );
	$series_options = get_option( SERIES . '_options' );

	echo "<input id='class_prefix' name='".SERIES."_options[class_prefix]' size='20' type='text' value='{$series_options['class_prefix']}' /> $series_class_prefix";
}

function series_series_wrap() {		
	$series_series_wrap = __( 'series wrap', SERIES_BASE );
	$series_options = get_option( SERIES . '_options' );

	echo "<input id='series_wrap' name='".SERIES."_options[series_wrap]' size='20' type='text' value='{$series_options['series_wrap']}' /> $series_series_wrap";
}

function series_title_wrap() {		
	$series_title_wrap = __( 'title wrap', SERIES_BASE );
	$series_options = get_option( SERIES . '_options' );

	echo "<input id='title_wrap' name='".SERIES."_options[title_wrap]' size='20' type='text' value='{$series_options['title_wrap']}' /> $series_title_wrap";
}

function series_show_future() {		
	$series_options = get_option( SERIES . '_options' );
?>
    
    <input id="future_on" type="radio" name="<?php echo SERIES. '_options[show_future]'; ?>" value="on" <?php checked( $series_options['show_future'], 'on' ); ?> />
    <label for="future_on" class="future-on-label"><?php _e('on',SERIES_BASE);?></label>
    <input id="future_off" type="radio" name="<?php echo SERIES. '_options[show_future]'; ?>" value="off" <?php checked( $series_options['show_future'], 'off' ); ?> />
    <label for="future_off" class="future-off-label"><?php _e('off',SERIES_BASE);?></label>
<?php
    
}

function series_auto_display() {
  $series_options = get_option( SERIES . '_options' );
?>
    
    <input id="auto_on" type="radio" name="<?php echo SERIES. '_options[auto_display]'; ?>" value="on" <?php checked( $series_options['auto_display'], 'on' ); ?> />
    <label for="auto_on" class="auto-on-label"><?php _e('on',SERIES_BASE);?></label>
    <input id="auto_off" type="radio" name="<?php echo SERIES. '_options[auto_display]'; ?>" value="off" <?php checked( $series_options['auto_display'], 'off' ); ?> />
    <label for="auto_off" class="auto-off-label"><?php _e('off',SERIES_BASE);?></label>
<?php
    
}

/*
function series_custom_archives() {
  $series_options = get_option( SERIES . '_options' );
?>
    
    <input id="custom_on" type="radio" name="<?php echo SERIES. '_options[custom_archives]'; ?>" value="on" <?php checked( $series_options['custom_archives'], 'on' ); ?> />
    <label for="custom_on" class="custom-on-label"><?php _e('use custom',SERIES_BASE);?></label>
    <input id="custom_off" type="radio" name="<?php echo SERIES. '_options[custom_archives]'; ?>" value="off" <?php checked( $series_options['custom_archives'], 'off' ); ?> />
    <label for="custom_off" class="custom-off-label"><?php _e('use plugin default',SERIES_BASE);?></label>
<?php
    
}
*/

?>