<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_Customize_Control' ) ) {
	require_once( ABSPATH . WPINC . '/class-wp-customize-control.php' );
}
if ( class_exists( 'WP_Customize_Control' ) ) {
	if ( ! class_exists( 'VIWCWL_Customize_Premium' ) ) {
		class VIWCWL_Customize_Premium extends WP_Customize_Control {
			protected function render_content() {
				?>
				<label>
					<?php
					if ( ! empty( $this->label ) ) {
						echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
					}
					if ( ! empty( $this->description ) ) {
						echo sprintf( '<span class="description customize-control-description">%s</span>', esc_html( $this->description ) );
					}
					if ( ! empty( $this->choices['button'] ) ) {
						echo sprintf( '<a class="button vi-wcwl-premium" href="https://1.envato.market/bW20B"  target="_blank" >%s</a>', __( 'Unlock This Feature', 'woo-cart-all-in-one' ));
					}
					if ( ! empty($this->choices['img_src'] ) ) {
						foreach ($this->choices['img_src'] as $img) {
							echo sprintf( '<a class="vi-wcwl-premium" href="https://1.envato.market/bW20B" target="_blank" title="%s"><img src="%s" alt=""></a>',
								__( 'Unlock This Feature', 'woo-cart-all-in-one' ), esc_url( $img ) );
						}
					}
					?>
				</label>
				<?php
			}
		}
	}
	if ( ! class_exists( 'VIWCWL_Customize_Range_Control' ) ) {
		class VIWCWL_Customize_Range_Control extends WP_Customize_Control {
			public function enqueue() {
				$admin = 'VI_WOO_PRODUCT_WISHLIST_Admin_Admin';
				$admin::enqueue_style(
					array( 'vi-wcwl-customize-range' ),
					array( 'range.css' )
				);
				$admin::enqueue_script(
					array( 'vi-wcwl-customize-range' ),
					array( 'range.js' )
				);
			}

			protected function render_content() {
				?>
				<label>
					<?php
					if ( ! empty( $this->label ) ) {
						echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
					}
					if ( ! empty( $this->description ) ) {
						echo sprintf( '<span class="description customize-control-description">%s</span>', esc_html( $this->description ) );
					}
					?>
					<div class="vi-wcwl-customize-range">
						<div class="vi-ui range vi-wcwl-customize-range1" data-start="<?php echo esc_attr( $this->value() ); ?>" <?php $this->input_attrs(); ?>></div>
						<input type="number" class="vi-wcwl-customize-range-value" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?>>
					</div>
					<div class="vi-wcwl-customize-range-min-max">
						<span class="vi-wcwl-customize-range-min"><?php echo esc_attr( $this->input_attrs['min'] ); ?></span>
						<span class="vi-wcwl-customize-range-max"><?php echo esc_attr( $this->input_attrs['max'] ); ?></span>
					</div>
				</label>
				<?php
			}
		}
	}
	if ( ! class_exists( 'VIWCWL_Customize_Checkbox_Control' ) ) {
		class VIWCWL_Customize_Checkbox_Control extends WP_Customize_Control {
			public function enqueue() {
				$admin = 'VI_WOO_PRODUCT_WISHLIST_Admin_Admin';
				$admin::enqueue_style(
					array( 'vi-wcwl-customize-checkbox' ),
					array( 'checkbox.min.css' )
				);
				$admin::enqueue_script(
					array( 'vi-wcwl-customize-checkbox' ),
					array( 'checkbox.min.js' )
				);
			}

			protected function render_content() {
				?>
				<label>
					<?php
					if ( ! empty( $this->label ) ) {
						echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
					}
					if ( ! empty( $this->description ) ) {
						echo sprintf( '<span class="description customize-control-description">%s</span>', esc_html( $this->description ) );
					}
					?>
					<div class="vi-ui toggle checkbox vi-wcwl-customize-checkbox-wrap">
						<input type="hidden"  value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?>>
						<input type="checkbox" name="<?php echo esc_attr( $this->id ); ?>" class="vi-wcwl-customize-checkbox"  <?php  checked( $this->value(), 1 ); ?>><label></label>
					</div>
				</label>
				<?php
			}
		}
	}
	if ( ! class_exists( 'VIWCWL_Customize_Radio_Control' ) ) {
		class VIWCWL_Customize_Radio_Control extends WP_Customize_Control {
			protected function render_content() {
				?>
				<label>
					<?php
					if ( ! empty( $this->label ) ) {
						echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
					}
					foreach ( $this->choices as $choice => $value ) {
						?>
						<div class="vi-wcwl-customize-radio">
							<input type="radio" value="<?php echo esc_attr( $choice ); ?>" name="_customize-<?php echo esc_attr( $this->type ) . '-' . esc_attr( $this->id ); ?>"
							       id="<?php echo esc_attr( $this->id ) . '-choice-' . esc_attr( $choice ); ?>" <?php $this->link();
							checked( $choice, $this->value() ) ?>>
							<label for="<?php echo esc_attr( $this->id ) . '-choice-' . esc_attr( $choice ); ?>"><?php echo wp_kses_post( $value ) ?></label>
						</div>
						<?php
					}
					?>
				</label>
				<?php
			}
		}
	}
}