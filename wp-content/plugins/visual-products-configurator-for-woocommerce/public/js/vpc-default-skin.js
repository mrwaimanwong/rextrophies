var VPC_CONFIG = (function ($, vpc_config) {
    'use strict';

    function vpc_ds_load_tooltips()
    {
        if (!('ontouchstart' in window))
        {
            $("[data-o-title]").tooltip({
                title: function () {
                    return $(this).attr('data-o-title');
                }
            });
        }
    }

    $(document).ready(function () {

        wp.hooks.addAction('vpc.ajax_loading_complete', vpc_ds_load_tooltips);

        vpc_ds_load_tooltips();

        $(document).on("change", ".vpc-options input", function (e) {
            //e.preventDefault();
//            console.log(e.originalEvent);
            wp.hooks.doAction('vpc.option_change', $(this), e);
            //To avoid unecessary server solicitation, we won't trigger the change if it's not a manual click
//            if(typeof e.originalEvent !== 'undefined')
//            {
            var selector = $(this).attr("id");
            vpc_build_preview();
            vpc_apply_rules("#" + selector);
//            }
            var checked_elements_values = $(this).parents(".vpc-options").find(":input:checked").map(function () {
                return $(this).val();
            }).get().join(' ');
            ;
            var checked_elements_img = $(this).parents(".vpc-options").find(":input:checked").map(function () {
                if ($(this).data('img') || $(this).data('icon'))
                    return "<img src='" + $(this).data('icon') + "'>";
                else
                    return "";
            }).get().join('');
            ;
//            console.log(checked);
            $(this).parents('.vpc-component').find('.vpc-selected-icon').html(checked_elements_img);
            $(this).parents('.vpc-component').find('.vpc-selected').html(checked_elements_values);
        });

        //Spot the checked element right before the checked item changes. That way we can trigger the reverse rules
        //$("label").on("mousedown", function() {
        $(document).on("mousedown", "label.custom", function (e) {
            //We trigger the change even for previously checked items
            var element = $(this);
//            var about_to_change_id=element.parent().parent().find("input:checked").attr("id");
            var about_to_change_id = element.parents('.vpc-options').find("input:checked").attr("id");
            setTimeout(
                    function () {
                        $("#" + about_to_change_id).trigger("change");
                    }, 200);
        });


        $(document).on("click", ".vpc-component-header", function (e) {
            $(this).parents('.vpc-component').find('.vpc-options').slideToggle('fast');
        });
        if (vpc.wvpc_conditional_rules.length == 0)
            vpc_build_preview();
//        else
            vpc_load_options();
    });

    return vpc_config;
}(jQuery, VPC_CONFIG));