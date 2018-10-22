<?php
/*
Plugin Name: Simple Post Series
Plugin URI: http://www.chaozh.com/simple-post-series-plugin-officially-publish/
Description: Better organize your posts by grouping them into series and display them within the series dynamically in your blog.  This version of Post Series Plugin requires at least WordPress 3.1 and PHP 5.0+ to work.
Version: 2.5
Author: chaozh
Author URI: http://chaozh.com/
GitHub Plugin URI: https://github.com/chaozh/Post-Series
*/

### INSTALLATION/USAGE INSTRUCTIONS ###
//  Installation and/or usage instructions for the Post Series Plugin
//  can be found at http://www.chaozh.com/simple-post-series-plugin-officially-publish/

define('SERIES','series');
define('SERIES_BASE', 'simple-post-series');
define('VERSION', 2.5);
/*  Copyright 2009-2012 CHAO ZHENG  (email: chao@whu.edu.cn)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define('SERIES_URL', plugins_url( '', __FILE__ )); 
define('SERIES_ROOT', dirname(__FILE__) );
define('SERIES_REL', dirname( plugin_basename( __FILE__ ) ));
define('SERIES_BASENAME', plugin_basename( __FILE__ ));
define('SERIES_FILE', __FILE__);
// aboud debug
define('SERIES_DEBUG',false);

// Adds translation support for language files
function series_localization() {
    load_plugin_textdomain( SERIES_BASE, false, SERIES_REL. '/languages' );
}
add_action( 'plugins_loaded', 'series_localization' );

if ( ! function_exists('series_log')) {
function series_log ( $log )  {
    if(WP_DEBUG === true && SERIES_DEBUG == true){
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}
}

function series_plugin_uninstall(){
    // Delete settings page options from options table
    series_log("uninstall plugin and delete options: " . SERIES . "_options");
    delete_option( SERIES . '_options' );
    
    // drop a custom database table
    // DELETE FROM wp_posts WHERE post_type = '要删除的post_type'; 
    // DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts);
    // SELECT * FROM `wp_term_taxonomy` where taxonomy = 'series'; 82
    // SELECT * FROM `wp_terms` WHERE `term_id` = 82; 
    // SELECT * FROM `wp_term_relationships` WHERE `term_taxonomy_id` = 82; object_id
    global $wpdb;
    $wpdb->query("DELETE FROM wp_terms WHERE term_id IN (SELECT term_id FROM wp_term_taxonomy where taxonomy = '". SERIES . "'");
    $wpdb->query("DELETE FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_id FROM wp_term_taxonomy where taxonomy = '". SERIES . "'");
    $wpdb->query("DELETE FROM wp_term_taxonomy WHERE taxonomy = '". SERIES . "'");
}
// register_deactivation_hook( __FILE__, 'series_plugin_uninstall' );
register_uninstall_hook( __FILE__, 'series_plugin_uninstall' );

function series_posttype_support() {
    $args = array(
        'public'   => true,
        '_builtin' => false
    );
    return apply_filters('series_posttype_support', array_merge(array('post', 'page'), get_post_types( $args )));
    //return apply_filters('series_posttype_support', array('post', 'page') );
}

function series_get_default_options() {  
    $series_defaults_args = array(
    
        'title_format'   => __('This entry is part %current of %count in the series: %link', SERIES_BASE),
        'class_prefix'   => 'post-series',
        'series_wrap'    => 'section',
        'title_wrap'     => 'h3',
        'show_future'    => true,
        'auto_display'   => 0,
        'custom_styles'  => false,
        'show_thumbnail' => false,
        'show_excerpt'   => false,
        'show_nav'       => false,
        'loop_display'   => false,
        'auto_hide'      => false
            
    );
    
    return apply_filters( SERIES . '_default_options', $series_defaults_args );
}

function series_register_taxonomy() {
    
    $posttypes = series_posttype_support();
    
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

    register_taxonomy( SERIES, $posttypes, $series_tax_args );
    
    $options = get_option( SERIES.'_options', series_get_default_options());
    if( $options['auto_display'] ){
        add_filter('the_content', 'series_auto_content_display',0);
    }
    
    if( $options['loop_display'] ){
        add_filter('the_content', 'series_auto_loop_display',0);
    }

    if( !$options['custom_styles'] ){
        add_action( 'wp_print_styles', 'series_css' );
    }
    
    // deal with auto hide style and script
    wp_enqueue_script( SERIES, SERIES_URL . '/autohide.js', array('jquery'), VERSION );
    wp_enqueue_style( 'series_font', SERIES_URL . '/inc/icomoon/style.css', null, VERSION );
    
    /*
     * we will not include sereis archives template by default 
     * and this setting section is removed from plugin settings
    if( $options['custom_archives'] ){
        add_filter('template_include', 'series_set_template');
    }
    */
} 
add_action('init', 'series_register_taxonomy', 0);

// Adds CSS for the series
function series_css() {
    if ( file_exists( get_stylesheet_directory()."/series.css" ) ) {                
        wp_enqueue_style( SERIES, get_stylesheet_directory_uri() . '/series.css', array(), VERSION );           
    }
    elseif ( file_exists( get_template_directory()."/series.css" ) ) {                          
        wp_enqueue_style( SERIES, get_template_directory_uri() . '/series.css', array(), VERSION );
    }
    else {
        wp_enqueue_style( SERIES, SERIES_URL . '/series.css', array(), VERSION );
    }
}

function series_is_template( $template_path ){

    //Get template name
    $template = basename($template_path);

    //Check if template is taxonomy-series.php
    //Check if template is taxonomy-series-{term-slug}.php
    if( 1 == preg_match('/^taxonomy-series((-(\S*))?).php/',$template) )
         return true;

    return false;
}

function series_attrs($attrs, $options){
    //TODO: try wp_parse_args instead
    if( isset($options) && is_array($options)){
        $attrs = $attrs + $options;
    } else{
        series_log("FAIL in ".__FUNCTION__.'for $options'.($options));
    }
    
    return $attrs;
}

function series_set_template( $template )
{
    if( is_tax(SERIES) && !series_is_template($template) ){
        $template = SERIES_ROOT."/template/taxonomy-".SERIES.".php";
    }

    return $template;
}

// Load admin functions if in the backend
if ( is_admin() ){
    require_once(SERIES_ROOT. '/series-admin.php');
}

require_once(SERIES_ROOT. '/series-display.php');
// The shortcode function of Post Series
function series_sc($atts) {
    $options = get_option( SERIES.'_options', series_get_default_options());
    //fetch class_prefix options first
    //this option is the only option that **must** sync with settings option
    $class_prefix = 'post-series';
    if( isset($options) && is_array($options))
        $class_prefix = $options["class_prefix"];
    //merge options
    $series_arg = shortcode_atts( array (
    
            "slug" => '',
            "id" => '',
            "title" => '', 
            "limit" => -1, 
            "show_future" => true,
            "class_prefix" => $class_prefix
                        
    ), $atts );
    
    if( isset($atts['show_future']) && $atts['show_future'] == 'off'){
        $series_arg['show_future'] = false;
    }else{
        $series_arg['show_future'] = true;
    } 
    
    //new sc args overrides the option settings   
    $series_arg = series_attrs($series_arg, $options);
        
    return series_display($series_arg);
} 
add_shortcode('series','series_sc');

function series_auto_content_display($content) {
    global $post;
    
    if(is_single() || is_page() || is_feed()){
        $options = get_option( SERIES.'_options', series_get_default_options());
        $series_arg = array(
            "limit" => -1
        );
        $series_arg =series_attrs($series_arg, $options);
        
        $series_display = series_display($series_arg);
        switch($options['auto_display']){
            case 2:// At the end of post
                $content =  $content."\n".$series_display."\n";
            break;
            
            case 3:// At the begining of post
                $content = $series_display."\n".$content."\n";
            break;
            
            case 4:
                // Case of teaser
                if(strpos($content, 'span id="more-')) {
                    $parts = preg_split('/(<span id="more-[0-9]*"><\/span>)/', $content, -1,  PREG_SPLIT_DELIM_CAPTURE);
                    $content = $parts[0].$parts[1].$series_display.$parts[2];
                } // End of detect tag "more"
            break;
        }    
    }
    
    return $content;
}

function series_auto_loop_display($content){
    if( is_home() || is_front_page() || is_archive() ){
        $options = get_option( SERIES.'_options', series_get_default_options());
        $series_arg = array(
            "limit" => -1,
            'auto_hide'=>true
        );
        $series_arg = series_attrs($series_arg, $options);
        $series_display = series_display($series_arg);
        $content .= $series_display;
        $pos1 = strpos($content, '<span id=”more-');
        $pos2 = strpos($content, '</span>', $pos1);
        $text1 = substr($content, 0, $pos2);
        $text2 = substr($content, $pos2);
        $text = $text1 . $series_display . $text2;
    }
    return $content;
}

class Post_Series_Widget extends WP_Widget {
    /** constructor */
    function __construct() {
        $widget_ops = array(
            'classname' => 'widget_' . SERIES,
            'description' => __( "A simple widget to display post series.", SERIES_BASE )
        );
        parent::__construct( 'widget_' . SERIES, __('Post Series', SERIES_BASE), $widget_ops );
        
        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

    /** @use WP_Widget::widget */
    function widget($args, $instance) {
        $cache = wp_cache_get('widget_'.SERIES, 'widget');
        
        if ( !is_array($cache) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = null;

        if ( isset($cache[$args['widget_id']]) ) {
            echo $cache[$args['widget_id']];
            return;
        }
        
        $options = get_option( SERIES.'_options', series_get_default_options());
        $series_arg = array(
        
            "id" => intval($instance["id"]),
            "limit" => intval($instance["limit"]),
            "show_future" => isset($instance["show_future"])?$instance["show_future"]:false,
            "class_prefix" => $instance["class_prefix"], 
            "show_nav" => false,
            "title_format" => '',
            "title_wrap" => ''
            
        );
        $series_arg = series_attrs($series_arg, $options);
        $title = apply_filters( 'widget_title', empty( $instance['widget_title'] ) ? __( 'Post Series List', SERIES_BASE) : $instance['widget_title'], $instance, $this->id_base );
        
        ob_start(); 
        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo series_display($series_arg);
        echo $args['after_widget'];
        
        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_'.SERIES, $cache, 'widget');
    }
    
    function flush_widget_cache() {
        wp_cache_delete( 'widget_'.SERIES, 'widget' );
    }

    /** @use WP_Widget::update */
    function update($new_instance, $old_instance) {             
        $instance = $old_instance;
        $instance['widget_title'] = sanitize_text_field($new_instance['widget_title']);
        $instance['id'] = absint($new_instance['id']);
        if( empty($new_instance['limit']) )
            $instance['limit'] = -1;
        
        $instance['class_prefix'] = sanitize_text_field($new_instance['class_prefix']);
        $instance['show_future'] = $new_instance['show_future'] == "on" ? true : false;
        
        $this->flush_widget_cache();
        
        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_'.SERIES]) )
            delete_option('widget_'.SERIES);
        
        return $instance;
    }

    /** @use WP_Widget::form */
    function form($instance) {
        $options = get_option( SERIES.'_options', series_get_default_options());

        $instance = wp_parse_args( (array) $instance, array(
            'id' => -1,
            'widget_title' => '',
            'class_prefix' => $options['class_prefix'],
            'show_future' => $options['show_future']
        ) );
        
        if( empty($instance['limit']) || $instance['limit'] == -1)
            $limit = '';
        else
            $limit = $instance['limit'];
        $id = $instance['id'];
        $class_prefix = sanitize_text_field($instance['class_prefix']);
        $show_future = $instance['show_future'];
        $widget_title = sanitize_text_field($instance['widget_title']);
        ?>
            <p>
                <label for="<?php echo $this->get_field_id('widget_title'); ?>"><?php _e('Title:'); ?> </label>
                <input id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" class="widefat" type="text" value="<?php echo esc_attr($widget_title); ?>" />
            </p>
            <?php 
            $series = get_terms( SERIES );
            
            if( count($series) > 0 ): ?>
            <p>
                <label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Choose post series to display:', SERIES_BASE); ?> </label>
                <select id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" class="widefat">
                    <?php foreach( $series as $term ): ?>
                    <option value="<?php echo $term->term_id; ?>" <?php echo ($id == $term->term_id) ? 'selected="selected"':""; ?>><?php echo $term->name . "(" . $term->count . ")"; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <?php endif; ?>
            <p>
                <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of posts to show:', SERIES_BASE); ?> </label>
                <input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" class="widefat" type="text" value="<?php echo $limit; ?>" size="3" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('class_prefix'); ?>"><?php _e('Special class prefix:', SERIES_BASE); ?> </label>
                <input id="<?php echo $this->get_field_id('class_prefix'); ?>" name="<?php echo $this->get_field_name('class_prefix'); ?>" class="widefat" type="text" value="<?php echo $class_prefix; ?>" />
            </p>
            <p>
                <input id="show_future" type="checkbox" name="<?php echo $this->get_field_name('show_future'); ?>" value="on" <?php checked( $show_future ); ?> />
                <label for="show_future" class="future-on-label"><?php _e('Show future',SERIES_BASE);?></label>
            </p>
        <?php 
    }

}

// for displaying list
class Series_List_Widget extends WP_Widget {
    /** constructor */
    function __construct() {
        $widget_ops = array(
            'classname' => 'widget_list_' . SERIES,
            'description' => __( "A simple widget to display all post series in list.", SERIES_BASE )
        );
        parent::__construct( 'widget_list_' . SERIES, __('Post Series List', SERIES_BASE), $widget_ops );
    }

    function widget( $args, $instance ) {
        // Widget output
        $title = apply_filters( 'widget_title', empty( $instance['widget_title'] ) ? __( 'Post Series List', SERIES_BASE) : $instance['widget_title'], $instance, $this->id_base );
        $list_args = array(
            "count" => ! empty( $instance['count'] ) ? '1' : '0',
            "hierarchical" => !empty( $instance['hierarchical'] ) ? '1' : '0'
        );

        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo series_list_display(apply_filters( 'widget_series_list_args', $list_args ));
        echo $args['after_widget'];
    }

    function update( $new_instance, $old_instance ) {
        // Save widget options
        $instance = $old_instance;
        $instance['widget_title'] = sanitize_text_field( $new_instance['widget_title'] );
        $instance['count'] = !empty($new_instance['count']) ? 1 : 0;
        $instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
        
        return $instance;
    }

    function form( $instance ) {
        // Output admin widget options form
        // title | number | category | hirechy
        $instance = wp_parse_args( (array) $instance, array( 'widget_title' => '') );
        $widget_title = sanitize_text_field( $instance['widget_title'] );
        $count = isset($instance['count']) ? (bool) $instance['count'] :false;
        $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
        ?>
        <p><label for="<?php echo $this->get_field_id('widget_title'); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>" /></p>

        <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
        <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
        <label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
        <?php
    }
}

function series_register_widgets() {
    register_widget( 'Post_Series_Widget' );
    register_widget( 'Series_List_Widget' );
}

add_action( 'widgets_init', 'series_register_widgets' );
?>