jQuery(document).ready(function ($) {
    'use strict';
    wp.customize('vi_wishlist_params[ic_add_icon]', function (value) {
        value.bind(function (newval) {
            $.ajax({
                url: vi_wcwl_preview.ajax_url,
                type: 'POST',
                data: {
                    action: 'vi_wcwl_get_class_icon',
                    icon_id: newval,
                    type: 'add_to_wishlist_icons',
                },
                success: function (response) {
                    if (response && response.status === 'success') {
                        jQuery('.vi-wishlist-icon-button i').attr('class', response.message + ' vi-wl-icon-button-like');
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        });
    });

    wp.customize('vi_wishlist_params[ic_color]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-icon-button-like').css('color', newval);
        });
    });
    wp.customize('vi_wishlist_params[ic_size]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-icon-button-like').css('font-size', newval + 'px');
        });
    });

    wp.customize('vi_wishlist_params[ic_position]', function (value) {
        value.bind(function (newval) {
            let wrap = jQuery('.vi-wishlist-icon-button');
            let oldval = wrap.data('position');
            wrap.removeClass('vi-wcwl-add-icon-position-' + oldval).addClass('vi-wcwl-add-icon-position-' + newval);
            wrap.data('position', newval);
            wrap.data('old_position', oldval);
        });
    });

    wp.customize('vi_wishlist_params[ft_select_icon]', function (value) {
        value.bind(function (newval) {
            $.ajax({
                url: vi_wcwl_preview.ajax_url,
                type: 'POST',
                data: {
                    action: 'vi_wcwl_get_class_icon',
                    icon_id: newval,
                    type: 'floating_icons',
                },
                success: function (response) {
                    if (response && response.status === 'success') {
                        jQuery('.vi-wl-floating-icon-sidebar').html(`<i class="vi-wl-floating-icon-set-flex ${response.message}"></i>`);
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        });
    });

    wp.customize('vi_wishlist_params[ft_position]', function (value) {
        value.bind(function (newval) {
            let wrap = jQuery('.vi-wl-icon-bar');
            let oldval = wrap.data('position');
            wrap.removeClass('vi-wl-icon-bar-position-' + oldval).addClass('vi-wl-icon-bar-position-' + newval);
            wrap.data('position', newval);
            wrap.data('old_position', oldval);
        });
    });

    wp.customize('vi_wishlist_params[ft_color]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-floating-icon-sidebar').css('color', newval);
        });
    });
    wp.customize('vi_wishlist_params[ft_size]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-icon-bar .vi-wl-floating-icon-sidebar').css('font-size', newval + 'px');
        });
    });
    wp.customize('vi_wishlist_params[wl_header_background]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-single-top-bar button').css('background-color', newval);
        });
    });
    wp.customize('vi_wishlist_params[wl_header_color]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-single-top-bar button').css('color', newval);
        });
    });
    wp.customize.preview.bind('open-sidebar', function () {
        $('.vi-wl-h-sidebar-cd-panel').addClass('cd-panel--is-visible');
    });
    wp.customize('vi_wishlist_params[wl_even_background]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-table-product-rsptable .vi-wcwl-h-table-content:nth-of-type(even)').css('background-color', newval);
        });
    });

    wp.customize('vi_wishlist_params[wl_odd_background]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-table-product-rsptable .vi-wcwl-h-table-content:nth-of-type(odd)').css('background-color', newval);
        });
    });
    wp.customize('vi_wishlist_params[fb_share]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.vi-wl-facebook').show()
            } else {
                $('.vi-wl-facebook').hide();
            }
        });
    });

    wp.customize('vi_wishlist_params[tumblr_share]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.vi-wl-tumblr').show()
            } else {
                $('.vi-wl-tumblr').hide();
            }
        });
    });

    wp.customize('vi_wishlist_params[twitter_share]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.vi-wl-twitter-sign').show()
            } else {
                $('.vi-wl-twitter-sign').hide();
            }
        });
    });

    wp.customize('vi_wishlist_params[pinterest_share]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.vi-wl-pinterest').show()
            } else {
                $('.vi-wl-pinterest').hide();
            }
        });
    });

    wp.customize('vi_wishlist_params[instagram_share]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.vi-wl-instagram').show()
            } else {
                $('.vi-wl-instagram').hide();
            }
        });
    });

    wp.customize('vi_wishlist_params[copy_link]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.vi-wl-copy').show()
            } else {
                $('.vi-wl-copy').hide();
            }
        });
    });

    wp.customize('vi_wishlist_params[sb_header_bg]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-cd-panel__container .cd-panel__header').css('background-color', newval);
        });
    });
    wp.customize('vi_wishlist_params[sb_header_color]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-cd-panel__container .cd-panel__header h4 a').css('color', newval);
            $('.vi-wl-h-cd-panel__container .cd-panel__header .vi-wl-add-btn').css('color', newval);
            $('.vi-wl-h-cd-panel__container .cd-panel__header .js-cd-close').css('color', newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_header_font]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-cd-panel__container .cd-panel__header h4 a').css('font-size', newval + 'px');
        });
    });

    wp.customize('vi_wishlist_params[sb_header_txt_tranform]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-cd-panel__container .cd-panel__header h4 a').css('text-transform', newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_header_text]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-h-cd-panel__container .cd-panel__header h4 a').html(newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_select_background]', function (value) {
        value.bind(function (newval) {
            $('select.vi-wl-h-sidebar-custom-select').css('background-color', newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_select_color]', function (value) {
        value.bind(function (newval) {
            $('select.vi-wl-h-sidebar-custom-select').css('color', newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_footer_btn_1_txt]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-sidebar-wrap-footer a.vi-wcwl-h-sidebar-footer-link').html(newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_footer_btn_1_bg]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-sidebar-wrap-footer a.vi-wcwl-h-sidebar-footer-link').css('background-color', newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_footer_btn_1_cl]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-sidebar-wrap-footer a.vi-wcwl-h-sidebar-footer-link').css('color', newval);
        });
    });


    wp.customize('vi_wishlist_params[sb_footer_btn_2_txt]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-sidebar-wrap-footer button.vi-wcwl-sidebar-btn-add-all-to-cart').html(newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_footer_btn_2_bg]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-sidebar-wrap-footer button.vi-wcwl-sidebar-btn-add-all-to-cart').css('background-color', newval);
        });
    });

    wp.customize('vi_wishlist_params[sb_footer_btn_2_cl]', function (value) {
        value.bind(function (newval) {
            $('.vi-wl-sidebar-wrap-footer button.vi-wcwl-sidebar-btn-add-all-to-cart').css('color', newval);
        });
    });

});
