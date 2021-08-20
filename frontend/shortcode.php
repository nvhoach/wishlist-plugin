<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 *
 * Class VI_WOO_PRODUCT_WISHLIST_Frontend_Shortcode
 *
 */
class VI_WOO_PRODUCT_WISHLIST_Frontend_Shortcode {
	protected $product_id;
	protected $author;
	protected $current_user;
	protected $post_id;
	protected $wishlist_id;
	protected $settings;

	public function __construct() {
		$this->settings = new VI_WOO_PRODUCT_WISHLIST_DATA();
		add_action( 'init', array( $this, 'shortcode_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'shortcode_enqueue_script' ) );
		add_action( 'wp_ajax_remove_product_in_wl', [ $this, 'remove_product_in_wl' ] );
		add_action( 'wp_ajax_nopriv_remove_product_in_wl', [ $this, 'remove_product_in_wl' ] );
		add_action( 'wp_ajax_vi_woo_my_wishlist_page_add', [ $this, 'create_wishlist' ] );
		add_action( 'wp_ajax_nopriv_vi_woo_my_wishlist_page_add', [ $this, 'create_wishlist' ] );
		add_action( 'wp_ajax_display_select_wishlist', [ $this, 'display_my_wishlist_ajax' ] );
		add_action( 'wp_ajax_nopriv_display_select_wishlist', [ $this, 'display_my_wishlist_ajax' ] );
		add_action( 'wp_ajax_delete_my_wishlist', [ $this, 'delete_my_wishlist' ] );
		add_action( 'wp_ajax_nopriv_delete_my_wishlist', [ $this, 'delete_my_wishlist' ] );
		add_action( 'wp_ajax_clone_publish_wishlist', [ $this, 'clone_publish_wishlist' ] );
		add_action( 'wp_ajax_nopriv_clone_publish_wishlist', [ $this, 'clone_publish_wishlist' ] );
		add_action( 'wp_ajax_add_to_cart_in_shortcode', [ $this, 'add_to_cart_in_shortcode' ] );
		add_action( 'wp_ajax_nopriv_add_to_cart_in_shortcode', [ $this, 'add_to_cart_in_shortcode' ] );
		add_action( 'wp_ajax_add_all_to_cart_shortcode', [ $this, 'add_all_to_cart_shortcode' ] );
		add_action( 'wp_ajax_nopriv_add_all_to_cart_shortcode', [ $this, 'add_all_to_cart_shortcode' ] );
		add_action( 'wp_ajax_render_wishlist_local', [ $this, 'render_wishlist_local' ] );
		add_action( 'wp_ajax_nopriv_render_wishlist_local', [ $this, 'render_wishlist_local' ] );
		add_action( 'wp_ajax_add_to_cart_grouped_products', [ $this, 'add_to_cart_grouped_products' ] );
		add_action( 'wp_ajax_nopriv_add_to_cart_grouped_products', [ $this, 'add_to_cart_grouped_products' ] );
		add_action( 'wp_ajax_update_wishlist_info', [ $this, 'update_wishlist_info' ] );
		add_action( 'wp_ajax_nopriv_update_wishlist_info', [ $this, 'update_wishlist_info' ] );
		add_action( 'wp_ajax_process_vote_wishlist', [ $this, 'process_vote_wishlist' ] );
		add_action( 'wp_ajax_nopriv_process_vote_wishlist', [ $this, 'process_vote_wishlist' ] );
		add_action( 'wp_ajax_show_add_to_cart_form', [ $this, 'show_add_to_cart_form' ] );
		add_action( 'wp_ajax_nopriv_show_add_to_cart_form', [ $this, 'show_add_to_cart_form' ] );
	}

	public function show_add_to_cart_form() {
		VI_WOO_PRODUCT_WISHLIST_Frontend_Frontend::render_variations_form_html();
	}

	public function process_vote_wishlist() {
		$wishlist_id       = sanitize_text_field( $_POST['wishlist_id'] );
		$get_wishlist_data = get_post_meta( $wishlist_id, 'vi_woo_wishlist_meta', true );

		if ( is_user_logged_in() ) {
			if ( ! isset( $get_wishlist_data['vote'] ) ) {
				$get_wishlist_data['vote'] = [ get_current_user_id() ];
			} else {
				if ( ! in_array( get_current_user_id(), $get_wishlist_data['vote'], true ) ) {
					array_push( $get_wishlist_data['vote'], get_current_user_id() );
				}
			}
			update_post_meta( $wishlist_id, 'vi_woo_wishlist_meta', $get_wishlist_data );
		}

		$data = [
			'total_voted' => count( $get_wishlist_data['vote'] ),
			'user_status' => is_user_logged_in() ? 'logged_in' : 'guest',
		];
		wp_send_json( $data );
	}

	public function update_wishlist_info() {
		if ( is_user_logged_in() ) {
			if ( sanitize_text_field( $_POST['wishlistName'] ) !== "" ) {
				wp_update_post( [
					'ID'           => sanitize_text_field( $_POST['wishlistId'] ),
					'post_title'   => sanitize_text_field( sanitize_text_field( $_POST['wishlistName'] ) ),
					'post_excerpt' => sanitize_textarea_field( $_POST['description'] ),
					'post_status'  => sanitize_text_field( $_POST['wishlistStatus'] ) === 'publish' ? 'pending' : 'private',
				] );
			} else {
				wp_send_json( [
					'wishlistTitle' => '',
				] );
			}
		}
		$data = array(
			'wishlistTitle'       => sanitize_text_field( $_POST['wishlistName'] ),
			'wishlistDescription' => sanitize_textarea_field( $_POST['description'] ),
		);

		wp_send_json( $data );
	}

	/**
	 * AJAX add to cart.
	 */
	public function add_to_cart_in_shortcode() {
		ob_start();
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$product           = wc_get_product( $product_id );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
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

	public function render_wishlist_local() {
		if ( is_user_logged_in() ) {
			$this->wishlist_id = sanitize_text_field( $_POST['wishlistId'] );

			$product_arr = get_post_meta( $this->wishlist_id, 'vi_woo_wishlist_meta', true );

			$this->display_guest_wishlist_ajax( $product_arr['product_id'] );
		} else {
			$product_arr = $_POST['productArr'];

			$this->display_guest_wishlist_ajax( $product_arr );
		}

	}

	protected function display_guest_wishlist_ajax( array $product_id ) {
		ob_start();
		if ( ! empty( $product_id ) ) {
			foreach ( $product_id as $id ):
				global $product;
				$product = wc_get_product( $id );
				$image   = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
				if ( empty( $image ) ) {
					$image[0] = wc_placeholder_img_src( 'shop_thumbnail' );
				}
				?>
                <div class="vi-wcwl-h-table-content">
                    <div class="vi-wcwl-h-table-image">
                        <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                            <img src="<?php echo esc_url( $image[0] ); ?>"
                                 alt="<?php echo esc_attr( $product->get_name() ); ?>">
                        </a>
                    </div>
                    <div class="vi-wcwl-h-table-title-price">
                        <div class="vi-wcwl-h-table-title-price-info">
							<?php printf( '<a target="_blank" href="%s">%s</a>', esc_url( $product->get_permalink() ), esc_html( $product->get_name() ) ); ?>
                            <div>
								<?php echo wp_kses_post( $product->get_price_html() ); ?>
                            </div>
                        </div>
                        <div class="vi-wcwl-h-table-add-to-cart-form">
                            <div class="vi-wcwl-h-table-add-to-cart-form-btn-option">
								<?php
								add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
								switch ( $product->get_type() ) {
									case 'variable':
										printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-variations">%s</button>', esc_attr( $product->get_id() ),
											esc_html( $product->add_to_cart_text() ) );
										break;
									case 'grouped':
										printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-grouped">%s</button>', esc_attr( $product->get_id() ),
											esc_html__( 'Select products', 'wishlist-for-woo' ) );
										break;
									case 'external':
										woocommerce_external_add_to_cart();
										break;
									default:
										woocommerce_simple_add_to_cart();
								}
								?>
                            </div>
                            <div class="vi-wcwl-h-table-show-add-to-cart-form"></div>
                        </div>
                    </div>
                    <div class="vi-wl-h-table-product-remove">
                        <i data-product_id="<?php echo esc_attr( $id ) ?>" class="vi-wl-cancel"></i>
                    </div>
                </div>
			<?php endforeach; ?>

			<?php
		} else {
			printf( '<div style="font-size: 25px; text-align: center">%s</div>', esc_html__( 'Empty Products', 'wishlist-for-woo' ) );
		}
		$product_table = ob_get_clean();

		wp_send_json( [
			'product_table' => $product_table
		] );
	}

	public function re_wp_override_woo_templates( $located, $template_name ) {
		if ( $template_name == 'global/quantity-input.php' ) {
			$located = VI_WOO_PRODUCT_WISHLIST_TEMPLATES . 'woocommerce/quantity-input.php';
		}

		return $located;
	}

	public function shortcode_init() {
		add_shortcode( 'vi_woo_wishlist_single_btn', [ $this, 'register_single_button_shortcode' ] );
		add_shortcode( 'vi_woo_wishlist', array( $this, 'register_single_wishlist' ) );
		add_shortcode( 'vi_woo_my_wishlist', [ $this, 'register_my_wishlist' ] );
	}

	public function register_single_button_shortcode() {
		global $product;
		?>
        <div class="vi-wcwl-h-single-product-display-btn">
            <button class="vi-wcwl-h-single-product-button" value="<?php echo esc_attr( $product->get_id() ); ?>">
				<?php esc_html_e( 'Add to wishlist', 'wishlist-for-woo' ); ?>
            </button>
        </div>
		<?php
	}

	public function clone_publish_wishlist() {
		if ( is_user_logged_in() ) {
			global $wpdb;
			if ( isset ( $_POST['wishlistId'] ) && sanitize_text_field( $_POST['wishlistId'] ) != '' ) {
				$this->wishlist_id = sanitize_text_field( $_POST['wishlistId'] );
				$wishlist          = get_post( $this->wishlist_id );
				$wishlist_meta     = get_post_meta( $this->wishlist_id, 'vi_woo_wishlist_meta', true );

				wp_insert_post( [
					'post_type'    => $wishlist->post_type,
					'post_author'  => get_current_user_id(),
					'post_title'   => $wishlist->post_title . '_clone',
					'post_content' => $wishlist->post_content,
					'post_excerpt' => $wishlist->post_excerpt,
					'post_status'  => 'private'
				] );

				$insert_id = $wpdb->insert_id;

				add_post_meta( $insert_id, 'vi_woo_wishlist_meta', $wishlist_meta );
				update_user_meta( get_current_user_id(), 'vi_wishlist_default', $insert_id );

				$this->render_local_storage();
			}
		} else {
			wp_send_json( 'guest' );
		}

	}

	public function render_local_storage() {
		if ( is_user_logged_in() ) {
			$all_wishlist     = [];
			$wishlist_items   = [];
			$default_wishlist = '';

			$get_wishlist = new WP_Query( [
				'post_type'      => 'vi_woo_wishlist',
				'author'         => get_current_user_id(),
				'post_status'    => [ 'publish', 'private', 'pending' ],
				'posts_per_page' => - 1
			] );

			if ( $get_wishlist->have_posts() ) {
				while ( $get_wishlist->have_posts() ) {
					$get_wishlist->the_post();
					$get_meta                           = get_post_meta( get_the_ID(), 'vi_woo_wishlist_meta', true );
					$wishlist_items['description']      = get_the_excerpt();
					$wishlist_items['wishlist_id']      = get_the_ID();
					$wishlist_items['wishlist_title']   = get_the_title();
					$wishlist_items['wishlist_product'] = $get_meta['product_id'];
					$wishlist_items['wishlist_html']    = $this->get_html_product_items( $get_meta['product_id'] );

					array_push( $all_wishlist, $wishlist_items );
				}
				$default_wishlist = get_user_meta( get_current_user_id(), 'vi_wishlist_default', true );
			}
			wp_send_json( [
				'all_wishlist' => $all_wishlist,
				'wishlist_id'  => $default_wishlist,
			] );
		}
		wp_die();
	}

	protected function get_html_product_items( array $product_id ) {
		add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
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
				$image[0] = wc_placeholder_img_src( 'shop_thumbnail' );
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

	public function delete_my_wishlist() {
		if ( isset ( $_POST['wishlistId'] ) && sanitize_text_field( $_POST['wishlistId'] ) != '' ) {
			wp_delete_post( sanitize_text_field( $_POST['wishlistId'] ) );
		}
		wp_die();
	}

	public function display_my_wishlist_ajax() {
		if ( is_user_logged_in() ) {
			if ( isset( $_POST['wishlistId'] ) && sanitize_text_field( $_POST['wishlistId'] ) ) {
				update_user_meta( get_current_user_id(), 'vi_wishlist_default',
					sanitize_text_field( $_POST['wishlistId'] ) );
				$this->display_table_ajax( sanitize_text_field( $_POST['wishlistId'] ) );
			}
		} else {
			if ( isset( $_POST['productArr'] ) && is_array( $_POST['productArr'] ) ) {
				$this->display_guest_wishlist_ajax( $_POST['productArr'] );
			}
		}
		wp_die();
	}

	private function display_table_ajax( $wishlist_id ) {
		if ( isset( $wishlist_id ) && sanitize_text_field( $wishlist_id ) ) {
			$this->wishlist_id = sanitize_text_field( $wishlist_id );
			$product_id_arr    = get_post_meta( $this->wishlist_id, 'vi_woo_wishlist_meta', true );
			ob_start();
			if ( ! empty( $product_id_arr['product_id'] ) ) {
				foreach ( $product_id_arr['product_id'] as $product_id ):
					global $product;
					$product = wc_get_product( $product_id );
					$image   = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
					if ( empty( $image ) ) {
						$image[0] = wc_placeholder_img_src( 'shop_thumbnail' );
					}
					?>
                    <div class="vi-wcwl-h-table-content">
                        <div class="vi-wcwl-h-table-image">
                            <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                                <img src="<?php echo esc_url( $image[0] ); ?>"
                                     alt="<?php echo esc_attr( $product->get_name() ); ?>">
                            </a>
                        </div>
                        <div class="vi-wcwl-h-table-title-price">
                            <div class="vi-wcwl-h-table-title-price-info">
								<?php printf( '<a target="_blank" href="%s">%s</a>', esc_url( $product->get_permalink() ), esc_html( $product->get_name() ) ); ?>
                                <div>
									<?php echo wp_kses_post( $product->get_price_html() ); ?>
                                </div>
                            </div>
                            <div class="vi-wcwl-h-table-add-to-cart-form">
                                <div class="vi-wcwl-h-table-add-to-cart-form-btn-option">
									<?php
									add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
									switch ( $product->get_type() ) {
										case 'variable':
											printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-variations">%s</button>', esc_attr( $product->get_id() ),
												esc_html( $product->add_to_cart_text() ) );
											break;
										case 'grouped':
											printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-grouped">%s</button>', esc_attr( $product->get_id() ),
												esc_html__( 'Select products', 'wishlist-for-woo' ) );
											break;
										case 'external':
											woocommerce_external_add_to_cart();
											break;
										default:
											woocommerce_simple_add_to_cart();
									}
									?>
                                </div>
                                <div class="vi-wcwl-h-table-show-add-to-cart-form"></div>
                            </div>
                        </div>
                        <div class="vi-wl-h-table-product-remove">
                            <i data-product_id="<?php echo esc_attr( $product_id ) ?>" class="vi-wl-cancel"></i>
                        </div>
                    </div>
				<?php endforeach; ?>
				<?php
			} else {
				printf( '<div>%s</div>', esc_html__( 'Empty wishlist', 'wishlist-for-woo' ) );
			}
			?>
			<?php
			$html = ob_get_clean();
			wp_send_json( [ 'product_table' => $html ] );
		}
	}

	public function create_wishlist() {
		if ( is_user_logged_in() ) {
			global $wpdb;
			if ( isset( $_POST['wishlistName'] ) && sanitize_text_field( $_POST['wishlistName'] ) != '' ) {
				$description     = sanitize_textarea_field( $_POST['description'] );
				$wishlist_status = sanitize_text_field( $_POST['wishlistStatus'] );

				wp_insert_post( [
					'post_type'    => 'vi_woo_wishlist',
					'post_title'   => sanitize_text_field( $_POST['wishlistName'] ),
					'post_status'  => $wishlist_status === 'publish' ? 'pending' : 'private',
					'post_content' => '<!-- wp:shortcode -->[vi_woo_wishlist]<!-- /wp:shortcode -->',
					'post_excerpt' => $description,
					'post_author'  => get_current_user_id()
				] );

				$this->wishlist_id = $wpdb->insert_id;

				add_post_meta( $this->wishlist_id, 'vi_woo_wishlist_meta', [ 'product_id' => [] ] );
				update_user_meta( get_current_user_id(), 'vi_wishlist_default', $this->wishlist_id );
			}
			$get_wishlist = get_post( $this->wishlist_id );

			$wishlist_info = [
				'id'          => $this->wishlist_id,
				'title'       => $get_wishlist->post_title,
				'description' => $get_wishlist->post_excerpt,
			];
			wp_send_json( $wishlist_info );
		}
		$data = array(
			'title'       => sanitize_text_field( $_POST['wishlistName'] ),
			'description' => sanitize_textarea_field( $_POST['description'] ),
			'id'          => '',
		);
		wp_send_json( $data );
	}

	public function register_my_wishlist() {
		wp_enqueue_style( 'vi-woo-wl-shortcode-style' );
		wp_enqueue_script( 'vi-woo-wl-shortcode-script' );
		wp_enqueue_script( 'vi-woo-wl-render-shortcode-script' );
		wp_localize_script( 'vi-woo-wl-render-shortcode-script', 'shortcodeAjaxObj', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		] );
		wp_localize_script( 'vi-woo-wl-shortcode-script', 'shortcodeAjaxObj', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( '_vi_wl_shortcode_nonce' ),
		] );

		$wishlist_default_id = get_user_meta( get_current_user_id(), 'vi_wishlist_default', true );

		global $post;

		$product_id_arr = get_post_meta( $wishlist_default_id, 'vi_woo_wishlist_meta', true );
		ob_start();
		?>
        <div class="vi-wl-h-single-top-bar">
			<?php if ( $this->settings->get_params( 'enable_multi' ) == 1 ): ?>
                <button class="vi-wl-h-default"><i
                            class="vi-wl-plus"></i> <?php echo esc_html( __( 'New Wishlist',
						'wishlist-for-woo' ) ); ?>
                </button>
			<?php endif; ?>
            <button class="vi-wl-h-warning"><i
                        class="vi-wl-pencil"></i> <?php echo esc_html( __( 'Edit', 'wishlist-for-woo' ) ); ?>
            </button>
            <button class="vi-wl-h-danger" value="<?php echo esc_html( $wishlist_default_id ); ?>">
                <i class="vi-wl-cancel"></i>
				<?php echo esc_html( __( 'Delete', 'wishlist-for-woo' ) ); ?>
            </button>
            <button type="button" class="vi-wl-h-form-btn-atc vi-wl-h-single-top-bar-atc">
				<?php echo esc_html( __( 'Add all to cart', 'wishlist-for-woo' ) ); ?>
            </button>

        </div>
        <!-- The Modal -->
        <div class="vi-wl-h-form-popup" id="vi-wl-h-form">
            <div class="vi-wl-h-form-form-container">
                <label for="wishlistName"><b>Wishlist Name</b>
                    <input type="text" placeholder="Wishlist Name..." name="wishlistName"
                           class="vi-wl-h-wishlist-name"
                           required>
                </label>
                <label for="psw"><b>Description</b>
                    <textarea class="vi-wl-h-wishlist-description" name="description" cols="15" rows="5"></textarea>
                </label>
				<?php if ( is_user_logged_in() ): ?>
                    <label class="vi-wl-h-custom-radio">Private
                        <input type="radio" checked="checked" name="wishlist-status" value="private">
                        <span class="checkmark"></span>
                    </label>
                    <label class="vi-wl-h-custom-radio">Public
                        <input type="radio" name="wishlist-status" value="publish">
                        <span class="checkmark"></span>
                    </label>

				<?php endif; ?>
                <button type="submit" class="vi-wcwl-h-submit-form">Add wishlist</button>
                <button type="button" class="vi-wl-h-form-btn-cancel">Cancel</button>
                <p class="vi-wcwl-edit-form-note">* The Shop Managers will review this wishlist if you select public
                    option</p>
            </div>
        </div>
        <div class="vi-wl-h-select-wishlist">
            <select class="vi-wl-h-select-option vi-wl-h-table-custom-select">
            </select>
			<?php
			wp_reset_postdata();
			?>
        </div>

        <div class="vi-wcwl-h-product-table-wishlist">
            <div class="vi-wl-h-table-product-rsptable-header">
                <div class="vi-wl-h-table-product-rsptable-header-image">Image</div>
                <div class="vi-wl-h-table-product-rsptable-header-title-price">Info</div>
                <div class="vi-wl-h-table-product-rsptable-header-atc"></div>
                <div class="vi-wl-h-table-product-rsptable-header-remove"></div>
            </div>
            <div class="vi-wl-h-table-product-rsptable">
				<?php
				if ( isset( $product_id_arr['product_id'] ) ):
					foreach ( $product_id_arr['product_id'] as $product_id ):
						global $product;

						$product = wc_get_product( $product_id );
						$image   = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
						if ( empty( $image ) ) {
							$image[0] = wc_placeholder_img_src( 'shop_thumbnail' );
						}
						?>
                        <div class="vi-wcwl-h-table-content">
                            <div class="vi-wcwl-h-table-image">
                                <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                                    <img src="<?php echo esc_url( $image[0] ); ?>"
                                         alt="<?php echo esc_attr( $product->get_name() ); ?>">
                                </a>
                            </div>
                            <div class="vi-wcwl-h-table-title-price">
                                <div class="vi-wcwl-h-table-title-price-info">
									<?php printf( '<a target="_blank" href="%s">%s</a>', esc_url( $product->get_permalink() ), esc_html( $product->get_name() ) ); ?>
                                    <div>
										<?php echo wp_kses_post( $product->get_price_html() ); ?>
                                    </div>
                                </div>
                                <div class="vi-wcwl-h-table-add-to-cart-form">
                                    <div class="vi-wcwl-h-table-add-to-cart-form-btn-option">
										<?php
										add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
										switch ( $product->get_type() ) {
											case 'variable':
												printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-variations">%s</button>', esc_attr( $product->get_id() ),
													esc_html( $product->add_to_cart_text() ) );
												break;
											case 'grouped':
												printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-grouped">%s</button>', esc_attr( $product->get_id() ),
													esc_html__( 'Select products', 'wishlist-for-woo' ) );
												break;
											case 'external':
												woocommerce_external_add_to_cart();
												break;
											default:
												woocommerce_simple_add_to_cart();
										}
										?>
                                    </div>
                                    <div class="vi-wcwl-h-table-show-add-to-cart-form"></div>
                                </div>
                            </div>
                            <div class="vi-wl-h-table-product-remove">
                                <i data-product_id="<?php echo esc_attr( $product_id ) ?>" class="vi-wl-cancel"></i>
                            </div>
                        </div>
					<?php endforeach;
				else:
					?>
                    <div style="text-align: center;font-size: 25px;">Empty product</div>
				<?php endif; ?>
            </div>
        </div>

		<?php if ( $post->post_status === 'publish' ): ?>
            <div class="vi-wl-single-bottom-bar">
                <span class="vi-wl-single-bottom-bar-title"><?php echo esc_html__( 'Share with: ',
		                'vi-woo-wishlist' ); ?> </span>
				<?php if ( $this->settings->get_params( 'fb_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-fb">
                        <a target="_blank" href="https://facebook.com/sharer.php?u=<?php the_permalink(); ?>">
                            <i class="vi-wl-facebook"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'tumblr_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-tb">
                        <a target="_blank" href="https://www.tumblr.com/share/link?url=<?php the_permalink(); ?>">
                            <i class="vi-wl-tumblr"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'twitter_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-tw">
                        <a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>" target="_blank">
                            <i class="vi-wl-twitter-sign"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'pinterest_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-prs">
                        <a href="https://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>">
                            <i class="vi-wl-pinterest"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'instagram_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-isg">
                        <a href="https://www.instagram.com/?url=<?php the_permalink(); ?>" target="_blank">
                            <i class="vi-wl-instagram"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'copy_link' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-copy vi-wl-h-tooltiptext"
                          data-share_url="<?php the_permalink(); ?>">
                        <i class="vi-wl-copy"></i>
                    </span>
				<?php endif; ?>
            </div>
		<?php
		endif;

		return ob_get_clean();
	}

	public function remove_product_in_wl() {
		if ( is_user_logged_in() ) {
			$this->product_id = sanitize_text_field( $_POST['productId'] );
			$wishlist_id      = sanitize_text_field( $_POST['wishlistId'] );
			$wishlist_meta    = get_post_meta( $wishlist_id, 'vi_woo_wishlist_meta', true );
			$key              = array_search( $this->product_id, $wishlist_meta['product_id'] );

			unset( $wishlist_meta['product_id'][ $key ] );
			$wishlist_meta['product_id'] = array_values( $wishlist_meta['product_id'] );
			update_post_meta( $wishlist_id, 'vi_woo_wishlist_meta', $wishlist_meta );

			$product_html = $this->get_html_product_items( $wishlist_meta['product_id'] );

			$data = array(
				'product_html' => $product_html
			);
			wp_send_json( $data );
		} else {
			if ( isset( $_POST['productArr'] ) && is_array( $_POST['productArr'] ) ) {
				$html = $this->get_html_product_items( wc_clean( $_POST['productArr'] ) );
				$data = array(
					'product_html' => $html
				);

				wp_send_json( $data );
			} else {
				wp_send_json( [
					'product_html' => '',
				] );
			}
		}
		wp_die();
	}

	public function shortcode_enqueue_script() {

		if ( ! wp_style_is( 'vi-woo-wl-shortcode-style', 'registered' ) ) {
			wp_register_style( 'vi-woo-wl-shortcode-style', VI_WOO_PRODUCT_WISHLIST_CSS . 'shortcode-style.css',
				array(), VI_WOO_PRODUCT_WISHLIST_VERSION );
		}
		if ( ! wp_script_is( 'vi-woo-wl-shortcode-script', 'registered' ) ) {
			wp_register_script( 'vi-woo-wl-shortcode-script', VI_WOO_PRODUCT_WISHLIST_JS . 'shortcode-script.js',
				[ 'jquery' ], VI_WOO_PRODUCT_WISHLIST_VERSION );
		}
		if ( ! wp_script_is( 'vi-woo-wl-render-shortcode-script', 'registered' ) ) {
			wp_register_script( 'vi-woo-wl-render-shortcode-script',
				VI_WOO_PRODUCT_WISHLIST_JS . 'render-wishlist-shortcode.js',
				[ 'jquery' ], VI_WOO_PRODUCT_WISHLIST_VERSION );
		}
	}

	public function register_single_wishlist() {
		global $post;
		wp_enqueue_style( 'vi-woo-wl-shortcode-style' );
		wp_enqueue_script( 'vi-woo-wl-shortcode-script' );
		wp_localize_script( 'vi-woo-wl-shortcode-script', 'shortcodeAjaxObj', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		] );

		ob_start();
		if ( is_singular() ):
			$this->post_id = $post->ID;
			$author = $post->post_author;
			$current_id = get_current_user_id();
			$products = get_post_meta( $this->post_id, 'vi_woo_wishlist_meta', true );

			?>
            <div class="vi-wl-h-single-top-bar">
                <button class="vi-wl-h-single-top-bar-vote" value="<?php echo esc_attr( $this->post_id ); ?>">
                    <i class="vi-wl-like-1"></i>
					<?php
					if ( isset( $products['vote'] ) ) {
						echo count( $products['vote'] );
					}
					?>
                </button>
				<?php if ( $current_id != $author && $post->post_status == 'publish' ): ?>
                    <button class="vi-wl-h-single-top-bar-clone" value="<?php esc_html_e( $post->ID ) ?>">
                        <i class="vi-wl-clone"></i>
						<?php esc_html_e( ' Clone this wishlist', 'wishlist-for-woo' ); ?>
                    </button>
				<?php endif; ?>
                <button type="button" class="vi-wl-h-form-btn-atc vi-wl-h-single-top-bar-atc">
                    <i class="vi-wl-shopping-cart"></i>
					<?php echo esc_html( __( 'Add all to cart', 'wishlist-for-woo' ) ); ?>
                </button>
            </div>

            <div class="vi-wcwl-h-product-table-wishlist">
                <div class="vi-wcwl-h-product-table-header"><?php echo esc_html( $post->post_title ); ?></div>
                <div class="vi-wl-h-table-product-rsptable">
					<?php
					if ( isset( $products['product_id'] ) ):
						foreach ( $products['product_id'] as $product_id ):
							global $product;

							$product = wc_get_product( $product_id );
							$image   = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
							if ( empty( $image ) ) {
								$image[0] = wc_placeholder_img_src( 'shop_thumbnail' );
							}
							?>
                            <div class="vi-wcwl-h-table-content">
                                <div class="vi-wcwl-h-table-image">
                                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                                        <img src="<?php echo esc_url( $image[0] ); ?>"
                                             alt="<?php echo esc_attr( $product->get_name() ); ?>">
                                    </a>
                                </div>
                                <div class="vi-wcwl-h-table-title-price">
                                    <div class="vi-wcwl-h-table-title-price-info">
										<?php printf( '<a target="_blank" href="%s">%s</a>', esc_url( $product->get_permalink() ), esc_html( $product->get_name() ) ); ?>
                                        <div>
											<?php echo wp_kses_post( $product->get_price_html() ); ?>
                                        </div>
                                    </div>
                                    <div class="vi-wcwl-h-table-add-to-cart-form">
                                        <div class="vi-wcwl-h-table-add-to-cart-form-btn-option">
											<?php
											add_filter( 'wc_get_template', [ $this, 're_wp_override_woo_templates' ], 10, 2 );
											switch ( $product->get_type() ) {
												case 'variable':
													printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-variations">%s</button>',
														esc_attr( $product->get_id() ),
														esc_html( $product->add_to_cart_text() ) );
													break;
												case 'grouped':
													printf( '<button value="%d" class="vi-wcwl-fake-btn vi-wcwl-table-select-grouped">%s</button>',
														esc_attr( $product->get_id() ),
														esc_html__( 'Select products', 'wishlist-for-woo' ) );
													break;
												case 'external':
													woocommerce_external_add_to_cart();
													break;
												default:
													woocommerce_simple_add_to_cart();
											}
											?>
                                        </div>
                                        <div class="vi-wcwl-h-table-show-add-to-cart-form"></div>
                                    </div>
                                </div>
                            </div>
						<?php endforeach;
					else:
						?>
                        <div style="text-align: center;font-size: 25px;">Empty product</div>
					<?php endif; ?>
                </div>
            </div>


			<?php if ( $post->post_status === 'publish' ): ?>
            <div class="vi-wl-single-bottom-bar">
                <span class="vi-wl-single-bottom-bar-title"><?php echo esc_html__( 'Share with: ',
		                'vi-woo-wishlist' ); ?> </span>
				<?php if ( $this->settings->get_params( 'fb_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-fb">
                        <a target="_blank" href="https://facebook.com/sharer.php?u=<?php the_permalink(); ?>">
                            <i class="vi-wl-facebook"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'tumblr_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-tb">
                        <a target="_blank" href="https://www.tumblr.com/share/link?url=<?php the_permalink(); ?>">
                            <i class="vi-wl-tumblr"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'twitter_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-tw">
                        <a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>" target="_blank">
                            <i class="vi-wl-twitter-sign"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'pinterest_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-prs">
                        <a href="https://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>">
                            <i class="vi-wl-pinterest"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'instagram_share' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-isg">
                        <a href="https://www.instagram.com/?url=<?php the_permalink(); ?>" target="_blank">
                            <i class="vi-wl-instagram"></i>
                        </a>
                    </span>
				<?php endif; ?>
				<?php if ( $this->settings->get_params( 'copy_link' ) == 1 ): ?>
                    <span class="vi-wl-single-bottom-bar-copy vi-wl-h-tooltiptext"
                          data-share_url="<?php the_permalink(); ?>">
                        <i class="vi-wl-copy"></i>
                    </span>
				<?php endif; ?>
            </div>
		<?php
		endif;
		endif;

		return ob_get_clean();
	}

	public function add_all_to_cart_shortcode() {

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
				$this->add_group_product();
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

	public static function add_group_product() {
		$groupArr = [];
		foreach ( $_REQUEST['group'] as $group ) {
			$groupArr[ $group['product_id'] ] = $group['quantity'];
		}

		$resutl = array_filter( $groupArr, function ( $v ) {
			return $v;
		} );
		if ( ! empty( $resutl ) ) {
			foreach ( $resutl as $product_id => $quantity ) {
				$product_status    = get_post_status( $product_id );
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id,
					$quantity );
				$variation_id      = 0;
				$variation         = array();
				if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity,
						$variation_id,
						$variation ) && 'publish' === $product_status ) {

					do_action( 'woocommerce_ajax_added_to_cart', $product_id );

					if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
						wc_add_to_cart_message( array( $product_id => $quantity ), true );
					}
				}
			}
		}
	}

	public function add_to_cart_grouped_products() {
		VI_WOO_PRODUCT_WISHLIST_Frontend_Frontend::add_to_cart_grouped_products();
	}
}