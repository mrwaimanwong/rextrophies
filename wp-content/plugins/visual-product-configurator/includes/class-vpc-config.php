<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-mb-confog
 *
 * @author HL
 */
class VPC_Config {

    public $id;
    public $settings;

    public function __construct($config_id) {
        if ($config_id)
        {
            $this->id = $config_id;
            $this->settings=  get_post_meta($config_id, "vpc-config", true);
//            $this->args=  $this->get_args($raw_args);
        }
    }

    /**
     * Register the config custom post type
     */
    public function register_cpt_config() {

        $labels = array(
            'name' => _x('Configuration', 'vpc'),
            'singular_name' => _x('Configuration', 'vpc'),
            'add_new' => _x('New configuration', 'vpc'),
            'add_new_item' => _x('New configuration', 'vpc'),
            'edit_item' => _x('Edit configuration', 'vpc'),
            'new_item' => _x('New configuration', 'vpc'),
            'view_item' => _x('View configuration', 'vpc'),
            //        'search_items' => _x('Search a group', 'vpc'),
            'not_found' => _x('No configuration found', 'vpc'),
            'not_found_in_trash' => _x('No configuration in the trash', 'vpc'),
            'menu_name' => _x('Product Builder', 'vpc'),
            'all_items' => _x('Configurations', 'vpc'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Configurations',
            'supports' => array('title'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true,
            'menu_icon' => VPC_URL . 'admin/images/vpc-dashicon.svg',
        );
        register_post_type('vpc-config', $args);
    }

    /**
     * Adds the metabox for the config CPT
     */
    public function get_config_metabox() {

        $screens = array('vpc-config');

        foreach ($screens as $screen) {

            add_meta_box(
                    'vpc-config-preview-box', __('Preview', 'vpc'), array($this, 'get_config_preview_page'), $screen
            );

            add_meta_box(
                    'vpc-config-settings-box', __('Configuration settings', 'vpc'), array($this, 'get_config_settings_page'), $screen
            );

            add_meta_box(
                    'vpc-config-conditional-rules-box', __('Conditional rules', 'vpc'), array($this, 'get_config_conditional_rules_page'), $screen
            );
        }
    }

    /**
     * Configuration CPT metabox callback
     */

    public function get_config_settings_page() {
        wp_enqueue_media();
        global $vpc_settings;
        $product_link=get_proper_value($vpc_settings, "product-link", "No");
        ?>
                <div class='block-form'>
                    <!--<div class="postbox" id="vpc-options-container">-->
                        <?php
                        $skin_begin = array(
                        'type' => 'sectionbegin',
                        'id' => 'vpc-skin-container',
//                        'table' => 'options',
                    );
                    $skins_arr=  apply_filters("vpc_configuration_skins", array(
                    "VPC_Default_Skin"=>__("Default", "vpc"),
                    "VPC_Right_Sidebar_Skin"=>__("Right Sidebar skin", "vpc")
                    ));

                    $components_skins=apply_filters("vpc_components_skins", array(
                        "VPC_Default_Skin"=>array(
                            "checkbox"=>__("Checkbox", "vpc"),
                            "radio"=>__("Radio", "vpc"),
                        )
                    ));

                    $components_skins_dropdowns=  $this->get_skin_components_dropdowns_contents($components_skins);

                    $skins = array(
                        'title' => __('Skin', 'vpc'),
                        'name' => 'vpc-config[skin]',
                        'type' => 'select',
                        'options'=> $skins_arr,
                        'default' => '',
                        'class' => 'chosen_select_nostd vpc-config-skin',
                        'desc' => __('Editor look and feel.', 'vpc'),
                    );

                    $components_default_aspect = array(
                        'title' => __('Components default aspect', 'vpc'),
                        'name' => 'vpc-config[components-aspect]',
                        'type' => 'select',
                        'options'=> array(
                            "opened"=>__("Opened", "vpc"),
                            "closed"=>__("Closed", "vpc")
                            ),
                        'default' => '',
                        'class' => 'chosen_select_nostd',
                        'desc' => __('Wether or not all components in the configuration should be opened or closed when the editor is loaded.', 'vpc'),
                    );

                    $components_aspect_on_click = array(
                        'title' => __('Components behavior on click', 'vpc'),
                        'name' => 'vpc-config[components-behavior-on-click]',
                        'type' => 'select',
                        'options'=> array(
                            "leave-others-opened"=>__("Leave others components opened", "vpc"),
                            "close-others"=>__("Close others components", "vpc")
                            ),
                        'default' => '',
                        'class' => 'chosen_select_nostd',
                        'desc' => __('What to do when the user clicks on a component.', 'vpc'),
                    );
                    $config_description = array(
                        'title' => __( 'Configurator description', 'vpc' ),
                        'name' => 'vpc-config[config-desc]',
                        'id' => 'config-desc-editor',
                        'type' => 'texteditor',
                        'desc' => __( 'Description of configurator on the configurator page to help the customer understand what to do', 'vpc' ),
                        'default' => '',
                    );

                    $form_builder_selector=$this->create_form_builder_selector();

                    $skin_end = array('type' => 'sectionend');
                    $skin_settings = apply_filters("vpc_skins_settings", array(
                        $skin_begin,
                        $skins,
                        $components_default_aspect,
                        $components_aspect_on_click,
                        $config_description,
                        $form_builder_selector,
                        $skin_end
                    ));
                    echo o_admin_fields($skin_settings);
                        ?>
                    <script>
                        var vpc_components_skins=<?php echo json_encode($components_skins_dropdowns);?>;
                    </script>
                    <!--</div>-->
            <?php

            $begin = array(
                'type' => 'sectionbegin',
                'id' => 'vpc-config-container'
                    );
            $component_id=array(
                'title' => __('ID', 'vpc'),
                'name' => 'component_id',
                'type' => 'text',
                'class' =>'vpc-component-id',
//                'custom_attributes' => array('disabled' => 'disabled')
            );
            $cname=array(
                'title' => __('Name', 'vpc'),
                'name' => 'cname',
                'type' => 'text',
                'class'=>'vpc-cname',
                'desc' => __('Component name', 'vpc'),
            );

            $cbehaviour=array(
                'title' => __('Behaviour', 'vpc'),
                'name' => 'behaviour',
                'type' => 'select',
                'options' => vpc_get_behaviours(),
                'class' => 'vpc-behaviour',
                'default'=> 'radio'
            );

            $c_image=array(
                'title' => __('Icon', 'vpc'),
                'name' => 'cimage',
                'url_name' => 'cimage_url',
                'type' => 'image',
                'set' => 'Set',
                'remove'=> 'Remove',
                'desc' => __('Component icon', 'vpc'),
//                'lazyload'=>true,
            );

            $o_image=array(
                'title' => __('Image', 'vpc'),
                'name' => 'image',
                'url_name' => 'image_url',
                'type' => 'image',
                'set' => 'Set',
                'remove'=> 'Remove',
                'class' => 'vpc-option-img',
//                'lazyload'=>true,
//                'desc' => __('Component icon', 'vpc'),
            );

            $o_icon=array(
                'title' => __('Icon', 'vpc'),
                'name' => 'icon',
                'url_name' => 'icon_url',
                'type' => 'image',
                'set' => 'Set',
                'remove'=> 'Remove',
//                'desc' => __('Component icon', 'vpc'),
//                'lazyload'=>true,
            );
            $option_product="";
            $option_price=0;
            $option_name=array(
                'title' => __('Name', 'vpc'),
                'name' => 'name',
                'type' => 'text',
                'class'=>'vpc-option-name'
//                'desc' => __('d', 'vpc'),
            );

            $option_group=array(
                'title' => __('Group', 'vpc'),
                'name' => 'group',
                'type' => 'text',
                'class'=>'vpc-option-group'
//                'desc' => __('d', 'vpc'),
            );

            $option_desc=array(
                'title' => __('Description', 'vpc'),
                'name' => 'desc',
                'type' => 'textarea',
//                'desc' => __('d', 'vpc'),
            );

            $option_id=array(
                'title' => __('ID', 'vpc'),
                'name' => 'option_id',
                'type' => 'text',
                'class' => 'vpc-option-id',
//                'custom_attributes' => array('disabled' => 'disabled')
//                'desc' => __('d', 'vpc'),
            );
            $option_product="";
            if(class_exists( 'WooCommerce' )){
                $option_price=array(
                    'title' => __('Price', 'vpc'),
                    'name' => 'price',
                    'type' => 'number',
                    'custom_attributes' => array('step' => 'any')
    //                'desc' => __('d', 'vpc'),
                );

                $args = array(
                    "post_type" => "product",
                    "nopaging" => true,
                );
                if($product_link=="Yes"){
                    $products_arr = get_posts($args);
                    $products=array(""=>"None");
                    foreach ($products_arr as $product)
                    {
                        $product_obj=wc_get_product($product);
                        $product_class=  get_class($product_obj);

                        if($product_class == "WC_Product_Variable")
                        {
                            $variations = $product_obj->get_available_variations();
                            foreach ($variations as $variation) {
                                $attributes_str = implode(", ", $variation["attributes"]);
                                $products[$variation["variation_id"]]=$product->post_title."($attributes_str)";
                            }
                        }
                        else
                            $products[$product->ID]=$product->post_title;
                    }

                $option_product=array(
                    'title' => __('Product / variation ID', 'vpc'),
                    'name' => 'product',
                                    'type' => 'select',
                    'options' => $products,
                    'tip' => 'yes'

                );
               }
            }
            $option_default=array(
                'title' => __('Default', 'vpc'),
                'name' => 'default',
                'type' => 'radio',
                'options' => array(1=>""),
                'class' => 'default-config',
                'tip' => 'yes'
//                'desc' => __('d', 'vpc'),
            );
            $duplicate_option = array(
                'title' => __('Duplicate', 'vpc'),
                'name' => 'duplicate_option',
                'type' => 'button',
                'class' => 'button duplicate_option',
            );
            $options=  apply_filters("vpc_components_options_fields", array(
                'title' => __('Options', 'vpc'),
                'name' => 'options',
                'type' => 'repeatable-fields',
                'class' => 'striped',
                'fields' => array_filter(array($option_id, $option_group, $option_name, $option_desc, $o_icon, $o_image, $option_price, $option_product, $option_default,$duplicate_option)),
                'desc' => __('Component options', 'vpc'),
                'row_class'=>'vpc-option-row',
                'popup'=> true,
                'popup_button'=> __("Manage options", "vpc"),
                'popup_title'=> __("Options", "vpc"),
                'add_btn_label'=> __("Add option", "vpc")
            ));
            $component_index =  array(
                'title' => __('Index', 'vpc'),
                'name' => 'c_index',
                'type' => 'number',
                'class' => 'vpc-component',
                'default'=> '1'
            );
            $duplicate_component = array(
                'title' => __('Duplicate', 'vpc'),
                'name' => 'duplicate_component',
                'type' => 'button',
                'class' => 'button duplicate',
            );
            
            $components=  apply_filters("vpc_components_fields", array(
                'title' => __('Components', 'vpc'),
                'name' => 'vpc-config[components]',
                'type' => 'repeatable-fields',
                'id' => 'vpc-config-components-table',
                'fields' => array($component_id, $cname, $c_image, $cbehaviour, $options,$component_index,$duplicate_component),
                'desc' => __('Component options', 'vpc'),
                'ignore_desc_col'=>true,
                'class' => 'striped',
                'add_btn_label'=> __("Add component", "vpc")
            ));

            $end = array('type' => 'sectionend');
            $settings=  apply_filters("vpc_components_settings", array(
                $begin,
                $components,
                $end
                ));
            echo o_admin_fields($settings);
            global $o_row_templates;
                    ?>
                </div>
                <script>
                    var o_rows_tpl=<?php echo json_encode($o_row_templates);?>;
                </script>
            <?php
    }

    private function create_form_builder_selector(){
        $form_builder_selector=array();
        if(class_exists('Ofb')){
            global $wpdb;
            $ofb = array("None" => "None");
            $resultats = $wpdb->get_results("SELECT ID,post_title From {$wpdb->prefix}posts Where post_type = 'ofb' And post_status = 'publish'", OBJECT_K);
            if($resultats){
              foreach($resultats as $index=>$data){
                $ofb[$data->ID] = $data->post_title;
              }
            }
            $form_builder_selector= array(
              'title' => __('Select id of form build to link', 'ofb'),
              'name' => 'vpc-config[ofb_id]',
              'type' => 'select',
              'options' => $ofb,
              'default' => 'None',
              'class' => 'chosen_ofb',
              'desc' => __('Select id of form build to link to your config', 'ofb'),
            );
        }
        return $form_builder_selector;
    }

    public function get_config_preview_page() {
        ?>
            <div id="vpc-preview"></div>
        <?php
    }

    /**
    * Saves the meta
    * @param type $post_id
    */
   public function save_config($post_id) {
       $meta_key="vpc-config";
       if(isset($_POST[$meta_key]))
       {
           update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
       }
   }

   private function get_skin_components_dropdowns_contents($components_skins)
   {
        $components_skins_dropdowns=array();
        foreach ($components_skins as $skin_class=>$skin_data)
        {
            $html="";
            foreach ($skin_data as $skin_name=>$skin_label)
            {
                $html.="<option value='$skin_name'>$skin_label</option>";
            }
            $components_skins_dropdowns[$skin_class]=$html;
        }

        return $components_skins_dropdowns;
   }

   public function get_all()
   {
       $args=  array(
            "post_type" => "vpc-config",
            "post_status" => "publish",
            "nopaging" => true,
                );
        $lists=  get_posts($args);
        $lists_arr=array();
        foreach ($lists as $list)
        {
            $lists_arr[$list->ID]=$list->post_title;
        }
        return $lists_arr;
   }

   public function get_config_conditional_rules_page() {
        $current_configuration = get_post_meta(get_the_ID(), "vpc-config", true);
        $this->pc_active_part = $current_configuration;
       //Localize conditional rules builder datas
       //Todo: get saved rules
        $wvpc_cl_trigger = array(
            'on_selection' => __('Is selected', 'vpc'),
            'on_deselection' => __('is deselected', 'vpc'),
        );

        $wvpc_cl_scope = array(
            'option' => __('Option', 'vpc'),
            'component' => __('Component', 'vpc'),
            'group_per_component' => __('Group per component', 'vpc'),
            'groups' => __('All groups', 'vpc'),
        );

        $wvpc_cl_action = array(
            'show' => __('Show', 'vpc'),
            'hide' => __('Hide', 'vpc'),
            'select' => __('Select', 'vpc'),
        );

        $wvpc_cl_group_container_tpl = '<div class = "wvpc-rules-group-container">'
                . ' <div>  '
                    . '<table class="wvpc-rules-table widefat"><tbody>'.$this->wvpc_set_group_rule_tpl_head().'{rule-group}</tbody></table> '
                . ' </div> '
                . '{enable-reverse-cb}'
                . '<div class = "remove remove-group"> <a class=" button wvpc-remove-rule">'.__('Remove rule','vpc').'</a></div></div>';

        $wvpc_admin_data = array(
            'wvpc_conditional_rule_container' => $this->wvpc_set_conditional_rules_container_tpl(),
            'wvpc_conditional_rule_tpl' => $this->wvpc_get_conditionnal_rule_tpl(),
            'wvpc_conditional_rule_tpl_first_row' => $this->wvpc_get_conditionnal_rule_tpl(true),
            'wvpc_cl_group_container_tpl' => $wvpc_cl_group_container_tpl,
//            'wvpc_conditional_rules' => $conditional_rules,
            'wvpc_cl_trigger' =>  $wvpc_cl_trigger,
            'wvpc_cl_scope' =>  $wvpc_cl_scope,
            'wvpc_cl_action' =>  $wvpc_cl_action,
            'current_configuration' =>  $current_configuration,
        );
        ?>
            <script>
                var wvpc_cond_rules_data = <?php echo json_encode($wvpc_admin_data);?>;
            </script>

            <div class="wvpc-conditional-rule-wrap">
            <?php
                $wvpc_conditional_rule_container = $this->wvpc_set_conditional_rules_container_tpl();
                echo $wvpc_conditional_rule_container;

            ?>
            </div>
        <?php


   }

   function wvpc_set_conditional_rules_container_tpl(){

        $conditional_rules_is_checked = '';
            if(!empty($this->pc_active_part)){

	        if(isset($this->pc_active_part['conditional_rules']) && isset($this->pc_active_part['conditional_rules']['enable_rules']) && $this->pc_active_part['conditional_rules']['enable_rules'] == 'enabled'){
	            $conditional_rules_is_checked = 'checked="checked"';

	        }
            }

        ob_start()
        ?>
            <div id="grid-container" class="wvpc-conditional-logic-main-container">
                    <div class='block-form'>
                            <div class="wvpc-conditional-logic-form">
                                    <table class="wp-list-table widefat fixed pages">
                                            <tbody>
                                                    <tr>
                                                            <td class='label'>
                                                                    <?php _e('Enable Conditional Logic', 'vpc')?>
                                                                            <div class='desc'>

                                                                            </div>
                                                            </td>
                                                            <td class='grid-src-type'>
                                                                    <input type="checkbox" name="vpc-config[conditional_rules][enable_rules]" class="wvpc_enable_conditional_logic" value="enabled" <?php echo $conditional_rules_is_checked;?>/>
                                                            </td>
                                                    </tr>

                                                    <?php
                                        if (isset($this->pc_active_part['conditional_rules']['enable_rules']) && $this->pc_active_part['conditional_rules']['enable_rules'] == 'enabled' ){
                                            ?>
                                                            <tr class="wvpc-conditional-logic-container wvpc-wvpc-conditional-logic-tr">
                                                                    <?php
                                        }else{
                                            ?>
                                                                            <tr class="wvpc-conditional-logic-container wvpc-wvpc-conditional-logic-tr" style="display: none;">
                                                                                    <?php
                                        }

                                    ?>

                                                                                            <td class='label'>
                                                                                                    <?php _e('Rules', 'vpc')?>
                                                                                                            <div class='desc'>

                                                                                                            </div>
                                                                                            </td>
                                                                                            <td>
                                                                                                    <div class='wvpc-rules-table-container'>
                                                                                                            {rules-editor}

                                                                                                    </div>
                                                                                                    <a class="button wvpc-add-group">Add rule</a>
                                                                                            </td>
                                                                            </tr>
                                            </tbody>
                                    </table>
                            </div>
                    </div>
            </div>

            <?php
        $wvpc_conditional_rule_container = ob_get_contents();
        ob_end_clean();
        return $wvpc_conditional_rule_container;
    }

    //Conditionnal rule editor: single rule template
    function wvpc_get_conditionnal_rule_tpl($is_first_row = false)
    {

        ob_start();
        ?>
                    <tr data-id="rule_{rule-group-index}" class="wvpc-rules-table-tr">


                            <?php
                    if ($is_first_row) {
                       ?>
                                    <td class="wvpc-shared-td">
                                            {wvpc-extraction-group-action}
                                    </td>
                                    <td class="wvpc-shared-td">
                                            {wvpc-extraction-group-scope}
                                    </td>
                                    <td class="wvpc-shared-td">
                                            <select name="vpc-config[conditional_rules][groups][{rule-group-index}][result][apply_on]" id="wvpc-group_{rule-group-index}_rule_{rule-index}_apply_on" class="select wvpc-extraction-group-apply_on">
                                                    {wvpc-extraction-group-apply_on}
                                            </select>
                                    </td>
                                    <?php
                    }
                ?>
                                    <td class="vpc-cl-option-td">
                                                <?php _e('IF', 'vpc');?>
                                    <select name="vpc-config[conditional_rules][groups][{rule-group-index}][rules][{rule-index}][option]" id="wvpc-group_{rule-group-index}_rule_{rule-index}_option" class="select wvpc-extraction-group-option">
                                            {wvpc-extraction-group-option}
                                    </select>
                            </td>
                            <td>
                                    {wvpc-extraction-group-trigger}
                            </td>

                                            <td class="add">
                                                    <a class="wvpc-add-rule" data-group='{rule-group-index}'>
                                                            <?php // _e("add","vpc");?>
                                                    </a>
                                            </td>

                                            <td class="remove">
                                                    <a class="wvpc-remove-rule acf-button-remove"></a>
                                            </td>
                    </tr>
                    <?php
        $rule_tpl=  ob_get_contents();
        ob_end_clean();
        return $rule_tpl;
    }

    public function wvpc_set_group_rule_tpl_head() {
	    ob_start();
	    ?>
				<tr>
					<th>
						<?php _e('Action','vpc')?>
					</th>
					<th>
						<?php _e('Scope','vpc')?>
					</th>
					<th>
						<?php _e('Apply on','vpc')?>
					</th>
					<th>
						<?php _e('Option','vpc')?>
					</th>
					<th>
						<?php _e('Status','vpc')?>
					</th>
					<th></th>
				</tr>
				<?php
	    $group_rule_head = ob_get_contents();
	    ob_end_clean();
	    return $group_rule_head;
	}

   /**
     * Adds new tabs in the product page
     */
    function get_product_tab_label() {
        ?>
        <li class="vpc-config-selection"><a href="#vpc_config_data"><?php _e('Configuration', 'vpc'); ?></a></li>
        <?php
    }

    private function get_product_variations($product_id)
    {
        $product=  wc_get_product($product_id);
        $variations_arr=array();
        $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                $variations_arr[$variation_id]=array();
                $attributes = $variation["attributes"];
                $attributes_str = "";
                foreach ($attributes as $attribute) {
                    array_push($variations_arr[$variation_id], $attribute);
                }
                }
        return $variations_arr;
    }

    function get_product_tab_data() {
        $id=  get_the_ID();
        $product=  wc_get_product($id);

        $args = array(
            "post_type" => "vpc-config",
            "nopaging" => true,
        );
        $configs = get_posts($args);
        $configs_ids=array(""=> "None");
        foreach ($configs as $config)
        {
            $configs_ids[$config->ID]=$config->post_title;
        }

        ?><div id="vpc_config_data" class="panel woocommerce_options_panel"><?php

        if($product->get_type()=="variable")
        {
            $variations_arr=$this->get_product_variations($id);
            foreach ($variations_arr as $variation_id => $attributes) {
                if (!is_array($attributes))
                    continue;
                $attributes_str=  implode(" ", $attributes);

                $this->get_product_tab_row($variation_id, $configs_ids, $attributes_str);
            }
        }
        else
        {
            $this->get_product_tab_row($id, $configs_ids, "Configuration");
        }

        ?>

            <?php

            ?>
        </div>
        <?php
    }

    public function get_product_config_selector() {
        $id=  get_the_ID();

        $args = array(
            "post_type" => "vpc-config",
            "nopaging" => true,
        );
        $configs = get_posts($args);
        $configs_ids=array(""=> "None");
        foreach ($configs as $config)
        {
            $configs_ids[$config->ID]=$config->post_title;
        }
        ?><div id="vpc_config_data" class="show_if_simple"><?php
         $this->get_product_tab_row($id, $configs_ids, "Configuration");
        ?></div><?php
    }

    /*
    * set Variables product configuration form
    */
    public function wvpc_variable_fields( $loop, $variation_data, $variation ) {
            //var_dump($variation);$variation_data
            $id = $variation->ID;
//            $wpb_product_configurator = self::get_product_config($variation_post_id);
//            $id=  get_the_ID();

            $args = array(
                "post_type" => "vpc-config",
                "nopaging" => true,
            );
            $configs = get_posts($args);
            $configs_ids=array(""=> "None");
            foreach ($configs as $config)
            {
                $configs_ids[$config->ID]=$config->post_title;
            }
        ?>
                <tr>
                    <td><?php
//                                woocommerce_wp_radio(array(
//                                    'name' => 'wvpc-variation-meta['.$variation_post_id.'][product_config]',
//                                    'class' => 'wpb_radio_field',
//                                    'id' => 'wpb_product_configurator_'.$variation_post_id,
//                                    'options' => $this->get_configuration_list(),
//                                    'label' => __('Product Configurator List: ', 'wpb'),
//                                    'value' => $wpb_product_configurator,
//                                    'wrapper_class' => 'wpb_radio_field_cont',
//
//                                ))
                        $this->get_product_tab_row($id, $configs_ids, "Configuration");
                    ?></td>
                </tr>
        <?php

    }

    private function get_product_tab_row($pid, $configs_ids, $title)
    {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'vpc-config-data',
        );

        $configurations = array(
            'title' => $title,
            'name' => "vpc-config[$pid][config-id]",
            'type' => 'select',
            'options' => $configs_ids,
        );


        $end = array('type' => 'sectionend');
        $settings = apply_filters("vpc_product_tab_settings", array(
            $begin,
            $configurations,
            $end
        ));

        echo "<div class='vpc-product-config-row'>".o_admin_fields($settings)."</div>";
    }

    public function get_components_by_name()
    {
        $components=array();
        foreach ($this->settings["components"] as $component)
        {
            $options=array();
            foreach ($component["options"] as $option)
            {
                $options[$option["name"]]=$option;
            }
            $components[$component["cname"]]=$options;
        }

        return $components;
    }

    public function save_variation_settings_fields($variation_id) {
       $meta_key="vpc-config";
       if(isset($_POST[$meta_key]))
       {
           $variation= wc_get_product($variation_id);
            if (get_class($variation) == "WC_Product_Variation")
                $product_id = $variation->get_parent_id();
            else
                $product_id = $item_id;

           //Careful this hooks only send the updated data, not the complete form
           $old_metas=  get_post_meta($product_id, $meta_key, true);
           if(empty($old_metas))
               $old_metas=array();
           $new_metas=  array_replace($old_metas, $_POST[$meta_key]);
//           var_dump($new_metas);
           update_post_meta($product_id, $meta_key, $new_metas);
       }
   }

   public function save_product_configuration($root_id) {
       $meta_key="vpc-config";
       if(isset($_POST[$meta_key]))
       {
           $old_metas=  get_post_meta($root_id, $meta_key, true);
           if(empty($old_metas))
               $old_metas=array();
//           var_dump($old_metas);
//            echo "<hr>";
           $new_metas=  array_replace($old_metas, $_POST[$meta_key]);
//           var_dump($new_metas);
           update_post_meta($root_id, $meta_key, $new_metas);
       }
   }
}
