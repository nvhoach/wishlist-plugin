jQuery(document).ready(function ($) {
    "use strict";

    function myFunction(x) {
        if (x.matches) { // If media query matches
            addToCartIcon();
            // $('.vi-wcwl-h-product-table-atc').removeClass('vi-wl-h-table-product-cell');
        }
    }

    const renderWishlist = () => {
        const selectWishlistPage = $('.vi-wl-h-select-option');
        const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
        const getDefaultWishlist = JSON.parse(localStorage.getItem('wishlist_active_index'));
        const topBarEl = $('.vi-wl-h-single-top-bar');
        if (getWishlistName === null) {
            topBarEl.find('button:not(.vi-wl-h-default)').hide();
            $('.vi-wl-h-select-wishlist').hide();
        } else {
            if (getWishlistName.length > 0) {
                selectWishlistPage.html('');
                for (const [idx, elem] of getWishlistName.entries()) {
                    selectWishlistPage.append(`<option ${idx === Number(getDefaultWishlist.index) ? 'selected' : ''} value="${idx}" data-wishlist_id="${elem.wishlistID}">${elem.wishlistName}</option>`)
                }
                $('.vi-wl-h-wishlist-name').val(getWishlistName[getDefaultWishlist.index].wishlistName);
                $('.vi-wl-h-wishlist-description').val(getWishlistName[getDefaultWishlist.index].description);
                const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                const productArr = getWishlistItems[getDefaultWishlist.index].product_id;
                const wishlistId = $(selectWishlistPage).find('option:selected').attr('data-wishlist_id');

                $.ajax({
                    url: shortcodeAjaxObj.ajaxUrl,
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'render_wishlist_local',
                        wishlistId: wishlistId,
                        productArr: productArr,
                    },
                    success(response) {
                        $('.vi-wl-h-table-product-rsptable').html(response.product_table);
                        $('.vi-wl-h-table-product-rsptable .single_add_to_cart_button:not(.vi-wcwl-fake-btn)').html(`<i class="vi-wl-shopping-cart"></i>`);
                        const windowWidth = window.matchMedia("(max-width: 500px)");
                        myFunction(windowWidth) // Call listener function at run time
                        if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                            $('.variations_form').each(function () {
                                $(this).wc_variation_form();
                            });
                        }
                    }
                });
            }
        }
    }
    renderWishlist();
});