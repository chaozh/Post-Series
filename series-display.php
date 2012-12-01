<?php 
function series_display($series_arg){
    global $post;
    
    extract($series_arg);
    
    $current_post_id = $post->ID;
	if(isset($id) && $id) {
		// Use the "id" attribute if it exists
		$tax_query = array(array('taxonomy' => SERIES, 'field' => 'id', 'terms' => $id));
        $tax_link = get_term_link((int)$id, SERIES);
        $term = &get_term((int)$id, $taxonomy);
        
	} else if (isset($slug) && $slug) {
		// Use the "slug" attribute if "id" does not exist
		$tax_query = array(array('taxonomy' => SERIES, 'field' => 'slug', 'terms' => $slug));
        $tax_link = get_term_link($slug, SERIES);
        $term = &get_term_by('slug', $slug, SERIES);
        
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
		foreach($the_posts as $the_post) {
			setup_postdata($the_post);
			if($the_post->post_status == 'publish') {
			    if($the_post->ID == $current_post_id){
			        $output .= '<li class="'.$class_prefix.'-item-current">'.get_the_title($the_post->ID);
                    $current = $iterator;
			    } else{
                    $output .= '<li class="'.$class_prefix.'-item"><a href="'.get_permalink($the_post->ID).'">'.get_the_title($the_post->ID).'</a>';
                    $iterator++;
                }
			} else {
				/* we can't link the post if the post is not published yet! */
				$output .= '<li class="'.$class_prefix.'-item-future">'.__('Future post',SERIES_BASE).': '.get_the_title($the_post->ID);
			}
            
            
            $output .= '</li>';
		}
		wp_reset_query();
		// close the list tag...
		$output .= '</ul>';
        //close section tag...
        $output .= '</'.$series_wrap.'>';
        // Create the title if the "title" attribute exists
        if($title_format){	
            if(!$title) 
                $title = $term->name;
            $title_format = str_replace( '%current', $current, $title_format );
            $title_format = str_replace( '%count', $count, $title_format );
            $link = sprintf('<a href="%1$s">%2$s</a>', $tax_link, $title);
            $title_format = str_replace( '%link', $link, $title_format );
            $title_output = '<'.$title_wrap.' class="'.$class_prefix.'-title">'.$title_format.'</'.$title_wrap.'>';
        }
		// display the title first
		$output = $section_output.$title_output.$output;
		// ...and return the whole output!
		return $output;
	}
}


?>