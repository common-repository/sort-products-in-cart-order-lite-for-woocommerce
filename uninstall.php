<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://parmarkartik19.wordpress.com/
 * @since      1.0.0
 *
 * @package    Sort_Woocommerce_Products_Cart_Order
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Delete the data for the WordPress Multisite.
 */
if ( is_multisite() ) {

	$bkap_blog_list = get_sites();

	foreach ( $bkap_blog_list as $bkap_blog_list_key => $bkap_blog_list_value ) {

		$bkap_blog_id = $bkap_blog_list_value->blog_id;
		delete_blog_option( $bkap_blog_id, 'swpco_enable_sorting' );
		delete_blog_option( $bkap_blog_id, 'swpco_sort_by' );
	}
} else {

	delete_option( 'swpco_enable_sorting' );
	delete_option( 'swpco_sort_by' );
}

// Clear any cached data that has been removed.
wp_cache_flush();
