"use strict";

const addToCartIcon = () => jQuery('.vi-wcwl-sidebar-content-btn-atc .single_add_to_cart_button').html(`<i class="vi-wl-shopping-cart"></i>`);
const getNumber = array => array.flatMap(({product_id}) => product_id).length;
const removeItem = data => {
    let arrProducts = [];
    const allProductId = jQuery('.vi-wishlist-icon-button');
    allProductId.each(function (idx, el) {
        arrProducts.push(el);
    });
    // for (let product of arrProducts) {
    jQuery(allProductId).children('.vi-wl-icon-button-like').removeClass(wishlistSidebar.addedIcon).addClass(wishlistSidebar.addIcon);
    // }
    if (data === null) return;
    for (const id of data) {
        for (const [idx, elem] of arrProducts.entries()) {
            if (Number(id) === jQuery(elem).data('product_id')) {
                jQuery(elem).children('.vi-wl-icon-button-like').removeClass(wishlistSidebar.addIcon).addClass(wishlistSidebar.addedIcon);
            }
        }
    }
}

jQuery(document).ready(function ($) {

    // Slide In Panel - by CodyHouse.co
    const panelTriggers = document.getElementsByClassName('js-cd-panel-trigger');
    if (panelTriggers.length > 0) {
        for (let i = 0; i < panelTriggers.length; i++) {
            (function (i) {
                const panelClass = 'js-cd-panel-' + panelTriggers[i].getAttribute('data-panel'),
                    panel = document.getElementsByClassName(panelClass)[0];
                // open panel when clicking on trigger btn
                panelTriggers[i].addEventListener('click', function (event) {
                    event.preventDefault();
                    addClass(panel, 'cd-panel--is-visible');
                });
                //close panel when clicking on 'x' or outside the panel
                panel.addEventListener('click', function (event) {
                    if (hasClass(event.target, 'vi-wcwl-sidebar-close') || hasClass(event.target, panelClass)) {
                        event.preventDefault();
                        removeClass(panel, 'cd-panel--is-visible');
                    }
                });
            })(i);
        }
    }

    //class manipulations - needed if classList is not supported
    //https://jaketrent.com/post/addremove-classes-raw-javascript/
    function hasClass(el, className) {
        if (el.classList) return el.classList.contains(className);
        else return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
    }

    function addClass(el, className) {
        if (el.classList) el.classList.add(className);
        else if (!hasClass(el, className)) el.className += " " + className;
    }

    function removeClass(el, className) {
        if (el.classList) el.classList.remove(className);
        else if (hasClass(el, className)) {
            var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
            el.className = el.className.replace(reg, ' ');
        }
    }


    $('.vi-wl-add-btn').on('click', function () {
        $('.vi-wl-add-section').toggle();
    });
    $('.vi-wl-cancel-button').on('click', function () {
        $('.vi-wl-add-section').hide();
        $('.vi-create-wl-sidebar').val('');
    });

    const changeIcon = idArray => {
        let arrProducts = [];
        const allProductId = $('.vi-wishlist-icon-button');

        allProductId.each(function (idx, el) {
            arrProducts.push(el);
        });
        // for (let product of arrProducts) {
        $(allProductId).children('.vi-wl-icon-button-like').removeClass('vi-wcwl-added');
        $(allProductId).children('.vi-wl-icon-button-like').removeClass(wishlistSidebar.addedIcon).addClass(wishlistSidebar.addIcon);
        // }
        for (const id of idArray.product_id) {
            for (const [idx, elem] of arrProducts.entries()) {
                // console.log(typeof id)
                if (Number(id) === Number($(elem).data('product_id'))) {
                    $(elem).children('.vi-wl-icon-button-like').addClass('vi-wcwl-added');
                    $(elem).children('.vi-wl-icon-button-like').removeClass(wishlistSidebar.addIcon).addClass(wishlistSidebar.addedIcon);
                }
            }
        }
    }


    // $('.woocommerce-variation-description').trigger('change');

    /*
        * Get data from localstorage
        * */

    const print_local = () => {
        const wishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
        const wishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
        const wishlistActive = JSON.parse(localStorage.getItem('wishlist_active_index'));
        const select = $('.vi-wl-sidebar-select-wishlist');
        if (wishlistName !== null) {
            $('.vi-wl-display-total-prod-number').html(getNumber(wishlistItems));
            for (const [idx, el] of wishlistName.entries()) {
                select.append(`<option data-product_id="${wishlistName[idx].wishlistID}" ${idx === wishlistActive.index ? 'selected' : ''} value="${idx}">${el.wishlistName}</option>`);
            }
        }
        if (wishlistItems !== null) {
            if (wishlistItems.length > 0) {
                $('.vi-wl-display-product').html(wishlistItems[wishlistActive.index].product_html);

                if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                    $('.variations_form').each(function () {
                        // console.log($(this))
                        $(this).wc_variation_form();
                    });
                }
                changeIcon(wishlistItems[wishlistActive.index]);
                $('.vi-wcwl-sidebar-content-btn-atc .single_add_to_cart_button').html(`<i class="vi-wl-shopping-cart"></i>`);
            }
        } else {
            $('.vi-wl-display-total-prod-number').html(0);
        }

    }

    const getCookie = name => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    const checkUser = () => {
        if (getCookie('vi_wl_check_login') === 'logged_in') {
            const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
            const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
            const wishlist_info = [];
            if (getWishlistName !== null) {
                for (const [idx, elem] of getWishlistName.entries()) {
                    wishlist_info.push({'name': elem, 'product_id': getWishlistItems[idx].product_id});
                }
                $.ajax({
                    url: wishlistSidebar.ajaxUrl,
                    type: 'POST',
                    cache: false,
                    dataType: 'JSON',
                    data: {
                        action: 'add_wishlist_from_localstorage',
                        info: wishlist_info,
                    }, success(response) {
                        const wishlistName = [];
                        const wishlistItems = [];
                        document.cookie = "vi_wl_check_login=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        const wishlist = response.all_wishlist;
                        for (const [idx, elem] of wishlist.entries()) {
                            wishlistName.push({
                                'wishlistID': wishlist[idx].wishlist_id,
                                'wishlistName': wishlist[idx].wishlist_title,
                                'description': wishlist[idx].description,
                            });
                            wishlistItems.push({
                                'product_html': wishlist[idx].wishlist_html,
                                'product_id': wishlist[idx].wishlist_product,
                            });
                        }
                        localStorage.setItem('wishlist_name', JSON.stringify(wishlistName));
                        localStorage.setItem('wishlist_items', JSON.stringify(wishlistItems));
                        const wlLen = wishlist.length;
                        for (let i = 0; i < wlLen; i++) {
                            if (Number(wishlist[i].wishlistID) === Number(response.wishlist_id)) {
                                localStorage.setItem('wishlist_active_index', JSON.stringify({
                                    'index': i,
                                }));
                                break;
                            }
                        }
                    }
                });
            } else {
                $.ajax({
                    url: wishlistSidebar.ajaxUrl,
                    type: 'POST',
                    cache: false,
                    dataType: 'JSON',
                    data: {
                        action: 'render_local_storage',
                        info: wishlist_info,
                    }, success(response) {
                        if (response.all_wishlist.length > 0) {
                            const wishlistName = [];
                            const wishlistItems = [];
                            document.cookie = "vi_wl_check_login=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                            const wishlist = response.all_wishlist;
                            for (const [idx, elem] of wishlist.entries()) {
                                wishlistName.push({
                                    'wishlistID': wishlist[idx].wishlist_id,
                                    'wishlistName': wishlist[idx].wishlist_title,
                                    'description': wishlist[idx].description,
                                });
                                wishlistItems.push({
                                    'product_html': wishlist[idx].wishlist_html,
                                    'product_id': wishlist[idx].wishlist_product,
                                });
                            }
                            localStorage.setItem('wishlist_name', JSON.stringify(wishlistName));
                            localStorage.setItem('wishlist_items', JSON.stringify(wishlistItems));
                            const wlLen = wishlist.length;
                            for (let i = 0; i < wlLen; i++) {
                                if (Number(wishlist[i].wishlist_id) === Number(response.wishlist_id)) {
                                    localStorage.setItem('wishlist_active_index', JSON.stringify({'index': i}));
                                    break;
                                }
                            }
                            print_local();
                        } else {
                            document.cookie = "vi_wl_check_login=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        }
                    }
                });
            }
        }
    }
    checkUser();
    print_local();
    /*
    * Ajax sidebar action
    * */
    $(document).on('change', '.vi-wl-sidebar-select-wishlist', function () {
        const loadContent = $('.vi-wl-display-product');
        const wishlistId = Number($(this).val());
        const userWishlistDefault = $(this).find('option:selected').data('product_id');
        $.ajax({
            url: wishlistSidebar.ajaxUrl,
            type: 'POST',
            cache: false,
            data: {
                action: 'vi_woo_wishlist_sidebar',
                wishlistId: userWishlistDefault,
            },
            beforeSend() {
                $('.vi-wl-display-product').html('');
                $('.vi-wl-h-cd-panel__container').prepend(`<div class="vi-wcwl-spin-icon"></div>`);
            }, success(data) {
                // if (data.user_status === 'guest') {
                $(document).find('.vi-wcwl-spin-icon').remove();
                const wishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                localStorage.setItem('wishlist_active_index', JSON.stringify({'index': wishlistId}));
                loadContent.html(wishlistItems[wishlistId].product_html);
                addToCartIcon();
                changeIcon(wishlistItems[wishlistId]);
                if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                    $('.variations_form').each(function () {
                        $(this).wc_variation_form();
                    });
                }
            }
        });
    });

    /*
    * Remove product on sidebar button
    * */
    $(document).on('click', '.vi-wcwl-sidebar-delete-product', function () {
        const productId = $(this).data('product_id');
        const this2 = this;
        const displayArea = $('.vi-wl-display-product');
        $.ajax({
            url: wishlistSidebar.ajaxUrl,
            dataType: 'JSON',
            type: 'POST',
            cache: false,
            data: {
                action: 'vi_woo_wishlist_sidebar_delete_prod',
                productId: productId,
            },
            beforeSend() {
                $(this2).css('cursor', 'wait');
                $('.cd-panel__content').css('cursor', 'wait');
            },
            success(data) {
                $('.cd-panel__content').css('cursor', 'unset');
                const wishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                const wishlistActive = JSON.parse(localStorage.getItem('wishlist_active_index'));
                const productArr = wishlistItems[wishlistActive.index].product_id;
                const index = productArr.indexOf(`${productId}`);

                if (index > -1) {
                    productArr.splice(index, 1);
                }
                $(this2).parent().parent().remove();
                wishlistItems[wishlistActive.index].product_id = productArr;
                wishlistItems[wishlistActive.index].product_html = displayArea.html();
                changeIcon(wishlistItems[wishlistActive.index]);
                localStorage.setItem('wishlist_items', JSON.stringify(wishlistItems));
                addToCartIcon();
                $('.vi-wl-display-total-prod-number').html(getNumber(wishlistItems));
            }
        });
    });
    /*
    * save wishlist button process
    * */
    $('.vi-wl-save-button').on('click', function () {
        const select = $('.vi-wl-sidebar-select-wishlist');
        const addSelectSection = $('.vi-wl-add-section');
        const selectedEl = $('.vi-wl-sidebar-select-wishlist option:selected');
        const wishlistSelectEl = $('.vi-create-wl-sidebar');
        const wishlistName = wishlistSelectEl.val();
        const displayArea = $('.vi-wl-display-product');
        const iconBtnAdd = $('.vi-wishlist-icon-button');
        if (wishlistName === '') {
            alert('Empty name');
            return;
        }
        $.ajax({
            url: wishlistSidebar.ajaxUrl,
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            data: {
                action: 'vi_add_wishlist_sidebar',
                wishlistName: wishlistName,
            },
            beforeSend() {
                $('.vi-wl-save-button').addClass('loading');
            },
            success(data) {
                $('.vi-wl-save-button').removeClass('loading');
                const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                if (getWishlistName === null) {
                    localStorage.setItem('wishlist_name', JSON.stringify([{
                        'wishlistID': data.user_status === 'guest' ? '' : data.wishlist_id,
                        'wishlistName': wishlistName,
                        'description': '',
                    }]));
                    localStorage.setItem('wishlist_items', JSON.stringify([{
                        'product_html': '',
                        'product_id': []
                    }]));
                    localStorage.setItem('wishlist_active_index', JSON.stringify({'index': 0, 'html': ''}));
                    addSelectSection.hide();
                    select.append(`<option value="0" data-wishlist_id="${data.wishlist_id}">${wishlistName}</option>`);
                    wishlistSelectEl.val('');

                } else {
                    const wishlist_items = JSON.parse(localStorage.getItem('wishlist_items'));
                    getWishlistName.push({
                        'wishlistID': data.user_status === 'guest' ? '' : data.wishlist_id,
                        'wishlistName': wishlistName,
                        'description': '',
                    });
                    wishlist_items.push({'product_html': '', 'product_id': []});
                    localStorage.setItem('wishlist_name', JSON.stringify(getWishlistName));
                    localStorage.setItem('wishlist_items', JSON.stringify(wishlist_items));
                    localStorage.setItem('wishlist_active_index', JSON.stringify({
                        'index': getWishlistName.length - 1,
                    }));

                    selectedEl.removeAttr('selected');
                    select.append(`<option data-wishlist_id="${data.wishlist_id}" selected value="${getWishlistName.length - 1}">${wishlistName}</option>`);
                    addSelectSection.hide();
                    displayArea.html('');
                    wishlistSelectEl.val('');
                    $(iconBtnAdd).children('.vi-wl-icon-button-like').removeClass('vi-wcwl-added');
                    $(iconBtnAdd).children('.vi-wl-icon-button-like').removeClass(wishlistSidebar.addedIcon).addClass(wishlistSidebar.addIcon);

                }
            }
        });

    });

    $.fn.serializeArrayAll = function () {
        const rCRLF = /\r?\n/g;
        return this.map(function () {
            return this.elements ? jQuery.makeArray(this.elements) : this;
        }).map(function (i, elem) {
            const val = jQuery(this).val();
            if (val == null) {
                return val == null
                //next 2 lines of code look if it is a checkbox and set the value to blank
                //if it is unchecked
            } else if (this.type === "checkbox" && this.checked === false) {
                return {name: this.name, value: this.checked ? this.value : ''}
                //next lines are kept from default jQuery implementation and
                //default to all checkboxes = on
            } else {
                return jQuery.isArray(val) ?
                    jQuery.map(val, function (val, i) {
                        return {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                    }) :
                    {name: elem.name, value: val.replace(rCRLF, "\r\n")};
            }
        }).get();
    };

    $(document).on('click', '.vi-wcwl-sidebar-card .single_add_to_cart_button:not(.disabled)', function (e) {

        const $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            data = $form.find(
                'input:not([name="product_id"]), select, button, textarea').serializeArrayAll() || 0;

        $.each(data, function (i, item) {
            if (item.name === 'add-to-cart') {
                item.name = 'product_id';
                item.value = $form.hasClass('grouped_form') ? $form.find('[name="add-to-cart"]').val() : $form.find('input[name=variation_id]').val() ||
                    $thisbutton.val();
            }
        });


        e.preventDefault();

        let action = {'name': 'action', 'value': 'add_to_cart_on_sidebar'};

        // data.push(nonce);
        data.push(action);

        $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

        if ($form.hasClass('grouped_form')) {
            data[data.length - 1].value = 'add_to_cart_grouped_products';

            $.ajax({
                type: 'POST',
                url: wishlistSidebar.ajaxUrl,
                data: data,
                beforeSend: function (response) {
                    $thisbutton.removeClass('added').addClass('loading');
                },
                complete: function (response) {
                    $thisbutton.addClass('added').removeClass('loading');
                },
                success: function (response) {

                    if (response.error && response.product_url) {
                        window.location = response.product_url;
                        return;
                    }
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);

                    // $(document.body).find('a.added_to_cart').css({"padding": 0, "font-size": "12px"});
                },
            });
            return;
        }
        $.ajax({
            type: 'POST',
            url: wishlistSidebar.ajaxUrl,
            data: data,
            beforeSend: function (response) {
                $thisbutton.removeClass('added').addClass('loading');
            },
            complete: function (response) {
                $thisbutton.addClass('added').removeClass('loading');
            },
            success: function (response) {

                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
            },
        });
        return false;
    });

    $('.vi-wcwl-sidebar-card .single_add_to_cart_button').html(`<i class="vi-wl-shopping-cart"></i>`);


    // $(document.body).find('.vi-wcwl-sidebar-content-btn-atc .grouped_form button').addClass('disabled');

    $(document).on('click', function (e) {
        if ($(e.target).closest('.vi-wl-add-btn, .vi-wl-add-section').length === 0) {
            $('.vi-wl-add-section').hide();
        }
    });

    // $(document.body).on('click', '.vi-wcwl-sidebar-content-btn-atc .single_add_to_cart_button.disabled', function (e) {
    //     e.preventDefault();
    //     const rootEl = $(this).closest('div'),
    //         formEl = rootEl.closest('form');
    //     $(formEl).find('table').css('display', 'grid');
    // });

    $(document.body).on('click', function (e) {
        if ($(e.target).closest('.variations_form, .grouped_form').length === 0) {
            // $('.vi-wcwl-sidebar-card .variations_form').remove();
            // $('.vi-wcwl-sidebar-card .grouped_form').remove();
            // $('form.variations_form').find('select').val('').trigger('change');
            // $('form.variations_form').trigger('reset_data');
            // $('.vi-wcwl-select-variations').show();
        }
    });


    $(document.body).on('click', '.vi-wcwl-sidebar-content-btn-atc .vi-wcwl-select-variations', function (e) {

        const this2 = $(this);
        const getParent = $(this).parent().parent();
        const productId = $(this).val();

        if ($(this).hasClass('vi-wcwl-got-product-variations')) {
            getParent.closest('.vi-wcwl-sidebar-card').find('.display-variations-form-html').html('');
            $(this).removeClass('vi-wcwl-got-product-variations');
            return;
        }

        $.ajax({
            url: wishlistSidebar.ajaxUrl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                productId: productId,
                action: 'render_variations_form_html'
            },
            beforeSend() {
                this2.addClass('vi-wcwl-got-product-variations');
                this2.addClass('loading');
            },
            success(response) {
                this2.removeClass('loading');
                // this2.hide();
                getParent.closest('.vi-wcwl-sidebar-card').find('.display-variations-form-html').html(response.add_to_cart_form);
                $('.display-variations-form-html .single_add_to_cart_button').html(`<i class="vi-wl-shopping-cart"></i>`);
                if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                    $('.variations_form').each(function () {
                        $(this).wc_variation_form();
                    });
                }
            }
        });
    });

    $(document.body).on('click', '.vi-wcwl-sidebar-btn-add-all-to-cart', function () {
        const this2 = $(this);
        const disabledBtn = $('.vi-wcwl-sidebar-card .single_add_to_cart_button.disabled');
        if (disabledBtn.length > 0) {
            let cf;
            cf = confirm(`You have ${disabledBtn.length} products that have not selected variations. Do you want to continue ?`);
            if (!cf) {
                return;
            }
        }
        const button = $('.vi-wcwl-sidebar-card .single_add_to_cart_button:not(.disabled)'),
            form = button.closest('form.cart');
        if (button.length === 0) {
            return;
        }
        let group = [];
        let variation = [];
        let data = [];
        form.each(function (id, el) {
            let quantity = $(el).find('input[name="quantity"]').val();
            let product_id = $(el).find('[name="add-to-cart"]').val();
            let variation_id = $(el).find('[name="variation_id"]').val();
            if (typeof variation_id === 'undefined') {
                variation_id = 0;
            }
            if ($(el).hasClass('variations_form')) {
                const data = $(this).find('input:not([name="product_id"]), select, button, textarea').serializeArrayAll();
                const pattern = /attribute/i;
                for (let elem of data) {
                    if (elem.name.match(pattern)) {
                        // console.log(elem.name)
                        variation.push({[elem.name]: elem.value});
                    }
                }
            }
            if ($(el).hasClass('grouped_form')) {
                let data = $(this).find('input:not([name="product_id"]), select, button, textarea').serializeArrayAll();
                const pattern = /quantity/g;
                const idPattern = /(?<=\[).*?(?=\])/g;
                let idGroup = 0;
                for (let elem of data) {
                    if (elem.name === 'add-to-cart') {
                        idGroup = elem.value;
                    }
                    if (elem.name.match(pattern)) {
                        let matches = elem.name.match(idPattern);
                        group.push({product_id: matches[0], quantity: elem.value});
                        // group.push({[matches[0]]: elem.value});
                    }
                }

            }
            let tempData = {product_id, quantity, variation_id, variation}
            data.push(tempData);
        });

        $.ajax({
            url: wishlistSidebar.ajaxUrl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'add_all_to_cart_sidebar',
                data: data, group
            },
            beforeSend() {
                $('.vi-wl-h-cd-panel__container .vi-wcwl-overlay').show();
            },
            success(response) {
                $('.vi-wl-h-cd-panel__container .vi-wcwl-overlay').hide();
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
            }
        });
    });
});