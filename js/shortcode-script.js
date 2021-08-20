jQuery(document).ready(function ($) {
    "use strict";

    const addToCartIcon = () => $('.vi-wl-h-table-product-rsptable .single_add_to_cart_button').html(`<i class="vi-wl-shopping-cart"></i>`);
    addToCartIcon();

    function myFunction(x) {
        if (x.matches) { // If media query matches
            addToCartIcon();
            // $('.vi-wcwl-h-product-table-atc').removeClass('vi-wl-h-table-product-cell');
        }
    }

    const windowWidth = window.matchMedia("(max-width: 500px)");
    myFunction(windowWidth) // Call listener function at run time

    $(document.body).on('click', '.vi-wl-h-table-product-remove', function () {

        const this2 = this;
        const selectEl = $('.vi-wl-h-select-option');
        const wishlistDefault = Number(selectEl.val());
        const productId = $(this).find('.vi-wl-cancel').attr('data-product_id');
        const wishlistId = $('.vi-wl-h-select-wishlist').find('option:selected').attr('data-wishlist_id');
        const getItems = JSON.parse(localStorage.getItem('wishlist_items'));
        const getIndex = JSON.parse(localStorage.getItem('wishlist_active_index'));
        const productArr = getItems[getIndex.index].product_id;
        const index = productArr.indexOf(productId);

        if (index > -1) {
            productArr.splice(index, 1);
        }
        $(this2).prepend(`<div style="width: 22px; height: 22px;" class="vi-wcwl-spin-icon"></div>`);
        setTimeout(function () {
            $.ajax({
                url: shortcodeAjaxObj.ajaxUrl,
                type: 'POST',
                dataType: 'JSON',
                async: false,
                data: {
                    action: 'remove_product_in_wl',
                    wishlistId,
                    productId,
                    productArr: productArr,
                },
                beforeSend() {
                    // $(this2).prepend(`<div style="width: 22px; height: 22px;" class="vi-wcwl-spin-icon"></div>`);
                },
                success(response) {
                    getItems[getIndex.index].product_html = response.product_html;
                    getItems[getIndex.index].product_id = productArr;
                    localStorage.setItem('wishlist_items', JSON.stringify(getItems));
                    $(this2).parent().remove();
                }
            });
        }, 10)
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

    $(document).on('click', '.vi-wl-h-table-product-rsptable .single_add_to_cart_button:not(.disabled)', function (e) {

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

        let action = {'name': 'action', 'value': 'add_to_cart_in_shortcode'};

        // data.push(nonce);
        data.push(action);
        $(document.body).trigger('adding_to_cart', [$thisbutton, data]);
        if ($form.hasClass('grouped_form')) {
            data[data.length - 1].value = 'add_to_cart_grouped_products';

            $.ajax({
                type: 'POST',
                url: shortcodeAjaxObj.ajaxUrl,
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

            return;
        }
        $.ajax({
            type: 'POST',
            url: shortcodeAjaxObj.ajaxUrl,
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

    $('.vi-wl-h-default').on('click', function () {
        const submitForm = $('.vi-wcwl-h-submit-form');
        $('.vi-wl-h-form-popup').toggle();
        $('.vi-wl-h-wishlist-name').val('');
        $('.vi-wl-h-wishlist-description').val('');
        submitForm.text('Add wishlist');
        submitForm.addClass('vi-wl-h-form-btn-add');
        submitForm.removeClass('vi-wl-h-form-btn-update');
    });
    $('.vi-wl-h-form-btn-cancel').on('click', function () {
        $(this).parent().parent().css('display', 'none');
        $('.vi-wl-h-wishlist-name').val('')
        $('.vi-wl-h-wishlist-description').val('');

    });
    $(document.body).on('click', '.vi-wl-h-form-btn-add', function () {
        $(this).addClass('loading');
        const this2 = $(this);
        const wishlistName = $('.vi-wl-h-wishlist-name').val();
        const description = $('.vi-wl-h-wishlist-description').val();
        const wishlistStatus = $('input[name=wishlist-status]:checked').val();
        if (wishlistName !== '') {
            $.ajax({
                url: shortcodeAjaxObj.ajaxUrl,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'vi_woo_my_wishlist_page_add',
                    wishlistName: wishlistName,
                    description: description,
                    wishlistStatus: wishlistStatus
                },
                success(data) {
                    $('.vi-wl-h-single-top-bar').find('button').show();
                    const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                    const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
                    if (getWishlistName === null) {
                        localStorage.setItem('wishlist_name', JSON.stringify([{
                            wishlistID: data.id,
                            wishlistName: data.title,
                            description: data.description,
                        }]));
                        localStorage.setItem('wishlist_items', JSON.stringify([{
                            product_html: '',
                            product_id: [],
                        }]));
                        localStorage.setItem('wishlist_active_index', JSON.stringify({index: 0}));
                    } else {
                        getWishlistName.push({
                            wishlistID: data.id,
                            wishlistName: data.title,
                            description: data.description,
                        });
                        localStorage.setItem('wishlist_name', JSON.stringify(getWishlistName));
                        getWishlistItems.push({
                            product_html: '',
                            product_id: [],
                        });
                        localStorage.setItem('wishlist_items', JSON.stringify(getWishlistItems));
                        localStorage.setItem('wishlist_active_index', JSON.stringify({index: getWishlistName.length - 1}));
                    }
                    $('.vi-wl-h-select-wishlist').show();
                    const selectEl = $('.vi-wl-h-select-option');
                    const getWishlistDefault = JSON.parse(localStorage.getItem('wishlist_active_index'));
                    this2.removeClass('loading');
                    selectEl.find('option:selected').removeAttr('selected');
                    selectEl.append(`<option data-wishlist_id="${data.id}" selected value="${getWishlistDefault.index}">${data.title}</option>`);
                    $('.vi-wl-h-table-product-rsptable').html(`<div style="font-size: 18px; text-align: center;">Empty</div>`);
                    $('.vi-wl-h-form-popup').hide();
                    $('.vi-wl-h-wishlist-name').val('');
                    $('.vi-wl-h-wishlist-description').val('');
                }
            });
        } else {
            $('.vi-wl-h-wishlist-name').css('border', '1px solid red');
        }
    });
    $('.vi-wl-h-wishlist-name').on('keypress', function (e) {
        $(this).css('border', 'none');
    });

    $(document).on('change', '.vi-wl-h-select-option', function (e) {
        const wishlistId = Number($(this).val());
        const wishlistSelected = $(this).find('option:selected').data('wishlist_id');
        const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));
        const productArr = getWishlistItems[wishlistId].product_id;
        $('.vi-wl-h-single-top-bar .vi-wl-h-danger').val(wishlistId);
        $.ajax({
            url: shortcodeAjaxObj.ajaxUrl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'display_select_wishlist',
                wishlistId: wishlistSelected,
                productArr: productArr,
            },
            success(response) {
                localStorage.setItem('wishlist_active_index', JSON.stringify({
                    'index': wishlistId
                }));
                const getWishlistIndex = JSON.parse(localStorage.getItem('wishlist_active_index'));
                const getwishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                $('.vi-wl-h-wishlist-name').val(getwishlistName[getWishlistIndex.index].wishlistName);
                $('.vi-wl-h-wishlist-description').val(getwishlistName[getWishlistIndex.index].description);
                $('.vi-wl-h-table-product-rsptable').html(response.product_table);
                addToCartIcon();
                $('.variations_form').each(function () {
                    $(this).wc_variation_form();
                });
            }
        });
    });
    $(document.body).on('click', '.vi-wl-h-single-top-bar .vi-wl-h-danger', function (e) {
        let cf;
        cf = confirm('Are you sure delete this wishlist ?');
        if (cf === false) {
            return;
        }
        $(this).addClass('loading');
        const this2 = $(this);
        const selectEl = $('.vi-wl-h-select-option');
        let wishlistId = selectEl.val();
        let wishlistIdSelected = $(selectEl).find('option:selected').data('wishlist_id');
        if (wishlistId !== '') {
            $.ajax({
                url: shortcodeAjaxObj.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'delete_my_wishlist',
                    wishlistId: wishlistIdSelected,
                },
                success(response) {
                    const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
                    const getWishlistItems = JSON.parse(localStorage.getItem('wishlist_items'));

                    getWishlistName.splice(Number(wishlistId), 1);
                    getWishlistItems.splice(Number(wishlistId), 1);

                    const wishlistDefault = $(selectEl).find('option:selected').val();

                    localStorage.setItem('wishlist_name', JSON.stringify(getWishlistName));
                    localStorage.setItem('wishlist_items', JSON.stringify(getWishlistItems));
                    localStorage.setItem('wishlist_active_index', JSON.stringify({
                        index: 0,
                    }));

                    $('.vi-wl-h-select-option option:selected').remove();
                    selectEl.html('');

                    const getUpdatedName = JSON.parse(localStorage.getItem('wishlist_name'));
                    const getUpdatedItems = JSON.parse(localStorage.getItem('wishlist_items'));
                    const getUpdatedDefault = JSON.parse(localStorage.getItem('wishlist_active_index'));
                    for (const [idx, elem] of getUpdatedName.entries()) {
                        selectEl.append(`<option ${idx === Number(getUpdatedDefault) ? 'selected' : ''} data-wishlist_id="${elem.wishlistID}" value="${idx}">${elem.wishlistName}</option>`);
                    }

                    wishlistId = selectEl.val();
                    wishlistIdSelected = $(selectEl).find('option:selected').data('wishlist_id');
                    this2.removeClass('loading');
                    if (getUpdatedName.length === 0) {
                        localStorage.removeItem('wishlist_name');
                        localStorage.removeItem('wishlist_items');
                        localStorage.removeItem('wishlist_active_index');
                        $('.vi-wl-h-table-product-rsptable').html(`<div style="font-size: 25px; text-align: center">Empty</div>`);
                        $('.vi-wl-h-single-top-bar').find('button:not(.vi-wl-h-default)').hide();
                        $('.vi-wl-h-select-wishlist').hide();
                        return;
                    }

                    $.ajax({
                        url: shortcodeAjaxObj.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'display_select_wishlist',
                            wishlistId: wishlistIdSelected,
                        },
                        success(response) {
                            $('.vi-wl-h-table-product-rsptable').html(response.product_table);
                            addToCartIcon();
                            if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                                $('.variations_form').each(function () {
                                    $(this).wc_variation_form();
                                });
                            }
                        }
                    });
                }
            });
        } else {
            return false;
        }
    });

    $('.vi-wl-h-warning').on('click', function () {
        const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name'));
        const getWlDefault = JSON.parse(localStorage.getItem('wishlist_active_index'));
        $('.vi-wl-h-wishlist-name').val(getWishlistName[getWlDefault.index].wishlistName);
        $('.vi-wl-h-wishlist-description').val(getWishlistName[getWlDefault.index].description);
        const submitFrom = $('.vi-wcwl-h-submit-form');
        $('.vi-wl-h-form-popup').css('display', 'block');
        submitFrom.text('Update');
        submitFrom.removeClass('vi-wl-h-form-btn-add');
        submitFrom.addClass('vi-wl-h-form-btn-update');
    });

    $(document).on('click', function (e) {
        if ($(e.target).closest('.vi-wl-h-default,.vi-wl-h-warning, .vi-wl-h-form-popup').length === 0) {
            $('.vi-wl-h-form-popup').hide();
        }
    });

    $('.vi-wl-h-single-top-bar-clone').on('click', function () {
        const this2 = $(this);
        this2.addClass('loading');
        const wishlistId = this2.val();
        $.ajax({
            url: shortcodeAjaxObj.ajaxUrl,
            type: 'POST',
            data: {
                action: 'clone_publish_wishlist',
                wishlistId: wishlistId,
            },
            success(response) {
                if (response === 'guest') {
                    $('.vi-wl-h-single-top-bar').append(`<div style="color:#991d1d;" class="vi-wishlist-login-notices">You must be login to clone the wishlist</div>`);
                    setTimeout(function () {
                        $('.vi-wishlist-login-notices').remove();
                    }, 3000);
                } else {
                    if (response.all_wishlist.length > 0) {
                        const wishlistName = [];
                        const wishlistItems = [];
                        const wishlist = response.all_wishlist;
                        for (const [idx, elem] of wishlist.entries()) {
                            wishlistName.push({
                                'wishlistID': wishlist[idx].wishlist_id,
                                'wishlistName': wishlist[idx].wishlist_title,
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
                                console.log(i)
                                localStorage.setItem('wishlist_active_index', JSON.stringify({'index': i}));
                                break;
                            }
                        }
                    }
                }

                this2.removeClass('loading');
            }
        });
    });

    $('.vi-wl-h-single-top-bar-atc').on('click', function () {
        const this2 = $(this);
        const disabledBtn = $('.vi-wcwl-h-product-table-atc .single_add_to_cart_button.disabled');
        if (disabledBtn.length > 0) {
            let cf;
            cf = confirm(`You have ${disabledBtn.length} products that have not selected variations. Do you want to continue ?`);
            if (!cf) {
                return;
            }
        }
        const button = $('.vi-wcwl-h-table-add-to-cart-form .single_add_to_cart_button:not(.disabled)'),
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
            url: shortcodeAjaxObj.ajaxUrl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'add_all_to_cart_shortcode',
                data: data, group
            },
            beforeSend() {
                this2.addClass('loading');
            },
            success(response) {
                this2.removeClass('loading');
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
            }
        });
    });

    $(document).on('click', '.vi-wl-h-form-btn-update', function (e) {
        e.preventDefault();
        const getWishlistName = JSON.parse(localStorage.getItem('wishlist_name')),
            getWishlistIndex = JSON.parse(localStorage.getItem('wishlist_active_index')),
            wishlistId = getWishlistName[getWishlistIndex.index].wishlistID,
            btn = $('.vi-wcwl-h-submit-form'),
            wishlistName = $('.vi-wl-h-wishlist-name').val(),
            wishlistDescription = $('.vi-wl-h-wishlist-description').val(),
            wishlistStatus = $('.vi-wl-h-custom-radio').find('input:checked').val();

        $.ajax({
            url: shortcodeAjaxObj.ajaxUrl,
            type: 'POST',
            data: {
                action: 'update_wishlist_info',
                wishlistName: wishlistName,
                description: wishlistDescription,
                wishlistId: wishlistId,
                wishlistStatus: wishlistStatus,

            },
            beforeSend() {
                btn.addClass('loading');
            },
            success(response) {
                if (response.wishlistTitle === '') {
                    alert('Wishlist name empty or data not valid');
                } else {
                    getWishlistName[getWishlistIndex.index].wishlistName = response.wishlistTitle;
                    getWishlistName[getWishlistIndex.index].description = response.wishlistDescription;
                    localStorage.setItem('wishlist_name', JSON.stringify(getWishlistName));
                    $('.vi-wl-h-select-option').find('option:selected').html(response.wishlistTitle);
                }
                btn.removeClass('loading');
                $('.vi-wl-h-form-popup').hide();
            }
        });

    })
    $(document).on('click', '.vi-wl-single-bottom-bar-copy', function () {
        const url = $(this).data('share_url');
        const textarea = document.createElement("textarea");
        textarea.textContent = url;
        textarea.style.position = "fixed";
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);

        document.querySelector('.vi-wl-h-tooltiptext').innerHTML = `<i class="vi-wl-copy"></i>Copied`;
        setTimeout(() => {
            document.querySelector('.vi-wl-h-tooltiptext').innerHTML = `<i class="vi-wl-copy"></i>`
        }, 500);
    });
    $('.vi-wl-h-single-top-bar-vote').on('click', function () {
        const this2 = $(this);
        const wishlist_id = $(this).val();
        $.ajax({
            url: shortcodeAjaxObj.ajaxUrl,
            type: 'POST',
            dataType: 'JSOn',
            data: {
                action: 'process_vote_wishlist',
                wishlist_id: wishlist_id,
            },
            beforeSend() {
                this2.addClass('loading');
            },
            success(reponse) {
                if (reponse.user_status === 'guest') {
                    $('.vi-wl-h-single-top-bar').append(`<div style="color:#991d1d;" class="vi-wishlist-login-notices">You must be login to vote the wishlist</div>`);
                    setTimeout(function () {
                        $('.vi-wishlist-login-notices').remove();
                    }, 3000);
                } else {
                    this2.html(`<i class="vi-wl-like-1"></i> ${reponse.total_voted}`);
                }
                this2.removeClass('loading');
            }
        });
    });

    $(document.body).on('click', '.vi-wcwl-fake-btn', function () {
        const parentEl = $(this).closest('.vi-wcwl-h-table-title-price');
        const this2 = $(this);
        const productId = $(this).val();
        $.ajax({
            url: shortcodeAjaxObj.ajaxUrl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'show_add_to_cart_form',
                productId: productId
            }, beforeSend() {
                this2.addClass('loading');
            },
            success(response) {
                this2.removeClass('loading');
                parentEl.find('.vi-wcwl-h-table-show-add-to-cart-form').html(response.add_to_cart_form);
                addToCartIcon();
                if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                    $('.variations_form').each(function () {
                        $(this).wc_variation_form();
                    });
                }
            }
        })
    });

});

