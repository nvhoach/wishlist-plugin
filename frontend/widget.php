<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class VI_WOO_PRODUCT_WISHLIST_Frontend_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'vi_wishlist_widget', // Base ID
			'VI Woocommerce Wishlist', // Name
			array( 'description' => __( 'Wishlist Woocommerce', 'wishlist-for-woo' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {

		extract( $args );
		$title           = apply_filters( 'widget_title', $instance['title'] );
		$number_wishlist = $instance['number_wishlist'];
		$date            = $instance['date'];
		$author          = $instance['author'];

		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		$wishlist = new WP_Query( [
			'post_type'      => 'vi_woo_wishlist',
			'post_status'    => 'publish',
			'posts_per_page' => $number_wishlist
		] );
		if ( $wishlist->have_posts() ): ?>
            <div class="vi-woo-wl-widget">
				<?php while ( $wishlist->have_posts() ):
					$wishlist->the_post();
					?>
                    <div class="vi-woo-wl-widget-item">
                        <div class="vi-woo-wl-widget-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </div>
                        <div class="vi-woo-wl-widget-date">
							<?php if ( $date != '' ) {
								echo get_the_date();
							} ?>
                        </div>
                        <div class="vi-woo-wl-widget-author">
							<?php if ( $author != '' ) {
								esc_html_e( 'By: ', 'wishlist-for-woo' ) . ( get_the_author() == '' ) ? esc_html_e( 'Guess' ) : the_author();
							}
							?>
                        </div>
                    </div>
				<?php endwhile; ?>
            </div>
		<?php
		endif;
		wp_reset_postdata();
		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {

		$date   = '';
		$author = '';
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Wishlist', 'wishlist-for-woo' );
		}
		if ( isset( $instance['number_wishlist'] ) ) {
			$number_wishlist = $instance['number_wishlist'];
		} else {
			$number_wishlist = 5;
		}
		if ( isset( $instance['date'] ) && $instance['date'] != '' ) {
			$date = 'checked';
		}
		if ( isset( $instance['author'] ) && $instance['author'] != '' ) {
			$author = 'checked';
		}
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                   value="<?php echo esc_attr( $title ); ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name( 'number_wishlist' ); ?>"><?php _e( 'Number of Wishlist:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'number_wishlist' ); ?>"
                   name="<?php echo $this->get_field_name( 'number_wishlist' ); ?>" type="number"
                   value="<?php echo esc_attr( $number_wishlist ) ?>"/>
        </p>

        <p>
            <input <?php echo esc_html( $date ); ?> type="checkbox" id="<?php echo $this->get_field_id( 'date' ) ?>"
                                                    name="<?php echo $this->get_field_name( 'date' ) ?>">
            <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e( 'Show Date', 'wishlist-for-woo' ); ?></label>
            <br>
            <input <?php echo esc_html( $author ); ?> type="checkbox" id="<?php echo $this->get_field_id( 'author' ) ?>"
                                                      name="<?php echo $this->get_field_name( 'author' ) ?>">
            <label for="<?php echo $this->get_field_id( 'author' ); ?>"><?php _e( 'Show Author', 'wishlist-for-woo' ); ?></label>
            <br>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'thumbnail' ) ?>"
                   name="<?php echo $this->get_field_name( 'thumbnail' ) ?>">
            <label for="<?php echo $this->get_field_id( 'thumbnail' ); ?>"><?php _e( 'Show Thumbnail', 'wishlist-for-woo' ); ?></label>
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = array();
		$instance['title']           = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['number_wishlist'] = ( ! empty( $new_instance['number_wishlist'] ) ) ? strip_tags( $new_instance['number_wishlist'] ) : '';
		$instance['date']            = ( ! empty( $new_instance['date'] ) ) ? strip_tags( $new_instance['date'] ) : '';
		$instance['author']          = ( ! empty( $new_instance['author'] ) ) ? strip_tags( $new_instance['author'] ) : '';

//		$instance['thumbnail']       = ( ! isset( $new_instance['thumbnail'] ) ) ? strip_tags( $new_instance['thumbnail'] ) : '';

		return $instance;
	}
}

function vi_wl_register_custom_widget() {
	register_widget( 'VI_WOO_PRODUCT_WISHLIST_Frontend_Widget' );
}

add_action( 'widgets_init', 'vi_wl_register_custom_widget' );