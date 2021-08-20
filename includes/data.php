<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOO_PRODUCT_WISHLIST_DATA {
	protected $prefix;
	private $params, $default, $class_icons;

	public function __construct() {
		global $vi_wcwl_settings;
		if ( ! $vi_wcwl_settings ) {
			$vi_wcwl_settings = get_option( 'vi_wishlist_params', array() );
		}

		$this->default = array(
			//setting
			'enable_wishlist'        => 1,
			'login_enable'           => '',
			'enable_multi'           => 1,
			//customize
			'ic_add_icon'            => 19,
			'ic_color'               => '#d55555',
			'ic_position'            => 'top_right',
			'ic_size'                => '25',
			'ft_select_icon'         => 1,
			'ft_position'            => 'middle_right',
			'ft_color'               => '#3d6b82',
			'ft_size'                => '40',
			'wl_header_background'   => '#323a37',
			'wl_header_color'        => '#ffffff',
			'wl_even_background'     => '#ffffff',
			'wl_odd_background'      => '#f2f2f2',
			'fb_share'               => 1,
			'tumblr_share'           => 1,
			'twitter_share'          => 1,
			'pinterest_share'        => 1,
			'instagram_share'        => 1,
			'copy_link'              => 1,
			'sb_header_font'         => 16,
			'sb_header_bg'           => '#ffffff',
			'sb_header_color'        => '#333333',
			'sb_header_txt_tranform' => 'uppercase',
			'sb_header_text'         => 'My Wishlist Manage',
			'sb_select_background'   => '#ffffff',
			'sb_select_color'        => '#000000',
			'sb_footer_btn_1_bg'     => '#000000',
			'sb_footer_btn_1_cl'     => '#ffffff',
			'sb_footer_btn_1_txt'    => 'Wishlist Manage',
			'sb_footer_btn_2_bg'     => '#000000',
			'sb_footer_btn_2_cl'     => '#ffffff',
			'sb_footer_btn_2_txt'    => 'Add All To Cart'
		);

		$this->params = apply_filters( 'vi_wishlist_params',
			wp_parse_args( $vi_wcwl_settings, $this->default ) );

		$this->class_icons = array(
			'add_to_wishlist_icons' => array(
				'vi-wcwl-h-heart',
				'vi-wcwl-h-favorite-1',
				'vi-wcwl-h-star',
				'vi-wcwl-h-favorite-3',
				'vi-wcwl-h-original-1',
				'vi-wcwl-h-5-stars-1',
				'vi-wcwl-h-stars',
				'vi-wcwl-h-favorite-4',
				'vi-wcwl-h-favorite-6',
				'vi-wcwl-h-hearts-1',
				'vi-wcwl-h-rating',
				'vi-wcwl-h-star-2',
				'vi-wcwl-h-quality-1',
				'vi-wcwl-h-best-seller-1',
				'vi-wcwl-h-thumbs-up',
				'vi-wcwl-h-like-2',
				'vi-wcwl-h-like-4',
				'vi-wcwl-h-like-5',
				'vi-wcwl-h-like-7',
				'vi-wcwl-h-like-9',

			),
			'like_icons'            => array(
				'vi-wcwl-h-like',
				'vi-wcwl-h-favorite',
				'vi-wcwl-h-star-1',
				'vi-wcwl-h-favorite-2',
				'vi-wcwl-h-original',
				'vi-wcwl-h-5-stars',
				'vi-wcwl-h-stars-1',
				'vi-wcwl-h-favorite-5',
				'vi-wcwl-h-favorite-7',
				'vi-wcwl-h-hearts',
				'vi-wcwl-h-rating-1',
				'vi-wcwl-h-star-3',
				'vi-wcwl-h-quality',
				'vi-wcwl-h-best-seller',
				'vi-wcwl-h-thumb-up',
				'vi-wcwl-h-like-1',
				'vi-wcwl-h-like-3',
				'vi-wcwl-h-like-6',
				'vi-wcwl-h-like-8',
				'vi-wcwl-h-like-10',

			),
			'floating_icons'        => array(
				'vi-wl-wishlist',
				'vi-wl-favorites',
				'vi-wl-favorites-1',
				'vi-wl-add-to-favorites',
				'vi-wl-like',
				'vi-wl-favorite-5',
			)
		);
	}

	public function get_class_icon( $index = 0, $type = '' ) {
		if ( ! $type ) {
			return false;
		}
		$icons = $this->get_class_icons( $type ) ?? array();
		if ( empty( $icons ) ) {
			return false;
		} else {
			return $icons[ $index ] ?? $icons[0];
		}
	}

	public function get_class_icons( $type = '' ) {
		if ( ! $type ) {
			return $this->class_icons;
		}

		return $this->class_icons[ $type ] ?? array();
	}

	public function enable( $prefix ) {
		if ( ! $prefix ) {
			return false;
		}
		if ( ! $this->get_params( $prefix . 'enable' ) ) {
			return false;
		}
		if ( wp_is_mobile() && ! $this->get_params( $prefix . 'mobile_enable' ) ) {
			return false;
		}

		return true;
	}

	public function get_params( $name = "" ) {
		if ( ! $name ) {
			return $this->params;
		}

		return apply_filters( 'vi_wishlist_params_' . $name, $this->params[ $name ] ?? false );
	}

	public function get_default( $name = "" ) {
		if ( ! $name ) {
			return $this->default;
		} elseif ( isset( $this->default[ $name ] ) ) {
			return apply_filters( 'vi_wishlist_params_default-' . $name, $this->default[ $name ] );
		} else {
			return false;
		}
	}

	public function set( $name ) {
		if ( is_array( $name ) ) {
			return implode( ' ', array_map( array( $this, 'set' ), $name ) );

		} else {
			return esc_attr__( $this->prefix . $name );
		}
	}

	public function add_inline_style( $element, $name, $style, $suffix = '' ) {
		if ( ! $element || ! is_array( $element ) ) {
			return '';
		}
		$element = implode( ',', $element );
		$return  = $element . '{';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$get_value  = $this->get_params( $value );
				$get_suffix = $suffix[ $key ] ?? '';
				$return     .= $style[ $key ] . ':' . $get_value . $get_suffix . ';';
			}
		}
		$return .= '}';

		return $return;
	}

}

