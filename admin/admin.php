<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOO_PRODUCT_WISHLIST_Admin_Admin {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'wpdocs_remove_meta_boxes' ] );
		}
		add_action( 'wp_ajax_delete_product_from_wishlist', [ $this, 'delete_product_from_wishlist' ] );
	}

	public static function enqueue_style( $handles = array(), $srcs = array(), $des = array(), $type = 'enqueue' ) {
		if ( empty( $handles ) || empty( $srcs ) ) {
			return;
		}
		$action = $type === 'enqueue' ? 'wp_enqueue_style' : 'wp_register_style';
		foreach ( $handles as $i => $handle ) {
			if ( ! $handle || empty( $srcs[ $i ] ) ) {
				continue;
			}
			$action( $handle, VI_WOO_PRODUCT_WISHLIST_CSS . $srcs[ $i ], ! empty( $des[ $i ] ) ? $des[ $i ] : array(),
				VI_WOO_PRODUCT_WISHLIST_VERSION );
		}
	}

	public static function enqueue_script(
		$handles = array(),
		$srcs = array(),
		$des = array(),
		$type = 'enqueue',
		$in_footer = false
	) {
		if ( empty( $handles ) || empty( $srcs ) ) {
			return;
		}
		$action = $type === 'register' ? 'wp_register_script' : 'wp_enqueue_script';
		foreach ( $handles as $i => $handle ) {
			if ( ! $handle || empty( $srcs[ $i ] ) ) {
				continue;
			}
			$action( $handle, VI_WOO_PRODUCT_WISHLIST_JS . $srcs[ $i ],
				! empty( $des[ $i ] ) ? $des[ $i ] : array( 'jquery' ), VI_WOO_PRODUCT_WISHLIST_VERSION, $in_footer );
		}
	}

	public static function remove_other_script() {
		global $wp_scripts;
		if ( isset( $wp_scripts->registered['jquery-vi-ui-accordion'] ) ) {
			unset( $wp_scripts->registered['jquery-vi-ui-accordion'] );
			wp_dequeue_script( 'jquery-vi-ui-accordion' );
		}
		if ( isset( $wp_scripts->registered['accordion'] ) ) {
			unset( $wp_scripts->registered['accordion'] );
			wp_dequeue_script( 'accordion' );
		}
		$scripts = $wp_scripts->registered;
		foreach ( $scripts as $k => $script ) {
			preg_match( '/^\/wp-/i', $script->src, $result );
			if ( count( array_filter( $result ) ) ) {
				preg_match( '/^(\/wp-content\/plugins|\/wp-content\/themes)/i', $script->src, $result1 );
				if ( count( array_filter( $result1 ) ) ) {
					wp_dequeue_script( $script->handle );
				}
			} else {
				if ( $script->handle != 'query-monitor' ) {
					wp_dequeue_script( $script->handle );
				}
			}
		}
	}

	public function delete_product_from_wishlist() {
		check_ajax_referer( '_vi_wishlist_post_type_nonce', 'nonce' );

		$wishlist_id = sanitize_text_field( $_POST['wishlist_id'] );
		$product_id  = sanitize_text_field( $_POST['product_id'] );

		$get_meta = get_post_meta( $wishlist_id, 'vi_woo_wishlist_meta', true );

		$key = array_search( $product_id, $get_meta['product_id'] );
		unset( $get_meta['product_id'][ $key ] );
		$get_meta['product_id'] = array_values( $get_meta['product_id'] );

		update_post_meta( $wishlist_id, 'vi_woo_wishlist_meta', $get_meta );
	}

	public function wpdocs_remove_meta_boxes() {
		remove_meta_box( 'trackbacksdiv', 'vi_woo_wishlist', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'vi_woo_wishlist', 'normal' ); //removes comments status
		remove_meta_box( 'commentsdiv', 'vi_woo_wishlist', 'normal' ); //removes comments
		remove_meta_box( 'authordiv', 'vi_woo_wishlist', 'normal' ); //removes author
	}

	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
	}

	public function add_metabox() {
		add_meta_box(
			'vi-woo-wishlist-meta-box',
			__( 'Product List', 'wishlist-for-woo' ),
			array( $this, 'render_metabox' ),
			'vi_woo_wishlist',
			'advanced',
			'default'
		);
	}

	public function render_metabox( $post ) {
		wp_enqueue_style( 'vi-woo-wishlist-button', VI_WOO_PRODUCT_WISHLIST_CSS . 'button.min.css' );
		wp_enqueue_style( 'vi-woo-wishlist-segment', VI_WOO_PRODUCT_WISHLIST_CSS . 'segment.min.css' );
		wp_enqueue_style( 'vi-woo-wishlist-table', VI_WOO_PRODUCT_WISHLIST_CSS . 'table.min.css' );
		wp_enqueue_style( 'vi-woo-wishlist-close-icon-meta', VI_WOO_PRODUCT_WISHLIST_CSS . 'icon.min.css' );
		wp_enqueue_script( 'vi-woo-wishlist-admin', VI_WOO_PRODUCT_WISHLIST_JS . 'woo-wishlist-admin.js',
			array( 'jquery' ) );
		wp_enqueue_script( 'vi-woo-wishlist-checkbox', VI_WOO_PRODUCT_WISHLIST_JS . 'checkbox.js',
			array( 'jquery' ) );
		wp_enqueue_script( 'vi-woo-wishlist-dropdown', VI_WOO_PRODUCT_WISHLIST_JS . 'dropdown.js',
			array( 'jquery' ) );
		wp_localize_script( 'vi-woo-wishlist-admin', 'viwcwlAdminObj', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( '_vi_wishlist_post_type_nonce' ),
		) );
		// Add nonce for security and authentication.
		wp_nonce_field( 'vi_woo_wishlist_meta_box', 'vi_wl_meta_box_nonce' );
		$meta_arr = get_post_meta( $post->ID, 'vi_woo_wishlist_meta', true );
		?>
        <table class="vi-ui celled padded table">
            <thead>
            <tr>
                <th class="single line"><?php echo esc_html__( 'Image', 'wishlist-for-woo' ) ?></th>
                <th><?php echo esc_html__( 'Title', 'wishlist-for-woo' ) ?></th>
                <th><?php echo esc_html__( 'Stock Stastus' ); ?></th>
                <th><?php echo esc_html__( 'Action', 'wishlist-for-woo' ) ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			if ( isset( $meta_arr['product_id'] ) ):
				foreach ( $meta_arr['product_id'] as $product_id ):
					$product_info = wc_get_product( $product_id );
					$image = wp_get_attachment_image_src( $product_info->get_image_id(), 'shop_thumbnail' );
					if ( empty( $image ) ) {
						$image[0] = wc_placeholder_img_src( 'woocommerce_thumbnail' );
					}
					?>
                    <tr>
                        <td>
                            <img class="vi-ui center aligned header" src="<?php echo esc_url( $image[0] ) ?>" alt="">
                        </td>
                        <td>
							<?php printf( '<a href="%s">%s</a>', esc_url( $product_info->get_permalink() ), esc_html( $product_info->get_name() ) ); ?>
                        </td>
                        <td>
                            <p><?php echo esc_html( $product_info->get_stock_status() ); ?></p>
                        </td>
                        <td>
							<?php printf( '<button data-wishlist_id="%d" class="vi-ui red button vi-wishlist-delete-product-admin" value="%d">%s</button>',
								esc_attr( $post->ID ),
								esc_attr( $product_id ),
								esc_html__( 'Delete', 'wishlist-for-woo' ) ) ?>
                        </td>
                    </tr>
				<?php endforeach;
			endif;
			?>
            </tbody>

        </table>
		<?php
	}

	public function init() {
		$this->load_plugin_textdomain();
		if ( class_exists( 'VillaTheme_Support' ) ) {
			new VillaTheme_Support(
				array(
					'support'   => 'https://wordpress.org/support/plugin/eu-cookies-bar/',
					'docs'      => 'https://docs.villatheme.com/?item=eu-cookies-bar',
					'review'    => 'https://wordpress.org/support/plugin/eu-cookies-bar/reviews/?rate=5#rate-response',
					'pro_url'   => '',
					'css'       => VI_WOO_PRODUCT_WISHLIST_CSS,
					'image'     => VI_WOO_PRODUCT_WISHLIST_IMAGES,
					'slug'      => 'product-wishlist-for-woo',
					'menu_slug' => 'product-wishlist-for-woo',
					'version'   => VI_WOO_PRODUCT_WISHLIST_VERSION
				)
			);
		}
	}

	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wishlist-for-woo' );
		// Global + Frontend Locale
		load_textdomain( 'wishlist-for-woo', VI_WOO_PRODUCT_WISHLIST_LANGUAGES . "wishlist-for-woo-$locale.mo" );
		load_plugin_textdomain( 'wishlist-for-woo', false, VI_WOO_PRODUCT_WISHLIST_LANGUAGES );
	}

	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['page'] ) ) : '';
		if ( $page === 'product-wishlist-for-woo' ) {
			wp_enqueue_style( 'vi-woo-wishlist-close-icon', VI_WOO_PRODUCT_WISHLIST_CSS . 'icon.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-form', VI_WOO_PRODUCT_WISHLIST_CSS . 'form.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-menu', VI_WOO_PRODUCT_WISHLIST_CSS . 'menu.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-segment', VI_WOO_PRODUCT_WISHLIST_CSS . 'segment.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-button', VI_WOO_PRODUCT_WISHLIST_CSS . 'button.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-admin', VI_WOO_PRODUCT_WISHLIST_CSS . 'woo-wishlist-admin.css' );
			wp_enqueue_style( 'vi-woo-wishlist-tab', VI_WOO_PRODUCT_WISHLIST_CSS . 'tab.css' );
			wp_enqueue_style( 'vi-woo-wishlist-checkbox', VI_WOO_PRODUCT_WISHLIST_CSS . 'checkbox.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-message', VI_WOO_PRODUCT_WISHLIST_CSS . 'message.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-transition', VI_WOO_PRODUCT_WISHLIST_CSS . 'transition.min.css' );
			wp_enqueue_style( 'vi-woo-wishlist-dropdown', VI_WOO_PRODUCT_WISHLIST_CSS . 'dropdown.min.css' );

			wp_enqueue_script( 'vi-woo-wishlist-tab', VI_WOO_PRODUCT_WISHLIST_JS . 'tab.js', [ 'jquery' ] );
			wp_enqueue_script( 'vi-woo-wishlist-admin', VI_WOO_PRODUCT_WISHLIST_JS . 'woo-wishlist-admin.js',
				array( 'jquery' ) );
			wp_enqueue_script( 'vi-woo-wishlist-dependsOn-1.0.2', VI_WOO_PRODUCT_WISHLIST_JS . 'dependsOn-1.0.2.min.js',
				array( 'jquery' ) );
			wp_enqueue_script( 'vi-woo-wishlist-checkbox', VI_WOO_PRODUCT_WISHLIST_JS . 'checkbox.js',
				array( 'jquery' ) );
			wp_enqueue_script( 'vi-woo-wishlist-select2', VI_WOO_PRODUCT_WISHLIST_JS . 'select2.js',
				array( 'jquery' ) );
			wp_enqueue_script( 'vi-woo-wishlist-form', VI_WOO_PRODUCT_WISHLIST_JS . 'form.js', array( 'jquery' ) );
			wp_enqueue_script( 'vi-woo-wishlist-transition', VI_WOO_PRODUCT_WISHLIST_JS . 'transition.min.js',
				array( 'jquery' ) );
			wp_enqueue_script( 'vi-woo-wishlist-dropdown', VI_WOO_PRODUCT_WISHLIST_JS . 'dropdown.js',
				array( 'jquery' ) );

			/*
			 * Color picker
			 * */
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-vi-ui-draggable',
				'jquery-vi-ui-slider',
				'jquery-touch-punch'
			), false, 1 );
		}
	}

}