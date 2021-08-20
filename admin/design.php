<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOO_PRODUCT_WISHLIST_Admin_Design {
	protected $settings, $admin, $customize;

	public function __construct() {
		$this->settings = new VI_WOO_PRODUCT_WISHLIST_DATA();
		$this->admin    = 'VI_WOO_PRODUCT_WISHLIST_Admin_Admin';
		add_action( 'customize_register', array( $this, 'design_option_customizer' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );
		add_action( 'wp_print_styles', array( $this, 'customize_controls_print_styles' ) );
	}

	public function customize_controls_print_styles() {
		if ( ! is_customize_preview() ) {
			return;
		}
		global $wp_customize;
		$this->customize = $wp_customize;

		?>
        <style type="text/css" id="wi-wcwl-preview-ic-add-icon-color">
            <?php
             $add_icon_color = $this->get_params_customize('ic_color');
             ?>
            .vi-wishlist-icon-button {
                color: <?php echo sprintf('%s', $add_icon_color) ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-preview-ic-icon-size">
            <?php $floating_icon_color =  $this->get_params_customize('ft_color'); ?>
            .vi-wl-icon-bar .vi-wl-floating-icon-sidebar {
                color: <?php echo sprintf('%s', $floating_icon_color) ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-preview-wl-header">
            <?php
             $header_background =  $this->get_params_customize('wl_header_background');
             $header_color =  $this->get_params_customize('wl_header_color');

            ?>
            .vi-wl-h-single-top-bar button {
                background-color: <?php echo sprintf('%s', $header_background) ?>;
                color: <?php echo sprintf('%s', $header_color) ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-preview-wl-body">
            <?php
            $even_background = $this->get_params_customize('wl_even_background');
            $odd_background = $this->get_params_customize('wl_odd_background');

            ?>
            .vi-wl-h-table-product-rsptable .vi-wcwl-h-table-content:nth-of-type(even) {
                background-color: <?php echo sprintf('%s', $even_background) ?>;
            }

            .vi-wl-h-table-product-rsptable .vi-wcwl-h-table-content:nth-of-type(odd) {
                background-color: <?php echo sprintf('%s', $odd_background) ?>;
            }
        </style>

        <style type="text/css" id="vi-wcwl-preview-wl-social">
            <?php
            $fb_share = $this->get_params_customize('fb_share');
            $tumblr_share = $this->get_params_customize('tumblr_share');
            $twitter_share = $this->get_params_customize('twitter_share');
            $pinterest_share = $this->get_params_customize('pinterest_share');
            $instagram_share = $this->get_params_customize('instagram_share');
            $copy_link = $this->get_params_customize('copy_link');
            ?>
            .vi-wl-facebook {
                display: <?php if ($fb_share != 1) echo 'none;' ?>;
            }

            .vi-wl-tumblr {
                display: <?php if ($tumblr_share != 1) echo 'none;' ?>;
            }

            .vi-wl-twitter-sign {
                display: <?php if ($twitter_share != 1) echo 'none;' ?>;
            }

            .vi-wl-pinterest {
                display: <?php if ($pinterest_share != 1) echo 'none;' ?>;
            }

            .vi-wl-instagram {
                display: <?php if ($instagram_share != 1) echo 'none;' ?>;
            }

            .vi-wl-copy {
                display: <?php if ($copy_link != 1) echo 'none;' ?>;
            }

        </style>
        <style id="vi-wcwl-sidebar-preview-style-custom">
            <?php
            $sb_header_background = $this->get_params_customize('sb_header_bg');
            $sb_header_color = $this->get_params_customize('sb_header_color');
            $sb_header_text_transform = $this->get_params_customize('sb_header_txt_tranform');
            $sb_select_background = $this->get_params_customize('sb_select_background');
            $sb_select_color = $this->get_params_customize('sb_select_color');
            $sb_footer_btn_1_background = $this->get_params_customize('sb_footer_btn_1_bg');
            $sb_footer_btn_1_color = $this->get_params_customize('sb_footer_btn_1_cl');
            $sb_footer_btn_2_background = $this->get_params_customize('sb_footer_btn_2_bg');
            $sb_footer_btn_2_color = $this->get_params_customize('sb_footer_btn_2_cl');
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
		$this->add_preview_style( 'ic_size', '.vi-wl-icon-button-like', 'font-size', 'px' );
		$this->add_preview_style( 'ft_size', '.vi-wl-icon-bar .vi-wl-floating-icon-sidebar', 'font-size', 'px' );
		$this->add_preview_style( 'sb_header_font', '.vi-wl-h-cd-panel__container .cd-panel__header h4 a', 'font-size', 'px' );
	}

	protected function get_params_customize( $name = '' ) {
		if ( ! $name ) {
			return '';
		}

		return $this->customize->post_value( $this->customize->get_setting( 'vi_wishlist_params[' . $name . ']' ),
			$this->settings->get_params( $name ) );
	}

	private function add_preview_style( $name, $element, $style, $suffix = '' ) {
		$id = 'vi-wcwl-preview-' . $name;
		?>
        <style type="text/css" id="<?php echo esc_attr( $id ); ?>">
            <?php
            $css = $element.'{';
            if($value = $this->get_params_customize($name)){
                $css .= $style.': '.$value.$suffix.' ;';
            }
            $css .= '}';
            echo wp_kses_post($css);
             ?>
        </style>
		<?php
	}

	public function customize_preview_init() {
		$this->admin::enqueue_script(
			array( 'vi-wcwl-customize-preview' ),
			array( 'customize-preview.js' ),
			array( array( 'jquery', 'customize-preview', 'flexslider' ) ),
			'enqueue', true
		);
		$args = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'vi-wcwl-customize-preview', 'vi_wcwl_preview', $args );

	}

	public function customize_controls_enqueue_scripts() {
		$this->admin::enqueue_style(
			array( 'vi-wcwl-add-icons' ),
			array( 'flaticon.css' )
		);
		$this->admin::enqueue_style(
			array( 'vi-wcwl-add-icons-2' ),
			array( 'flaticon_icon2.css' )
		);
		$this->admin::enqueue_style(
			array( 'vi-wcwl-customize-preview' ),
			array( 'customize-preview.css' )
		);
		$this->admin::enqueue_script(
			array( 'vi-wcwl-customize-setting' ),
			array( 'customize-setting.js' ),
			array( array( 'jquery', 'jquery-ui-button' ) ),
			'enqueue', true
		);

		$wishlist_page = get_option( 'vi_wishlist_page_id', array() );
		$page          = get_permalink( $wishlist_page );


		$args = array(
			'page_url' => $page,
		);

		wp_localize_script( 'vi-wcwl-customize-setting', 'vi_wcwl_preview_setting', $args );
	}

	public function design_option_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'vi_wcwl_design', array(
			'priority'       => 200,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Woocommerce Product Wishlist', 'wishlist-for-woo' ),
		) );
		$this->custom_add_to_wishlist_icon( $wp_customize );
		$this->custom_floating_wishlist_icon( $wp_customize );
		$this->custom_wishlist_single( $wp_customize );
		$this->custom_sidebar_panel( $wp_customize );

	}

	protected function custom_add_to_wishlist_icon( $wp_customize ) {

		$wp_customize->add_section( 'add_to_wishlist_icon', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Add to wishlist icon', 'wishlist-for-woo' ),
			'panel'          => 'vi_wcwl_design',
		) );

		$add_wishlist_icons   = $this->settings->get_class_icons( 'add_to_wishlist_icons' );
		$add_wishlist_icons_t = array();
		foreach ( $add_wishlist_icons as $k => $class ) {
			$add_wishlist_icons_t[ $k ] = '<i class="vi-wl-icon-button-like ' . $class . '"></i>';
		}

		$wp_customize->add_setting( 'vi_wishlist_params[ic_add_icon]',
			array(
				'default'    => '',
				'type'       => 'option',
				'capability' => 'manage_options',
				'transport'  => 'postMessage',
			) );
		$wp_customize->add_control( new VIWCWL_Customize_Radio_Control( $wp_customize,
			'vi_wishlist_params[ic_add_icon]',
			array(
				'label'   => __( 'Add to wishlist icon', 'wishlist-for-woo' ),
				'section' => 'add_to_wishlist_icon',
				'choices' => $add_wishlist_icons_t
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[ic_position]',
			array(
				'default'           => 'top_right',
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'vi_wishlist_params[ic_position]', array(
				'label'    => esc_html__( 'Add to wishlist icon position (thumbnail)', 'wishlist-for-woo' ),
				'settings' => 'vi_wishlist_params[ic_position]',
				'section'  => 'add_to_wishlist_icon',
				'type'     => 'select',
				'choices'  => array(
					'top_left'     => __( 'Top Left', 'wishlist-for-woo' ),
					'top_right'    => __( 'Top Right', 'wishlist-for-woo' ),
					'bottom_left'  => __( 'Bottom Left', 'wishlist-for-woo' ),
					'bottom_right' => __( 'Bottom Right', 'wishlist-for-woo' ),
				),
			)
		);
		//Icon color
		$wp_customize->add_setting( 'vi_wishlist_params[ic_color]', array(
			'default'           => '#dd3333',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[ic_color]', array(
				'label'    => __( 'Add to wishlist icon color', 'wishlist-for-woo' ),
				'section'  => 'add_to_wishlist_icon',
				'settings' => 'vi_wishlist_params[ic_color]',
			) ) );

		//Icon size
		$wp_customize->add_setting( 'vi_wishlist_params[ic_size]', array(
			'default'           => $this->settings->get_params( 'ic_size' ),
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'manage_options',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new VIWCWL_Customize_Range_Control( $wp_customize, 'vi_wishlist_params[ic_size]',
			array(
				'label'       => __( 'Icon size (px)', 'wishlist-for-woo' ),
				'section'     => 'add_to_wishlist_icon',
				'input_attrs' => array(
					'min'  => 15,
					'max'  => 40,
					'step' => 1,
					'id'   => 'vi-wcwl-ic-size',
				),
			) ) );
	}

	protected function custom_floating_wishlist_icon( $wp_customize ) {
		$wp_customize->add_section( 'floating_wishlist_icon', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Floating wishlist icon', 'wishlist-for-woo' ),
			'panel'          => 'vi_wcwl_design',
		) );

		$floating_icons   = $this->settings->get_class_icons( 'floating_icons' );
		$floating_icons_t = array();
		foreach ( $floating_icons as $k => $class ) {
			$floating_icons_t[ $k ] = '<i class="' . $class . '"></i>';
		}

		$wp_customize->add_setting( 'vi_wishlist_params[ft_select_icon]',
			array(
				'default'    => $this->settings->get_default( 'ft_select_icon' ),
				'type'       => 'option',
				'capability' => 'manage_options',
				'transport'  => 'postMessage',
			) );
		$wp_customize->add_control( new VIWCWL_Customize_Radio_Control( $wp_customize,
			'vi_wishlist_params[ft_select_icon]',
			array(
				'label'   => __( 'Select floating icon', 'wishlist-for-woo' ),
				'section' => 'floating_wishlist_icon',
				'choices' => $floating_icons_t
			) ) );
		//Floating icon position

		$wp_customize->add_setting( 'vi_wishlist_params[ft_position]',
			array(
				'default'           => 'middle_right',
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'vi_wishlist_params[ft_position]', array(
				'label'    => esc_html__( 'Floating wishlist icon position', 'wishlist-for-woo' ),
				'settings' => 'vi_wishlist_params[ft_position]',
				'section'  => 'floating_wishlist_icon',
				'type'     => 'select',
				'choices'  => array(
					'middle_left'  => __( 'Middle Left', 'wishlist-for-woo' ),
					'middle_right' => __( 'Middle_right', 'wishlist-for-woo' ),
					'top_left'     => __( 'Top Left', 'wishlist-for-woo' ),
					'top_right'    => __( 'Top Right', 'wishlist-for-woo' ),
					'bottom_left'  => __( 'Bottom Left', 'wishlist-for-woo' ),
					'bottom_right' => __( 'Bottom Right', 'wishlist-for-woo' ),
				),
			)
		);

		//Set floating icon color
		$wp_customize->add_setting( 'vi_wishlist_params[ft_color]', array(
			'default'           => $this->settings->get_default( 'ft_color' ),
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vi_wishlist_params[ft_color]',
			array(
				'label'    => __( 'Select floating icon color', 'wishlist-for-woo' ),
				'section'  => 'floating_wishlist_icon',
				'settings' => 'vi_wishlist_params[ft_color]',
			) ) );

		//Set floating wishlist icon size

		$wp_customize->add_setting( 'vi_wishlist_params[ft_size]', array(
			'default'           => $this->settings->get_default( 'ft_size' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new VIWCWL_Customize_Range_Control( $wp_customize, 'vi_wishlist_params[ft_size]',
			array(
				'label'       => __( 'Floating wishlist icon size (px)', 'wishlist-for-woo' ),
				'section'     => 'floating_wishlist_icon',
				'input_attrs' => array(
					'min'  => 20,
					'max'  => 100,
					'step' => 1,
					'id'   => 'vi-wcwl-ft-size',
				),
			) ) );
	}

	protected function custom_wishlist_single( $wp_customize ) {
		$wp_customize->add_section( 'wishlist_single_page_option', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Custom wishlist single', 'wishlist-for-woo' ),
			'panel'          => 'vi_wcwl_design',
		) );

		$wp_customize->add_setting( 'vi_wishlist_params[wl_header_background]', array(
			'default'           => $this->settings->get_default( 'wl_header_background' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[wl_header_background]', array(
				'label'    => __( 'Button Background', 'wishlist-for-woo' ),
				'section'  => 'wishlist_single_page_option',
				'settings' => 'vi_wishlist_params[wl_header_background]',
			) ) );

		//set table header color

		$wp_customize->add_setting( 'vi_wishlist_params[wl_header_color]', array(
			'default'           => $this->settings->get_default( 'wl_header_color' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[wl_header_color]', array(
				'label'    => __( 'Button text color', 'wishlist-for-woo' ),
				'section'  => 'wishlist_single_page_option',
				'settings' => 'vi_wishlist_params[wl_header_color]',
			) ) );

		//body color

		$wp_customize->add_setting( 'vi_wishlist_params[wl_even_background]', array(
			'default'           => $this->settings->get_default( 'wl_even_background' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[wl_even_background]', array(
				'label'    => __( 'Table row color (even)', 'wishlist-for-woo' ),
				'section'  => 'wishlist_single_page_option',
				'settings' => 'vi_wishlist_params[wl_even_background]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[wl_odd_background]', array(
			'default'           => $this->settings->get_default( 'wl_odd_background' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[wl_odd_background]', array(
				'label'    => __( 'Table row color (odd)', 'wishlist-for-woo' ),
				'section'  => 'wishlist_single_page_option',
				'settings' => 'vi_wishlist_params[wl_odd_background]',
			) ) );

		// Share social
		$wp_customize->add_setting( 'vi_wishlist_params[fb_share]',
			array(
				'default'           => $this->settings->get_default( 'fb_share' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCWL_Customize_Checkbox_Control( $wp_customize,
				'vi_wishlist_params[fb_share]', array(
					'label'    => esc_html__( 'Facebook Share button', 'wishlist-for-woo' ),
					'settings' => 'vi_wishlist_params[fb_share]',
					'section'  => 'wishlist_single_page_option',
				) )
		);

		$wp_customize->add_setting( 'vi_wishlist_params[tumblr_share]',
			array(
				'default'           => $this->settings->get_default( 'tumblr_share' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCWL_Customize_Checkbox_Control( $wp_customize,
				'vi_wishlist_params[tumblr_share]', array(
					'label'    => esc_html__( 'Tumblr Share button', 'wishlist-for-woo' ),
					'settings' => 'vi_wishlist_params[tumblr_share]',
					'section'  => 'wishlist_single_page_option',
				) )
		);

		$wp_customize->add_setting( 'vi_wishlist_params[twitter_share]',
			array(
				'default'           => $this->settings->get_default( 'twitter_share' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCWL_Customize_Checkbox_Control( $wp_customize,
				'vi_wishlist_params[twitter_share]', array(
					'label'    => esc_html__( 'Twitter Share button', 'wishlist-for-woo' ),
					'settings' => 'vi_wishlist_params[twitter_share]',
					'section'  => 'wishlist_single_page_option',
				) )
		);

		$wp_customize->add_setting( 'vi_wishlist_params[pinterest_share]',
			array(
				'default'           => $this->settings->get_default( 'pinterest_share' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCWL_Customize_Checkbox_Control( $wp_customize,
				'vi_wishlist_params[pinterest_share]', array(
					'label'    => esc_html__( 'Pinterest Share button', 'wishlist-for-woo' ),
					'settings' => 'vi_wishlist_params[pinterest_share]',
					'section'  => 'wishlist_single_page_option',
				) )
		);

		$wp_customize->add_setting( 'vi_wishlist_params[instagram_share]',
			array(
				'default'           => $this->settings->get_default( 'instagram_share' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCWL_Customize_Checkbox_Control( $wp_customize,
				'vi_wishlist_params[instagram_share]', array(
					'label'    => esc_html__( 'Pinterest Share button', 'wishlist-for-woo' ),
					'settings' => 'vi_wishlist_params[instagram_share]',
					'section'  => 'wishlist_single_page_option',

				) )
		);

		$wp_customize->add_setting( 'vi_wishlist_params[copy_link]',
			array(
				'default'           => $this->settings->get_default( 'copy_link' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCWL_Customize_Checkbox_Control( $wp_customize,
				'vi_wishlist_params[copy_link]', array(
					'label'    => esc_html__( 'Share link button', 'wishlist-for-woo' ),
					'settings' => 'vi_wishlist_params[copy_link]',
					'section'  => 'wishlist_single_page_option',
				) )
		);
	}

	protected function custom_sidebar_panel( $wp_customize ) {
		$wp_customize->add_section( 'wishlist_sidebar_panel', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Custom Sidebar Panel', 'wishlist-for-woo' ),
			'panel'          => 'vi_wcwl_design',
		) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_header_text]', array(
			'default'           => $this->settings->get_default( 'sb_header_text' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( 'vi_wishlist_params[sb_header_text]', array(
			'type'     => 'textarea',
			'priority' => 10,
			'section'  => 'wishlist_sidebar_panel',
			'label'    => __( 'Header Text', 'wishlist-for-woo' )
		) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_header_txt_tranform]',
			array(
				'default'           => $this->settings->get_default( 'sb_header_txt_tranform' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'vi_wishlist_params[sb_header_txt_tranform]', array(
				'label'    => esc_html__( 'Header text transform', 'wishlist-for-woo' ),
				'settings' => 'vi_wishlist_params[sb_header_txt_tranform]',
				'section'  => 'wishlist_sidebar_panel',
				'type'     => 'select',
				'choices'  => array(
					'uppercase'  => __( 'Upper Case', 'wishlist-for-woo' ),
					'lowercase'  => __( 'Lower Case', 'wishlist-for-woo' ),
					'capitalize' => __( 'Capitalize', 'wishlist-for-woo' ),
					'none'       => __( 'None', 'wishlist-for-woo' ),
				),
			)
		);

		$wp_customize->add_setting( 'vi_wishlist_params[sb_header_font]', array(
			'default'           => $this->settings->get_params( 'sb_header_font' ),
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'manage_options',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new VIWCWL_Customize_Range_Control( $wp_customize,
			'vi_wishlist_params[sb_header_font]',
			array(
				'label'       => __( 'Header font size', 'wishlist-for-woo' ),
				'section'     => 'wishlist_sidebar_panel',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 50,
					'step' => 1,
					'id'   => 'vi-wcwl-sidebar-font-size',
				),
			) ) );


		$wp_customize->add_setting( 'vi_wishlist_params[sb_header_bg]',
			array(
				'default'           => $this->settings->get_default( 'sb_header_bg' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_header_bg]', array(
				'label'    => __( 'Sidebar header color', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_header_bg]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_header_color]',
			array(
				'default'           => $this->settings->get_default( 'sb_header_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_header_color]', array(
				'label'    => __( 'Sidebar header color', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_header_color]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_select_background]',
			array(
				'default'           => $this->settings->get_default( 'sb_select_background' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_select_background]', array(
				'label'    => __( 'Select Background', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_select_background]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_select_color]',
			array(
				'default'           => $this->settings->get_default( 'sb_select_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_select_color]', array(
				'label'    => __( 'Select Background', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_select_color]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_footer_btn_1_txt]', array(
			'default'           => $this->settings->get_default( 'sb_footer_btn_1_txt' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( 'vi_wishlist_params[sb_footer_btn_1_txt]', array(
			'type'     => 'textarea',
			'priority' => 10,
			'section'  => 'wishlist_sidebar_panel',
			'label'    => __( 'Wishlist manage button text', 'wishlist-for-woo' )
		) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_footer_btn_1_bg]',
			array(
				'default'           => $this->settings->get_default( 'sb_footer_btn_1_bg' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_footer_btn_1_bg]', array(
				'label'    => __( 'Wishlist manage button background', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_footer_btn_1_bg]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_footer_btn_1_cl]',
			array(
				'default'           => $this->settings->get_default( 'sb_footer_btn_1_cl' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_footer_btn_1_cl]', array(
				'label'    => __( 'Wishlist manage button color', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_footer_btn_1_cl]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_footer_btn_2_txt]', array(
			'default'           => $this->settings->get_default( 'sb_footer_btn_2_txt' ),
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( 'vi_wishlist_params[sb_footer_btn_2_txt]', array(
			'type'     => 'textarea',
			'priority' => 10,
			'section'  => 'wishlist_sidebar_panel',
			'label'    => __( 'Wishlist manage button text', 'wishlist-for-woo' )
		) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_footer_btn_2_bg]',
			array(
				'default'           => $this->settings->get_default( 'sb_footer_btn_1_bg' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_footer_btn_2_bg]', array(
				'label'    => __( 'Wishlist manage button background', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_footer_btn_2_bg]',
			) ) );

		$wp_customize->add_setting( 'vi_wishlist_params[sb_footer_btn_2_cl]',
			array(
				'default'           => $this->settings->get_default( 'sb_footer_btn_2_cl' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'vi_wishlist_params[sb_footer_btn_2_cl]', array(
				'label'    => __( 'Wishlist manage button color', 'wishlist-for-woo' ),
				'section'  => 'wishlist_sidebar_panel',
				'settings' => 'vi_wishlist_params[sb_footer_btn_2_cl]',
			) ) );
	}
}