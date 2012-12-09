<?php 
/*
 special page for bulk edition 
*/

//page hooks for callback
$page_hooks = array();

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
    }
}
add_action( 'admin_menu', 'series_edition_menu' );

function series_edition_page(){
    global $page_hooks;
    // Get the list of series and store it in an array
    $series_list = get_terms(SERIES, array('hide_empty' => false, 'hierarchical' => true));
    $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
    $page_hook = $page_hooks[$post_type];
    // Add main metabox (to display list of posts)
    foreach($page_hooks as $type => $hook){
        $id = SERIES.'_list_'.$type;
        $args = array(
            'series_list' => $series_list,
            'post_type' => $type
        );
        add_meta_box( $id, __('Available posts',SERIES_BASE), 'series_edition_display_posts', $page_hook, 'normal', 'core', $args);
    }
    
    // Add metaboxes for series
	if ($series_list !== FALSE && sizeof($series_list) > 0) {
		foreach ($series_list as $serie) {
			add_meta_box(SERIES.'_box_'.$serie->term_id, $serie->name, 'series_edition_display_series', $page_hook, 'side', 'core', $serie->term_id);
		}
	} // Series found
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

function series_edition_load_scripts(){
    wp_enqueue_script('postbox');
}

function series_edition_display_posts($args, $box){
    $post_type = $box["args"]["post_type"];
    // Get date parameter
	$status_title = array(
		'any'     => __('All posts'),
		'future'  => __('Future'),
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
		$terms[] = $serie->term_taxonomy_id;
	}

	if (sizeof($terms)>0) {
	    $tax_query = array(array('taxonomy' => SERIES, 'field' => 'id', 'terms' => $terms));
		$posts = get_posts(array(
					'tax_query'  => $terms,
					'posts_per_page' => -1,
					'post_type'		=> $post_type)
				);
		if ($posts) {
			foreach ($posts as $post) {
				$params['post__not_in'][] = $post->ID;
			}
		}
	} // End of slug not empty
    
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
    $num_posts   = wp_count_posts($post_type, 'readable');
	$total_posts = array_sum( (array) $num_posts );
    // TODO: only works in apache
	$url_page    = $_SERVER['REQUEST_URI'];//admin_url('edit.php?page=egs_post_edit');
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
    $filter_categories = wp_dropdown_categories( array(
			'show_option_all'    => __('View all categories'),
			'orderby'            => 'name',
			'show_count'         => 1,
			'hide_empty'         => 1,
			'echo'               => 0,
			'selected'           => $params['cat'])
	);
    
    query_posts( $params );

	global  $wp_query;
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
					<?php echo implode(' | ', $filter_post_status); ?>
				</ul>
				<div class="tablenav top">
					<div class="alignleft actions">
						<?php echo $filter_archives; ?>
						<?php echo $filter_categories; ?>
						<?php /* echo $filter_post_type; */?>
						<input name="" id="post-query-submit" class="button-secondary" value="Filtrer" type="submit">
					</div>
					<div class="tablenav-pages">
						<?php echo $page_links ; ?>
					</div>
				</div>
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

function series_edition_display_series($serie_id, $box){
    $serie_id = $box["args"];
    $post_types = series_posttype_support();
    $tax_query = array(array('taxonomy' => SERIES, 'field' => 'id', 'terms' => $serie_id));
    
    $args = array(
		'tax_query' => $tax_query,
		'orderby'	=> 'menu_order',
		'order'		=> 'ASC',
		'post_type'	=> $post_types,
		'posts_per_page' => -1
    );
    $posts = get_posts($args);

	$output = '<ul id="'.SERIES.'-'.$serie_id.'" class="'.SERIES.' '.SERIES.'-sortable">';
	if (is_array($posts)) {
		foreach ($posts as $post) {
			$output .= '<li id="post-'.$post->ID.'">'.htmlspecialchars($post->post_title).'</li>';
		}
	}
	$output .= '</ul>';
	echo $output;
}

function series_display_conversion_message(){
    
}

?>