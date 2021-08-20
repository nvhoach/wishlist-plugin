jQuery(document).ready(function ($) {
    'use strict';

    $('.vi-ui .checkbox').checkbox();
    $('.vi-ui .dropdown').dropdown();

    $('.vi-wishlist-delete-product-admin').on('click', function (e) {
        e.preventDefault();
        const product_id = $(this).val(),
            wishlist_id = $(this).data('wishlist_id'),
            this2 = $(this);

        $.ajax({
            url: viwcwlAdminObj.ajaxUrl,
            type: 'POST',
            data: {
                action: 'delete_product_from_wishlist',
                nonce: viwcwlAdminObj.nonce,
                product_id: product_id,
                wishlist_id: wishlist_id,
            },
            success(response) {
                this2.parent().closest('tr').remove();
            }
        });
    });

})