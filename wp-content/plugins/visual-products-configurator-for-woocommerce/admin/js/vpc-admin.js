(function ($) {
    'use strict';
    $(document).ready(function () {
        $("[data-tooltip-title]").tooltip();

//        $("img.lazy").lazyload({
//            threshold: 200,
//            effect: "fadeIn"
//        });
        setTimeout(function ()
        {
            if ($('body.post-type-vpc-config').length > 0)
            {
                reindex_components_table();

            }
            build_preview();
            wvpc_load_conditionnal_rule_panel();

            $("#vpc-config-components-table > tbody").sortable({
                update: function (event, ui) {
                    reindex_components_table();
                }
            });

            //Fix the selected value for a rule while loading for edition
            $(".wvpc-rules-table td.value select[data-selected]").each(function () {
                var selected = $(this).data("selected");
                $(this).val(selected);
            });

            add_sortable_options_callback();
            set_modals_position();
            update_components_id();
            update_options_id();

        }, 3000);

        $(document).on("change", ".default-config", function (e) {
//            $(this).closest(".repeatable-fields-table").find("input[type=radio]").not($(this)).attr('checked', false);
            $(this).parents(".omodal-body").find("input[type=radio]").not($(this)).attr('checked', false);
            build_preview();
        });

        function build_preview()
        {
            $("#vpc-preview").html("");
            $(".default-config:checked").each(function ()
            {
                var src = $(this).closest(".vpc-option-row").find(".vpc-option-img img").attr("data-original");
                if (!src)
                    src = $(this).closest(".vpc-option-row").find(".vpc-option-img img").attr("src");
                if (src)
                    $("#vpc-preview").append("<img src='" + src + "'>");
            });
        }

        //Activation of conditionnal logic on item
        $(document).on('change', ".wvpc_enable_conditional_logic", function () {
            if ($(this).is(':checked')) {
                $(this).parents('.wvpc-conditional-logic-main-container').find(".wvpc-wvpc-conditional-logic-tr").show();
            } else {
                $(this).parents('.wvpc-conditional-logic-main-container').find(".wvpc-wvpc-conditional-logic-tr").hide();
            }
        });

        $(document).on("change", ".wvpc-extraction-group-scope", function (e) {
            var selected_scope = $(this).val();
            var apply_on_select = $(this).parent().parent().find('.wvpc-extraction-group-apply_on')
            //var componement_data_for_html_select = wvpc_get_componement_data_for_html_select(global_part);

            if (selected_scope == 'option') {
                var componement_option_html_select = wvpc_set_select_options(wvpc_cond_rules_data.current_configuration.components, apply_on_select.data('selected_option'));
                apply_on_select.html(componement_option_html_select);
            } else if (selected_scope == 'component') {
                var componement_html_select = wvpc_set_select_componement(wvpc_cond_rules_data.current_configuration.components, apply_on_select.data('selected_option'));
                apply_on_select.html(componement_html_select);
            }
        });

        //set all options selector to delete ?
        function wvpc_set_select_options(componement_data_for_html_select, selected_option) {
            var html_select = '';
            //        console.log(componement_data_for_html_select);
            if (componement_data_for_html_select != undefined) {
//                   console.log(componement_data_for_html_select);
                $.each(componement_data_for_html_select, function (componement_index, componement_data) {
                    if (componement_data.cname !== undefined && componement_data.options !== undefined) {
                        html_select += '<optgroup label="' + componement_data.cname + '">';
                        $.each(componement_data.options, function (options_index, options_data) {
                            var selected = ' ';
                            var option_value = '';
                            if (options_data.hasOwnProperty('option_id') && options_data.option_id != "") {
                                option_value = options_data.option_id;
                            } else if (options_data.hasOwnProperty('name')) {
                                option_value = 'component_' + sanitize_title(componement_data.cname.replace(/ /g, '')) + '_group_' + sanitize_title(options_data.group.replace(/ /g, '')) + '_option_' + sanitize_title(options_data.name.replace(/ /g, ''));
                            }
                            //console.log(option_value)
                            if (selected_option != undefined && selected_option == option_value) {
                                selected = 'selected="selected"';
                            }
                            html_select += '<option value="' + option_value + '"  ' + selected + '>' + componement_data.cname + " > " + options_data.name + '</option>';
                        });
                        html_select += '</optgroup>';
                    }
                });
            }
//                console.log(html_select);
            return html_select;
        }

        function sanitize_title(value) {
            var rExps = [
                {re: /[\xC0-\xC6]/g, ch: 'A'},
                {re: /[\xE0-\xE6]/g, ch: 'a'},
                {re: /[\xC8-\xCB]/g, ch: 'E'},
                {re: /[\xE8-\xEB]/g, ch: 'e'},
                {re: /[\xCC-\xCF]/g, ch: 'I'},
                {re: /[\xEC-\xEF]/g, ch: 'i'},
                {re: /[\xD2-\xD6]/g, ch: 'O'},
                {re: /[\xF2-\xF6]/g, ch: 'o'},
                {re: /[\xD9-\xDC]/g, ch: 'U'},
                {re: /[\xF9-\xFC]/g, ch: 'u'},
                {re: /[\xC7-\xE7]/g, ch: 'c'},
                {re: /[\xD1]/g, ch: 'N'},
                {re: /[\xF1]/g, ch: 'n'}];

            // converti les caractères accentués en leurs équivalent alpha
            for (var i = 0, len = rExps.length; i < len; i++)
                value = value.replace(rExps[i].re, rExps[i].ch);

            // 1) met en bas de casse
            // 2) remplace les espace par des tirets
            // 3) enleve tout les caratères non alphanumeriques
            // 4) enlève les doubles tirets
            // 6) enlève les tirets en début de chaine
            // 6) enlève les tirets en fin de chaine
            return value.toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '')
                    .replace(/\-{2,}/g, '-')
                    .replace(/^\-/g, '')
                    .replace(/\-$/, '');
        }

        //set component selector
        function wvpc_set_select_componement(componement_data_for_html_select, selected_option) {
            var html_select = '';
            if (componement_data_for_html_select != undefined) {
                $.each(componement_data_for_html_select, function (componement_index, componement_data) {
                    if (componement_data.cname !== undefined && componement_data.options !== undefined) {
                        //console.log(selected_option)
                        //console.log(componement_data)
                        var selected = ' ';
                        if (componement_data.hasOwnProperty('component_id') && componement_data.component_id != "") {
                            var option_value = componement_data.component_id;
                        } else {
                            var option_value = 'component_' + sanitize_title(componement_data.cname.replace(/ /g, ''));
                        }

                        if (selected_option != undefined && selected_option == option_value) {
                            //html_select += '<option value="'+'layer_wrap_'+componement_index+'" selected="selected" >'+componement_data.group_label +'</option>';
                            selected = 'selected="selected"';
//                            }else{
//                                html_select += '<option value="'+'layer_wrap_'+componement_index+'">'+componement_data.group_label +'</option>';                            
                        }
                        html_select += '<option value="' + option_value + '" ' + selected + '>' + componement_data.cname + '</option>';
                    }
                });
            }
            return html_select;
        }

        function wvpc_load_conditionnal_rule_panel() {
//                console.log(wvpc_cond_rules_data)
            if ($('.wvpc-conditional-rule-wrap').length > 0) {
                var conditional_rules = wvpc_cond_rules_data.current_configuration.conditional_rules;
//                    console.log(conditional_rules);
                //if(conditional_rules && conditional_rules['enable_rules']=='enabled'){
                if (conditional_rules && conditional_rules.groups != undefined) {
                    var html_rules = '';
                    $.each(conditional_rules.groups, function (group_index, group_object) {
                        //                    group_index = group_index.replace('group_','');
                        var raw_tpl = wvpc_cond_rules_data.wvpc_conditional_rule_tpl;
                        var html_group_rules = '';
                        //console.log(group_object);
                        if (typeof group_object.rules == "undefined")
                            return true;
                        $.each(group_object.rules, function (rule_index, rule) {
                            //                            rule_index = rule_index.replace('rule_','');
                            var tpl2 = wvpc_cond_rules_data.wvpc_conditional_rule_tpl.replace(/{rule-index}/g, rule_index);

                            tpl2 = wvpc_set_new_single_rule(rule_index, group_index, rule, group_object.result);
                            html_group_rules += tpl2;
                        });
                        var html_rule = wvpc_cond_rules_data.wvpc_cl_group_container_tpl
                                .replace(/{rule-group}/g, html_group_rules)
                                .replace(/{enable-reverse-cb}/g, get_enable_reverse_cb(group_object, group_index));

                        html_rules += set_rules_index(html_rule, group_index);
                    })
                    var wvpc_rules_table = $(".wvpc-rules-table-container").html();
                    wvpc_rules_table = wvpc_rules_table.replace(/{rules-editor}/g, html_rules);
                    $(".wvpc-rules-table-container").html(wvpc_rules_table);

                } else {
                    var rule_tpl = wvpc_set_new_single_rule(0, 0);
                    rule_tpl = wvpc_cond_rules_data.wvpc_cl_group_container_tpl
                            .replace(/{rule-group}/g, rule_tpl)
                            .replace(/{enable-reverse-cb}/g, get_enable_reverse_cb('', 0));
                    ;
                    var wvpc_rules_table = $(".wvpc-rules-table-container").html();
                    wvpc_rules_table = wvpc_rules_table.replace(/{rules-editor}/g, rule_tpl);
                    $(".wvpc-rules-table-container").html(wvpc_rules_table);
                }
                wvpc_update_rowspan();
                wvpc_reload_conditionnal_rule_panel();
            }
            ;
        }

        function set_rules_index(html, group_index, rule_index) {
            if (html) {
                if (group_index != undefined) {
                    html = html.replace(/{rule-group-index}/g, group_index);
                }
                if (rule_index != undefined) {
                    html = html.replace(/{rule-index}/g, rule_index);
                }
            }

            return html;
        }

        function wvpc_reload_conditionnal_rule_panel() {
//            console.log('wvpc_reload_conditionnal_rule_panel ');
//            console.log( $(':input[name*="vpc-config"]').serializeJSON());
            var current_configuration = $(':input[name*="vpc-config["]').serializeJSON();
            wvpc_cond_rules_data.current_configuration = current_configuration['vpc-config'];

//            console.log(wvpc_cond_rules_data.current_configuration);
            //var componement_data_for_html_select = wvpc_get_componement_data_for_html_select(global_part);
            $('.wvpc-extraction-group-option').each(function () {
                var componement_option_html_select = wvpc_set_select_options(wvpc_cond_rules_data.current_configuration.components, $(this).val());
                $(this).html(componement_option_html_select);
            });

            $('.wvpc-extraction-group-apply_on').each(function () {
                var scope = $(this).parents('.wvpc-rules-table-tr').find('.wvpc-extraction-group-scope').val();
                if (scope == 'option') {
                    var componement_option_html_select = wvpc_set_select_options(wvpc_cond_rules_data.current_configuration.components, $(this).val());
                    $(this).html(componement_option_html_select);
                } else if (scope == 'component') {
                    var componement_apply_on_html_select = wvpc_set_select_componement(wvpc_cond_rules_data.current_configuration.components, $(this).val());
                    $(this).html(componement_apply_on_html_select);
                }
                ;

            });
            wvpc_update_rowspan();
        }

        function wvpc_set_html_select(select_name, select_id, select_class, opt_list, selected_opt) {
            //console.log(selected_opt);
            var html_select = '<select name="' + select_name + '" id="' + select_id + '" class="' + select_class + '">';
            $.each(opt_list, function (opt_name, opt_label) {
                if (opt_name == selected_opt) {
                    html_select += '<option value="' + opt_name + '"  selected="selected" >' + opt_label + '</option>'
                } else {
                    html_select += '<option value="' + opt_name + '">' + opt_label + '</option>'
                }
            });
            html_select += '</select>';
            return html_select;
        }

        function wvpc_update_rowspan() {
            $.each($('.wvpc-shared-td'), function (index, shared_td) {
                var rowspan = $(shared_td).parents('table.wvpc-rules-table').find('tr').length;
                $(shared_td).attr('rowspan', rowspan);
            });
        }

        //Add single rule to item
        function wvpc_set_new_single_rule(new_rule_index, group_index, rules, group_result) {
            //console.log(new_rule_index)
            //console.log(rules['apply_on']);
            //        console.log(group_result);
            var raw_tpl = "";
            if (new_rule_index == 0) {
                raw_tpl = wvpc_cond_rules_data.wvpc_conditional_rule_tpl_first_row;
            } else {
                //            console.log(wvpc_cond_rules_data.wvpc_conditional_rule_tpl);
                raw_tpl = wvpc_cond_rules_data.wvpc_conditional_rule_tpl;
            }

            var trigger_select = wvpc_set_html_select("vpc-config[conditional_rules][groups][{rule-group-index}][rules][{rule-index}][trigger]", "wvpc-group_{rule-group-index}_rule_{rule-index}_trigger", "select wvpc-extraction-group-trigger", wvpc_cond_rules_data.wvpc_cl_trigger, (rules) ? rules['trigger'] : "")
            //ToDo: updtate the lines bellow to the new struct
            //var global_part = ;
//            var componement_data_for_html_select = wvpc_get_componement_data_for_html_select(wvpc_cond_rules_data.current_configuration);
            var componement_option_html_select = wvpc_set_select_options(wvpc_cond_rules_data.current_configuration.components, (rules) ? rules['option'] : "");
            var componement_scope_html_select = wvpc_set_html_select("vpc-config[conditional_rules][groups][{rule-group-index}][result][scope]", "wvpc-group_{rule-group-index}_rule_{rule-index}_scope", "select wvpc-extraction-group-scope", wvpc_cond_rules_data.wvpc_cl_scope, (group_result) ? group_result['scope'] : "")

            var componement_apply_on_html_select = "";
            if (rules && group_result['scope'] && group_result['scope'] == 'component') {
                componement_apply_on_html_select = wvpc_set_select_componement(wvpc_cond_rules_data.current_configuration.components, (group_result) ? group_result['apply_on'] : "");
            } else {
                componement_apply_on_html_select = wvpc_set_select_options(wvpc_cond_rules_data.current_configuration.components, (group_result) ? group_result['apply_on'] : "");
            }
            var componement_action_html_select = wvpc_set_html_select("vpc-config[conditional_rules][groups][{rule-group-index}][result][action]", "wvpc-group_{rule-group-index}_rule_{rule-index}_action", "select wvpc-extraction-group-action", wvpc_cond_rules_data.wvpc_cl_action, (group_result) ? group_result['action'] : "");
            var tpl2 = raw_tpl.replace(/{wvpc-extraction-group-trigger}/g, trigger_select);
            //        console.log(componement_option_html_select)
            tpl2 = tpl2.replace(/{wvpc-extraction-group-option}/g, componement_option_html_select);
            tpl2 = tpl2.replace(/{wvpc-extraction-group-scope}/g, componement_scope_html_select);
            tpl2 = tpl2.replace(/{wvpc-extraction-group-action}/g, componement_action_html_select);
            tpl2 = tpl2.replace(/{wvpc-extraction-group-apply_on}/g, componement_apply_on_html_select);

            tpl2 = set_rules_index(tpl2, group_index, new_rule_index);
            //tpl2=tpl2.replace(/{rule-group-index}/g,group_index);
            //tpl2=tpl2.replace(/{rule-index}/g,new_rule_index);
            return tpl2;

        }

        function get_enable_reverse_cb(rules, group_index) {
            var is_checked = '';
            var is_or_checked = '';
            var is_and_checked = '';
            //console.log(rules)
            if (rules.apply_reverse == 'on' || rules == '') {
                is_checked = 'checked="checked"';
            }

            if (rules.conditions_relationship == "or")
                is_or_checked = "selected";
            else
                is_and_checked = "selected";

            var enable_reverse_cb = '<div class="enable-reverse"><label for=""><input type="checkbox" name="vpc-config[conditional_rules][groups][{rule-group-index}][apply_reverse]" ' + is_checked + ' />' + string_translations.reverse_cb_label + '</label> </div>';
            var condtions_relation = '<div class="vpc-conditions-relationship"><label for="">' + string_translations.group_conditions_relation + '<select name="vpc-config[conditional_rules][groups][{rule-group-index}][conditions_relationship]"><option value="and" ' + is_and_checked + '>AND</option><option value="or" ' + is_or_checked + '>OR</option></select></label> </div>';

            return set_rules_index(enable_reverse_cb + condtions_relation, group_index);
        }

        $(document).on("click", ".wvpc-add-rule", function (e)
        {
            setTimeout(check_the_max_input_vars, 200);
            var new_rule_index = $(".wvpc-rules-table tr").length;
            var group_index = $(this).data("group");

            var tpl2 = wvpc_set_new_single_rule(new_rule_index, group_index);
            $(this).parents(".wvpc-rules-table").find("tbody").append(tpl2);
            wvpc_update_rowspan();
        });

        //Add group rule to item
        $(document).on("click", ".wvpc-add-group", function (e)
        {
            setTimeout(check_the_max_input_vars, 200);
            var new_rule_index = 0;
            // var group_index = $(".wvpc-rules-table").length;
            var group_index = $(".wvpc-rules-table-container .wvpc-rules-group-container").last().find('.wvpc-rules-table-tr').attr('data-id');
            group_index = parseInt(group_index.replace('rule_', '')) + 1;
            var tpl2 = wvpc_set_new_single_rule(new_rule_index, group_index);
            var enable_reverse_cb = get_enable_reverse_cb('', group_index);
            var html = wvpc_cond_rules_data.wvpc_cl_group_container_tpl.replace(/{rule-group}/g, tpl2);
            html = html.replace(/{enable-reverse-cb}/g, enable_reverse_cb);
            html = html.replace(/{rule-group-index}/g, group_index);
            html = html.replace(/{rule-index}/g, new_rule_index);
            $(".wvpc-rules-table-container").append(html);
            //wvpc_load_conditionnal_rule_panel();
        });

        //Remove rule
        $(document).on("click", ".wvpc-remove-rule", function (e) {
            e.preventDefault();
            setTimeout(check_the_max_input_vars, 200);
            $(this).parent().parent().remove();
        });

        $(".vpc-config-skin").change(function ()
        {
            var selected_skin = $(this).val();
            var component_skins = vpc_components_skins[selected_skin];
            $(".vpc-behaviour").html(component_skins);
        });

        function reindex_components_table()
        {
            $("#vpc-config-components-table > tbody > tr").each(function (i, e)
            {
                //Replace the inputs names
                var prefix = "vpc-config[components][" + i + "]";
                $(this).find(":input[name^='vpc-config[components]']").each(function (i2, e2)
                {
                    var new_name = this.name.replace(/vpc-config\[components\]\[\d+\]/, prefix);
//                    console.log(this.name+" => "+new_name);
                    $(this).attr("name", new_name);
                });
            });
        }

        function reindex_options_table()
        {
            $(".o-modal .omodal-body>.table-fixed-layout> tbody > tr:visible").each(function (i, e)
            {
                //Replace the inputs names
                var replacement = "[options][" + i + "]";
                $(this).find(":input[name*='[options]']").each(function (i2, e2)
                {
                    var new_name = this.name.replace(/\[options\]\[\d+\]/, replacement);
//                    console.log(this.name+" => "+new_name);
                    $(this).attr("name", new_name);
                });
            });
        }

        function add_sortable_options_callback()
        {
            $(".o-modal .table-fixed-layout>tbody").sortable({
                update: function (event, ui) {
                    reindex_options_table();
                }
            });//.disableSelection();
        }

        $("#vpc-config-container>div>table>tbody>tr>td>.add-rf-row").click(function ()
        {
            setTimeout(add_sortable_options_callback, 1000);
        });

        function set_modals_position()
        {
            if (!$("#vpc-config-settings-box").length)
                return;
            var left = $("#vpc-config-settings-box").offset().left;
            var width = $("#vpc-config-settings-box").outerWidth();
            $("body").append("<style>.o-modal .omodal-dialog{width: " + width + "px;margin-left: " + left + "px;}</style");
        }

        function update_components_id() {
            $('.vpc-component-id[value=""]').each(function (index, component_id_field) {
                //console.log($(component_id_field).val());
                //if($(component_id_field).val()==""){
                var component_id = o_uniqid("component-");
                $(component_id_field).val(component_id);
                var old_component_id = 'component_' + sanitize_title($(component_id_field).parent().parent().find('.vpc-cname').val().replace(/ /g, ''));

//                 console.log($('option[value="'+old_component_id+'"]'))
                $('option[value="' + old_component_id + '"]').attr('value', component_id);
                //$(component_id_field).attr('disabled', 'disabled');
                //}
            })
        }

        function update_options_id() {
            $('.vpc-option-id[value=""]').each(function (index, option_id_field) {
                //console.log($(this).val())
                //if($(option_id_field).val() ==""){
                var old_option_id = 'component_' + sanitize_title($(option_id_field).parents('.omodal').parents().parents().find('.vpc-cname').val().replace(/ /g, '')) + '_group_' + sanitize_title($(option_id_field).parents().parent().find('.vpc-option-group').val().replace(/ /g, '')) + '_option_' + sanitize_title($(option_id_field).parent().parent().find('.vpc-option-name').val().replace(/ /g, ''));

                var option_id = o_uniqid("option-");
//                    console.log($('option[value="'+old_option_id+'"]'));
                $('option[value="' + old_option_id + '"]').attr('value', option_id);
                $(option_id_field).val(option_id);
//                }
            });

        }

        $(document).on('change', '.vpc-cname, .vpc-option-group, .vpc-option-name', function () {
            wvpc_reload_conditionnal_rule_panel();
        });
        $(document).on("click", ".add-rf-row", function (e) {
            setTimeout(function () {
                update_components_id();
                update_options_id();
            }, 200);
        });

    });

})(jQuery);
