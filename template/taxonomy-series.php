<?php
/**
 * The template for displaying Post Series Archive pages.
 *
 * @package Post Series
 * @subpackage Template
 * @since 1.0
 */

get_header(); ?>

<section id="primary">
			<div id="content" role="main">

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<h1 class="page-title"><?php
						printf( __( 'Post Series Archives: %s', 'simple-post-series'), '<span>' . single_cat_title( '', false ) . '</span>' );
					?></h1>

					<?php
						$category_description = term_description(0, 'series');
						if ( ! empty( $category_description ) )
							echo apply_filters( 'category_archive_meta', '<div class="category-archive-meta">' . $category_description . '</div>' );
					?>
				</header>

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php
						/* Include the Post-Format-specific template for the content.
						 * If you want to overload this in a child theme then include a file
						 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
						 */
                        if ( 'post' == get_post_type() ) :
                    ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        	<div class="blog-item-wrap">
                				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" > </a>
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="single-featured" style="float: left;">
                                        <?php the_post_thumbnail( 'thumbnail'); ?>
    			                     </div>
                                <?php endif; ?>
                                
                                <div class="post-inner-content" style="float:left; margin-left:14px; margin-left: 1rem;">
                               
                                    <header class="entry-header page-header">
                        				<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
                        				<div class="entry-meta">
                        					<?php $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
                                                	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
                                                		$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
                                                	}
                                                
                                                	$time_string = sprintf( $time_string,
                                                		esc_attr( get_the_date( 'c' ) ),
                                                		esc_html( get_the_date() ),
                                                		esc_attr( get_the_modified_date( 'c' ) ),
                                                		esc_html( get_the_modified_date() )
                                                	);
                                                
                                                	printf( '<span class="posted-on"><i class="fa fa-calendar"></i> %1$s</span><span class="byline"> <i class="fa fa-user"></i> %2$s</span>',
                                                		sprintf( '<a href="%1$s" rel="bookmark">%2$s</a>',
                                                			esc_url( get_permalink() ),
                                                			$time_string
                                                		),
                                                		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span>',
                                                			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
                                                			esc_html( get_the_author() )
                                                		)
                                                	); ?>
                                        <?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
                        				    <span class="comments-link"><i class="fa fa-comment-o"></i><?php comments_popup_link( __( 'Leave a comment'), __( '1 Comment'), __( '% Comments') ); ?></span>
                        				    <?php edit_post_link( __( 'Edit'), '<i class="fa fa-pencil-square-o"></i><span class="edit-link">', '</span>' ); ?>
                        				<?php endif; ?>
                                        </div><!-- .entry-meta -->
                        			</header><!-- .entry-header -->
                                 
                        			<div class="entry-summary">
                        
                   					    <?php the_excerpt(); ?>
                        
                        				<p><a class="btn btn-default read-more" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php _e( 'Read More'); ?></a></p>
                        
                        				<?php
                        					wp_link_pages( array(
                        						'before'            => '<div class="page-links">'.__( 'Pages:'),
                        						'after'             => '</div>',
                        						'link_before'       => '<span>',
                        						'link_after'        => '</span>',
                        						'pagelink'          => '%',
                        						'echo'              => 1
                        		       		) );
                        		    	?>
                        			</div><!-- .entry-summary -->
           		                </div>
                                <div style="clear: both;"></div>
                        	</div>
                        </article><!-- #post-## -->
                <?php 
                    else:
                        get_template_part( 'content', get_post_format() );
                    endif; 
				 endwhile; 
                 
                 ?>
                
                <?php if ( $wp_query->max_num_pages > 1 ) : ?>
        			<nav id="nav-below">
            			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts') ); ?></div>
    			         <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>') ); ?></div>
        			</nav><!-- #nav-below -->
        		  <?php endif; ?>

			<?php else : ?>

				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'Nothing Found'); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.'); ?></p>
						<?php get_search_form(); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-0 -->

			<?php endif; ?>

			</div><!-- #content -->
		</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>