<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOO_PRODUCT_WISHLIST_Admin_Settings {
	protected $settings, $error;

	public function __construct() {
		$this->settings = new VI_WOO_PRODUCT_WISHLIST_DATA();
		add_action( 'admin_menu', [ $this, 'create_options_page' ] );
		add_action( 'admin_init', array( $this, 'save_settings' ), 1 );
		add_filter( 'plugin_action_links_product-wishlist-for-woo/product-wishlist-for-woo.php', array( $this, 'settings_link' ) );
	}

	public function settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=vi_woo_wishlist&page=product-wishlist-for-woo" title="' . __( 'Settings', 'wishlist-for-woo' ) . '">' . __( 'Settings',
				'wishlist-for-woo' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function save_settings() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';

		if ( $page !== 'product-wishlist-for-woo' ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['_woo_product_wishlist_nonce'] ) || ! wp_verify_nonce( $_POST['_woo_product_wishlist_nonce'],
				'vi_wishlist_woo_settings' ) ) {
			return;
		}
		if ( ! isset( $_POST['vi_wcwl_setting_save'] ) ) {
			return;
		}

		global $vi_wcwl_settings;

		$map_array = array(
			'enable_wishlist',
			'login_enable',
			'enable_multi',
		);

		$args = array();

		foreach ( $map_array as $item ) {
			$args[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( wp_unslash( $_POST[ $item ] ) ) : '';
		}

		$args = wp_parse_args( $args, get_option( 'vi_wishlist_params', $vi_wcwl_settings ) );

		update_option( 'vi_wishlist_params', $args );

		$vi_wcwl_settings = '';
		$this->settings   = new VI_WOO_PRODUCT_WISHLIST_DATA();

	}

	public function create_options_page() {
		add_submenu_page(
			'edit.php?post_type=vi_woo_wishlist',
			esc_html__( 'Settings', 'wishlist-for-woo' ),
			esc_html__( 'Settings', 'wishlist-for-woo' ),
			'manage_options',
			'product-wishlist-for-woo',
			array( $this, 'setting_callback' )
		);
	}

	public function setting_callback() {

		?>
        <div class="wrap woo-wishlist">
            <h2><?php esc_attr_e( 'Product Wishlist for WooCommerce Settings', 'wishlist-for-woo' ) ?></h2>
            <form method="post" class="vi-ui form">
				<?php wp_nonce_field( 'vi_wishlist_woo_settings', '_woo_product_wishlist_nonce' ); ?>
                <div class="vi-ui segment">
					<?php
					$wl_enable       = $this->settings->get_params( 'enable_wishlist' );
					$wl_login_enable = $this->settings->get_params( 'login_enable' );
					$wl_enable_multi = $this->settings->get_params( 'enable_multi' );
					?>
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="vi_wcwl_enable_wishlist">
									<?php esc_html_e( 'Enable', 'wishlist-for-woo' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="vi_wcwl_enable_wishlist" type="checkbox" tabindex="0" class="hidden"
										<?php checked( $wl_enable, 1 ) ?>
                                           value="1" name="enable_wishlist"/>
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="vi_wcwl_login_enable">
									<?php esc_html_e( 'Available for only logged in users', 'wishlist-for-woo' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="vi_wcwl_login_enable" type="checkbox" tabindex="0" class="hidden"
										<?php checked( $wl_login_enable, 1 ) ?>
                                           value="1" name="login_enable"/>
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="vi_wcwl_enable_multiple_wishlist">
									<?php esc_html_e( 'Enable multiple wishlist', 'wishlist-for-woo' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="vi_wcwl_enable_multiple_wishlist"
										<?php checked( $wl_enable_multi, 1 ) ?>
                                           type="checkbox"
                                           tabindex="0" class="hidden"
                                           value="1"
                                           name="enable_multi"/>
                                    <label></label>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="vi_wcwl_manage_my_wishlist">
									<?php esc_html_e( 'Select Wishlist Manage Page', 'wishlist-for-woo' ) ?>
                                </label>
                            </th>
                            <td>
                                <select class="vi-ui fluid dropdown" name="vi_wcwl_manage_page">
                                    <option value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option>
									<?php
									$get_page_option = get_option( 'vi_wishlist_page_id' );
									$pages           = get_pages();
									foreach ( $pages as $page ) {
										printf( '<option %s value="%d">%s</option>',
											$get_page_option == $page->ID ? 'selected' : '', $page->ID,
											$page->post_title );
									}
									?>
                                </select>
                                <p>Make sure insert this shortcode into the page <strong>[vi_woo_my_wishlist]</strong>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="vi_wcwl_custom_design">
									<?php esc_html_e( 'Custom Design', 'wishlist-for-woo' ) ?>
                                </label>
                            </th>
                            <td>
								<?php
								$url = admin_url( 'customize.php' ) . '?url=' . urlencode( get_site_url() ) . '&autofocus[panel]=vi_wcwl_design';
								?>
                                <a target="_blank" class="vi_wcwl_custom_design" href="<?php echo esc_attr( esc_url( $url ) ) ?>"><?php esc_html_e( 'Go to design',
										'wishlist-for-woo' ) ?></a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>

                <!--button-->
                <button name="vi_wcwl_setting_save" class="vi-ui button labeled icon primary wn-submit">
                    <i class="send icon"></i> <?php esc_html_e( 'Save', 'wishlist-for-woo' ) ?>
                </button>

            </form>
			<?php do_action( 'villatheme_support_product-wishlist-for-woo' ); ?>
        </div>
		<?php
	}

}