<?php
/*
 * Plugin Name: Product Wishlist For Woocommerce
 * Description: Product wishlist for woocommerce, free version.
 * Plugin URI: villatheme.com
 * Version: 1.0.0
 * Requires PHP: 7.0
 * Author: VillaTheme
 * Author URI: villatheme.com
 * Text Domain: wishlist-for-woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
define( 'VI_WOO_PRODUCT_WISHLIST_VERSION', '1.0.0' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "product-wishlist-for-woo" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
	require_once $init_file;
}

class VI_WISHLIST_FOR_WOO_F {


	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'woocommerce_account_menu_items', [ $this, 'create_wishlist_menu_item' ] );
		add_filter( 'woocommerce_get_endpoint_url', [ $this, 'vi_wishlist_hook_endpoint' ], 10, 4 );
		add_action( 'admin_notices', array( $this, 'global_note' ) );
	}

	public function global_note() {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			?>
            <div id="message" class="error">
                <p><?php _e( 'Please install and activate WooCommerce to use Product Wishlist for WooCommerce plugin.', 'wishlist-for-woo' ); ?></p>
            </div>
			<?php
		}
	}

	public function vi_wishlist_hook_endpoint( $url, $endpoint, $value, $permalink ) {
		$page_id  = get_option( 'vi_wishlist_page_id' );
		$page     = get_post( $page_id );
		$page_url = $page->guid;
		if ( $endpoint === 'vi-wishlist-menu' ) {
			$url = $page_url;
		}

		return $url;
	}

	public function create_wishlist_menu_item( $menu_link ) {
		$new = array( 'vi-wishlist-menu' => 'My Wishlist' );

		return array_slice( $menu_link, 0, 1, true ) + $new + array_slice( $menu_link, 1, null, true );
	}

	public function install() {
		$this->save_default_options();
		$this->create_page_my_wishlist();
	}

	public function save_default_options() {
		$data_default_options = array(
			"enable_logged" => "0",
			"popup"         => "1",
			"expired_time"  => "1",
			"item_per_page" => "1",
			'product_price' => '1',
			'stock'         => '1',
			'date'          => '0',
		);
		if ( ! get_option( 'vi_wishlist_params', '' ) ) {
			update_option( 'vi_wishlist_params', $data_default_options );
		}
	}

	public function create_page_my_wishlist() {
		global $wpdb;
		if ( ! get_option( 'vi_wishlist_page_id', '' ) ) {
			wp_insert_post( [
				'post_type'      => 'page',
				'post_content'   => '<!-- wp:shortcode -->[vi_woo_my_wishlist]<!-- /wp:shortcode -->',
				'comment_status' => 'closed',
				'post_status'    => 'publish',
				'post_title'     => 'My Wishlist Manage',
			] );

			$page_id = $wpdb->insert_id;

			update_option( 'vi_wishlist_page_id', $page_id );
		}
	}

	public function register_post_type() {
		$wishlist = new WP_Query( [
			'post_type'   => 'vi_woo_wishlist',
			'post_status' => 'pending'
		] );
		$count    = $wishlist->found_posts;
		$labels   = array(
			'name'          => __( 'All Wishlist', 'wishlist-for-woo' ),
			'all_items'     => $count ? __( 'All Wishlist', 'wishlist-for-woo' ) . ' <span class="awaiting-mod">' . $count . '</span>' : __( 'All Wishlist', 'wishlist-for-woo' ),
			'singular_name' => __( 'Wishlist', 'wishlist-for-woo' ),
			'menu_name'     => __( 'Wishlist', 'wishlist-for-woo' ),
		);
		register_post_type( 'vi_woo_wishlist',
			array(
				'labels'          => $labels,
				'public'          => true,
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'product-wishlist' ),
				'capability_type' => 'post',
				'menu_icon'       => 'dashicons-heart',
				'supports'        => array( 'title', 'excerpt', 'author' ),
			)
		);
	}

}

new VI_WISHLIST_FOR_WOO_F();