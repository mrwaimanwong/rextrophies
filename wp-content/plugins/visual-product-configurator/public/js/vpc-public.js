var VPC_CONFIG = (function ($, vpc_config) {
    'use strict';
    $(document).ready(function () {
      var new_variation_attributes = "";
        if (typeof vpc != 'undefined') {
            accounting.settings = {
                currency: {
                    symbol: vpc.currency,   // default currency symbol is '$'
                    format: vpc.price_format, // controls output: %s = symbol, %v = value/number (can be object: see below)
                    decimal: vpc.decimal_separator,  // decimal point separator
                    thousand: vpc.thousand_separator,  // thousands separator
                    precision: vpc.decimals   // decimal places
                },
                number: {
                    precision: vpc.decimals,  // default precision on numbers is 0
                    thousand: vpc.thousand_separator,
                    decimal: vpc.decimal_separator
                }
            }
        }

        if (typeof wc_cart_fragments_params != 'undefined') {

            var $fragment_refresh = {
                url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
                type: 'POST',
                success: function (data) {
                    if (data && data.fragments) {

                        $.each(data.fragments, function (key, value) {
                            $(key).replaceWith(value);
                        });

                        $(document.body).trigger('wc_fragments_refreshed');
                    }
                }
            };
        }

        window.vpc_ds_load_tooltips=function ()
        {
          if (!('ontouchstart' in window))
          {
            $("[data-o-title]").tooltip({
              title: function () {
                return $(this).attr('data-o-title');
              },
              container:"body"
            });
          }
        }

        window.vpc_build_preview = function () {
            if (typeof vpc == 'undefined' || (!$("#vpc-add-to-cart").length))
                return;
            $("#vpc-preview").html("");
            if ($("#vpc-add-to-cart").data("price")) {
                if (vpc.decimal_separator == ',')
                    var total_price = parseFloat($("#vpc-add-to-cart").data("price").toString().replace(',', '.'));
                else
                    var total_price = parseFloat($("#vpc-add-to-cart").data("price"));
            }
            var total_option_price = 0;
            var configurator_array = [];
            if (!total_price)
                total_price = 0;
            var selected_items_selector = wp.hooks.applyFilters('vpc.items_selected', vpc.vpc_selected_items_selector);
            var default_preview_builder_process = wp.hooks.applyFilters('vpc.default_preview_builder_process', true);
            if (default_preview_builder_process) {
                $(selected_items_selector).each(function () {
                    var src = $(this).data("img");
                    var option_price = $(this).data("price");
                    if (option_price)
                        total_option_price += parseFloat(option_price);
                    if (src) {
                        $("#vpc-preview").append("<img src='" + src + "' style='z-index:"+$(this).data("index")+"'>");
                        configurator_array.push(src);
                    }
                });
                total_price += total_option_price;
                total_price = wp.hooks.applyFilters('vpc.total_price', total_price);
                $("#vpc-price").html(accounting.formatMoney(total_price));
            }
            else
                wp.hooks.doAction('vpc.default_preview_builder_process', selected_items_selector);
        }

        window.vpc_apply_rules = function (selector) {
            
            if (typeof vpc == 'undefined')
                return;
            if (typeof selector == "undefined")
                selector = vpc.vpc_selected_items_selector;
            var check_selections = false;
            
            $(selector).each(function (i, e) {
                var item_id = $(this).attr("id");
                var rules_triggered_by_item = vpc.wvpc_conditional_rules[item_id];

                //If there is no rule attached to that component we skip this iteration
                if (typeof rules_triggered_by_item == 'undefined')
                    return true;
                $.each(rules_triggered_by_item, function (index, groups_arr) {
                    $.each(groups_arr, function (group_index, rules_groups) {
                        var group_verified = true;
                        $.each(rules_groups.rules, function (rule_index, rule) {
                            if (typeof rules_groups.conditions_relationship == "undefined")
                                rules_groups.conditions_relationship = "and";
                            //Some jquery versions don't return true in these two cases
                            var is_selected = $(".vpc-options input[data-oid='" + rule.option + "']").is(':checked');
                            if (!is_selected)
                                is_selected = $(".vpc-options input[data-oid='" + rule.option + "']").is(':checked');
                            if ($("option#" + rule.option).length) {
                                is_selected = $("option#" + rule.option).is(':selected');
                            }

                            is_selected = wp.hooks.applyFilters('vpc.is_option_selected', is_selected, rule.option);

                            //If it's an OR relationship, we only need one rule to be true
                            if (rules_groups.conditions_relationship == "or" && ((rule.trigger == "on_selection" && is_selected) || (rule.trigger == "on_deselection" && !is_selected))) {
                                group_verified = true;
                                return false;
                            }
                            //If it's an or relation and the condition is not met
                            else if (rules_groups.conditions_relationship == "or") {
                                group_verified = false;
                            }
                            else if (rules_groups.conditions_relationship == "and" && ((rule.trigger == "on_selection" && !is_selected) || (rule.trigger == "on_deselection" && is_selected))) {
                                group_verified = false;
                                return false;
                            }
                        });
                        
                        //
                        //If all rules of the group are true
                        if (group_verified) {
                            //We make sure that the group action has not been applied yet before applying it to avoid infinite loops
                            if (rules_groups.result.action == "hide") {
                                check_selections = true;
                                hide_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "show") {
                                check_selections = true;
                                show_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "select") {
                                check_selections = true;
                                select_options_or_component(rules_groups);
                            }
                        } else if (rules_groups.apply_reverse == "on") {
                            if (rules_groups.result.action == "hide") {
                                check_selections = true;
                                
                            
                                show_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "show") // && $("#" + rules_groups.result.apply_on).not("[style*='display: none;']").length)
                            {
                                check_selections = true;
                                hide_options_or_component(rules_groups);
                            }
                            else if (rules_groups.result.action == "select") {
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
       /* if (typeof vpc != 'undefined') {
            $(vpc.reverse_triggers).each(function (i, e) {
                vpc_apply_rules("#" + e);
            });
        }
*/
        window.vpc_load_options = function () {
            if (typeof vpc !== 'undefined') {
                setTimeout(function(){
                    $(vpc.reverse_triggers).each(function (i, e) {
                        vpc_apply_rules("#" + e);
                    });
                    $(vpc.vpc_selected_items_selector).each(function () {
                        $(this).trigger('change');
                    });
                },2000);
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
            var form_data = {};
            var div = get_vpc_div_capture();
            var product_id = $(this).data("pid");
            var alt_products = [];
            $('#vpc-container input:checked').each(function (i) {
                if ($(this).data("product"))
                    alt_products.push($(this).data("product"));
            });

            var quantity = $("#vpc-qty").val();
            var recap = $('#vpc-container').find(':input').serializeJSON();//.serializeJSON();
            recap = wp.hooks.applyFilters('vpc.filter_recap', recap);
            console.log(recap);
            if (recap.id_ofb)
                delete recap.id_ofb;
            var custom_vars = {};

            if (typeof vpc.query_vars["edit"] !== 'undefined')
                custom_vars["item_key"] = vpc.query_vars["edit"];

            custom_vars = wp.hooks.applyFilters('vpc.custom_vars', custom_vars);
            if (vpc.isOfb === true) {
                var form_is_valid = $('form.formbuilt').validationEngine('validate', { showArrow: false });
                if (form_is_valid) {
                    form_data = $('form.formbuilt').serializeJSON();
                    var process = wp.hooks.applyFilters('vpc.proceed_default_add_to_cart', true);
                }
            } else
                var process = wp.hooks.applyFilters('vpc.proceed_default_add_to_cart', true);

            if (process) {
                $('#vpc-add-to-cart').addClass('disabledClick');
                html2canvas(div).then(function (canvas) {
                    if (canvas.toDataURL('image/png')) {
                        custom_vars['preview_saved'] = canvas.toDataURL('image/png').replace('data:image/png;base64,', '');
                        $.post(
                            ajax_object.ajax_url,
                            {
                                action: "add_vpc_configuration_to_cart",
                                product_id: product_id,
                                alt_products: alt_products,
                                quantity: quantity,
                                recap: recap,
                                custom_vars: custom_vars,
                                form_data: form_data
                            },
                            function (data) {
                                $("#debug").html(data);
                                $.ajax($fragment_refresh);
                                switch (vpc.action_after_add_to_cart) {
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
                                $('#vpc-add-to-cart').removeClass('disabledClick');
                                wp.hooks.doAction('vpc.after_add_to_cart', data);
                            }
                        );
                    }
                });
            }
            else
                wp.hooks.doAction('vpc.proceed_default_add_to_cart', custom_vars);

        });

        function vpc_add_product_attributes_to_btn() {
            var attributes = {};
            var options = $("select[name^='attribute_']");
            var product_id = $("[name='variation_id']").val();
            var new_options = {};
            $.each(options, function () {
                var option_name = $(this).attr("name");
                new_options[option_name] = $(this).find("option:selected").val();
            });
            attributes[product_id] = new_options;
            return attributes;
        }

        $(".single_variation_wrap").on("show_variation", function (event, variation) {
            // Fired when the user selects all the required dropdowns / attributes
            // and a final variation is selected / shown
            var variation_id = $("input[name='variation_id']").val();
            if (variation_id) {
                new_variation_attributes = vpc_add_product_attributes_to_btn();
                $("select[name^='attribute_']").on('change', function () {
                    new_variation_attributes = vpc_add_product_attributes_to_btn();
                });
                $(".vpc-configure-button").hide();
                $(".vpc-configure-button[data-id='" + variation_id + "']").show();
            }
        });

        function get_vpc_div_capture() {
            var div;
            if (vpc.views) {
                var target = $(".vpc-preview:not(.bx-clone)");
                target.each(function () {
                    var target_id = $(this).attr('id');
                    div = $("#" + target_id + ":not(.bx-clone)")[0];
                    return false;
                });
            }
            else {
                if ($(".vpc-global-preview").length > 0)
                    div = $(".vpc-global-preview")[0];
                else
                    div = $("#vpc-preview")[0];
            }
            return div;
        }


        function hide_options_or_component(rules_groups) {
            //Check the scope and apply the rule if it is required
            if (rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").not("[style*='display: none;']").length)) {
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").hide();
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find('input:checked').removeAttr('checked').trigger('change');
            } else if (rules_groups.result.scope == "option" && $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").not("[style*='display: none;']").length) {
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").hide();
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "'] input:checked").removeAttr('checked').trigger('change');
                //We automatically select the next element available
                if (!$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length && vpc.select_first_elt == "Yes")
                    $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input").first().prop("checked", true).trigger("change");
            } else if (rules_groups.result.scope == "groups" && $(".vpc-options div.vpc-group div.vpc-group-name").not("[style*='display: none;']").length) {
                $.each($(".vpc-options div.vpc-group div.vpc-group-name"), function () {
                    if ($(this).html() == rules_groups.result.apply_on)
                        $(this).parent().hide();
                });
            } else if (rules_groups.result.scope == "group_per_component") {
                var split_apply_value = rules_groups.result.apply_on.split('>');
                var component = split_apply_value[0];
                var group = split_apply_value[1];
                $.each($('#' + component + ' .vpc-options .vpc-group .vpc-group-name'), function () {
                    if ($(this).html() == group && $(this).not("[style*='display: none;']").length){
                        $(this).parent().hide();
                        if ($(this).parent().find("input:checked").length) 
                            $(this).parent().find("input").removeAttr('checked').trigger('change');
                    }
                });
            }

            if (rules_groups.result.scope == "option" && $(".vpc-options option[data-oid='" + rules_groups.result.apply_on + "']").not(":disabled").length) {
                $(".vpc-options option[data-oid='" + rules_groups.result.apply_on + "']").prop('disabled', true).trigger('change');
                var next_val = $(".vpc-options option[data-oid='" + rules_groups.result.apply_on + "']").siblings("option").not(":disabled").first().val();
                $(".vpc-options option[data-oid='" + rules_groups.result.apply_on + "']").parent("select").val(next_val).trigger("change");
                //                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "'] input:checked").removeAttr('checked').trigger('change');
            }
            else if (rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find("select").length)) {
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find("select").prop('disabled', true);
            }
            //            else if (rules_groups.result.scope == "group" && $(".vpc-options div.vpc-group").find("select").length){
            //                $.each($(".vpc-options div.vpc-group div.vpc-group-name"), function(){
            //                    if($(this).html() == rules_groups.result.apply_on)
            //                        $(this).parent().find("select").prop('disabled', true);
            //                });
            //            }

            wp.hooks.doAction('vpc.hide_options_or_component', rules_groups);
        }

        function show_options_or_component(rules_groups) {
            //Check the scope and apply the rule if it is required
            if (rules_groups.result.scope == "component" && $("#vpc-container div.vpc-component[data-component_id='" + rules_groups.result.apply_on + "'][style*='display: none;']").length) {
            //if ((rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "][style*='display: none;']").length))
              //  || !(rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").is(":visible")))) {

                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").show();
                if ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input[data-default]").length)
                    $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input[data-default]").click();
                else if ((!$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length) && vpc.select_first_elt == "Yes")
                    $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find(".vpc-options input").first().click();
            } else if (rules_groups.result.scope == "option" && $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "'][style*='display: none;']").length) {
                $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").show();

                //                console.log("showing "+rules_groups.result.apply_on);
                //                console.log($(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parent(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length)
                //If there is no element checked, we automatically slect the next element available
                if (!$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length) {
                    //                    console.log("Checking "+$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").length);
                    if (!$(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input:checked").length && vpc.select_first_elt == "Yes")
                        $(".vpc-options div[data-oid='" + rules_groups.result.apply_on + "']").parents(".vpc-options").find(".vpc-single-option-wrap").not("[style*='display: none;']").find("input").first().prop("checked", true).trigger("change");
                }
            } else if (rules_groups.result.scope == "groups" && $(".vpc-options div.vpc-group[style*='display: none;']").length) {
                $.each($(".vpc-options div.vpc-group div.vpc-group-name"), function () {
                    if ($(this).html() == rules_groups.result.apply_on) {
                        $(this).parent().show();
                        $(this).parents(".vpc-options").find("input").first().click();
                    }
                });
            } else if (rules_groups.result.scope == "group_per_component") {
                var split_apply_value = rules_groups.result.apply_on.split('>');
                var component = split_apply_value[0];
                var group = split_apply_value[1];
                $.each($('#' + component + ' .vpc-options .vpc-group .vpc-group-name'), function () {
                    if ($(this).html() == group) {
                        $(this).parent().show();
                        if (!$(this).parents(".vpc-options").find("input:checked").length && vpc.select_first_elt == "Yes"){
                            $(this).parent().find("input").first().click()
                        }
                        //$(this).parents(".vpc-options").find("input").first().click();
                    }
                });
            }
            if (rules_groups.result.scope == "option" && $(".vpc-options option[data-oid='" + rules_groups.result.apply_on + "']:disabled").length) {
                $(".vpc-options option[data-oid='" + rules_groups.result.apply_on + "']").prop('disabled', false);
            }
            else if (rules_groups.result.scope == "component" && ($("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find("select").length)) {
                $("#vpc-container div.vpc-component[data-component_id=" + rules_groups.result.apply_on + "]").find("select").prop('disabled', false);
            }
            //            else if (rules_groups.result.scope == "group" && $(".vpc-options div.vpc-group")){
            //                $.each($(".vpc-options div.vpc-group div.vpc-group-name"), function(){
            //                    if($(this).html() == rules_groups.result.apply_on)
            //                        $(this).parent().find("select").prop('disabled', false);
            //                });
            //            }
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
        if ($("#vpc-ajax-container").length) {
            //            console.log("editor loading");
            $.post(
                ajax_object.ajax_url,
                {
                    action: "get_vpc_editor",
                    vpc: vpc,
                },
                function (data) {
                    //console.log(data);
                    
                    vpc_load_options();
                    removeEmptyImage();
                    wp.hooks.doAction('vpc.ajax_loading_complete');
                    $("#vpc-ajax-container").append(data);
                    setTimeout(function(){
                        vpc_ds_load_tooltips();
                       $('#vpc-ajax-loader-container').hide(); 
                    },2000);
                    
                });
        }

        $(document).on("click", ".cart .vpc-configure-button", function (e) {
            e.preventDefault();
            var product_id = $("[name='variation_id']").val();
            if (!product_id)
                product_id = $(this).parents().find('[name="add-to-cart"]').val();
            var qty = $(this).parents().find("input[name='quantity']").val();
            if (!qty)
                qty = 1;
            var process = wp.hooks.applyFilters('vpc.proceed_default_build_your_own', true);
            if (process) {
                $.post(
                    ajax_object.ajax_url,
                    {
                        action: "get_vpc_product_qty",
                        prod_id: product_id,
                        qty: qty,
                        new_variation_attributes: new_variation_attributes
                    },
                    function (data) {
                        // e.parents().find('.vpc-configure-button').attr('href',data);
                        window.location = data;
                    });
            }
            else {
                wp.hooks.doAction('vpc.proceed_default_build_your_own', product_id, qty);
            }
        });

        var global_previously_selected = "";
        $(document).on("change", ".vpc-options select", function (e) {

            vpc_build_preview();
            vpc_apply_rules();
            var img = $(this).find("option:selected").data('img');
            var val = $(this).val();
            var id = $(this).attr("id");
            $(this).parents('.vpc-component').find('.vpc-selected-icon img').attr('src', img);
            $(this).parents('.vpc-component').find('.vpc-selected').html(val);
            //Reverse rules management
            if (global_previously_selected && id != global_previously_selected) {
                //                console.log(global_previously_selected);
                vpc_apply_rules("#" + global_previously_selected);
            }
        }).on('click', ".vpc-options select", function (e) {
            global_previously_selected = $(this).find("option:selected").attr('id');
        });

        $(document).on("change", ".vpc-options select>option", function (e) {
            $(this).parent().trigger("change");
        });

        $(document).on("change", ".vpc-options input", function (e) {
            $('.vpc-component-header > span.vpc-selected-icon img[src=""]').hide();
            $('.vpc-component-header > span.vpc-selected-icon img:not([src=""])').show();
        });

        $(document).on("change", 'form.formbuilt input', function (e) {
            window.vpc_build_preview();
        });

        $(document).on("change", 'form.formbuilt textarea', function (e) {
            window.vpc_build_preview();
        });

        $(document).on("change", 'form.formbuilt select', function (e) {
            window.vpc_build_preview();
        });

        wp.hooks.addFilter('vpc.total_price', update_total_price);

        function update_total_price(price) {

            var form_data = $('form.formbuilt').serializeJSON();
            var form_price = get_form_total('form.formbuilt', form_data);
            price += form_price;
            return price;
        }

        function get_form_total(form_id, fields) {
            var total_price = 0;
            $(form_id).find('[name]').each(function (index, value) {
                var that = $(this),
                    name = that.attr('name'),
                    type = that.prop('type');
                if (type == 'select-one') {
                    $(that).find('[value]').each(function (index, value) {
                        var option = $(this);
                        var price = option.attr('data-price');
                        var value = option.attr('value');
                        for (var i in fields) {
                            if (name == i && value == fields[i]) {
                                if (undefined !== price && '' !== price) {

                                    total_price += parseFloat(price);
                                }
                            }
                        }
                    });
                } else if (type == 'radio' || type == 'checkbox') {
                    var price = that.attr('data-price');
                    for (var i in fields) {
                        if (name == i) {
                            if (typeof (fields[i]) == 'object') {
                                var options = fields[i];
                                for (var j in options) {
                                    if (value.value == options[j]) {
                                        if (undefined !== price && '' !== price) {
                                            total_price += parseFloat(price);
                                            // console.log(total_price);
                                        }
                                    }
                                }
                            } else {
                                if (value.value == fields[i]) {
                                    if (undefined !== price && '' !== price) {
                                        total_price += parseFloat(price);
                                        // console.log(total_price);
                                    }
                                }
                            }
                        }
                    }
                }
                else if (type == 'file') {
                    var price = that.attr('data-price');
                    var file = that.prop('files');
                    var files = get_files_in_ofb();
                    if (file[0]) {
                        for (var i in files) {
                            if (name == i) {
                                if (undefined !== price && '' !== price) {
                                    total_price += parseFloat(price);
                                    // console.log(total_price);
                                }
                            }
                        }
                    }
                }
                else {
                    var price = that.attr('data-price');
                    var value = that.val();
                    if (value.length > 0) {
                        for (var i in fields) {
                            if (name == i) {
                                if (undefined !== price && '' !== price) {
                                    total_price += parseFloat(price);
                                    // console.log(total_price);
                                }
                            }
                        }
                    }
                }

            });
            return total_price;
        }

        function set_form_builder_preload_data(fields) {
            $('form.formbuilt').find('[name]').each(function (index, value) {
                var that = $(this),
                    name = that.attr('name'),
                    type = that.prop('type');
                // console.log(name);

                if (type == 'file') {
                    for (var i in fields) {
                        if (name == i) {
                            // that.prop('value', fields[i]);
                            // var file = that.prop('file');
                            // file[0]['name'] = fields[i]
                        }
                    }
                }
            });
        }

        function isEmpty(obj) {
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop))
                    return false;
            }
            return JSON.stringify(obj) === JSON.stringify({});
        }

        window.get_files_in_ofb = function () {
            var files = [];
            $('form.formbuilt').find('[name]').each(function (index, value) {
                var that = $(this),
                    name = that.attr('name'),
                    value = that.attr('value'),
                    type = that.prop('type');
                if (type == 'file') {
                    if (value != null)
                        files[name] = value;
                }
            });
            return files;
        }
        removeEmptyImage();
         function removeEmptyImage(){
            $('.vpc-selected-icon').each(function (index, value) {
                if($(this).find('img').attr('src').length==0){
                    $(this).find('img').hide();
                }
            });
            
        }
        
        $(document).on("click", ".reset_variations", function (e) { 
            $('.variations_button .vpc-configure-button.button').hide();
        });
    });
    return vpc_config;
}(jQuery, VPC_CONFIG));
