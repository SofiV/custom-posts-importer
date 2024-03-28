<?php
/**
 * Custom Posts Importer Plugin Shortcode File
 *
 * Custom shortcode that fetches posts from the database and displays them in a custom format.
 * Example usage:
 * [custom_posts title="Latest Posts" count="5" sort="date"]
 * This shortcode will display the latest 5 posts in the default format.
 * You can customize the shortcode attributes to change the title, number of posts, and sort order.
 *
 * @package CustomPostsImporter
 */

/**
 * Shortcode to display posts using custom layout.
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string HTML content to display for the shortcode.
 */
function custom_posts_shortcode( $atts ) {
	// Default attributes.
	$atts = shortcode_atts(
		array(
			'title' => 'Latest Posts', // Default title of posts to show.
			'count' => 5, // Default number of posts to show.
			'sort'  => 'date', // Default sort field.
			'ids'   => '', // Default post IDs to include.
		),
		$atts,
		'custom_posts'
	);

	$count = intval( $atts['count'] );
	$sort = in_array( $atts['sort'], array( 'date', 'title', 'rating' ) ) ? $atts['sort'] : 'date';
	$ids = ! empty( $atts['ids'] ) ? explode( ',', $atts['ids'] ) : array();

	// Query arguments.
	$query_args = array(
		'posts_per_page' => $count,
		'orderby'        => $sort,
		'post__in'       => $ids,
		'post_status'    => 'publish',
		'post_type'      => 'post',
	);

	$posts = get_posts( $query_args );

	// Start output buffering to capture HTML.
	ob_start();

	if ( $posts ) {
		echo '<div class="custom-posts-list">';
		echo '<h2 class="custom-posts-list___title">' . esc_html( $atts['title'] ) . '</h2>';
		echo '<div class="custom-posts-list__list">';
		foreach ( $posts as $post ) {
			setup_postdata( $post );

			echo '<div class="custom-posts-list__item">';
			// Display thimbnaill.
			if ( has_post_thumbnail( $post->ID ) ) {
				echo '<a class="custom-posts-list__thumbnail-wrap" href="' . esc_url( get_permalink( $post->ID ) ) . '">';
				echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'custom-posts-list__thumbnail' ) );
				echo '</a>';
			}

			echo '<div class="custom-posts-list__content">';
			// Display category.
			echo '<div class="custom-posts-list__post-category">';
			foreach ( ( get_the_category( $post->ID ) ) as $category ) {
				echo esc_html( $category->cat_name ) . ' ';
			}
			echo '</div>';

			// Display the title.
			echo '<h3 class="custom-posts-list__caption"><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></h3>';

			echo '<div class="custom-posts-list__bottom flex-justify-between align-center gap-14">';
			echo '<a class="custom-posts-list__btn custom-posts-list__btn--tertiary" href="' . esc_url( get_permalink( $post->ID ) ) . '">Read More</a>';

			// Display rating and visit site button if rating exists or site link is available.
			$rating = get_post_meta( $post->ID, 'rating', true );
			$site_link = get_post_meta( $post->ID, 'site_link', true );
			if ( ! empty( $rating ) || ! empty( $site_link ) ) {
				echo '<div class="custom-posts-list__post-meta flex-justify-between align-center gap-14">';
				if ( ! empty( $rating ) ) {
					echo '<span class="custom-posts-list__rating">⭐ ' . esc_html( $rating ) . '</span>';
				}
				if ( ! empty( $site_link ) ) {
					echo '<a href="' . esc_url( $site_link ) . '" rel="nofollow noopener" target="_blank" class="custom-posts-list__btn custom-posts-list__bnt--primary">Visit Site</a>';
				}
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		wp_reset_postdata();
		echo '</div>';
	}

	$output = ob_get_clean();
	return $output;
}

add_shortcode( 'custom_posts', 'custom_posts_shortcode' );
