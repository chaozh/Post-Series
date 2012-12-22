<?php 
/*
 special page for bulk edition 
*/

//page hooks for callback
$page_hooks = array();

function series_months_dropdown( $post_type, $selected ) {
	global $wpdb, $wp_locale;

	$months = $wpdb->get_results( "SELECT DISTINCT YEAR(post_date) AS year, MONTH(post_date) AS month FROM {$wpdb->posts} ORDER BY post_date DESC");
	$string = '';
	foreach ( $months as $arc_row ) {
		if ( 0 == $arc_row->year )
			continue;

		$value = $arc_row->year.zeroise( $arc_row->month, 2 );
		$string .= '<option value="'.$value.'" '.($value==$selected?'selected':'').'>'.
				$wp_locale->get_month($arc_row->month).' '.$arc_row->year.
				'</option>';
	}
	if ($string != '')
		$string = '<select name="m">'.
					'<option value="0" '.(0==$selected?'selected':'').'>'.__( "Show all dates" ).'</option>'.
					$string.
				'</select>';

	return ($string);

} // End of months_dropdown

function series_edition_menu(){
    global $page_hooks;
    //deal with custom type!
    $post_types = series_posttype_support();
    foreach($post_types as $post_type){
        $parent = 'edit.php';
        if( $post_type != 'post' ){
             $parent .= '?post_type='.$post_type;
        }
        $menu_slug = SERIES.'_bulk_edit_'.$post_type;
        $hook = add_submenu_page($parent, __('Series Bulk Edit',SERIES_BASE), __('Series Bulk Edit',SERIES_BASE), series_set_options_cap(), $menu_slug, 'series_edition_page');
        $page_hooks[$post_type] = $hook;
        add_action('admin_print_scripts-'.$hook, 'series_edition_load_scripts');
        add_action('admin_print_styles-'.$hook, 'series_edition_load_style');
    }
}
add_action( 'admin_menu', 'series_edition_menu' );

function series_edition_page(){
    global $page_hooks;
    // Get the list of series and store it in an array
    $series_list = get_terms(SERIES, array('hide_empty' => false, 'hierarchical' => true));
    $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
    $page_hook = $page_hooks[$post_type];

    $args = array(
        'post_type' => $post_type,
        'series_list' => $series_list
    );
    // Add main metabox (to display list of posts)
    add_meta_box( SERIES.'_list_posts', __('Available posts',SERIES_BASE), 'series_edition_display_posts', $page_hook, 'normal', 'core', $args);
    
    // Add metaboxes for series
	if ($series_list !== FALSE && sizeof($series_list) > 0) {
		foreach ($series_list as $serie) {
			add_meta_box(SERIES.'_box_'.$serie->term_id, $serie->name, 'series_edition_display_series', $page_hook, 'side', 'core', $serie->term_id);
		}
	} // Series found
    
    add_meta_box( SERIES.'_add_serie', __('Add New Series',SERIES_BASE), 'series_edition_add_serie', $page_hook, 'normal', 'core' ,$post_type);
?>
    <div class="wrap">
	   <?php screen_icon(); ?>
		<h2><?php _e('Series Bulk Edit', SERIES_BASE); ?></h2>
		<div id="ajax-wait" class="updated">
            <p><img height="16" width="16" src="<?php echo admin_url('images/loading.gif'); ?>"/> 
            <?php _e('Processing, please wait ...', SERIES_BASE); ?></p>
        </div>
		<div id="ajax-response"> </div>
		<div id="poststuff" class="metabox-holder has-right-sidebar">
			<div id="side-info-column" class="inner-sidebar">
				<?php if (sizeof($series_list)>0)  do_meta_boxes($page_hook, 'side', FALSE); ?>
			</div>
			<div id="post-body" class="has-sidebar">
				<div id="post-body-content" class="has-sidebar-content">
					<?php
							do_meta_boxes($page_hook, 'normal', FALSE);
					?>
				</div>
			</div>
			<br class="clear"/>
		</div>
	</div>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			postboxes.add_postbox_toggles('<?php echo $page_hook; ?>');
		});
		//]]>
	</script>
<?php
}

function series_edition_load_style(){
    wp_enqueue_style(SERIES.'_admin_style',SERIES_URL.'/series-edition.css',null,VERSION);
}

function series_edition_load_scripts(){
    wp_enqueue_script('postbox');
    wp_enqueue_script(SERIES.'_admin_js', SERIES_URL.'/series-edition.js', array('jquery-ui-sortable'), VERSION, TRUE);
	wp_localize_script(SERIES.'_admin_js', 'seriesEdition', array(
        'class_prefix'   => SERIES,
		'ajax_url'		 => admin_url('admin-ajax.php'),
		'UpdateSeries' 	 => 'series_update',
        'AddSerie'       => 'series_add',
        'DeleteSerie'    => 'series_delete',
		'nonce' 		 => wp_create_nonce( 'series-ajax' )
		)
	);
}

function series_edition_display_posts($args, $box){
    $post_type = $box["args"]["post_type"];
    // Get date parameter
    $status_title = array(
		'any'     => __('All Posts'),
		'future'  => __('Scheduled'),
		'publish' => __('Published'),
		'draft'   => __('Draft'),
		'pending' => __('Pending')
	);
	$date = isset($_GET['m'])?$_GET['m']:'';

	$params = array(
		'cat' 				=> isset($_GET['cat'])?$_GET['cat']:'',
		'year' 				=> substr($date, 0, 4),
		'monthnum' 			=> substr($date, 5),
		'paged' 			=> isset($_GET['paged'])?$_GET['paged']:1,
        'post_status' 		=> isset($_GET['post_status'])?$_GET['post_status']:'any',
		'posts_per_page'	=> 25,
		'orderby'			=> 'date',
		'order'				=> 'desc',
		'post_type'			=> $post_type
	);

	$slugs = array();
	$params['post__not_in'] = array();
	$series_list = $box["args"]["series_list"];
	foreach ($series_list as $serie) {
		$terms[] = $serie->term_id;
	}

	if (sizeof($terms)>0) {
		$posts = series_edition_get_posts($terms);
		if ($posts) {
			foreach ($posts as $post) {
				$params['post__not_in'][] = $post->ID;
			}
		}
	} // End of slug not empty
    
    $num_posts   = wp_count_posts($post_type, 'readable');
	$total_posts = array_sum( (array) $num_posts );
    $menu_slug = SERIES.'_bulk_edit_'.$post_type;
    $type_slug = ($post_type == 'post'?'':'post_type='.$post_type.'&');
    $url_page = admin_url('edit.php?'.$type_slug.'page='.$menu_slug);
	$num_posts   = array_merge( array('any' => $total_posts), (array) $num_posts);
	foreach ($num_posts as $status => $number) {
		if (isset($status_title[$status])) {
			$class = ($params['post_status'] == $status ? ' class="current"' : '');
			$filter_post_status[] = '<li>'.
				'<a href="'.$url_page.($status=='any'?'':'&post_status='.$status).'" '.$class.'>'.
				$status_title[$status].' <span class="count">'.($number<0?'':'('.$number.')').'</span>'.
				'</a></li>';
		}
	} // End of foreach
    
    $post_types = series_posttype_support();
    foreach($post_types as $type){
        $menu_slug = SERIES.'_bulk_edit_'.$type;
        $type_slug = ($type == 'post'?'':'post_type='.$type.'&');
        $url_page = admin_url('edit.php?'.$type_slug.'page='.$menu_slug);
        $class = ($params['post_type'] == $type ? ' class="current"' : '');
        $filter_post_types[] = '<li>'.
				'<a href="'.$url_page.'" '.$class.'>'.$type.'</a></li>';
    }
    
    $filter_archives   = series_months_dropdown($post_type, $date);
    if($post_type == 'post'){
        $filter_categories = wp_dropdown_categories( array(
			'show_option_all'    => __('View all categories'),
			'orderby'            => 'name',
			'show_count'         => 1,
			'hide_empty'         => 1,
			'echo'               => 0,
			'selected'           => $params['cat'])
	   );
    }
    
    query_posts( $params );
	global  $wp_query;
    //var_dump($wp_query);
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
	$page_links = paginate_links(array(
				'base'		=> add_query_arg( 'paged', '%#%' ), /* '%_%', */
				/* 'format'    => '?paged=%#%', */
				'total'     => $wp_query->max_num_pages,
				'current'   => max( 1, $params['paged'])
			)
		);
?>
		<p class="description"><?php _e('Drag posts from here to a serie on the right to include them in a serie. Drag posts back here remove them from series.', SERIES_BASE); ?></p>
		<div id="mass_edit_filter">
			<form id="posts-filter" method="get" action="">
				<input type="hidden" name="page" value="<?php echo $page; ?>" />
                <ul class="subsubsub">
                    <?php echo implode(' | ', $filter_post_status);?>
                </ul>
				<div class="tablenav top">
					<div class="alignleft actions">
						<?php echo $filter_archives; ?>
						<?php echo $filter_categories; ?>
						<input name="" id="post-query-submit" class="button-secondary" value="Filtrer" type="submit">
					</div>
					<div class="tablenav-pages">
						<?php echo $page_links ; ?>
					</div>
				</div>
                <ul class="subsubsub">
					<?php echo implode(' | ', $filter_post_types); ?>                 
				</ul>
				<br class="clear" />
			</form>
		</div>
			<ul id="posts-list" class="series-sortable">
<?php
				while (have_posts()) {
					the_post();
					echo '<li id="post-'.get_the_ID().'" >'.get_the_title().'</li>';
				}
?>
			</ul>
<?php
}

function series_edition_add_serie(){
    $tax = get_taxonomy(SERIES);
    //code from wp-admin/includes/meta-boxes.php post_categories_meta_box
    //deal with wp-admin/includes/ajax-actions.php
    //deal with wp-admin/edit-tags.php
?>    
<div class="form-wrap">
    <div class="form-field form-required">
    	<label for="tag-name"><?php _ex('Name', 'Taxonomy Name'); ?></label>
    	<input name="tag-name" id="tag-name" type="text" value="" size="40" aria-required="true" />
    	<p><?php _e('The name is how it appears on your site.'); ?></p>
    </div>
    <div class="form-field">
    	<label for="tag-slug"><?php _ex('Slug', 'Taxonomy Slug'); ?></label>
    	<input name="slug" id="tag-slug" type="text" value="" size="40" />
    	<p><?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></p>
    </div>
    <div class="form-field">
    	<label for="parent"><?php _ex('Parent', 'Taxonomy Parent'); ?></label>
    	<?php wp_dropdown_categories(array('hide_empty' => 0, 'hide_if_empty' => false, 'taxonomy' => SERIES, 'name' => 'parent', 'orderby' => 'name', 'hierarchical' => true, 'show_option_none' => __('None'))); ?>
    </div>
    <div class="form-field">
    	<label for="tag-description"><?php _ex('Description', 'Taxonomy Description'); ?></label>
    	<textarea name="description" id="tag-description" rows="5" cols="40"></textarea>
    	<p><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></p>
    </div>
	<p id="<?php echo SERIES; ?>-add" class="category-add">
		<input type="button" id="<?php echo SERIES; ?>-add-submit" class="button category-add-submit" value="<?php echo esc_attr( $tax->labels->add_new_item ); ?>" tabindex="3" />
	</p>
</div>
<?php
      
}

function series_edition_get_posts($series_id){
    $post_types = series_posttype_support();
    $tax_query = array(array('taxonomy' => SERIES, 'field' => 'id', 'terms' => $series_id));
    
    $args = array(
		'tax_query' => $tax_query,
		'orderby'	=> 'menu_order',
		'order'		=> 'ASC',
		'post_type'	=> $post_types,
		'posts_per_page' => -1
    );
    return get_posts($args);
}

function series_edition_display_series($serie_id, $box){
    $serie_id = $box["args"];
    $posts = series_edition_get_posts($serie_id);

	$output = '<ul id="'.SERIES.'-'.$serie_id.'" class="'.SERIES.' '.SERIES.'-sortable">';
	if (is_array($posts)) {
		foreach ($posts as $post) {
			$output .= '<li id="post-'.$post->ID.'">'.htmlspecialchars($post->post_title).'</li>';
		}
	}
	$output .= '</ul>';
    $output .= '<span id="'.SERIES.'-delete-'.$serie_id.'" class="delete">'.__( 'Delete' ).'</span>';
	echo $output;
}

/**
 * series_update_post_order
 *
 *
 *
 * @param 	$id			int		id of the post to modify
 * @param 	$meta_key	string	meta key to use for series
 * @param 	$order		int		order of the post in the serie
 * @return 	none
 */
function series_edition_update_post_order($id, $order) {
	global $wpdb;

	$sql = $wpdb->prepare('UPDATE '.$wpdb->posts.' SET menu_order=%u WHERE ID=%u', $order, $id );
	$wpdb->query($sql);
} // End of update_post_order

/**
 * series_bulk_edition_update
 *
 * Ajax function. Update series according actions in the admin interface
 *
 * @param 	none
 * @return 	string		error code | message
 */
function series_bulk_edition_update(){
    // Check nonce
	$error_code = 0;
	$msg = __('Serie successfully updated', SERIES_BASE);
	if (! check_ajax_referer('series-ajax', 'series_nonce', FALSE)) {
		$error_code = 3;
		$msg = __('Error, security check failed');
		// $this->display_debug_info('check_ajax_referer FAILED');
	}
	else if ( ! current_user_can(series_set_options_cap()) ) {
		$error_code = 4;
		$msg = __('Error, access denied');
		// $this->display_debug_info('Error, access denied');
	}
	else {
	   // get parameters
        $serie_id   = (int)substr($_POST['serie'], strlen(SERIES.'-'));
        $post_order = (isset($_POST['post'])?$_POST['post']:array());
        
        $serie = get_term($serie_id, SERIES);

		if (! $serie)  {
			$error_code = 2;
			$msg = __('Error, Serie not identified', SERIES_BASE);
		}
		else {
			$new_posts = array();
			foreach ($post_order as $key => $value) {
				$post_id = str_replace('post-', '', $value);
				$new_posts[$post_id] = $key;
			}
			// unset($post_order);
			$posts = series_edition_get_posts($serie_id);
            
			$current_posts = array();
			foreach ($posts as $post) {
				$current_posts[$post->ID] = $post->menu_order;
			}

			$posts_to_keep   = array_intersect_key($current_posts, 	$new_posts);
			$posts_to_delete = array_diff_key($current_posts, $posts_to_keep);
			$posts_to_add    = array_diff_key($new_posts, $posts_to_keep);

			foreach ($posts_to_delete as $post_id => $order) {
				wp_delete_object_term_relationships($post_id, SERIES);
			}
			foreach ($posts_to_add as $post_id => $order) {
				$result = wp_set_object_terms($post_id, $serie_id, SERIES);
			}
		//	clean_object_term_cache($serie_id, EGS_TAXONOMY) ;

			// Re-order the list
			if (sizeof($new_posts)>0) {
				foreach ($new_posts as $post_id => $order) {
					series_edition_update_post_order($post_id, $order+1);
				} // End of re-order loop
			} // End of is_array($post_order)
		} // End of no error
	}// End of user capabilities ok.
    // Just concatenate error_code and string.
	// Still cannot use json_encode, because this function is only available since PHP 5.3
    die ($error_code.'|'.$msg);
}
add_action('wp_ajax_series_update', 'series_bulk_edition_update' );

function series_edition_add(){
    // Check nonce
	$error_code = 0;
    
    if (! check_ajax_referer('series-ajax', 'series_nonce', FALSE)) {
		$error_code = 3;
		$msg = __('Error, security check failed');
		// $this->display_debug_info('check_ajax_referer FAILED');
	}else if ( ! current_user_can(series_set_options_cap()) ) {
		$error_code = 4;
		$msg = __('Error, access denied');
	}else{
	   $ret = wp_insert_term( $_POST['name'], SERIES, $_POST );
       if ( $ret && !is_wp_error( $ret ) ){
            $msg = __('New serie successfully added', SERIES_BASE);
       }else{
            $error_code = 4;
	        $msg = __('Error, add serie fail', SERIES_BASE);
       }
	}
     die ($error_code.'|'.$msg);
}
add_action('wp_ajax_series_add', 'series_edition_add' );

function series_edition_delete(){
    // Check nonce
	$error_code = 0;
    
    if (! check_ajax_referer('series-ajax', 'series_nonce', FALSE)) {
		$error_code = 3;
		$msg = __('Error, security check failed');
		// $this->display_debug_info('check_ajax_referer FAILED');
	}else if ( ! current_user_can(series_set_options_cap()) ) {
		$error_code = 4;
		$msg = __('Error, access denied');
	}else{
	   $tag_ID = (int) (int)substr($_POST['tag_ID'], strlen(SERIES.'-delete-'));
       $ret = wp_delete_term( $tag_ID, SERIES );
       if ( $ret && !is_wp_error( $ret ) ){
            $msg = __('Serie successfully deleted', SERIES_BASE);
       }else{
            $error_code = 4;
	        $msg = __('Error, delete serie fail', SERIES_BASE);
       }
	}
     die ($error_code.'|'.$msg);
}
add_action('wp_ajax_series_delete', 'series_edition_delete' );
?>