<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOO_PRODUCT_WISHLIST_DIR',
	WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "product-wishlist-for-woo" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_ADMIN', VI_WOO_PRODUCT_WISHLIST_DIR . "admin" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_FRONTEND', VI_WOO_PRODUCT_WISHLIST_DIR . "frontend" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_LANGUAGES', VI_WOO_PRODUCT_WISHLIST_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_INCLUDES', VI_WOO_PRODUCT_WISHLIST_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_TEMPLATES', VI_WOO_PRODUCT_WISHLIST_DIR . "templates" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_FONTS', VI_WOO_PRODUCT_WISHLIST_DIR . "fonts" . DIRECTORY_SEPARATOR );
//$plugin_url = plugins_url( 'woo-coupon-box' );
$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'VI_WOO_PRODUCT_WISHLIST_CSS', $plugin_url . "/css/" );
define( 'VI_WOO_PRODUCT_WISHLIST_CSS_DIR', VI_WOO_PRODUCT_WISHLIST_DIR . "css" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_JS', $plugin_url . "/js/" );
define( 'VI_WOO_PRODUCT_WISHLIST_JS_DIR', VI_WOO_PRODUCT_WISHLIST_DIR . "js" . DIRECTORY_SEPARATOR );
define( 'VI_WOO_PRODUCT_WISHLIST_IMAGES', $plugin_url . "/images/" );

/*Include functions file*/
if ( is_file( VI_WOO_PRODUCT_WISHLIST_INCLUDES . "functions.php" ) ) {
	require_once VI_WOO_PRODUCT_WISHLIST_INCLUDES . "functions.php";
}
if ( is_file( VI_WOO_PRODUCT_WISHLIST_INCLUDES . "install.php" ) ) {
	require_once VI_WOO_PRODUCT_WISHLIST_INCLUDES . "install.php";
}

if ( is_file( VI_WOO_PRODUCT_WISHLIST_INCLUDES . "data.php" ) ) {
	require_once VI_WOO_PRODUCT_WISHLIST_INCLUDES . "data.php";
}
if ( is_file( VI_WOO_PRODUCT_WISHLIST_INCLUDES . "support.php" ) ) {
	require_once VI_WOO_PRODUCT_WISHLIST_INCLUDES . "support.php";
}


vi_include_folder( VI_WOO_PRODUCT_WISHLIST_ADMIN, 'VI_WOO_PRODUCT_WISHLIST_Admin_' );
vi_include_folder( VI_WOO_PRODUCT_WISHLIST_FRONTEND, 'VI_WOO_PRODUCT_WISHLIST_Frontend_' );
//vi_include_folder( VI_WOO_PRODUCT_WISHLIST_TEMPLATES, 'VI_WOO_PRODUCT_WISHLIST_Template_' );
if ( is_file( VI_WOO_PRODUCT_WISHLIST_INCLUDES . "archive.php" ) ) {
	require_once VI_WOO_PRODUCT_WISHLIST_INCLUDES . "archive.php";
}
if ( is_file( VI_WOO_PRODUCT_WISHLIST_INCLUDES . "customize-control.php" ) ) {
	require_once VI_WOO_PRODUCT_WISHLIST_INCLUDES . "customize-control.php";
}