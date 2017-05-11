<?php

    // If uninstall not called from WordPress exit

    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
       
        exit();
    
    }
    
    // Delete settings page options from options table
    series_log("uninstall plugin and delete options");
    delete_option( SERIES . '_options' );
    
    // drop a custom database table
    // DELETE FROM wp_posts WHERE post_type = '要删除的post_type'; 
    // DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts);
    // SELECT * FROM `wp_term_taxonomy` where taxonomy = 'series'; 82
    // SELECT * FROM `wp_terms` WHERE `term_id` = 82; 
    // SELECT * FROM `wp_term_relationships` WHERE `term_taxonomy_id` = 82; object_id
    global $wpdb;
    $wpdb->query("DROP FROM wp_terms WHERE term_id IN (SELECT term_id FROM wp_term_taxonomy where taxonomy = 'series')");
    $wpdb->query("DROP FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_id FROM wp_term_taxonomy where taxonomy = 'series')");
    $wpdb->query("DROP FROM wp_term_taxonomy WHERE taxonomy = 'series'");
?>