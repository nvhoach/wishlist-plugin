jQuery(document).ready(function ($) {
    'use strict';
    $(document).on('click', '.vi-wishlist-icon-button', function (e) {
        e.preventDefault();
        if ($(this).children().hasClass('vi-wcwl-added')) {
            return;
        }
        const productId = $(this).data('product_id');
        const this2 = this;
        const getLoadingEl = $(this).parent().find('.vi-wcwl-h-waiting-loading-spiner');
        getLoadingEl.addClass('vi-wcwl-spin-icon');
        $(this).find('.vi-wl-icon-button-like').hide();

        setTimeout(function () {
            $.ajax({
                url: wishlistButtonIcon.ajaxUrl,
                type: 'POST',
                dataType: 'JSON',
                async: false,
                data: {
                    action: 'vi_wishlist_icon_button',
                    productId: productId,
                },
                beforeSend() {
                    getLoadingEl.addClass('vi-wcwl-spin-icon');
                },
                success: function (data) {
                    getLoadingEl.removeClass('vi-wcwl-spin-icon');
                    $(this2).find('.vi-wl-icon-button-like').show();
                    const sidebarPanel = $('.vi-wl-side-panel');
                    const displayArea = $('.vi-wl-display-product');
                    const select = $('.vi-wl-sidebar-select-wishlist');
                    const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                    const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                    if (getWishlistName === null) {
                        const wishlistName = [{
                            "wishlistID": data.user_status === 'logged_in' ? Number(data.wishlist_id) : '',
                            "wishlistName": 'My Wishlist',
                            "description": '',
                        }];
                        const wishlistItems = [{
                            "product_html": data.product_html,
                            "product_id": [data.product_id],
                        }];
                        localStorage.setItem('wishlist_name', JSON.stringify(wishlistName));
                        localStorage.setItem('wishlist_items', JSON.stringify(wishlistItems));
                        localStorage.setItem('wishlist_active_index', JSON.stringify({
                            'index': 0,
                        }));

                        const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                        const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                        $('.vi-wl-display-total-prod-number').html(getNumber(getWishlistItems));

                        displayArea.html(getWishlistItems[0].product_html);
                        if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                            $('.variations_form').each(function () {
                                $(this).wc_variation_form();
                            });
                        }
                        select.append(`<option selected value="0" data-wishlist_id="${getWishlistName[0].wishlistID}">${getWishlistName[0].wishlistName}</option>`);
                        $(this2).find('.vi-wl-icon-button-like').addClass('vi-wcwl-added');
                        $(this2).find('.vi-wl-icon-button-like').removeClass(wishlistButtonIcon.addIcon).addClass(wishlistButtonIcon.addedIcon);

                    } else {
                        const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                        const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                        const getWishlistActive = JSON.parse(localStorage.getItem('wishlist_active_index'));

                        if (getWishlistItems[getWishlistActive.index].product_id.includes(data.product_id)) {
                            return false;
                        } else {
                            const dataItems = getWishlistItems[Number(getWishlistActive.index)];
                            dataItems.product_html += data.product_html;
                            dataItems.product_id.push(data.product_id);
                            localStorage.setItem('wishlist_items', JSON.stringify(getWishlistItems));

                            displayArea.html(getWishlistItems[getWishlistActive.index].product_html);

                            if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                                $('.variations_form').each(function () {
                                    $(this).wc_variation_form();
                                });
                            }

                            $('.vi-wcwl-sidebar-content-btn-atc .single_add_to_cart_button').html(`<i class="vi-wl-shopping-cart"></i>`);
                            $(this2).find('.vi-wl-icon-button-like').addClass('vi-wcwl-added');
                            $(this2).find('.vi-wl-icon-button-like').removeClass(wishlistButtonIcon.addIcon).addClass(wishlistButtonIcon.addedIcon);
                            $('.vi-wl-display-total-prod-number').html(getNumber(getWishlistItems));
                            $(this2).find('.vi-wl-icon-button-like').show();
                        }
                    }

                }
            });
        }, 10);
    });

    /*
    * Change favorite icon if wishlist has changed
    * */
    const changeIcon = idArray => {
        let arrProducts = [];
        const allProductId = $('.vi-wishlist-icon-button');
        allProductId.each(function (idx, el) {
            arrProducts.push(el);
        });

        for (const id of idArray.product_id) {
            for (const [idx, elem] of arrProducts.entries()) {
                if (Number(id) === $(elem).data('product_id')) {
                    $(elem).children('.vi-wl-icon-button-like').addClass('vi-wcwl-added');
                    $(elem).children('.vi-wl-icon-button-like').removeClass(wishlistButtonIcon.addIcon).addClass(wishlistButtonIcon.addedIcon);
                }
            }
        }
    }
    const getNumber = array => array.flatMap(({product_id}) => product_id).length;



});
