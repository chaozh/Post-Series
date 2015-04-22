<?php 
function series_get_thumbnail_img($the_post){
    if (has_post_thumbnail($the_post->ID)) {
		$attr = array(
			'alt' => esc_attr(wptexturize($the_post->post_title)),
			'title' => false
		);
		$img = get_the_post_thumbnail($the_post->ID, 'thumbnail', $attr);
		return $img;
	}
    //$img = '<img src="'. esc_attr(series_get_default_thumbnail_url($the_post->ID)) . '" alt="' . esc_attr(wptexturize($the_post->post_title)) . '" />';
	//return $img;
}

function series_get_adjacent_post_nav($format, $link, $post, $previous = true){
    if ( !$post )
		return;

	$title = get_the_title( $post->ID );
	$text = $previous ? __('Previous Post') : __('Next Post');
    
    $rel = $previous ? 'prev' : 'next';
   	$string = '<a href="'.get_permalink( $post->ID, false ).'" rel="'.$rel.'" title="'.$title.'">';
	$link = str_replace('%title', $title, $link);
    $link = str_replace('%rel', $text, $link);
    
	$link = $string . $link . '</a>';
    $format = str_replace('%link', $link, $format);
    
    return $format;
}

function series_get_the_excerpt( $the_post ) {

	return $the_post->post_excerpt;
}
/**
 * $series_arg = array(
         //come from settings option
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
        
        //come from widget or shortcode
        "slug" => '',
        "id" => '',
        "title" => '', //should have!
        "limit" => -1,
        'show_all'=> true,//only used by loop display
        "show_future" => true,
        "class_prefix" => $class_prefix, //must have!
        "show_nav" => false,
        "title_format" => ''
        );
*/
function series_display($series_arg){
    global $post;
    
    extract($series_arg);
    
    $current_post_id = $post->ID;
	if(isset($id) && $id) {
		// Use the "id" attribute if it exists
		$tax_query = array(array('taxonomy' => SERIES, 'field' => 'id', 'terms' => $id));
        $tax_link = get_term_link((int)$id, SERIES);
        $term = get_term((int)$id, SERIES);
        
	} else if (isset($slug) && $slug) {
		// Use the "slug" attribute if "id" does not exist
		$tax_query = array(array('taxonomy' => SERIES, 'field' => 'slug', 'terms' => $slug));
        $tax_link = get_term_link($slug, SERIES);
        $term = get_term_by('slug', $slug, SERIES);
        
	} else {
		// Use post's own Series tax if neither "id" nor "slug" exist
		$terms = get_the_terms($current_post_id,SERIES);
		if ($terms && !is_wp_error($terms)) {
            $term = array_shift($terms);
            $tax_query = array(array('taxonomy' => SERIES, 'field' => 'slug', 'terms' => $term->slug));
            $tax_link = get_term_link($term->slug, SERIES);
		} else {
		  return;
        }
	}
	if($show_future) {
		// Include the future posts if the "future" attribute is set to "on"
		$post_status = array('publish','future');
	} else {
		// Exclude the future posts if the "future" attribute is set to "off"
		$post_status = 'publish';
	}
    
	$args = array(
		'tax_query' => $tax_query,
		'posts_per_page' => $limit,
		'orderby' => 'date',
		'order' => 'ASC',
        'post_type'=> series_posttype_support(),
 		'post_status' => $post_status
	);
	$the_posts = get_posts($args);
	/* if there's more than one post with the specified "series" taxonomy, display the list. if there's just one post with the specified taxonomy, there's no need to list the only post! */
	if(($count = count($the_posts)) > 1) {
	    //display section
        $section_output = '<'.$series_wrap.' class="'.$class_prefix.'">';
		// create the list tag - notice the "post-series-list" class
		$output = '<ul class="'.$class_prefix.'-list">';
		// the loop to list the posts
        $iterator=1;
        $prev_post = $next_post = null;
		foreach($the_posts as $the_post) {
			setup_postdata($the_post);
			if($the_post->post_status == 'publish') {
			    if($the_post->ID == $current_post_id){
			        $output .= '<li class="'.$class_prefix.'-item-current">'
                    .'<span class="'.$class_prefix.'-item-title">'.get_the_title($the_post->ID).'</span>';
                    $current = $iterator;
                    $prev_post = (isset($tmp_post)?$tmp_post:null);
			    } else{
                    $output .= '<li class="'.$class_prefix.'-item"><span class="'.$class_prefix.'-item-title">'
                    .'<a href="'. get_permalink($the_post->ID) .'">'.get_the_title($the_post->ID).'</a></span>';
                    $tmp_post = $the_post;//for prev post
                    if(isset($current)&&($iterator - $current == 1)){
                        $next_post = $tmp_post;
                    }
                }
			} else {
				/* we can't link the post if the post is not published yet! */
				$output .= '<li class="'.$class_prefix.'-item-future"><span class="'.$class_prefix.'-item-title">'
                .__('Future post',SERIES_BASE).': '.get_the_title($the_post->ID).'</span>';
			}
            $iterator++;
            
            $output .= ($show_thumbnail?'<span class="'.$class_prefix.'-item-thumbnail">'.series_get_thumbnail_img($the_post).'</span>':'');
            $output .= ($show_excerpt?'<span class="'.$class_prefix.'-item-excerpt">'.series_get_the_excerpt($the_post).'</span>':'');
            
            $output .= '</li>';
		}
		wp_reset_query();
		// close the list tag...
		$output .= '</ul>';
        if($show_nav){
            $output .= '<nav class="'.$class_prefix.'-nav">';
            if($prev_post){
                $output .= '<span class="'.$class_prefix.'-nav-prev">'. series_get_adjacent_post_nav('&laquo; %link', '%rel', $prev_post). '</span>';
            }
            
            if($next_post){
                $output .= '<span class="'.$class_prefix.'-nav-next">'. series_get_adjacent_post_nav('%link &raquo;', '%rel', $next_post, false). '</span>';
            }
            $output .= '</nav>';
        }
        //close section tag...
        $output .= '</'.$series_wrap.'>';
        // Create the title if the "title" attribute exists
        $link = sprintf('<a href="%1$s">%2$s</a>', $tax_link, isset($title)?$title:$term->name);
        if($current){	  
            $title_format = str_replace( '%current', $current, $title_format );
            $title_format = str_replace( '%count', $count, $title_format );
            $title_format = str_replace( '%link', $link, $title_format );
            if( isset($show_all) && $show_all ){
                $title_format .= '<a href="JavaScript:void(0);" class="show-all">'.__('Show All', SERIES_BASE).'</a>';
            }
            $title_output = '<'.$title_wrap.' class="'.$class_prefix.'-title">'.$title_format.'</'.$title_wrap.'>';
        }else{
            $title_output = '<'.$title_wrap.' class="'.$class_prefix.'-title">'.$link.'</'.$title_wrap.'>';
        }
		// display the title first
		$output = $section_output.$title_output.$output;
		// ...and return the whole output!
		return $output;
	}
}


?>