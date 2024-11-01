<?php
/**
 * Sort Products in Cart & Order Lite for WooCommerce
 *
 * This plugin is used to sort the Cart items and Order items. Products in the Cart and Order will be sorted in ascending and descending order by product name.
 *
 * @link              https://kartechify.com/product/sort-woocommerce-products-in-cart-and-order/
 * @since             1.0
 * @package           Sort_Woocommerce_Products_Cart_Order_Lite
 *
 * @wordpress-plugin
 * Plugin Name:          Sort Products in Cart & Order Lite  for WooCommerce
 * Plugin URI:           https://kartechify.com/product/sort-woocommerce-products-in-cart-and-order/
 * Description:          This plugin is used to sort the Cart items and Order items. Products in the Cart and Order will be sorted in ascending & descending order.
 * Version:              1.3
 * Author:               Kartik Parmar
 * Author URI:           https://parmarkartik19.wordpress.com/
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          sort-woocommerce-products-cart-order-lite
 * Tested up to:         6.6.2
 * Requires PHP:         7.3
 * WC requires at least: 3.0.0
 * WC tested up to:      9.3.3
 * Requires Plugins:     woocommerce
 * Domain Path:          /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Currently plugin version.
 * Start at version 1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SWPCOL_VERSION', '1.3' );
define( 'SWPCOL_PLUGIN_NAME', 'Sort WooCommerce Products in Cart/Order Lite' );
define( 'SWPCOL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

add_filter( 'woocommerce_get_sections_products', 'swpcol_cart_products_sorting_add_settings_tab' );
add_filter( 'woocommerce_get_settings_products', 'swpcol_cart_products_sorting_get_settings', 10, 2 );

add_filter( 'woocommerce_order_get_items', 'swpcol_woocommerce_order_get_items', 10, 3 );
add_action( 'woocommerce_cart_loaded_from_session', 'swpcol_sort_woocommerce_products_cart_items' );

add_filter( 'plugin_action_links_' . SWPCOL_PLUGIN_BASENAME, 'swpcol_plugin_settings_link' );

add_action( 'before_woocommerce_init', 'swpcol_cart_products_sorting_custom_order_tables_compatibility', 999 );

/**
 * HPOS Compatibility.
 *
 * @since 1.2
 */
function swpcol_cart_products_sorting_custom_order_tables_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'sort-products-in-cart-order-lite-for-woocommerce/sort-products-in-cart-order-lite-for-woocommerce.php', true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', 'sort-products-in-cart-order-lite-for-woocommerce/sort-products-in-cart-order-lite-for-woocommerce.php', true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', 'sort-products-in-cart-order-lite-for-woocommerce/sort-products-in-cart-order-lite-for-woocommerce.php', true );
	}
}

/**
 * Adding Settings page link to plugin meta.
 *
 * @param array $links Array of links.
 *
 * @since 1.0
 */
function swpcol_plugin_settings_link( $links ) {
	$setting_text             = __( 'Settings', 'sort-woocommerce-products-cart-order-lite' );
	$setting_link['settings'] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=products&section=swpco_sort_product_cart' ) ) . '">' . $setting_text . '</a>';
	$links                    = $setting_link + $links;
	return $links;
}

/**
 * Registering the section in Products tab.
 *
 * @param array $settings_tab Array of Setting tab.
 *
 * @since 1.0
 */
function swpcol_cart_products_sorting_add_settings_tab( $settings_tab ) {
	$settings_tab['swpco_sort_product_cart'] = __( 'Sort Products in Cart/Order', 'sort-woocommerce-products-cart-order-lite' );
	return $settings_tab;
}

/**
 * Adding Fields in Sort Products in Cart/Order section.
 *
 * @param array  $settings Array of Settings options.
 * @param string $current_section Current Screen.
 * @since 1.0
 */
function swpcol_cart_products_sorting_get_settings( $settings, $current_section ) {
	$custom_settings = array();

	if ( 'swpco_sort_product_cart' === $current_section ) {

		$custom_settings = array(
			array(
				'name' => __( 'Sort Products in Cart & Order', 'sort-woocommerce-products-cart-order-lite' ),
				'type' => 'title',
				'desc' => __( 'Sorting products in cart, orders and email notification.', 'sort-woocommerce-products-cart-order-lite' ),
				'id'   => 'swpco_sort_product_in_cart',
			),
			array(
				'name' => __( 'Enable Sorting', 'sort-woocommerce-products-cart-order' ),
				'type' => 'checkbox',
				'desc' => __( 'Enabling this option will sort the products in complete purchase cycle.', 'sort-woocommerce-products-cart-order-lite' ),
				'id'   => 'swpco_enable_sorting',
			),
			array(
				'name'    => __( 'Sort by', 'sort-woocommerce-products-cart-order-lite' ),
				'type'    => 'select',
				/* translators: %s: Link to the pro version. */
				'desc'    => sprintf( __( 'Sorting the products in the cart/order by ascending & descending order. <br><br>Purchase <b><a href="%s" target="_blank">Sort WooCommerce Products in Cart and Order</a></b> for additional 20 sorting options.', 'sort-woocommerce-products-cart-order-lite' ), 'https://kartechify.com/product/sort-woocommerce-products-in-cart-and-order/' ),
				'id'      => 'swpco_sort_by',
				'options' => array(
					'ascending'  => __( 'Ascending', 'sort-woocommerce-products-cart-order-lite' ),
					'descending' => __( 'Descending', 'sort-woocommerce-products-cart-order-lite' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'swpco_sorting_product_section',
			),
		);

		return $custom_settings;
	} else {
		return $settings;
	}
}

/**
 * This function is reposnsible for sorting the products on cart.
 *
 * @since 1.0
 */
function swpcol_sort_woocommerce_products_cart_items() {

	$swpco_enable = get_option( 'swpco_enable_sorting', 'no' );

	if ( 'yes' === $swpco_enable ) {

		$swpco_sort_by = get_option( 'swpco_sort_by', 'ascending' );

		// Read the cart.
		$products_in_cart = array();
		foreach ( WC()->cart->get_cart_contents() as $key => $item ) {
			$products_in_cart[ $key ] = strtolower( $item['data']->get_title() );
		}

		// Sorting cart items.
		$descending = array( 'descending' );
		natsort( $products_in_cart );
		if ( in_array( $swpco_sort_by, $descending, true ) ) {
			$products_in_cart = array_reverse( $products_in_cart );
		}

		// Assigning sorted items to cart.
		$cart_contents = array();
		foreach ( $products_in_cart as $cart_key => $product_title ) {
			$cart_contents[ $cart_key ] = WC()->cart->cart_contents[ $cart_key ];
		}
		WC()->cart->cart_contents = $cart_contents;
	}
}

/**
 * This function is reposnsible for sorting the products on edit order page.
 *
 * @param array  $items Array of Items.
 * @param obj    $order Order Object.
 * @param string $types Type of Item.
 * @since 1.0
 */
function swpcol_woocommerce_order_get_items( $items, $order, $types ) {

	$swpco_enable = get_option( 'swpco_enable_sorting', 'no' );

	if ( 'line_item' !== $types[0] || 'yes' !== $swpco_enable ) {
		return $items;
	}

	$swpco_sort_by    = get_option( 'swpco_sort_by', 'ascending' );
	$products_in_cart = array();

	foreach ( $items as $key => $value ) {
		$_product                 = $value->get_product();
		$products_in_cart[ $key ] = ( $_product ) ? strtolower( $_product->get_title() ) : '';
	}

	// Sorting cart items.
	$descending = array( 'descending' );
	natsort( $products_in_cart );
	if ( in_array( $swpco_sort_by, $descending, true ) ) {
		$products_in_cart = array_reverse( $products_in_cart, true );
	}

	// Assigning sorted items to cart.
	$cart_contents = array();
	foreach ( $products_in_cart as $cart_key => $cart_value ) {
		$cart_contents[ $cart_key ] = $items[ $cart_key ];
	}

	$items = $cart_contents;

	return $items;
}
