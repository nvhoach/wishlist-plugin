<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * show icon add to wishlist
 * show sidebar
 * Class VI_WOO_PRODUCT_WISHLIST_Frontend
 *
 */
class VI_WOO_PRODUCT_WISHLIST_Frontend_Frontend {

	const POST_TYPE = 'vi_woo_wishlist';
	const WISHLIST_DEFAULT = 'vi_wishlist_default';
	const POST_META_KEY = 'vi_woo_wishlist_meta';
	protected $settings;
	protected $product_id;
	protected $wishlist_id;
	protected $wishlist_name;
	protected $author;
	protected $flag;

	public function __construct() {
		$this->settings = new VI_WOO_PRODUCT_WISHLIST_DATA();
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [ $this, 'display_add_to_wishlist_icon' ] );
		add_filter( 'woocommerce_product_get_image', [ $this, 'display_add_to_wishlist_icon_archive' ] );
		add_filter( 'woocommerce_before_widget_product_list', [ $this, 'woocommerce_before_widget_product_list' ] );
		add_filter( 'woocommerce_after_widget_product_list', [ $this, 'woocommerce_after_widget_product_list' ] );
		add_action( 'woocommerce_before_mini_cart', [ $this, 'woocommerce_before_mini_cart' ] );
		add_action( 'woocommerce_after_mini_cart', [ $this, 'woocommerce_after_mini_cart' ] );
		add_action( 'woocommerce_before_cart', [ $this, 'woocommerce_before_cart' ] );
		add_action( 'woocommerce_after_cart', [ $this, 'woocommerce_after_cart' ] );
		add_action( 'wp_ajax_vi_wishlist_icon_button', array( $this, 'save_to_wishlist' ) );
		add_action( 'wp_ajax_nopriv_vi_wishlist_icon_button', array( $this, 'save_to_wishlist' ) );
		add_action( 'wp_ajax_vi_woo_wishlist_sidebar', array( $this, 'show_product_on_sidebar' ) );
		add_action( 'wp_ajax_nopriv_vi_woo_wishlist_sidebar', array( $this, 'show_product_on_sidebar' ) );
		add_action( 'wp_ajax_vi_woo_wishlist_sidebar_delete_prod', array( $this, 'delete_prod_on_sidebar' ) );
		add_action( 'wp_ajax_nopriv_vi_woo_wishlist_sidebar_delete_prod', array( $this, 'delete_prod_on_sidebar' ) );
		add_action( 'wp_ajax_vi_add_wishlist_sidebar', array( $this, 'add_wishlist_sidebar' ) );
		add_action( 'wp_ajax_nopriv_vi_add_wishlist_sidebar', array( $this, 'add_wishlist_sidebar' ) );
		add_action( 'wp_ajax_add_to_cart_on_sidebar', array( $this, 'add_to_cart_on_sidebar' ) );
		add_action( 'wp_ajax_nopriv_add_to_cart_on_sidebar', array( $this, 'add_to_cart_on_sidebar' ) );
		add_action( 'wp_ajax_add_wishlist_from_localstorage', array( $this, 'add_wishlist_from_localstorage' ), 20 );
		add_action( 'wp_ajax_nopriv_add_wishlist_from_localstorage', array(
			$this,
			'add_wishlist_from_localstorage'
		), 20 );
		add_action( 'wp_footer', [ $this, 'create_floating_icon' ] );
		add_action( 'wp_footer', [ $this, 'create_sidebar' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 20 );
		add_action( 'wp_ajax_render_local_storage', array( $this, 'render_local_storage' ), 25 );
		add_action( 'wp_ajax_nopriv_render_local_storage', array( $this, 'render_local_storage' ), 25 );
		add_action( 'wp_login', [ $this, 'set_cookie_after_login' ] );
		add_action( 'wp_ajax_vi_wcwl_get_class_icon', [ $this, 'vi_wcwl_get_class_icon' ] );
		add_action( 'wp_ajax_nopriv_vi_wcwl_get_class_icon', [ $this, 'vi_wcwl_get_class_icon' ] );
		add_action( 'wp_head', [ $this, 'apply_customize_option' ] );
		add_action( 'wp_ajax_add_to_cart_grouped_products', [ $this, 'add_to_cart_grouped_products' ] );
		add_action( 'wp_ajax_nopriv_add_to_cart_grouped_products', [ $this, 'add_to_cart_grouped_products' ] );
		add_action( 'woocommerce_before_single_variation', [ $this, 'woocommerce_before_single_variation' ] );
		add_action( 'wp_default_scripts', function ( $scripts ) {
			if ( isset( $scripts->registered['jquery'] ) ) {
				$script = $scripts->registered['jquery'];
				if ( $script->deps ) { // Check whether the script has any dependencies
					$script->deps = array_diff( $script->deps, array(
						'jquery-migrate'
					) );
				}
			}
		} );
		add_action( 'wp_ajax_render_variations_form_html', [ $this, 'render_variations_form_html' ] );
		add_action( 'wp_ajax_nopriv_render_variations_form_html', [ $this, 'render_variations_form_html' ] );
		add_action( 'wp_ajax_add_all_to_cart_sidebar', [ $this, 'add_all_to_cart_sidebar' ] );
		add_action( 'wp_ajax_nopriv_add_all_to_cart_sidebar', [ $this, 'add_all_to_cart_sidebar' ] );
	}

	public static function add_to_cart_on_sidebar() {
		ob_start();
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$product           = wc_get_product( $product_id );
		$quantity          = empty( sanitize_text_field( $_POST['quantity'] ) ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = 0;
		$variation         = array();

		if ( $product && 'variation' === $product->get_type() ) {
			$cart_item_data = $_POST;
			unset( $cart_item_data['quantity'] );
			foreach ( $cart_item_data as $key => $value ) {
				if ( preg_match( "/^attribute*/", sanitize_text_field( $key ) ) ) {
					$variation[ $key ] = sanitize_text_field( $value );
				}
			}
			$variation_id = $product_id;
			$product_id   = $product->get_parent_id();

		}

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id,
				$variation ) && 'publish' === $product_status ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}

			WC_AJAX::get_refreshed_fragments();

		} else {

			// If there was an error adding to the cart, redirect to the product page to show any errors.
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ),
					$product_id ),
			);

			wp_send_json( $data );
		}
		// phpcs:enable
	}

	public static function add_to_cart_grouped_products() {

		$productArr = $_POST['quantity'];

		$result = array_filter( $productArr, function ( $v ) {
			return trim( $v );
		} );

		if ( ! empty( $result ) ) {
			foreach ( $result as $product_id => $quantity ) {
				$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
				$product           = wc_get_product( $product_id );
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id,
					$quantity );
				$product_status    = get_post_status( $product_id );
				$variation_id      = 0;
				$variation         = array();

				if ( $product && 'variation' === $product->get_type() ) {
					$cart_item_data = $_POST;
					unset( $cart_item_data['quantity'] );
					foreach ( $cart_item_data as $key => $value ) {
						if ( preg_match( "/^attribute*/", sanitize_text_field( $key ) ) ) {
							$variation[ $key ] = sanitize_text_field( $value );
						}
					}
					$variation_id = $product_id;
					$product_id   = $product->get_parent_id();

				}

				if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id,
						$variation ) && 'publish' === $product_status ) {

					do_action( 'woocommerce_ajax_added_to_cart', $product_id );

					if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
						wc_add_to_cart_message( array( $product_id => $quantity ), true );
					}

				} else {
					// If there was an error adding to the cart, redirect to the product page to show any errors.
					$data = array(
						'error'       => true,
						'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error',
							get_permalink( $product_id ),
							$product_id ),
					);
					wp_send_json( $data );
				}
//			 phpcs:enable
			}
			WC_AJAX::get_refreshed_fragments();
		}

	}

	public static function render_variations_form_html() {
		if ( isset ( $_POST['productId'] ) ) {
			global $product;
			$product_id = sanitize_text_field( $_POST['productId'] );
			$product    = wc_get_product( $product_id );
			ob_start();
			switch ( $product->get_type() ) {
				case 'variable':
					woocommerce_variable_add_to_cart();
					break;
				case 'grouped':
					woocommerce_grouped_add_to_cart();
					break;
			}

			wp_send_json( [
				'add_to_cart_form' => ob_get_clean(),
			] );
		}
	}

	public static function re_wp_override_woo_templates( $located, $template_name ) {
		if ( $template_name == 'global/quantity-input.php' ) {
			$located = VI_WOO_PRODUCT_WISHLIST_TEMPLATES . 'woocommerce/quantity-input.php';
		}

		return $located;
	}


	public function add_all_to_cart_sidebar() {

		foreach ( $_REQUEST['data'] as $data ) {

			ob_start();

			$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $data['product_id'] ) );
			$product           = wc_get_product( $product_id );
			$quantity          = empty( $data['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $data['quantity'] ) );
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
			$product_status    = get_post_status( $product_id );
			$variation_id      = $data['variation_id'];
			$variation         = array();

			if ( $product && 'variation' === $product->get_type() ) {
				foreach ( $data['variation'] as $value ) {
					foreach ( $value as $k => $v ) {
						$variation[ $k ] = $v;
					}
				}
				$product_id = $product->get_parent_id();
			}
			if ( $product && 'grouped' === $product->get_type() ) {
				VI_WOO_PRODUCT_WISHLIST_Frontend_Shortcode::add_group_product();
			} elseif ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id,
					$variation ) && 'publish' === $product_status ) {

				do_action( 'woocommerce_ajax_added_to_cart', $product_id );

				if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
					wc_add_to_cart_message( array( $product_id => $quantity ), true );
				}

			} else {
				// If there was an error adding to the cart, redirect to the product page to show any errors.
				$data2 = array(
					'error'       => true,
					'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error',
						get_permalink( $product_id ),
						$product_id ),
				);

				wp_send_json( $data2 );
			}
		}
		WC_AJAX::get_refreshed_fragments();
	}

	public function woocommerce_before_single_variation() {
		?>
        <style type="text/css">
            .woocommerce-variation-description {
                display: none;
            }
        </style>
		<?php
	}

	public function woocommerce_before_cart() {
		$this->flag = true;
	}

	public function woocommerce_after_cart() {
		$this->flag = false;
	}

	public function woocommerce_before_mini_cart() {
		$this->flag = true;
	}

	public function woocommerce_after_mini_cart() {
		$this->flag = false;
	}

	public function woocommerce_before_widget_product_list( $html ) {
		$this->flag = true;

		return $html;
	}

	public function woocommerce_after_widget_product_list( $html ) {
		$this->flag = false;

		return $html;
	}

	public function apply_customize_option() {
		?>
        <style type="text/css" id="vi-wcwl-ic-icon">
            <?php $add_icon_size =  $this->settings->get_params('ic_size');
            $add_icon_color =  $this->settings->get_params('ic_color');
            ?>
            .vi-wl-icon-button-like {
                font-size: <?php echo sprintf('%spx', $add_icon_size) ?>;
                color: <?php echo sprintf('%s', $add_icon_color) ?>;
                display: flex;
                /*background-color: #ffffff;*/
                /*border-radius: 50%;*/
            }

            .vi-wcwl-h-waiting-loading-spiner.vi-wcwl-spin-icon {
                width: <?php echo sprintf('%spx', $add_icon_size); ?>;
                height: <?php echo sprintf('%spx', $add_icon_size); ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-ft">
            <?php
             $get_floating_icon_color =  $this->settings->get_params('ft_color');
            $get_floating_icon_size =  $this->settings->get_params('ft_size');
            ?>
            .vi-wl-icon-bar .vi-wl-floating-icon-sidebar {
                color: <?php echo sprintf('%s', $get_floating_icon_color) ?>;
                font-size: <?php echo sprintf('%spx', $get_floating_icon_size); ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-wl-header">
            <?php
             $header_background =  $this->settings->get_params('wl_header_background');
             $header_color =  $this->settings->get_params('wl_header_color');

            ?>
            .vi-wl-h-table-product-rsptable .vi-wl-h-table-product-header {
                background-color: <?php echo sprintf('%s', $header_background) ?>;
                color: <?php echo sprintf('%s', $header_color) ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-wl-body">
            <?php
            $even_background = $this->settings->get_params('wl_even_background');
            $odd_background = $this->settings->get_params('wl_odd_background');

            ?>
            .vi-wl-h-table-product-rsptable .vi-wcwl-h-table-content:nth-of-type(even) {
                background-color: <?php echo sprintf('%s', $even_background) ?>;
            }

            .vi-wl-h-table-product-rsptable .vi-wcwl-h-table-content:nth-of-type(odd) {
                background-color: <?php echo sprintf('%s', $odd_background) ?>;
            }
        </style>

        <style id="vi-wcwl-sidebar-style-custom">
            <?php
            $sb_header_background = $this->settings->get_params('sb_header_bg');
            $sb_header_color = $this->settings->get_params('sb_header_color');
            $sb_header_text_transform = $this->settings->get_params('sb_header_txt_tranform');
            $sb_select_background = $this->settings->get_params('sb_select_background');
            $sb_select_color = $this->settings->get_params('sb_select_color');
            $sb_footer_btn_1_background = $this->settings->get_params('sb_footer_btn_1_bg');
            $sb_footer_btn_1_color = $this->settings->get_params('sb_footer_btn_1_cl');
            $sb_footer_btn_2_background = $this->settings->get_params('sb_footer_btn_2_bg');
            $sb_footer_btn_2_color = $this->settings->get_params('sb_footer_btn_2_cl');
            ?>

            .vi-wl-h-cd-panel__container .cd-panel__header {
                background-color: <?php echo $sb_header_background ?>;
            }

            .vi-wl-h-cd-panel__container .cd-panel__header h4 a {
                text-transform: <?php echo $sb_header_text_transform ?>;
                color: <?php echo  $sb_header_color?>;
            }

            .select.vi-wl-h-sidebar-custom-select {
                background-color: <?php echo $sb_select_background ?>;
                color: <?php echo $sb_select_color ?>;
            }

            .vi-wl-sidebar-wrap-footer a.vi-wcwl-h-sidebar-footer-link {
                background-color: <?php echo $sb_footer_btn_1_background ?>;
                color: <?php echo $sb_footer_btn_1_color ?>;
            }

            .vi-wl-sidebar-wrap-footer button.vi-wcwl-sidebar-btn-add-all-to-cart {
                background-color: <?php echo $sb_footer_btn_2_background ?>;
                color: <?php echo $sb_footer_btn_2_color ?>;
            }


        </style>
		<?php
	}

	public function display_add_to_wishlist_icon( $html ) {
		if ( $this->settings->get_params( 'enable_wishlist' ) != 1 ) {
			return $html;
		}
		if ( $this->flag ) {
			return $html;
		}

		global $product;
		$icon     = $this->settings->get_class_icon( $this->settings->get_params( 'ic_add_icon' ),
			'add_to_wishlist_icons' );
		$position = $this->settings->get_params( 'ic_position' );

		$pattern = "/<a.+?>/";

		preg_match( $pattern, $html, $matches );

		return preg_replace( $pattern, '
            <span class="vi-wishlist-icon-button vi-wcwl-add-icon-position-' . esc_html( $position ) . '"
                  data-position="' . esc_attr( $position ) . '"
                  data-product_id="' . esc_attr( $product->get_id() ) . '">
                    <i class="' . esc_attr( $icon ) . ' vi-wl-icon-button-like"></i>
            </span><div class="vi-wcwl-h-waiting-loading-spiner vi-wishlist-icon-button vi-wcwl-add-icon-position-' . esc_html( $position ) . '"></div>' . $matches[0],
			$html );

	}

	public function display_add_to_wishlist_icon_archive( $html ) {

		if ( $this->settings->get_params( 'enable_wishlist' ) != 1 ) {
			return $html;
		}
		if ( $this->flag ) {
			return $html;
		}
		global $product;

		if ( ! $product ) {
			return $html;
		}

		if ( ! in_the_loop() ) {
			return $html;
		}

		$icon     = $this->settings->get_class_icon( $this->settings->get_params( 'ic_add_icon' ),
			'add_to_wishlist_icons' );
		$position = $this->settings->get_params( 'ic_position' );
		ob_start();
		?>
        <div class="vi-wcwl-display-icon-add-to-wishlist">
            <span class="vi-wishlist-icon-button vi-wcwl-add-icon-position-<?php echo esc_html( $position ) ?>"
                  data-position="<?php echo esc_attr( $position ) ?>"
                  data-product_id="<?php echo esc_attr( $product->get_id() ) ?>">
                    <i class="<?php echo esc_attr( $icon ); ?> vi-wl-icon-button-like"></i>
            </span>
            <div class="vi-wcwl-h-waiting-loading-spiner vi-wishlist-icon-button vi-wcwl-add-icon-position-<?php echo esc_html( $position ) ?>"></div>
			<?php echo wp_kses_post( $html ); ?>
        </div>

		<?php

		return ob_get_clean();
	}

	public function vi_wcwl_get_class_icon() {
		$result   = array(
			'status'  => '',
			'message' => '',
		);
		$settings = new VI_WOO_PRODUCT_WISHLIST_DATA();
		$icon_id  = isset( $_POST['icon_id'] ) ? sanitize_text_field( $_POST['icon_id'] ) : '';
		$type     = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		if ( is_numeric( $icon_id ) && $type && $class = $settings->get_class_icon( $icon_id, $type ) ) {
			$result['status']  = 'success';
			$result['message'] = $class;
		}
		wp_send_json( $result );
	}

	public function set_cookie_after_login() {
		setcookie( 'vi_wl_check_login', 'logged_in', 0, '/' );
	}

	public function add_wishlist_from_localstorage() {

		if ( is_user_logged_in() ) {
			global $wpdb;
			if ( isset( $_POST['info'] ) ) {
				foreach ( $_POST['info'] as $wishlist_info ) {
					if ( empty( $wishlist_info['name']['wishlistID'] ) ) {
						wp_insert_post( [
							'post_title'   => sanitize_text_field( $wishlist_info['name']['wishlistName'] ),
							'post_type'    => 'vi_woo_wishlist',
							'post_author'  => get_current_user_id(),
							'post_content' => '<!-- wp:shortcode -->[vi_woo_wishlist]<!-- /wp:shortcode -->',
							'post_status'  => 'private',
						] );
					} else {
						if ( false === get_post_status( $wishlist_info['name']['wishlistID'] ) ) {
							wp_insert_post( [
								'post_title'   => sanitize_text_field( $wishlist_info['name']['wishlistName'] ),
								'post_type'    => 'vi_woo_wishlist',
								'post_author'  => get_current_user_id(),
								'post_content' => '<!-- wp:shortcode -->[vi_woo_wishlist]<!-- /wp:shortcode -->',
								'post_status'  => 'private',
							] );
						} else {
							wp_update_post( [
								'ID'         => $wishlist_info['name']['wishlistID'],
								'post_title' => sanitize_text_field( $wishlist_info['name']['wishlistName'] ),
							] );
						}
					}

					$this->wishlist_id = $wpdb->insert_id;

					update_post_meta( $this->wishlist_id, self::POST_META_KEY, [
						'product_id' => $wishlist_info['product_id'] ?? []
					] );
					update_user_meta( get_current_user_id(), 'vi_wishlist_default',
						$this->wishlist_id == 0 ? $wishlist_info['name']['wishlistID'] : $this->wishlist_id );
				}
				$this->render_local_storage();
			}
		}
		wp_die();
	}

	public function render_local_storage() {
		if ( is_user_logged_in() ) {
			$all_wishlist     = [];
			$wishlist_items   = [];
			$default_wishlist = '';

			$get_wishlist = new WP_Query( [
				'post_type'      => self::POST_TYPE,
				'author'         => get_current_user_id(),
				'post_status'    => [ 'publish', 'private', 'pending' ],
				'posts_per_page' => - 1
			] );

			if ( $get_wishlist->have_posts() ) {
				while ( $get_wishlist->have_posts() ) {
					$get_wishlist->the_post();
					$get_meta                           = get_post_meta( get_the_ID(), self::POST_META_KEY, true );
					$wishlist_items['description']      = get_the_excerpt();
					$wishlist_items['wishlist_id']      = get_the_ID();
					$wishlist_items['wishlist_title']   = get_the_title();
					$wishlist_items['wishlist_product'] = $get_meta['product_id'];
					$wishlist_items['wishlist_html']    = $this->get_html_product_items( $get_meta['product_id'] );

					array_push( $all_wishlist, $wishlist_items );
				}
				$default_wishlist = get_user_meta( get_current_user_id(), self::WISHLIST_DEFAULT, true );
			}
			wp_send_json( [
				'all_wishlist' => $all_wishlist,
				'wishlist_id'  => $default_wishlist,
			] );
		}
		wp_die();
	}

	public function get_html_product_items( array $product_id ) {
		ob_start();
		foreach ( $product_id as $id ): ?>
			<?php
			global $product;
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			$image = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
			if ( empty( $image ) ) {
				$image[0] = wc_placeholder_img_src();
			}
			?>
            <div class="vi-wcwl-sidebar-card">
                <div class="vi-wcwl-sidebar-card-content">
                    <div class="vi-wcwl-sidebar-image pull-left">
                        <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><img
                                    src="<?php echo esc_url( $image[0] ); ?>" alt=""></a>
                    </div>
                    <div class="vi-wcwl-sidebar-content pull-left">
                        <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                        <div class="vi-wcwl-sidebar-content-price">
							<?php echo $product->get_price_html(); ?>
                        </div>
                        <div class="vi-wcwl-sidebar-content-btn-atc">
							<?php
							add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
							switch ( $product->get_type() ) {
								case 'variable':
									printf( '<button value="%d" class="vi-wcwl-select-variations">%s</button>',
										esc_attr( $product->get_id() ),
										esc_html( $product->add_to_cart_text() ) );
									break;
								case 'grouped':
									printf( '<button value="%d" class="vi-wcwl-sidebar-select-grouped vi-wcwl-select-variations">%s</button>',
										esc_attr( $product->get_id() ),
										esc_html( $product->add_to_cart_text() ) );
									break;
								case 'external':
									printf( '<a href="%s">%s</a>', esc_url( $product->add_to_cart_url() ),
										esc_html( $product->add_to_cart_text() ) );
									break;
								default:
									woocommerce_simple_add_to_cart();
							}
							?>
                        </div>
                    </div>
                    <div class="vi-wcwl-sidebar-delete-product"
                         data-product_id="<?php echo esc_attr( $id ); ?>">
                        <i class="vi-wl-cancel"></i>
                    </div>
                </div>
                <div class="display-variations-form-html"></div>
            </div>
		<?php
		endforeach;

		return ob_get_clean();
	}

	public function add_wishlist_sidebar() {
		if ( $this->settings->get_params( 'enable_multi' ) == 1 ) {
			if ( is_user_logged_in() ) {
				global $wpdb;
				$this->wishlist_name = isset( $_REQUEST['wishlistName'] ) ? sanitize_text_field( $_REQUEST['wishlistName'] ) : '';
				if ( $this->wishlist_name != '' ) {
					wp_insert_post( array(
						'post_title'   => $this->wishlist_name,
						'post_content' => '<!-- wp:shortcode -->[vi_woo_wishlist]<!-- /wp:shortcode -->',
						'post_status'  => 'publish',
						'post_author'  => get_current_user_id(),
						'post_type'    => self::POST_TYPE,
					) );
					$this->wishlist_id = $wpdb->insert_id;

					add_post_meta( $this->wishlist_id, self::POST_META_KEY, [ 'product_id' => [] ] );
					update_user_meta( get_current_user_id(), self::WISHLIST_DEFAULT, $this->wishlist_id );

					$wishlist = get_post( $this->wishlist_id );

					$data = [
						'wishlist_name' => $wishlist->post_title,
						'wishlist_id'   => $this->wishlist_id,
						'user_status'   => is_user_logged_in() ? 'logged_in' : 'guest',
					];
					wp_send_json( $data );
				}
			} else {
				$data = [ 'user_status' => 'guest' ];
				wp_send_json( $data );
			}
		}
		wp_die();
	}

	public function delete_prod_on_sidebar() {
		$this->product_id = sanitize_text_field( $_POST['productId'] );
		if ( is_user_logged_in() ) {
			$default_wishlist_id = get_user_meta( get_current_user_id(), 'vi_wishlist_default', true );
			$wishlist_meta       = get_post_meta( $default_wishlist_id, 'vi_woo_wishlist_meta', true );
			$key                 = array_search( $this->product_id, $wishlist_meta['product_id'] );
			unset( $wishlist_meta['product_id'][ $key ] );
			$wishlist_meta['product_id'] = array_values( $wishlist_meta['product_id'] );

			update_post_meta( $default_wishlist_id, self::POST_META_KEY, $wishlist_meta );

			$product_id = get_post_meta( $default_wishlist_id, self::POST_META_KEY, true );

			wp_send_json( $product_id );
		}
		wp_send_json( [ 'product_id' => [ $this->product_id ] ] );
	}

	public function wp_enqueue_scripts() {
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		wp_enqueue_style( 'vi-woo-wishlist', VI_WOO_PRODUCT_WISHLIST_CSS . 'woo-wishlist.css', array(),
			VI_WOO_PRODUCT_WISHLIST_VERSION );
		wp_enqueue_style( 'vi-woo-wishlist-flaticon', VI_WOO_PRODUCT_WISHLIST_CSS . 'flaticon.css', array(),
			VI_WOO_PRODUCT_WISHLIST_VERSION );
		wp_enqueue_style( 'vi-woo-wishlist-flaticon-frontend-icon', VI_WOO_PRODUCT_WISHLIST_CSS . 'flaticon_icon2.css',
			array(),
			VI_WOO_PRODUCT_WISHLIST_VERSION );
		wp_enqueue_style( 'vi-woo-wishlist', VI_WOO_PRODUCT_WISHLIST_CSS . 'woo-wishlist.css', array(),
			VI_WOO_PRODUCT_WISHLIST_VERSION );
		wp_enqueue_script( 'vi-add-to-wishlist-add-icon', VI_WOO_PRODUCT_WISHLIST_JS . 'woo-wishlist-add-icon.js',
			array( 'jquery' ), '', true );
		wp_localize_script( 'vi-add-to-wishlist-add-icon', 'wishlistButtonIcon', array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( '_vi_wishlist_icon' ),
			'imageDir'  => VI_WOO_PRODUCT_WISHLIST_IMAGES,
			'addIcon'   => $this->settings->get_class_icon( $this->settings->get_params( 'ic_add_icon' ),
				'add_to_wishlist_icons' ),
			'addedIcon' => $this->settings->get_class_icon( $this->settings->get_params( 'ic_add_icon' ),
				'like_icons' ),
		) );

		wp_enqueue_script( 'vi-woo-wishlist-sidebar', VI_WOO_PRODUCT_WISHLIST_JS . 'woo-wishlist-sidebar.js',
			array( 'jquery' ), '', true );
		wp_localize_script( 'vi-woo-wishlist-sidebar', 'wishlistSidebar', array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( '_vi_wishlist_sidebar' ),
			'imageDir'  => VI_WOO_PRODUCT_WISHLIST_IMAGES,
			'addIcon'   => $this->settings->get_class_icon( $this->settings->get_params( 'ic_add_icon' ),
				'add_to_wishlist_icons' ),
			'addedIcon' => $this->settings->get_class_icon( $this->settings->get_params( 'ic_add_icon' ),
				'like_icons' ),

		) );

	}


	public function show_product_on_sidebar() {
		if ( is_user_logged_in() ) {
			$this->wishlist_id = isset( $_POST['wishlistId'] ) ? sanitize_text_field( $_POST['wishlistId'] ) : __return_false();
			/*
			 * Update wishlist page default in user_meta table
			 * */
			update_user_meta( get_current_user_id(), self::WISHLIST_DEFAULT, $this->wishlist_id );
		}
		wp_die();
	}

	public function create_sidebar() {
		if ( $this->settings->get_params( 'enable_wishlist' ) != 1 ) {
			return;
		}
		$page_id = get_option( 'vi_wishlist_page_id' );
		if ( is_singular( 'vi_woo_wishlist' ) || is_page( $page_id ) ) {
			return;
		}

		$get_position = $this->settings->get_params( 'ft_position' );

		$set_position = '';
		switch ( $get_position ) {
			case 'middle_left':
			case 'top_left':
			case 'bottom_left':
				$set_position = 'left';
				break;
			case 'middle_right':
			case 'top_right':
			case 'bottom_right':
				$set_position = 'right';
				break;
		}
		$sb_header_text = $this->settings->get_params( 'sb_header_text' );
		$button_1_text  = $this->settings->get_params( 'sb_footer_btn_1_txt' );
		$button_2_text  = $this->settings->get_params( 'sb_footer_btn_2_txt' );
		?>

        <div class="vi-wl-h-sidebar-cd-panel cd-panel--from-<?php echo esc_html( $set_position ); ?> js-cd-panel-main">
            <div class="vi-wl-h-cd-panel__container">
                <div class="cd-panel__header">
                    <h4>
                        <a href="<?php echo esc_url( get_permalink( $page_id ) ) ?>">
							<?php esc_html_e( $sb_header_text, 'wishlist-for-woo' ); ?>
                        </a>
                    </h4>
                    <div class="vi-wl-sidebar-select-element">
                        <select class="vi-wl-sidebar-select-wishlist vi-wl-h-sidebar-custom-select"></select>
                    </div>
                    <span class="js-cd-close">
                        <i class="vi-wcwl-sidebar-close vi-wl-cancel"></i>
                    </span>
					<?php if ( $this->settings->get_params( 'enable_multi' ) == 1 ): ?>
                        <div class="vi-wl-add-btn"><i class="vi-wl-plus"></i></div>
					<?php endif; ?>
                    <div class="vi-wl-add-section">
                        <input class="vi-create-wl-sidebar" type="text" placeholder="Enter wishlist name...">
                        <div class="vi-wl-sidebar-button">
                            <button class="vi-wl-cancel-button"><?php esc_html_e( 'Cancel', 'wishlist-for-woo' ); ?></button>
                            <button class="vi-wl-save-button"><?php esc_html_e( 'Save', 'wishlist-for-woo' ); ?></button>
                        </div>
                    </div>
                </div>
                <div class="cd-panel__content">
                    <div class="vi-wcwl-overlay">
                        <div class="vi-wcwl-spin-icon"></div>
                    </div>
                    <div class="vi-wl-display-product"></div>
                </div> <!-- cd-panel__content -->
                <div class="vi-wl-sidebar-wrap-footer">
                    <a href="<?php echo esc_url( get_permalink( $page_id ) ) ?>" class="vi-wcwl-h-sidebar-footer-link">
						<?php esc_html_e( $button_1_text, 'wishlist-for-woo' ); ?>
                    </a>
                    <button class="vi-wcwl-h-sidebar-footer-btn vi-wcwl-sidebar-btn-add-all-to-cart vi-wcwl-h-button__text"><?php esc_html_e( $button_2_text,
							'wishlist-for-woo' ); ?></button>
                </div>
            </div> <!-- vi-wl-h-cd-panel__container -->
        </div> <!-- cd-panel -->
		<?php
	}

	public function create_floating_icon() {
		if ( $this->settings->get_params( 'enable_wishlist' ) != 1 ) {
			return;
		}
		$page_id = get_option( 'vi_wishlist_page_id' );
		if ( is_singular( 'vi_woo_wishlist' ) || is_page( $page_id ) ) {
			return;
		}
		$get_icon_index = $this->settings->get_params( 'ft_select_icon' );
		$get_icon       = $this->settings->get_class_icon( $get_icon_index, 'floating_icons' );
		$get_position   = $this->settings->get_params( 'ft_position' );
		?>
        <div class="js-cd-panel-trigger vi-wl-icon-bar vi-wl-icon-bar-position-<?php echo esc_html( $get_position ) ?> "
             data-panel="main"
             data-position="<?php echo esc_attr( $get_position ) ?>">
            <div class="vi-wl-display-total-prod">
                <div class="vi-wl-display-total-prod-number">0</div>
            </div>
            <span class="vi-wl-floating-icon-sidebar">
                <i class="vi-wl-floating-icon-set-flex <?php echo esc_attr( $get_icon ) ?>"></i></span>
        </div>
		<?php
	}

	public function save_to_wishlist() {
		global $wpdb;
		$product = sanitize_text_field( $_POST['productId'] );

		if ( is_user_logged_in() ) {
			$user_posts = new WP_Query( [
				'post_type' => self::POST_TYPE,
				'author'    => get_current_user_id()
			] );
			if ( ! $user_posts->have_posts() ) {
				wp_insert_post( array(
					'post_title'   => 'My Wishlist',
					'post_content' => '<!-- wp:shortcode -->[vi_woo_wishlist]<!-- /wp:shortcode -->',
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id(),
					'post_type'    => 'vi_woo_wishlist',
				) );

				$this->wishlist_id = $wpdb->insert_id;
				add_post_meta( $this->wishlist_id, self::POST_META_KEY, [ 'product_id' => [ $product ] ] );
				update_user_meta( get_current_user_id(), self::WISHLIST_DEFAULT, $this->wishlist_id );

			} else {
				$this->action_exist_wishlist( get_current_user_id(), $product );
			}
		}
		$this->show_product_on_sidebar_ajax_guest( $product );
	}

	protected function action_exist_wishlist( $author_id, $product ) {

		$this->wishlist_id  = get_user_meta( $author_id, self::WISHLIST_DEFAULT, true );
		$get_meta_to_update = get_post_meta( $this->wishlist_id, self::POST_META_KEY, true );

		if ( ! isset( $get_meta_to_update['product_id'] ) ) {
			update_post_meta( $this->wishlist_id, self::POST_META_KEY, array(
				'product_id' => [ $product ],
			) );
		} else {
			if ( in_array( $product, $get_meta_to_update['product_id'] ) != true ) {
				$get_meta_to_update['product_id'][] = $product;
				update_post_meta( $this->wishlist_id, self::POST_META_KEY, $get_meta_to_update );
			}
		}
	}

	protected function show_product_on_sidebar_ajax_guest( $product_id ) {
		global $product;
		add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
		$product = wc_get_product( $product_id );
		$image   = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
		if ( empty( $image ) ) {
			$image[0] = wc_placeholder_img_src();
		}

		ob_start();
		?>
        <div class="vi-wcwl-sidebar-card">
            <div class="vi-wcwl-sidebar-card-content">
                <div class="vi-wcwl-sidebar-image pull-left">
                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><img
                                src="<?php echo esc_url( $image[0] ); ?>" alt=""></a>
                </div>
                <div class="vi-wcwl-sidebar-content pull-left">
                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                    <div class="vi-wcwl-sidebar-content-price">
						<?php echo $product->get_price_html(); ?>
                    </div>
                    <div class="vi-wcwl-sidebar-content-btn-atc">
						<?php
						add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
						switch ( $product->get_type() ) {
							case 'variable':
								printf( '<button value="%d" class="vi-wcwl-select-variations">%s</button>',
									esc_attr( $product->get_id() ),
									esc_html( $product->add_to_cart_text() ) );
								break;
							case 'grouped':
								printf( '<button value="%d" class="vi-wcwl-sidebar-select-grouped vi-wcwl-select-variations">%s</button>',
									esc_attr( $product->get_id() ),
									esc_html( $product->add_to_cart_text() ) );
								break;
							case 'external':
								printf( '<a href="%s">%s</a>', esc_url( $product->add_to_cart_url() ),
									esc_html( $product->add_to_cart_text() ) );
								break;
							default:
								woocommerce_simple_add_to_cart();
						}
						?>
                    </div>
                </div>
                <div class="vi-wcwl-sidebar-delete-product"
                     data-product_id="<?php echo esc_attr( $product_id ); ?>">
                    <i class="vi-wl-cancel"></i>
                </div>
            </div>
            <div class="display-variations-form-html"></div>
        </div>
		<?php
		$html = ob_get_clean();

		if ( is_user_logged_in() ) {
			$this->wishlist_id = get_user_meta( get_current_user_id(), self::WISHLIST_DEFAULT, true );
		}

		$data = [
			'user_status'      => is_user_logged_in() ? 'logged_in' : 'guest',
			'product_html'     => $html,
			'product_id'       => $product_id,
			'product_name'     => $product->get_name(),
			'product_image'    => $image[0],
			'add_to_cart_form' => $this->get_add_to_cart_form( $product_id ),
			'product_link'     => $product->get_permalink(),
			'wishlist_id'      => $this->wishlist_id
		];

		wp_send_json( $data );
	}

	protected function get_add_to_cart_form( $product_id ) {
		global $product;
		$product = wc_get_product( $product_id );
		add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
		ob_start();

		switch ( $product->get_type() ) {
			case 'variable':
				printf( '<button value="%d" class="vi-wcwl-select-variations">%s</button>',
					esc_attr( $product->get_id() ),
					esc_html( $product->add_to_cart_text() ) );
				break;
			case 'grouped':
				printf( '<button value="%d" class="vi-wcwl-sidebar-select-grouped vi-wcwl-select-variations">%s</button>',
					esc_attr( $product->get_id() ),
					esc_html( $product->add_to_cart_text() ) );
				break;
			case 'external':
				printf( '<a href="%s">%s</a>', esc_url( $product->add_to_cart_url() ),
					esc_html( $product->add_to_cart_text() ) );
				break;
			default:
				woocommerce_simple_add_to_cart();
		}

		return ob_get_clean();
	}

}