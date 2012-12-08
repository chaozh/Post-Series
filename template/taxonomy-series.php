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
						printf( __( 'Post Series Archives: %s'), '<span>' . single_cat_title( '', false ) . '</span>' );
					?></h1>

					<?php
						$category_description = category_description();
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
						//get_template_part( 'content', get_post_format() );
                    ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    		<header class="entry-header">
                    			<h1 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s'), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
                    
                    			<?php if ( 'post' == get_post_type() ) : ?>
                    			<div class="entry-meta">
                    				<?php printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>' ),
                                        		esc_url( get_permalink() ),
                                        		esc_attr( get_the_time() ),
                                        		esc_attr( get_the_date( 'c' ) ),
                                        		esc_html( get_the_date() ),
                                        		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
                                        		esc_attr( sprintf( __( 'View all posts by %s' ), get_the_author() ) ),
                                        		get_the_author()
                                        	); 
                                    ?>
                    			</div><!-- .entry-meta -->
                    			<?php endif; ?>
                    
                    			<?php if ( comments_open() && ! post_password_required() ) : ?>
                    			<div class="comments-link">
                    				<?php comments_popup_link( '<span class="leave-reply">' . __( 'Reply') . '</span>', _x( '1', 'comments number'), _x( '%', 'comments number') ); ?>
                    			</div>
                    			<?php endif; ?>
                    		</header><!-- .entry-header -->
                    
                    		<div class="entry-content">
                    			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>') ); ?>
                    			<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:') . '</span>', 'after' => '</div>' ) ); ?>
                    		</div><!-- .entry-content -->
                    
                    		<footer class="entry-meta">
                    			<?php $show_sep = false; ?>
                    			<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
                    			<?php
                    				/* translators: used between list items, there is a space after the comma */
                    				$categories_list = get_the_category_list( __( ', ') );
                    				if ( $categories_list ):
                    			?>
                    			<span class="cat-links">
                    				<?php printf( __( '<span class="%1$s">Posted in</span> %2$s'), 'entry-utility-prep entry-utility-prep-cat-links', $categories_list );
                    				$show_sep = true; ?>
                    			</span>
                    			<?php endif; // End if categories ?>
                    			<?php
                    				/* translators: used between list items, there is a space after the comma */
                    				$tags_list = get_the_tag_list( '', __( ', ') );
                    				if ( $tags_list ):
                    				if ( $show_sep ) : ?>
                    			<span class="sep"> | </span>
                    				<?php endif; // End if $show_sep ?>
                    			<span class="tag-links">
                    				<?php printf( __( '<span class="%1$s">Tagged</span> %2$s'), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list );
                    				$show_sep = true; ?>
                    			</span>
                    			<?php endif; // End if $tags_list ?>
                    			<?php endif; // End if 'post' == get_post_type() ?>
                    
                    			<?php if ( comments_open() ) : ?>
                    			<?php if ( $show_sep ) : ?>
                    			<span class="sep"> | </span>
                    			<?php endif; // End if $show_sep ?>
                    			<span class="comments-link"><?php comments_popup_link( '<span class="leave-reply">' . __( 'Leave a reply') . '</span>', __( '<b>1</b> Reply'), __( '<b>%</b> Replies') ); ?></span>
                    			<?php endif; // End if comments_open() ?>
                    
                    			<?php edit_post_link( __( 'Edit'), '<span class="edit-link">', '</span>' ); ?>
                    		</footer><!-- #entry-meta -->
                    	</article><!-- #post-<?php the_ID(); ?> -->

				<?php endwhile; ?>
                
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