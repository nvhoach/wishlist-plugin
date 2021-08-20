<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_filter( 'template_include', 'get_custom_post_type_template' ,10);

function get_custom_post_type_template( $archive_template ) {
	global $post;

	if ( is_post_type_archive( 'vi_woo_wishlist' ) && is_archive() ) {
		$archive_template = VI_WOO_PRODUCT_WISHLIST_TEMPLATES . 'archive-vi-woo-wishlist.php';
	}

	return $archive_template;
}