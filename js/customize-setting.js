jQuery(document).ready(function ($) {
    'use strict';

    wp.customize.section('wishlist_single_page_option', function (section) {
        section.expanded.bind(function (isExpanded) {
            let url;
            if (isExpanded) {
                url = vi_wcwl_preview_setting.page_url;
                wp.customize.previewer.previewUrl.set(url);
            }
        });
    });

    wp.customize.section('add_to_wishlist_icon', function (section) {
        section.expanded.bind(function (isExpanded) {
            let url;
            if (isExpanded) {
                url = wp.customize.settings.url.home;
                wp.customize.previewer.previewUrl.set(url);
            }
        });
    });

    wp.customize.section('floating_wishlist_icon', function (section) {
        section.expanded.bind(function (isExpanded) {
            let url;
            if (isExpanded) {
                url = wp.customize.settings.url.home;
                wp.customize.previewer.previewUrl.set(url);
            }
        });
    });

    wp.customize.section('wishlist_sidebar_panel', function (section) {
        section.expanded.bind(function (isExpanded) {
            wp.customize.previewer.send('open-sidebar');
            let url;
            if (isExpanded) {
                url = wp.customize.settings.url.home;
                wp.customize.previewer.previewUrl.set(url);
            }
        });
    });

    viwwl_design_init();
});

function viwwl_design_init() {
    jQuery('.vi-wcwl-customize-range').each(function () {
        let range_wrap = jQuery(this),
            range = jQuery(this).find('.vi-wcwl-customize-range1');
        let min = range.attr('min') || 0,
            max = range.attr('max') || 0,
            start = range.data('start');
        range.range({
            min: min,
            max: max,
            start: start,
            input: range_wrap.find('.vi-wcwl-customize-range-value'),
            onChange: function (val) {
                let setting = range_wrap.find('.vi-wcwl-customize-range-value').attr('data-customize-setting-link');
                wp.customize(setting, function (e) {
                    e.set(val);
                });
            }
        });
        range_wrap.next('.vi-wcwl-customize-range-min-max').find('.vi-wcwl-customize-range-min').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            range.range('set value', min);
            let setting = range_wrap.find('.vi-wcwl-customize-range-value').attr('data-customize-setting-link');
            wp.customize(setting, function (e) {
                e.set(min);
            });
        });
        range_wrap.next('.vi-wcwl-customize-range-min-max').find('.vi-wcwl-customize-range-max').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            range.range('set value', max);
            let setting = range_wrap.find('.vi-wcwl-customize-range-value').attr('data-customize-setting-link');
            wp.customize(setting, function (e) {
                e.set(max);
            });
        });
        range_wrap.find('.vi-wcwl-customize-range-value').on('change', function () {
            let setting = jQuery(this).attr('data-customize-setting-link'),
                val = parseInt(jQuery(this).val() || 0);
            if (val > parseInt(max)) {
                val = max
            } else if (val < parseInt(min)) {
                val = min;
            }
            range.range('set value', val);
            wp.customize(setting, function (e) {
                e.set(val);
            });
        });
    });
    jQuery('.vi-wcwl-customize-radio').each(function () {
        jQuery(this).buttonset();
        jQuery(this).find('input:radio').on('change', function () {
            let setting = jQuery(this).attr('data-customize-setting-link'),
                val = parseInt(jQuery(this).val() || 0);
            wp.customize(setting, function (e) {
                e.set(val);
            });
        });
    });

    jQuery('.vi-wcwl-customize-checkbox').each(function () {
        jQuery(this).checkbox();
        jQuery(this).on('change', function () {
            let input = jQuery(this).parent().find('input[type="hidden"]');
            if (jQuery(this).prop('checked')) {
                input.val('1');
            } else {
                input.val('');
            }
            let setting = input.attr('data-customize-setting-link');
            wp.customize(setting).set(input.val());
        });
    });
}






