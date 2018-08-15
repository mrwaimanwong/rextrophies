var VPC_CONFIG = (function ($, vpc_config) {
    'use strict';
    $(document).ready(function () {

        if (typeof vpc != 'undefined')
        {
            accounting.settings = {
                    currency: {
                            symbol : vpc.currency,   // default currency symbol is '$'
                            format: vpc.price_format, // controls output: %s = symbol, %v = value/number (can be object: see below)
                            decimal : vpc.decimal_separator,  // decimal point separator
                            thousand: vpc.thousand_separator,  // thousands separator
                            precision : vpc.decimals   // decimal places
                    },
                    number: {
                            precision : vpc.decimals,  // default precision on numbers is 0
                            thousand: vpc.thousand_separator,
                            decimal : vpc.decimal_separator
                    }
            }
        }

        window.vpc_build_preview = function ()
        {
            if (typeof vpc == 'undefined')
                return;
            $("#vpc-preview").html("");
            if($("#vpc-add-to-cart").data("price")){
                if (vpc.decimal_separator == ',')
                    var total_price = parseFloat($("#vpc-add-to-cart").data("price").toString().replace(',', '.'));
                else
                    var total_price = parseFloat($("#vpc-add-to-cart").data("price"));
            }
            var configurator_array = [];
            if (!total_price)
                total_price = 0;
            var selected_items_selector = wp.hooks.applyFilters('vpc.items_selected', vpc.vpc_selected_items_selector);
            var default_preview_builder_process = wp.hooks.applyFilters('vpc.default_preview_builder_process', true);
            if (default_preview_builder_process) {
                $(selected_items_selector).each(function ()
                {
                    var src = $(this).data("img");
                    var option_price = $(this).data("price");
//                console.log(option_price);
                    if (option_price)
                        total_price += parseFloat(option_price);
                    if (src)
                    {
                        $("#vpc-preview").append("<img src='" + src + "'>");
                        configurator_array.push(src);
                    }
                });
                total_price = wp.hooks.applyFilters('vpc.total_price', total_price);
                $("#vpc-price").html(accounting.formatMoney(total_price));
            }
            else
                wp.hooks.doAction('vpc.default_preview_builder_process', selected_items_selector);


        }

        window.vpc_apply_rules = function (selector)
        {
            if (typeof vpc == 'undefined')
                return;
            if (typeof selector == "undefined")
                selector = vpc.vpc_selected_items_selector;
            var check_selections = false;
            $(selector).each(function (i, e)
            {
                var item_id = $(this).attr("id");

                var rules_triggered_by_item = vpc.wvpc_conditional_rules[item_id];

                //If there is no rule attached to that component we skip this iteration
                if (typeof rules_triggered_by_item == 'undefined')
                    return true;
                $.each(rules_triggered_by_item, function (index, groups_arr)
                {
//                    console.log(groups_arr);
                    $.each(groups_arr, function (group_index, rules_groups)
                    {
                        var group_verified = true;
                        $.each(rules_groups.rules, function (rule_index, rule)
                        {
                            if (typeof rules_groups.conditions_relationship == "undefined")
                                rules_groups.conditions_relationship = "and";
                            //Some jquery versions don't return true in these two cases
                            var is_selected = $(".vpc-options input[data-oid='" + rule.option + "']").attr('checked') == "checked";
                            if (!is_selected)
                                is_selected = $(".vpc-options input[data-oid='" + rule.option + "']").is(':checked');
                            is_selected = wp.hooks.applyFilters('vpc.is_option_selected', is_selected, rule.option);

                            //If it's an OR relationship, we only need one rule to be true
                            if (rules_groups.conditions_relationship == "or" && ((rule.trigger == "on_selection" && is_selected) || (rule.trigger == "on_deselection" && !is_selected)))
                            {
                                group_verified = true;
                                return false;
                            }
                            //If it's an or relation and the condition is not met
                            else if (rules_groups.conditions_relationship == "or")
                            {
                                group_verified = false;
                            }
                            else if (rules_groups.conditions_relationship == "and" && ((rule.trigger == "on_selection" && !is_selected) || (rule.trigger == "on_deselection" && is_selected)))
                            {
                                group_verified = false;
                                return false;
                            }
                        });
//                            
                        //If all rules of the group are true
                        if (group_verified)
                        {
                            //We make sure that the group action has not been applied yet before applying it to avoid infinite loops
                            if (rules_groups.result.action == "hide")
                            {
                                check_selections = true;
                                hide_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "show")
                            {
                                check_selections = true;
                                show_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "select")
                            {
                                check_selections = true;
                                select_options_or_component(rules_groups);
                            }
                        } else if (rules_groups.apply_reverse == "on") {
                            if (rules_groups.result.action == "hide")
                            {
                                check_selections = true;
                                show_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "show" && $("#" + rules_groups.result.apply_on).not("[style*='display: none;']").length)
                            {
                                check_selections = true;
                                hide_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "select")
                            {
                                check_selections = true;
                                unselect_options_or_component(rules_groups);
                            }
                        }


                    });

                });
            });
            if (check_selections)
                vpc_build_preview();
        }

        //We manually trigger the reverse rules to make sure they are activated when the page is loaded
        if (typeof vpc != 'undefined')
        {
            $(vpc.reverse_triggers).each(function (i, e)
            {
                vpc_apply_rules("#" + e);
            });
        }

        window.vpc_load_options = function () {
            if (typeof vpc !== 'undefined')
            {
                console.log(vpc.vpc_selected_items_selector)
                $(vpc.vpc_selected_items_selector).each(function () {
                    $(this).trigger('change');
                });
            }
        }
//
        $(document).on('click', '#vpc-qty-container .plus, #vpc-qty-container .minus', function () {

            // Get values
            var $qty = $("#vpc-qty");
            var currentVal = parseFloat($qty.val());
            var max = parseFloat($qty.attr('max'));
            var min = parseFloat($qty.attr('min'));
            var step = $qty.attr('step');

            // Format values
            if (!currentVal || currentVal === '' || currentVal === 'NaN')
                currentVal = 0;
            if (max === '' || max === 'NaN')
                max = '';
            if (min === '' || min === 'NaN')
                min = 0;
            if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN')
                step = 1;

            // Change the value
            if ($(this).is('.plus')) {

                if (max && (max == currentVal || currentVal > max)) {
                    $qty.val(max);
                } else {
                    $qty.val(currentVal + parseFloat(step));
                }

            } else {

                if (min && (min == currentVal || currentVal < min)) {
                    $qty.val(min);
                } else if (currentVal > 0) {
                    $qty.val(currentVal - parseFloat(step));
                }

            }

            // Trigger change event
//            $qty.trigger('change');
        });

        $(document).on('click', '#vpc-add-to-cart', function () {
            var product_id = $(this).data("pid");
            var alt_products = [];
            $('#vpc-container input:checked').each(function (i)
            {
                if ($(this).data("product"))
                    alt_products.push($(this).data("product"));
            });

//            console.log(alt_products);
            var quantity = $("#vpc-qty").val();
            var recap = $('#vpc-container').find(':input').serializeJSON();//.serializeJSON();
//            console.log(product_id);
//            console.log(alt_products);
//            console.log(quantity);
//            console.log(recap);
            var custom_vars = {};
            custom_vars = wp.hooks.applyFilters('vpc.custom_vars', custom_vars);
//            console.log(custom_vars);
            var process=wp.hooks.applyFilters('vpc_proceed_add_to_cart', true);
           
            if(process){
            $.post(
                    ajax_object.ajax_url,
                    {
                        action: "add_vpc_configuration_to_cart",
                        product_id: product_id,
                        alt_products: alt_products,
                        quantity: quantity,
                        recap: recap,
                        custom_vars: custom_vars
                    },
            function (data) {
                $("#debug").html(data);
                switch (vpc.action_after_add_to_cart)
                {
                    case 'refresh':
                        setTimeout(function () {
                            window.location.reload(true);
                        }, 3000);
                        break;
                    case 'redirect':
                        window.location.href = vpc.cart_url;
                        break;
                    case 'redirect_to_product_page':
                        window.location.href = vpc.current_product_page;
                        break;
                    default:
                        break;
                }
                wp.hooks.doAction('vpc.after_add_to_cart', data);
            });
            }
            else
                wp.hooks.doAction('vpc_proceed_add_to_cart',custom_vars);
        });
//        
        $(".single_variation_wrap").on("show_variation", function (event, variation) {
            // Fired when the user selects all the required dropdowns / attributes
            // and a final variation is selected / shown
            var variation_id = $("input[name='variation_id']").val();
            if (variation_id)
            {
                $(".vpc-configure-button").hide();
                $(".vpc-configure-button[data-id='" + variation_id + "']").show();
            }
        });

        function hide_options_or_component(rules_groups) {
            //Check the scope and apply the rule if it is required
            if (rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").not("[style*='display: none;']").length))
            {
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").hide();
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find('input:checked').removeAttr('checked').trigger('change');
            } else if (rules_groups.result.scope == "option" && $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").not("[style*='display: none;']").length) {
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").hide();
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "'] input:checked").removeAttr('checked').trigger('change');
                //We automatically select the next element available
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input").first().prop("checked", true).trigger("change");
            }
            wp.hooks.doAction('vpc.hide_options_or_component', rules_groups);
        }

        function show_options_or_component(rules_groups) {
            //Check the scope and apply the rule if it is required
            if (rules_groups.result.scope == "component" && $("#vpc-container div.vpc-component[data-component_id='" + rules_groups.result.apply_on + "'][style*='display: none;']").length)
            {
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").show();
                if ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input[data-default]").length)
                    $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input[data-default]").click();
                else
                    $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input").first().click();
            } else if (rules_groups.result.scope == "option" && $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "'][style*='display: none;']").length) {
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").show();

//                console.log("showing "+rules_groups.result.apply_on);
//                console.log($(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parent(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length)
                //If there is no element checked, we automatically slect the next element available
                if (!$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length)
                {
//                    console.log("Checking "+$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").length);
                    $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input").first().prop("checked", true).trigger("change");
                }
            }
            wp.hooks.doAction('vpc.show_options_or_component', rules_groups);
        }
        
        function select_options_or_component(rules_groups) {
            //Check the scope and apply the rule if it is required
//            if (rules_groups.result.scope == "component" && $("#vpc-container div.vpc-component[data-component_id='" + rules_groups.result.apply_on + "'][style*='display: none;']").length)
//            {
//                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").show();
//                if ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input[data-default]").length)
//                    $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input[data-default]").click();
//                else
//                    $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input").first().click();
//            } else 
                if (rules_groups.result.scope == "option" && $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").length) {
                    $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").show();
                    $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']>input").prop('checked', true).trigger('change');

//                console.log("showing "+rules_groups.result.apply_on);
//                console.log($(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parent(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length)
                //If there is no element checked, we automatically slect the next element available
//                if (!$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length)
//                {
//                    $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input").first().prop("checked", true).trigger("change");
//                }
            }
            wp.hooks.doAction('vpc.select_options_or_component', rules_groups);
        }
        
        function unselect_options_or_component(rules_groups) {
            //Check the scope and apply the rule if it is required
//            if (rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").not("[style*='display: none;']").length))
//            {
//                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").hide();
//                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find('input:checked').removeAttr('checked').trigger('change');
//            } else 
                if (rules_groups.result.scope == "option" && $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").not("[style*='display: none;']").length) {
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']>input").prop('checked', false).trigger('change');
            }
            wp.hooks.doAction('vpc.hide_options_or_component', rules_groups);
        }

        //We're in ajax mode
        if ($("#vpc-ajax-container").length)
        {
//            console.log("editor loading");
            $.post(
                    ajax_object.ajax_url,
                    {
                        action: "get_vpc_editor",
                        vpc: vpc,
                    },
                    function (data) {
//                    console.log(data);
                        $("#vpc-ajax-container").removeClass("vpc-loading").html(data);
                        vpc_load_options();
                        wp.hooks.doAction('vpc.ajax_loading_complete');
                    });
        }
        
        $(document).on("click", ".cart .vpc-configure-button", function (e) {
            e.preventDefault();
            var product_id = $("[name='variation_id']").val();
            if (!product_id)
                product_id=$('[name="add-to-cart"]').val();
            var qty=$(this).parent().find("input[name='quantity']").val();
            $.post(
                ajax_object.ajax_url,
                {
                    action: "get_vpc_product_qty",
                    prod_id: product_id,
                    qty:qty
                },
            function (data) {
               // e.parents().find('.vpc-configure-button').attr('href',data);
                window.location=data;
            });
        });
        
    });
    return vpc_config;
}(jQuery, VPC_CONFIG));