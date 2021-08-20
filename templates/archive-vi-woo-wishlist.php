<?php
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$wishlist = new WP_Query( [
	'post_type'      => 'vi_woo_wishlist',
	'post_status'    => 'publish',
	'posts_per_page' => - 1
] );
?>
    <h2><?php echo esc_html__( 'All wishlist published', 'wishlist-for-woo' ) ?></h2>
    <p>Resize the browser window to see the effect.</p>
<?php
if ( $wishlist->have_posts() ): ?>
    <div class="vi-woo-wl-h-archive-row">
		<?php
		$image_arr = [];
		while ( $wishlist->have_posts() ):
			$wishlist->the_post();
			$get_the_products_id = get_number_products_in_wishlist( get_the_ID() );
			?>
            <div class="vi-woo-wl-h-archive-column">
                <div class="vi-woo-wl-h-archive-card">
                    <div class="vi-woo-wl-h-archive-image">
						<?php for ( $i = 0; $i < count( $get_the_products_id ); $i ++ ): ?>
							<?php if ( $i === 5 ): ?>
                                <div class="view-more-product-images">
                                    <a href="<?php the_permalink(); ?>">+<?php echo count( $get_the_products_id ) - 5; ?></a>
                                </div>
								<?php
								break;
							endif; ?>
							<?php
							$product = wc_get_product( $get_the_products_id[ $i ] );
							$image   = wp_get_attachment_image_src( $product->get_image_id(), 'shop_thumbnail' );
							if ( empty( $image ) ) {
								$image[0] = wc_placeholder_img_src();
							}
							?>
                            <img src="<?php echo esc_html( $image[0] ); ?>" alt="">
						<?php endfor; ?>
                    </div>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="vi-woo-wl-h-title"><?php the_excerpt(); ?></p>
                    <p><?php echo esc_html__( 'Published by: ', 'wishlist-for-woo' ); ?><?php the_author() ?></p>
                    <p><?php echo esc_html__( 'Published date: ',
							'wishlist-for-woo' ); ?><?php echo get_the_date() ?></p>
                </div>
            </div>
		<?php
		endwhile;
		?>
    </div>
<?php
else:
	?>
    <h3><?php echo __( 'No wishlist', 'wishlist-for-woo' ) ?></h3>
<?php
endif;
wp_reset_postdata();

get_sidebar( 'shop' );

get_footer( 'shop' );