<?php
/*
Plugin Name: Post Series
Plugin URI: http://chaozh.com/
Version: 1.0
Author: chaozh
Author URI: http://chaozh.com/
origin: http://wp.tutsplus.com/tutorials/plugins/adding-post-series-functionality-to-wordpress-with-taxonomies/
*/
define('SERIES','series');
define('SERIES_URL', plugins_url( '', __FILE__ )); 
define('SERIES_ROOT', dirname(__FILE__) );
define('SERIES_BASE', dirname( plugin_basename( __FILE__ ) )); 
// Adds translation support for language files
function series_localization() {
	load_plugin_textdomain( SERIES_BASE, false, SERIES_BASE. '/languages' );
}
add_action( 'plugins_loaded', 'series_localization' );

function series_register_taxonomy() {
    
	$labels = array(
    
		'name' => _x('Series', 'taxonomy general name',SERIES_BASE),
		'singular_name' => _x('Series', 'taxonomy singular name',SERIES_BASE),
		'all_items' => __('All Series',SERIES_BASE),
		'edit_item' => __('Edit Series',SERIES_BASE), 
		'update_item' => __('Update Series',SERIES_BASE),
		'add_new_item' => __('Add New Series',SERIES_BASE),
		'new_item_name' => __('New Series Name',SERIES_BASE),
		'menu_name' => __('Series',SERIES_BASE)
        
	);
    
    $series_tax_args = array(
    
        'labels' => $labels,
		'hierarchical' => true,
        'show_ui' => true,
        'query_var' => true,
		'rewrite' => array('slug' => SERIES)
        
   );

	register_taxonomy( SERIES, array('post','page'), $series_tax_args );
} 
add_action('init', 'series_register_taxonomy', 0);

// Adds CSS for the slideshow
function series_css() {
	if ( file_exists( get_stylesheet_directory()."/series.css" ) ) {				
		wp_enqueue_style( SERIES, get_stylesheet_directory_uri() . '/series.css', array(), '1.0' );			
	}
	elseif ( file_exists( get_template_directory()."/series.css" ) ) {							
		wp_enqueue_style( SERIES, get_template_directory_uri() . '/series.css', array(), '1.0' );
	}
	else {
		wp_enqueue_style( SERIES, SERIES_URL . '/series.css', array(), '1.0' );
	}
}
add_action( 'wp_enqueue_scripts', 'series_css' );

// Load admin functions if in the backend
if ( is_admin() ){
    require_once(SERIES_ROOT. '/series-admin.php');
}

// Adds default values for options on settings page
register_activation_hook( __FILE__, 'series_default_options' );
	
function series_default_options() {

	$series_temp = get_option( SERIES.'_options' );
	
	if ( ( $series_temp['series_wrap'] == '' )||( !is_array( $series_temp ) ) ) {

		$series_defaults_args = series_get_default_options();
		update_option( SERIES.'_options', $series_defaults_args );
        
	}
}

function series_get_default_options() {
    
	$series_defaults_args = array(
    
		'title_format'   => __('This entry is part %current of %count in the series: %link', SERIES_BASE),
        'class_prefix'   => 'post-series',
        'series_wrap'    => 'section',
		'title_wrap'     => 'h3',
		'show_future'    => 'on'
            
	);
	return apply_filters( SERIES . '_default_options', $series_defaults_args );
}

require_once(SERIES_ROOT. '/series-display.php');
// The shortcode function of Post Series
function series_sc($atts) {
    $options = get_option( SERIES.'_options' );
    //merge options
	$series_arg = shortcode_atts( array (
    
            "slug" => '',
            "id" => '',
            "title" => '', 
            "limit" => -1, 
            "future" => 'on',
            "class_prefix" => '',
            "title_format" => ''
            			
    ), $atts );
    $series_arg = $options + $series_arg;
    return series_display($series_arg);
} 
add_shortcode('series','series_sc');

?>