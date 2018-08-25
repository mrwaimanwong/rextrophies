<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.orionorigin.com
 * @since      1.0.0
 *
 * @package    Vpc
 * @subpackage Vpc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Vpc
 * @subpackage Vpc/public
 * @author     ORION <help@orionorigin.com>
 */
class VPC_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Vpc_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Vpc_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vpc-public.min.css', array(), $this->version, 'all');

        wp_enqueue_style("o-flexgrid", plugin_dir_url(__FILE__) . '../admin/css/flexiblegs.css', array(), $this->version, 'all');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Vpc_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Vpc_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        vpc_enqueue_core_scripts();
    }

    public function register_shortcodes() {
        add_shortcode('wpb_builder', array($this, 'get_vpc_editor_handlers'));
    }

    public function get_vpc_editor_handlers($atts) {
        $product_id = get_query_var("vpc-pid", false);

        extract(shortcode_atts(array(
            'product' => '',
                        ), $atts, 'wpb_builder'));

        //Maybe the product ID is included in the shortcode
        if (!$product_id)
            $product_id = $product;

        if (!$product_id)
            $output = __("Looks like you're trying to access the configuration page directly. This page can only be accessed by clicking on the Build your own button from the product or the shop page.", "vpc");
        else
            $output = $this->get_vpc_editor($product_id);

        return $output;
    }

    public function get_vpc_editor($product_id, $config_id = false) {
        
        global $vpc_settings;
        global $woocommerce;
            if ($product_id){
                if(vpc_woocommerce_version_check())
                    $product = new WC_Product($product_id);
                else
                    $product = wc_get_product($product_id);
                $config = vpc_get_product_config($product_id);
            }else if ($config_id) {
                $config = new VPC_Config($config_id);
            }
        $skin = Orion_Library::get_proper_value($config->settings, "skin", "VPC_Default_Skin");
        $wvpc_conditional_rules = array();
        $reverse_triggers = array();
        $rules_structure = Orion_Library::get_proper_value($config->settings, "conditional_rules", array());
        $rules_enabled = Orion_Library::get_proper_value($rules_structure, "enable_rules", false);
        /*if ($rules_enabled == "enabled") {
            $rules_groups = Orion_Library::get_proper_value($rules_structure, "groups", array());
            $reorganized_rules = vpc_get_reorganized_rules($rules_groups);
            ` = $reorganized_rules["per-option"];
            $reverse_triggers = $reorganized_rules["reverse-triggers"];
        }*/
        $cart_url = "";
        $product_url = "";
        $price_format = '';
        $decimal_separator = '';
        $symbol = '';
        $price_separator = '';
        $price_unit = '';
        if (is_admin() && !is_ajax()) {
            $cart_url = "";
            $product_url = "";
        } else {
            //Déclenche une erreur lorsqu'utilisée dans l'interface de conception d'un template
           
            if(vpc_woocommerce_version_check()){
                $product_url = get_permalink($product->id);
                 $cart_url = $woocommerce->cart->get_cart_url();
            }
            else{
                $product_url = get_permalink($product->get_id());
                 $cart_url = wc_get_cart_url();
            }
        }

        $price_format = vpc_get_price_format(); //str_replace(html_entity_decode(htmlentities(get_woocommerce_currency_symbol())), "$", $raw_price_format);

        $decimal_separator = wc_get_price_decimal_separator();

        $editor = new $skin($product_id, $config_id);

        $to_load = apply_filters("vpc_config_to_load", array(), $product_id);

        $ajax_loading = Orion_Library::get_proper_value($vpc_settings, "ajax-loading", "No");
        $action_after_add_to_cart = Orion_Library::get_proper_value($vpc_settings, "action-after-add-to-cart", "Yes");
        $vpc_parameters = apply_filters("vpc_data", array(
            "preload" => $to_load,
            "product" => $product_id,
            'action_after_add_to_cart' => $action_after_add_to_cart,
            'wvpc_conditional_rules' => $wvpc_conditional_rules,
           // 'reverse_triggers' => $reverse_triggers,
            'cart_url' => $cart_url,
            'current_product_page' => $product_url,
            'vpc_selected_items_selector' => apply_filters("vpc_selected_items_selector", ".vpc-options input:checked"),
            'currency' => html_entity_decode(htmlentities(get_woocommerce_currency_symbol())),
            'decimal_separator' => $decimal_separator,
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals' => wc_get_price_decimals(),
            'price_format' => $price_format,
            'config' => $config->settings
        ));
        ?>
        <script>
            var vpc =<?php echo html_entity_decode(json_encode($vpc_parameters)); ?>;
        </script>
        <?php
        if ($ajax_loading == "Yes" && !is_admin()) {
            $editor->enqueue_styles_scripts();
            $output = "<div id='vpc-ajax-container' class='vpc-loading'><img src='" . VPC_URL . "public/images/preloader.gif'></div>";
        } else {
            $raw_output = $editor->display($to_load);
            $output = apply_filters("vpc_output_editor", $raw_output, $product_id, $config->id);
        }

        return $output;
    }

    public function get_vpc_editor_ajax() {
        $posts_datas  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if(isset($posts_datas["vpc"])){
            $vpc =vpc_array_sanitize( $posts_datas["vpc"]);
            $to_load = Orion_Library::get_proper_value($vpc, "preload", false);
            if(isset($posts_datas["vpc"]["product"]))
                $product_id = intval($posts_datas["vpc"]["product"]);
            $config = vpc_get_product_config($product_id);
            $skin = Orion_Library::get_proper_value($config->settings, "skin", "VPC_Default_Skin");
            $editor = new $skin($product_id);
            $raw_output = $editor->display($to_load);
            $output = apply_filters("vpc_output_editor", $raw_output, $product_id, $config->id);
            echo $output;
        }
        die();
    }

    public function add_query_vars($aVars) {
//        $aVars[] = "vpc-pid";
        return $aVars;
    }

    public function add_rewrite_rules($param) {
        GLOBAL $vpc_settings;
        GLOBAL $wp_rewrite;
        add_rewrite_tag('%vpc-pid%', '([^&]+)');

        $config_page_id = Orion_Library::get_proper_value($vpc_settings, "config-page", false);
        if (!$config_page_id)
            return;

        $rule_match = 1;
        
            $wpc_page = get_post($config_page_id);
            if (is_object($wpc_page)) {
                $slug = $wpc_page->post_name;
                $wp_rewrite->add_rule($slug . '/configure/([^/]+)', 'index.php?pagename=' . $slug . '&vpc-pid=$matches[' . $rule_match . ']', 'top');

                $wp_rewrite->flush_rules();
            }
        
    }

    function init_globals() {
        global $vpc_settings;
        $vpc_settings = get_option("vpc-options");
        $_SESSION["vpc_calculated_totals"]=false;
    }

    function get_configure_btn() {
        $post_id = get_the_ID();
        $button = $this->get_configuration_button($post_id, true);

        if ($button)
            echo $button;
    }

    private function get_configuration_button($product_id, $wrap = false) {
        global $vpc_settings;
        ob_start();
        $metas = get_post_meta($product_id, 'vpc-config', true);
        $hide_wc_add_to_cart = Orion_Library::get_proper_value($vpc_settings, "hide-wc-add-to-cart", "Yes");
        $vpc_product_is_configurable = vpc_product_is_configurable($product_id);
        if ($vpc_product_is_configurable && $hide_wc_add_to_cart == 'Yes') {
            ?>
            <style type="text/css" > .woocommerce div.product form.cart .button.single_add_to_cart_button, [data-product_id="<?php echo $product_id; ?>"].add_to_cart_button{display:none !important;} </style>
            <?php
        }
        $product = wc_get_product($product_id);
            if(vpc_woocommerce_version_check())
                $product_type = $product->product_type;
            else
                $product_type = $product->get_type();
            if ($product_type == "variable") {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                echo $this->get_button($variation["variation_id"], $metas, $wrap, false);
            }
        } else {
            echo $this->get_button($product_id, $metas, $wrap);
        }
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    private function get_button($id, $metas, $wrap, $display = true) {

        $configs = Orion_Library::get_proper_value($metas, $id, array());

        $config_id = Orion_Library::get_proper_value($configs, "config-id", false);

        if (!$config_id)
            return false;

        $design_url = vpc_get_configuration_url($id);

        if ($display)
            $style = "";
        else
            $style = "style='display:none;'";

        if ($wrap)
            $design_url = "<a class='vpc-configure-button button' href='$design_url' data-id='$id' $style>" . __("Build your own", "vpc") . "</a>";


        return $design_url;
    }

   /* function get_configure_btn_loop($html, $product) {
        $button = $this->get_configuration_button($product->id, true);
        if ($button)
            $html.=$button;
        return $html;
    }*/

    function set_variable_action_filters() {
        global $vpc_settings;
        $append_content_filter = Orion_Library::get_proper_value($vpc_settings, "manage-config-page", "Yes");

        if ($append_content_filter === "Yes" && !is_admin()) {

            add_filter("the_content", array($this, "filter_content"), 99);
        }
    }

    function filter_content($content) {
        global $vpc_settings;
//        global $wp_query;
//        var_dump($wp_query->query_vars);
        $vpc_page_id = Orion_Library::get_proper_value($vpc_settings, "config-page", false);
        if (!$vpc_page_id)
            return $content;

        $current_page_id = get_the_ID();
        if ($vpc_page_id == $current_page_id) {
            $product_id = get_query_var("vpc-pid", false); //$wp_query->query_vars["vpc-pid"];
            if (!$product_id)
                $content.=__("Looks like you're trying to access the configuration page directly. This page can only be accessed by clicking on Build your own button from the product or the shop page.", "vpc");
            else
                $content.= $this->get_vpc_editor($product_id);
        }
        return $content;
    }

    public function add_vpc_configuration_to_cart() {
        global $woocommerce;

        $message = "";
        $cart_url = $woocommerce->cart->get_cart_url();
        $posts_datas  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if(isset($posts_datas["product_id"]))
            $product_id = intval($posts_datas["product_id"]);
        if(isset($posts_datas["quantity"]))
            $quantity = intval($posts_datas["quantity"]);
        if(isset($posts_datas["recap"])){
            $recap =vpc_array_sanitize($posts_datas["recap"]);
        }
        if(isset($posts_datas["custom_vars"])){
            $custom_vars =vpc_array_sanitize($posts_datas["custom_vars"]);
        }
//        var_dump($custom_vars);

        $alt_products = array();
        if (isset($posts_datas["alt_products"])){
            $alt_products = vpc_array_sanitize($posts_datas["alt_products"]);
        }
        if (!is_array($alt_products))
            $alt_products = array();
        if(isset($posts_datas)){
            $posts=vpc_array_sanitize($posts_datas);
            $proceed_addition_to_cart = apply_filters("vpc_proceed_add_to_cart", true, $posts);
        }

        //Check if there is enought items in the stock
        $products_are_availables = $this->check_product_availability($product_id, $quantity);
        if ($proceed_addition_to_cart && !empty($alt_products)) {
            foreach ($alt_products as $key => $alt_product_id) {
                if (!$this->check_product_availability($alt_product_id, $quantity)) {
                    $products_are_availables = false;
                }
            }
        }
        if (!$products_are_availables) {
            $message = __('You can not add that ammount of product to the cart', "vpc");
            echo $message;
            die();
        }

        $ids = vpc_get_product_root_and_variations_ids($product_id);

        if ($proceed_addition_to_cart && $products_are_availables) {
            if(vpc_woocommerce_version_check())
                $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], $ids["variation"], array('visual-product-configuration' => $recap, 'vpc-custom-vars' => $custom_vars));
            else
                $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], "", array('visual-product-configuration' => $recap, 'vpc-custom-vars' => $custom_vars));
           do_action("vpc_add_to_cart_main", $ids["product-id"], $quantity, $ids["variation-id"]);
            if (method_exists($woocommerce->cart, "maybe_set_cart_cookies"))
                $woocommerce->cart->maybe_set_cart_cookies();
            if ($newly_added_cart_item_key) {

                //Alternate products
                foreach ($alt_products as $alt_product_id) {
                    $ids = vpc_get_product_root_and_variations_ids($alt_product_id);
                    if(vpc_woocommerce_version_check())
                        $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], $ids["variation"], array('vpc-is-secondary-product' => true, 'main_product_cart_item_key' => $newly_added_cart_item_key));
                    else
                        $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], "", array('vpc-is-secondary-product' => true, 'main_product_cart_item_key' => $newly_added_cart_item_key));
                    if (method_exists($woocommerce->cart, "maybe_set_cart_cookies"))
                        $woocommerce->cart->maybe_set_cart_cookies();
                    do_action("vpc_add_to_cart_alt", $ids["product-id"], $quantity, $ids["variation-id"]);
                }
                $raw_message = "<div class='vpc-success f-right'>" . __("Product successfully added to basket.", "vpc") . " <a href='$cart_url'>" . __("View Cart", "vpc") . "</a></div>";
                $message = apply_filters("vpc_add_to_cart_success_message", $raw_message);
            } else {
                $raw_message = "<div class='vpc-failure f-right'>" . __("A problem occured. Please try again.", "vpc") . "</div>";
                $message = apply_filters("vpc_add_to_cart_failure_message", $raw_message);
            }

            echo $message;
        } else {
            do_action("vpc_add_to_cart_processing", $posts_datas);
        }

        die();
    }

    function get_vpc_data_image($product_image_code, $values, $cart_item_key) {

        if ($values["variation_id"])
            $product_id = $values["variation_id"];
        else
            $product_id = $values["product_id"];
        $config = vpc_get_product_config($product_id);

        //We extract the recap from the cart item key
        $recap = vpc_get_recap_from_cart_item($values);
        if (!empty($recap)) {
            $config_image = $this->get_config_image($recap, $config->settings, $values);
            $product_image_code = $config_image;
        }
        return $product_image_code;
    }

    function get_vpc_data($thumbnail_code, $values, $cart_item_key) {
        if ($values["variation_id"])
            $product_id = $values["variation_id"];
        else
            $product_id = $values["product_id"];
        $config = vpc_get_product_config($product_id);

        //We extract the recap from the cart item key
        $recap = vpc_get_recap_from_cart_item($values);
        if (!empty($recap)) {
//                $config_image = $this->get_config_image($recap, $config->settings);
            $formatted_config = $this->get_formatted_config_data($recap, $config->settings, $values);
            $thumbnail_code.= "<div class='vpc-cart-config o-wrap'><div class='col xl-1-1'>" . $formatted_config . "</div> </div>";
        }
        return $thumbnail_code;
    }

    private function get_config_image($recap, $config, $item) {
        $output = "";
        if (is_array($recap)) {
            foreach ($recap as $component => $raw_options) {
                if (is_array($raw_options)) {
                    //$options=  implode (", ", $raw_options);
                    foreach ($raw_options as $options) {
                        $image = $this->extract_option_field_from_config($options, $component, $config, "image");
                        $img_src = Orion_Library::o_get_proper_image_url($image);
                        $title = $raw_options;
                        if (is_array($raw_options))
                            $title = implode(", ", $raw_options);
                        if ($img_src) {
                            $img_code = "<img src='$img_src' data-tooltip-title='$title'>";
                            $output.=$img_code;
                        }
                    }
                } else {
                    $options = $raw_options;
                    $image = $this->extract_option_field_from_config($raw_options, $component, $config, "image");
                    $img_src = Orion_Library::o_get_proper_image_url($image);
                    $title = $raw_options;
                    if (is_array($raw_options))
                        $title = implode(", ", $raw_options);
                    if ($img_src) {
                        $img_code = "<img src='$img_src' data-tooltip-title='$title'>";
                        $output.=$img_code;
                    }
                }
            }
        }
        $output = apply_filters("vpc_get_config_image", $output, $recap, $config, $item);

        return "<div class='vpc-cart-config-image'>$output</div>";
    }

    private function get_formatted_config_data($recap, $config, $show_icons = true) {
        $output = "<div class='vpc-cart-options-container'>";
        $option = "";
        $filtered_recap = apply_filters("vpc_filter_recap", $recap, $config, $show_icons);

        if (is_array($filtered_recap)) {
            foreach ($filtered_recap as $component => $raw_options) {
                $options_arr = $raw_options;
                if (!is_array($raw_options))
                    $options_arr = array($raw_options);
                $options_html = "";
                $labels_html = "";
                if ($show_icons) {
                    foreach ($options_arr as $option) {
                        $icon = $this->extract_option_field_from_config($option, $component, $config);
                        $img_code = "";
                        if ($icon) {
                            $img_src = Orion_Library::o_get_proper_image_url($icon);
                            $img_code = "<div>" . stripslashes($option) . "<img src='$img_src' data-tooltip-title='$option'></div>";
//                        $img_code = "<img src='$img_src' data-tooltip-title='$option'>";
                            $options_html.=$img_code;
                        } else
                            $options_html.=stripslashes($option);//To escape quotes in the name
                    }
                } else
                    $options_html = implode(", ", $options_arr);

                $option = stripslashes($option);
                $component = stripslashes($component);

                $output.="<div><strong>$component: </strong>$options_html</div>";
            }
        }
        $output.="</div>";

        return $output;
    }

    public function extract_option_field_from_config($searched_option, $searched_component, $config, $field = "icon") {
        $unslashed_searched_option = stripslashes($searched_option);
        $unslashed_searched_component = stripslashes($searched_component);
        $field = apply_filters("extracted_option_field_from_config", $field);
        if(!is_array($config))
            $config=  unserialize ($config);
        foreach ($config["components"] as $i => $component) {
            if (stripslashes($component["cname"]) == $unslashed_searched_component) {
                foreach ($component["options"] as $component_option) {
                    if (stripslashes($component_option["name"]) == $unslashed_searched_option) {
                        if(isset($component_option[$field]))
                            return $component_option[$field];
                    }
                }
            }
        }

        return false;
    }

    function save_customized_item_meta($item_id, $values, $cart_item_key) {
        global $vpc_settings;
        $store_original_config = Orion_Library::get_proper_value($vpc_settings, "store-original-configs", "Yes");
        
        if ($values["variation_id"])
            $product_id = $values["variation_id"];
        else
            $product_id = $values["product_id"];

        //We extract the recap from the cart item key
        $recap = vpc_get_recap_from_cart_item($values);
        $original_config = vpc_get_product_config($product_id);
        if (!empty($recap) && $original_config != false) {
            wc_add_order_item_meta($item_id, 'vpc-cart-data', $recap);
            if($store_original_config=="Yes")
                wc_add_order_item_meta($item_id, 'vpc-original-config', $original_config->settings);
        }
    }

    function get_user_account_products_meta($output, $item) {
        if (isset($item["vpc-cart-data"])) {
            $original_config = vpc_get_order_item_configuration($item);
            $output.="<br>";
            if(vpc_woocommerce_version_check())
            $recap = unserialize($item["vpc-cart-data"]);
            else
                $recap = $item["vpc-cart-data"];
//            foreach ($data_arr as $recap) {
            $config_image = $this->get_config_image($recap, $original_config, $item);
            $formatted_config = $this->get_formatted_config_data($recap, $original_config);
            $output.= "<div class='vpc-cart-config o-wrap'>" . $config_image . "<div class='o-col xl-2-3'>" . $formatted_config . "</div></div>";
//            }
        }
        return $output;
    }

    function get_admin_products_metas($item_id, $item, $_product) {
        $output = "";
        if (isset($item["vpc-cart-data"])) {
            $original_config = vpc_get_order_item_configuration($item);
            $output.="<br>";
            if(vpc_woocommerce_version_check())
            $recap = unserialize(strip_tags($item["vpc-cart-data"]));
            else
                $recap = $item["vpc-cart-data"];
//            foreach ($data_arr as $recap) {
            $config_image = $this->get_config_image($recap, $original_config, $item);
            $formatted_config = $this->get_formatted_config_data($recap, $original_config);
            $output.= "<div class='vpc-order-config o-wrap xl-gutter-8'>" . $config_image . "<div class='col xl-2-3'>" . $formatted_config . "</div></div>";
//            }
        }
        echo $output;
    }

    private function get_config_price($product_id, $config, $cart_item) {
        $original_config = vpc_get_product_config($product_id);
        $total_price = 0;
//        foreach ($config as $recap) {
        foreach ($config as $component => $raw_options) {
            $options_arr = $raw_options;
            if (!is_array($raw_options))
                $options_arr = array($raw_options);
            foreach ($options_arr as $option) {
                $linked_product = $this->extract_option_field_from_config($option, $component, $original_config->settings, "product");
                $option_price = $this->extract_option_field_from_config($option, $component, $original_config->settings, "price");
                //We ignore the linked products prices since they're already added in the cart
                if ($linked_product) {
                    $option_price = 0;
                }
                //We make sure we're not handling any empty priced option
                if (empty($option_price))
                    $option_price = 0;

                $total_price+=$option_price;
            }
        }
        return apply_filters("vpc_config_price", $total_price, $product_id, $config, $cart_item);
    }

    function get_cart_item_price($cart) {
        if($_SESSION["vpc_calculated_totals"]==TRUE)
            return;
            
        foreach ($cart->cart_contents as $cart_item_key => $cart_item) {
            if ($cart_item["variation_id"])
                $product_id = $cart_item["variation_id"];
            else
                $product_id = $cart_item["product_id"];

            $recap = vpc_get_recap_from_cart_item($cart_item);
            if (!empty($recap)) {
                $a_price = $this->get_config_price($product_id, $recap, $cart_item);
                if(vpc_woocommerce_version_check())
                $cart_item['data']->price += $a_price;
                else{
                    $total=$cart_item['data']->get_price() + $a_price;
                    $cart_item['data']->set_price($total);
            }
        }
        }
        $_SESSION["vpc_calculated_totals"]=TRUE;
    }


   
    public function set_email_order_item_meta($item_id, $item, $order) {
        $output = "";
        if (is_order_received_page())
            return;
        if (isset($item["vpc-cart-data"])) {
            $original_config = vpc_get_order_item_configuration($item);
//            $output.="<br>";
            $recap = unserialize($item["vpc-cart-data"]);
//            foreach ($data_arr as $recap) {
            $formatted_config = $this->get_formatted_config_data($recap, $original_config, false);
            $output = "<div class='vpc-order-config o-wrap xl-gutter-8'><div class='col xl-2-3'>" . $formatted_config . "</div></div>";
//            }
        }
        echo $output;
    }

    public function add_class_to_body($classes) {

        $current_ID = get_the_ID();
        $test = vpc_product_is_configurable($current_ID);
        if ($test) {
            $classes[] = 'vpc-is-configurable';
        }
        return $classes;
    }

    public function set_order_again_cart_item_data($datas, $item, $order) {
        $item_metas = $item['item_meta'];
        $cart_datas = $item_metas["vpc-cart-data"];
        $recap = unserialize($cart_datas[0]);
        return array('visual-product-configuration' => $recap);
    }

  
    public function check_product_availability($product_id, $quantity) {
        $product = wc_get_product($product_id);
        $numleft = $product->get_stock_quantity();

        if ($numleft == NULL || $numleft >= $quantity) {
            return true;
        } else {
            return false;
        }
    }

   

    function vpc_remove_secondary_products($cart_item_key) {
        global $woocommerce;
        foreach (WC()->cart->cart_contents as $key => $values) {
            if ((isset($values['main_product_cart_item_key'])) && (($values['main_product_cart_item_key'] == $cart_item_key) || !isset(WC()->cart->cart_contents[$values['main_product_cart_item_key']]))) {
                unset(WC()->cart->cart_contents[$key]);
            }
        }
    }

    function prevent_secondary_product_deletion($cart_item_key) {
        if (isset(WC()->cart->cart_contents[$cart_item_key]['vpc-is-secondary-product']) && WC()->cart->cart_contents[$cart_item_key]['vpc-is-secondary-product'] == true) {
            wc_add_notice(sprintf(__('You can not remove the secondary product', 'vpc')));
            $referer = wp_get_referer() ? remove_query_arg(array('undo_item', '_wpnonce'), wp_get_referer()) : WC()->cart->get_cart_url();
            wp_safe_redirect($referer);
            exit;
        }
    }
    
    function get_vpc_product_qty_ajax(){
        if(isset($_POST['prod_id']))
            $prod_id=intval($_POST['prod_id']);
        if(isset($_POST['qty']))
            $qty=intval($_POST['qty']);
        $design_url = vpc_get_configuration_url($prod_id);
        $design_url=add_query_arg( 'qty', $qty, $design_url);
        echo $design_url;
        die();
    }
}
