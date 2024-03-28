<?php
/**
 * Plugin Name: Custom Posts Importer
 * Description: Import posts from an external API and display them via a shortcode.
 * Version: 1.0
 * Author: Sofiia Vynnytska
 *
 * @package WordPress
 */

// Include the shortcode and meta box definitions.
require_once plugin_dir_path( __FILE__ ) . 'shortcodes/custom-posts-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/meta-box-definitions.php';

add_action( 'custom_posts_importer_event', 'import_custom_posts' );

/**
 * Imports custom posts from an external API.
 *
 * Fetches posts using the API, checks for existing posts with the same title to avoid duplicates,
 * creates necessary categories, sets the first admin as the author, handles the image upload, and
 * assigns meta values for 'site_link' and 'rating'.
 *
 * @return void
 */
function import_custom_posts() {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$api_url = "https://my.api.mockaroo.com/posts.json";
	$response = wp_remote_get($api_url, [
		'headers' => ['X-API-Key' => '413dfbf0']
	]);

	if ( is_wp_error( $response ) ) {
		error_log( 'Error retrieving posts: ' . $response->get_error_message() );
		return;
	}

	$posts = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $posts ) ) {
		error_log( 'Error: No posts to import' );
		return;
	}

	foreach ( $posts as $post ) {
		// Check if the post with the title already exists.
		if ( get_page_by_title( $post['title'], OBJECT, 'post' ) ) {
			continue;
		}

		// Create category if needed.
		$category_id = null;
		$category = get_category_by_slug( sanitize_title( $post['category'] ) );
		if ( ! $category ) {
			$term = wp_insert_term( $post['category'], 'category' );
			if ( ! is_wp_error( $term ) ) {
				$category_id = $term['term_id'];
			}
		} else {
			$category_id = $category->term_id;
		}

		// First administrator user.
		$users = get_users( [ 'role' => 'administrator', 'orderby' => 'ID', 'order' => 'ASC', 'number' => 1 ] );
		$admin_id = ! empty( $users ) ? $users[0]->ID : 1;

		// Date.
		$random_date = date( 'Y-m-d H:i:s', strtotime( '-' . rand( 0, 30 ) . ' days' ) );

		// Post data.
		$post_data = array(
			'post_title'     => wp_strip_all_tags( $post['title'] ),
			'post_content'   => $post['content'],
			'post_status'    => 'publish',
			'post_author'    => $admin_id,
			'post_category'  => array( $category_id ),
			'post_date'      => $random_date,
		);

		// Insert the post.
		$post_id = wp_insert_post( $post_data );

		// Add image.
		if ( ! is_wp_error( $post_id ) && ! empty( $post['image'] ) ) {
			$image = media_sideload_image( $post['image'], $post_id, $post['title'], 'id' );
			if ( ! is_wp_error( $image ) ) {
				set_post_thumbnail( $post_id, $image );
			}

			// Save 'site_link' as post meta.
			if ( ! empty( $post['site_link'] ) ) {
				update_post_meta( $post_id, 'site_link', esc_url_raw( $post['site_link'] ) );
			}

			// Save 'rating' as post meta.
			if ( ! empty( $post['rating'] ) ) {
				update_post_meta( $post_id, 'rating', floatval( $post['rating'] ) );
			}
		}
	}
}

// Schedule the event to run daily.
register_activation_hook( __FILE__, 'custom_posts_importer_activation' );

/**
 * Schedules a daily event to import custom posts when the plugin is activated.
 *
 * @return void
 */
function custom_posts_importer_activation() {
	if ( ! wp_next_scheduled( 'custom_posts_importer_event' ) ) {
		wp_schedule_event( time(), 'daily', 'custom_posts_importer_event' );
	}
}

// WP Cron Clearing on Deactivation.
register_deactivation_hook( __FILE__, 'custom_posts_importer_deactivation' );

/**
 * Clears the scheduled daily event for importing custom posts upon plugin deactivation.
 *
 * @return void
 */
function custom_posts_importer_deactivation() {
	$timestamp = wp_next_scheduled( 'custom_posts_importer_event' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'custom_posts_importer_event' );
	}
}

/**
 * Adding styles.
 * Enqueue the custom-posts.css file only when the 'custom_posts' shortcode is present on the page.
 */
function custom_posts_shortcode_css() {
	// Check if the current post has the 'custom_posts' shortcode.
	global $post;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'custom_posts' ) ) {
		wp_enqueue_style( 'custom-posts-css', plugins_url( 'assets/css/custom-posts.css', __FILE__ ) );
	}
}
add_action( 'wp_enqueue_scripts', 'custom_posts_shortcode_css' );
