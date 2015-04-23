<?php 
require_once(SERIES_ROOT. '/series-edition.php');

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
        wp_enqueue_style('jquery-ui-dialog', SERIES_URL . '/inc/jquery-ui.css');
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

function series_custom_column_filter($defaults) {
	$post_types = series_posttype_support();
	if ( isset($_REQUEST['post_type']) && !in_array($_REQUEST['post_type'], $post_types) )
		return $defaults; //get out we only want this showing up on post post types for now.*/
	$defaults[SERIES] = __('Series', SERIES_BASE);
	return $defaults;
}

function series_custom_column_action( $column_name,$post_id ) {
    global $post;
    if ($column_name == SERIES) {
        $series = get_the_terms($post_id, SERIES);
        if (is_array($series)) {
            foreach($series as $key => $series_term) {
                $edit_link = esc_url( add_query_arg( array( 'post_type' => $post->post_type, SERIES => $series_term->term_id ), 'edit.php' ) );
                $series[$key] = '<a href="'.$edit_link.'">' . $series_term->name . '('. $series_term->count .')'.'</a>';
            }
            //echo implode("<br/>",$businesses);
            echo join(__( ', ' ), $series);
        }
    }
}

function series_load_custom_columns(){
    global $post;
    $post_types = series_posttype_support();
    $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
	switch($post_type){
		case 'post':
		case 'page':
			add_filter('manage_posts_columns', 'series_custom_column_filter' );
			add_action('manage_posts_custom_column', 'series_custom_column_action', 12, 2 );
		break;

		default:
			if (in_array($post_type, $post_types)) {
				add_filter("manage_{$post_type}_posts_columns", 'series_custom_column_filter' );
				add_action("manage_{$post->post_type}_posts_custom_column", 'series_custom_column_action', 12, 2 );
			}
		break;
	} // End of switch $post_type
}
add_action('admin_init', 'series_load_custom_columns', 10);

//2.1 update: settings added action links
function series_plugin_action_links($links, $file) {
    //series_log($file);
    if ( $file == SERIES_BASENAME ) {
        $settings_link = '<a href="options-general.php?page='.SERIES.'_settings">' . __('Settings', SERIES_BASE) . '</a>';
    	$links = array_merge( array( $settings_link ), $links );
    }
	
	return $links;
}
add_filter( 'plugin_action_links', 'series_plugin_action_links', 10, 2 );

// Adds default values for options on settings page
register_activation_hook( SERIES_FILE, 'series_default_options' );
	
function series_default_options() {

	$series_temp = get_option( SERIES.'_options');
	
	if ( !is_array( $series_temp ) || ( $series_temp['series_wrap'] == '' ) ) {

		$series_defaults_args = series_get_default_options();
		update_option( SERIES.'_options', $series_defaults_args );
        
	}
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
    $series_settings_fileds = array(
        array(
            'name' => 'title_format',
            'title'=> __( 'Title Format', SERIES_BASE),
            'desc' => __( '"Title Format" means you can customly define the format and content to display as series title.%current will be replaced by the current posts number in the serie, %count will be replaced by series total post number, %link will be replaced by displaying a url link. These three words started with "%" are special template tokens and rest words are free to use.', SERIES_BASE ),
            'type' => 'text',
            'size' => 40
        ),
        array(
            'name' => 'class_prefix',
            'title'=> __( 'CSS Class Prefix', SERIES_BASE),
            'desc' => __( 'set css class prefix for series element', SERIES_BASE ),
            'type' => 'text',
            'size' => 20
        ),
        array(
            'name' => 'series_wrap',
            'title'=>  __( 'Series Wrap Element', SERIES_BASE),
            'desc' => __( 'set series element wrapper eg. div, section...', SERIES_BASE ),
            'type' => 'text',
            'size' => 20
        ),
        array(
            'name' => 'title_wrap',
            'title'=> __( 'Title Wrap Element', SERIES_BASE),
            'desc' => __( 'set title element wrapper eg. h1, h2...', SERIES_BASE ),
            'type' => 'text',
            'size' => 20
        ),
        array(
            'name' => 'show_future',
            'title'=> __( 'Show Unpublished', SERIES_BASE),
            'type' => 'checkbox'
        ),
        array(
            'name' => 'auto_display',
            'title'=> __('Auto Show Series On Post', SERIES_BASE),
            'desc' => __('The option "Between excerpt and content" doesn\'t work if you use automatic excerpts. It works on with manual excerpt or with &lt!--more--&gt; tag. See the <a href="http://codex.wordpress.org/Excerpt" title="Excerpt in WordPress Codex">WordPress documentation</a> to get more details on excerpt.', SERIES_BASE),
            'type' => 'radio',
            'options' => array(
                0 => __('Not activated', SERIES_BASE), 2 => __('At the end', SERIES_BASE), 
                3 => __('At the begining', SERIES_BASE), 4 => __('Between excerpt and content', SERIES_BASE)
            )
        ),
        array(
            'name' => 'custom_styles',
            'title'=> __('Use Custom CSS Style Sheet', SERIES_BASE),
            'type' => 'checkbox'
        ),
        array(
            'name' => 'show_thumbnail',
            'title'=> __('Show Thumbnail', SERIES_BASE),
            'type' => 'checkbox'
        ),
        array(
            'name' => 'show_excerpt',
            'title'=> __('Show Excerpt', SERIES_BASE),
            'type' => 'checkbox'
        ),
        array(
            'name' => 'show_nav',
            'title'=> __('Show Navigation', SERIES_BASE),
            'type' => 'checkbox'
        ),
        array(
            'name' => 'loop_display',
            'title'=> __('Auto Show Series In Loop', SERIES_BASE),
            'type' => 'checkbox'
        )
    );
    
    register_setting( SERIES.'_options', SERIES.'_options', 'series_settings_validate' );
    add_settings_section(SERIES,  __( 'Configure Post Series', SERIES_BASE ), 'series_section_text', SERIES.'_settings');
    
    foreach($series_settings_fileds as $filed ){
        add_settings_field($filed['name'], $filed['title'], 'series_display_field', SERIES.'_settings', SERIES, $filed);
    }
}
add_action('admin_init', 'series_register_settings');

/**
 * series_settings_validate()
 * 
 * @return
 */
function series_settings_validate($series_input) {
    if( isset($_POST['Reset']) ) {
        series_delete_options();
        return series_get_default_options();
    }
    
    $series_options = array();

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
    
    $series_options['show_future'] = $series_input['show_future'] ? true: false;

    $series_options['auto_display'] = $series_input['auto_display'];

    $series_options['custom_styles'] = $series_input['custom_styles'] ? true: false;
    
    $series_options['show_thumbnail'] = $series_input['show_thumbnail'] ? true: false;
    
    $series_options['show_excerpt'] = $series_input['show_excerpt'] ? true: false;
    
    $series_options['show_nav'] = $series_input['show_nav'] ? true: false;
    
    $series_options['loop_display'] = $series_input['loop_display'] ? true: false;
    
    //$series_options['custom_archives'] = $series_input['custom_archives'] == "on" ? true: false;

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
		<?php 
        //adds options_group on settings page				
		settings_fields( SERIES. '_options' );
        //display all settings sections	in a page			
		do_settings_sections(  SERIES.'_settings' );
        //submit_button( 'Reset', 'secondary' ); 
        //submit_button( 'Delete', 'delete' );       
		?>
		
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php _e( 'Save Changes') ?>" />
            <input name="Reset" type="submit" class="button-secondary" value="<?php _e( 'Reset Default', SERIES_BASE) ?>" />
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
function series_display_field($args){
    $series_options = get_option( SERIES . '_options' );
    $prefix = $args['name'];
    switch($args['type']){
        case 'checkbox':  
            echo "<input id='{$prefix}' type='checkbox' name='" .SERIES. "_options[{$prefix}]' value='1'". checked( $series_options[$prefix], true, false). "/>";
            echo "<label for='{$prefix}' class='{$prefix}-label'>".__('on',SERIES_BASE)."</label>";
        break;
        case 'text':
            echo "<input id='{$prefix}' name='".SERIES."_options[{$prefix}]' size='{$args['size']}' type='text' value='{$series_options[$prefix]}' /> <p class='description'>{$args['desc']}</p>";
        break;
        case 'radio':
            echo "<fieldset id='{$prefix}'>";
            foreach($args['options'] as $value => $option){
                echo "<input type='radio' name='" .SERIES. "_options[{$prefix}]' value='{$value}'". checked( $series_options[$prefix], $value, false ). "/>";
                echo "<label for='{$value}' class='{$prefix}-label'>{$option}</label><br/>";
            }
            echo "</fieldset>";
            echo "<span class='description'>{$args['desc']}</span>";
        break;
    }
}

?>