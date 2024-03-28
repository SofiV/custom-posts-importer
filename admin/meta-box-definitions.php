<?php
/**
 * Adds custom meta boxes for 'Rating' and 'Site Link' to the post editor.
 *
 * @package Custom_Posts_Importer
 */

/**
 * Adds meta boxes for custom fields in the post editor.
 */
function add_custom_meta_boxes() {
	add_meta_box(
		'custom_rating', // Unique ID.
		'Rating', // Box title.
		'custom_rating_meta_box_html', // Content callback for rating.
		'post' // Post type.
	);
	add_meta_box(
		'custom_site_link',
		'Site Link',
		'custom_site_link_meta_box_html',
		'post'
	);
}

/**
 * Renders the HTML for the rating meta box.
 *
 * @param WP_Post $post The current post object.
 */
function custom_rating_meta_box_html( $post ) {
	$rating = get_post_meta( $post->ID, 'rating', true );
	?>
	<label for="custom_rating_field">Rating:</label>
	<input type="number" step="0.1" name="custom_rating_field" id="custom_rating_field" value="<?php echo esc_attr($rating); ?>">
	<?php
}

/**
 * Renders the HTML for the site link meta box.
 *
 * @param WP_Post $post The current post object.
 */
function custom_site_link_meta_box_html( $post ) {
	$site_link = get_post_meta( $post->ID, 'site_link', true );
	?>
	<label for="site_link_field">Site Link:</label>
	<input type="text" name="site_link_field" id="site_link_field" value="<?php echo esc_url( $site_link ); ?>">
	<?php
}

/**
 * Saves the custom meta field data when a post is saved.
 *
 * @param int $post_id The ID of the current post being saved.
 */
function save_post_meta_data( $post_id ) {
	if ( array_key_exists( 'custom_rating_field', $_POST ) ) {
		update_post_meta(
			$post_id,
			'rating',
			$_POST['custom_rating_field']
		);
	}
	if ( array_key_exists( 'site_link_field', $_POST ) ) {
		update_post_meta(
			$post_id,
			'site_link',
			esc_url_raw( $_POST['site_link_field'] )
		);
	}
}

add_action( 'add_meta_boxes', 'add_custom_meta_boxes' );
add_action( 'save_post', 'save_post_meta_data' );
