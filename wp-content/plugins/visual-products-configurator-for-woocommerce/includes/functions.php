<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function vpc_get_product_config($product_id) {
    $ids = vpc_get_product_root_and_variations_ids($product_id);
    $config_meta = get_post_meta($ids["product-id"], "vpc-config", true);
    $configs = Orion_Library::get_proper_value($config_meta, $product_id, array());
    $config_id = Orion_Library::get_proper_value($configs, "config-id", false);

//        $config_meta = get_post_meta($product_id, "vpc-config", true);
//        $config_id=  Orion_Library::get_proper_value($config_meta, "config-id");
    if (!$config_id || empty($config_id))
        return false;

    $config_obj = new VPC_Config($config_id);
    return $config_obj;
}

function vpc_get_price_container() {
    if (is_admin() && !is_ajax())
        return;
    ?>
    <div id="vpc-price-container">
        <span class="vpc-price-label" style="font-weight: normal;color:#768e9d"> <?php _e("Total:", "vpc"); ?> </span>
        <span id="vpc-price"></span>   
    </div>
    <?php
}

function vpc_get_action_buttons_arr($product_id) {
    $product = wc_get_product($product_id);
    $product_price = $product->get_price();

    $add_to_cart = array(
        "id" => "vpc-add-to-cart",
        "label" => __("Add to cart", "vpc"),
        "class" => "",
        "attributes" => array(
            "data-pid" => $product_id,
            "data-price" => $product_price,
        ),
    );

    $cid = "";
    if (isset($_GET["cid"]))
        $cid = intval($_GET["cid"]);

//    $save = array(
//        "id"=>"vpc-save-config",
//        "label"=>__("Save", "vpc"),
//        "class"=>"",
//        "attributes"=>array(
//            "data-cid"=>$cid,
//        ),
//        "requires_login"=>true,
//        "visible_admin"=>false
//    );

    $buttons = array(
//        $save,
        $add_to_cart,
    );
    return apply_filters("vpc_action_buttons", $buttons);
}

function vpc_get_action_buttons($product_id) {
    if (!$product_id)
        return;
    $buttons = vpc_get_action_buttons_arr($product_id);
    ob_start();
    ?>
    <div class="vpc-action-buttons o-col xl-2-3">
        <div class="o-col xl-1-1">
            <?php
            vpc_get_quantity_container();

            foreach ($buttons as $button) {
                if (!isset($button["requires_login"]))
                    $button["requires_login"] = false;
                if (!isset($button["visible_admin"]))
                    $button["visible_admin"] = true;
                if (!isset($button["attributes"]))
                    $button["attributes"] = array();

                if (!is_user_logged_in() && $button["requires_login"])
                    continue;
                else if (is_admin() && !is_ajax() && !$button["visible_admin"])
                    continue;
                // Custom attribute handling
                $custom_attributes = array();

                foreach ($button['attributes'] as $attribute => $attribute_value) {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
                ?>
                <button
                    id="<?php echo esc_attr($button['id']); ?>"
                    class="<?php echo esc_attr($button['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                    >
                        <?php echo esc_attr($button["label"]); ?>
                </button>

                <?php
            }
            ?>
        </div>
    </div>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return apply_filters("vpc_action_buttons_html", $output, $product_id);
}

function vpc_enqueue_core_scripts() {
    wp_enqueue_script('vpc-accounting', VPC_URL . 'public/js/accounting.min.js', array('jquery'), VPC_VERSION, false);
    wp_enqueue_script("vpc-public", VPC_URL . 'public/js/vpc-public.js', array('jquery', 'vpc-accounting'), VPC_VERSION, false);
    wp_localize_script("vpc-public", 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_script('wp-js-hooks', VPC_URL . 'public/js/wp-js-hooks.min.js', array('jquery'), VPC_VERSION, false);

    do_action("vpc_enqueue_core_scripts");
}

function vpc_get_quantity_container() {
    global $vpc_settings;
    if (is_admin() && !is_ajax())
        return;
    $action_qtity_box = Orion_Library::get_proper_value($vpc_settings, "hide-qty", "Yes");
    $qty=1;
    if(isset($_GET['qty']))
       $qty=intval($_GET['qty']);
    if($action_qtity_box=="No")
        $style="";
    else
        $style="display:none;";
    
    ?>
    <div id="vpc-qty-container" class="" style="<?php echo $style;?>">
        <input type="button" value="-" class="minus">
        <input id="vpc-qty" type="number" step="1" value="<?php echo $qty;?>" min="1">
        <input type="button" value="+" class="plus">
    </div>
    <?php
    
}

function vpc_get_product_root_and_variations_ids($id) {
    $product_id = 0;
    $variation_id = 0;
    $variation = array();

    $variable_product = wc_get_product($id);
    if (!$variable_product)
        return false;

        if(vpc_woocommerce_version_check())
            $product_type= $variable_product->product_type;
        else
            $product_type=$variable_product->get_type();

        if ( $product_type== "simple")
            $product_id = $id;
        else {
                if(vpc_woocommerce_version_check()){
                    $variation = $variable_product->variation_data;
                    $product_id =$variable_product->parent->id;
                }
                else{
                    $variation = $variable_product->get_data();
                    $product_id = $variable_product->get_parent_id();
                }
            $variation_id = $id;
        }
    
    return array("product-id" => $product_id, "variation-id" => $variation_id, "variation" => $variation);
}



function vpc_sort_options_by_group($options) {
    $sorted_options = array();
    foreach ($options as $option) {
        if (!isset($sorted_options[$option["group"]]))
            $sorted_options[$option["group"]] = array();
        array_push($sorted_options[$option["group"]], $option);
    }
    $merged = call_user_func_array('array_merge', $sorted_options);

    return array_merge($merged);
}

function vpc_get_configuration_url($product_id, $saved_config_id = false, $template_id = false) {
    global $vpc_settings;
    $config_page_id = Orion_Library::get_proper_value($vpc_settings, "config-page");
    if (!$config_page_id)
        return false;

    $design_url = get_permalink($config_page_id);
    if ($product_id) {
//                $query = parse_url($design_url, PHP_URL_QUERY);
        // Returns a string if the URL has parameters or NULL if not
        $use_pretty_url=  apply_filters("vpc_use_pretty_url", true);
        if (get_option('permalink_structure')&&$use_pretty_url) {
            if (substr($design_url, -1) != '/') {
                $design_url .= '/';
            }
           // $design_url.='?vpc-pid=' . $product_id;
             $design_url .= 'configure/' . $product_id . '/';
            if ($saved_config_id)
                $design_url.="?cid=$saved_config_id";
            else if ($template_id)
                $design_url.="?tid=$template_id";
        } else {
            $url_args=array("vpc-pid"=>$product_id);
            if ($saved_config_id)
                $url_args["cid"]=$saved_config_id;
            else if ($template_id)
                $url_args["tid"]=$template_id;;
            $design_url=add_query_arg($url_args, $design_url);
        }
    }

    return $design_url;
}

function vpc_extract_configuration_images($saved_config, $original_config) {
    $components_by_names = $original_config->get_components_by_name();
    $output = "";

    foreach ($saved_config as $saved_component_name => $saved_options) {
        $original_options = $components_by_names[$saved_component_name];
        if (!is_array($saved_options)) {
            $saved_options = array($saved_options);
        }

        foreach ($saved_options as $saved_option) {
            $original_option = Orion_Library::get_proper_value($original_options, $saved_option);
            $img_id = Orion_Library::get_proper_value($original_option, "image");
            if ($img_id) {
                $img_url = Orion_Library::get_media_url($img_id);
                $output.="<img src='$img_url'>";
            }
        }
    }

    return $output;
}

function vpc_get_behaviours() {
    $behaviours_arr = apply_filters("vpc_configuration_behaviours", array(
        "radio" => __("Single choice", "vpc"),
        "checkbox" => __("Multiple choices", "vpc"),
    ));

    return $behaviours_arr;
}

function vpc_is_configurable($metas) {
    return (!empty($metas['config-id']));
}

function vpc_product_is_configurable($id) {
    $metas = get_post_meta($id, 'vpc-config', true);
    $product = wc_get_product($id);
    if (!$product)
        return false;
    $class_name = get_class($product);
    if ($class_name == "WC_Product_Variable") {
        $variations = $product->get_available_variations();
        foreach ($variations as $variation) {
            $variation_id = $variation["variation_id"];
            $variation_metas = Orion_Library::get_proper_value($metas, $variation_id, false);
            $variation_config_id = $variation_metas['config-id'];
            if (!empty($variation_config_id)) {
                return true;
            } else {
                return false;
            }
        }
    } else if ($class_name == "WC_Product_Variation") {
        $Parent_ID = get_the_ID($product);
        $metas = get_post_meta($Parent_ID, 'vpc-config', true);
        $variation_metas = Orion_Library::get_proper_value($metas, $id, false);
        $variation_config_id = $variation_metas['config-id'];
        if (!empty($variation_config_id)) {
            return true;
        } else {
            return false;
        }
    } else {
        $configs = Orion_Library::get_proper_value($metas, $id, array());
        $config_id = Orion_Library::get_proper_value($configs, "config-id", false);
        if (!empty($config_id)) {
            return true;
        } else {
            return false;
        }
    }
}

function vpc_get_recap_from_cart_item($data) {
    if (empty($data) || !is_array($data))
        return array();
    // $merged_with_keys=array(
    //     'product_id', 
    //     'variation_id', 
    //     'variation', 
    //     'quantity', 
    //     'data', 
    //     'line_tax',
    //     'line_total',
    //     'line_subtotal', 
    //     'line_subtotal_tax', 
    //     'line_tax_data',
    //     'addons');
    // $output=array_diff_key($data,array_flip($merged_with_keys));
    $output = array();
    if (isset($data['visual-product-configuration']) && !empty($data['visual-product-configuration']))
        $output = $data['visual-product-configuration'];

    return $output;
}

function vpc_merge_pictures($images, $path = false, $url = false) {
    $tmp_dir = uniqid();
    $upload_dir = wp_upload_dir();
    $generation_path = $upload_dir["basedir"] . "/VPC";
    $generation_url = $upload_dir["baseurl"] . "/VPC";
    if (wp_mkdir_p($generation_path)) {
        $output_file_path = $generation_path . "/$tmp_dir.png";
        $output_file_url = $generation_url . "/$tmp_dir.png";
        foreach ($images as $imgs) {
            list($width, $height) = getimagesize($imgs);
            $img = imagecreatefrompng($imgs);
            imagealphablending($img, true);
            imagesavealpha($img, true);
            if (isset($output_img)) {
                imagecopy($output_img, $img, 0, 0, 0, 0, 1000, 500);
            } else {
                $output_img = $img;
                imagealphablending($output_img, true);
                imagesavealpha($output_img, true);
                imagecopymerge($output_img, $img, 10, 12, 0, 0, 0, 0, 100);
            }
        }
        imagepng($output_img, $output_file_path);
        imagedestroy($output_img);
        if ($path)
            return $output_file_path;
        if ($url)
            return $output_file_url;
    } else
        return false;
}

function vpc_get_price_format() {
    $currency_pos = get_option('woocommerce_currency_pos');
    $format = '%s%v';

    switch ($currency_pos) {
        case 'left' :
            $format = '%s%v';
            break;
        case 'right' :
            $format = '%v%s';
            break;
        case 'left_space' :
            $format = '%s %v';
            break;
        case 'right_space' :
            $format = '%v %s';
            break;
        default:
            $format = '%s%v';
            break;
    }
    return $format;
}

function vpc_get_order_item_configuration($item)
{
    if(isset($item["vpc-original-config"])){
        if(vpc_woocommerce_version_check())
        $original_config = unserialize($item["vpc-original-config"]);
    else
            $original_config =$item["vpc-original-config"];
    }
    else
    {
        if ($item["variation_id"])
            $product_id = $item["variation_id"];
        else
            $product_id = $item["product_id"];

        $original_config_obj = vpc_get_product_config($product_id);
        $original_config=$original_config_obj->settings;
    }
    
    return $original_config;
}

function vpc_array_sanitize($arr){
    $newArr = array();
    foreach( $arr as $key => $value )
    {
        $newArr[ $key ]=(is_array($value) ? vpc_array_sanitize($value) : sanitize_text_field(esc_html($value))) ;
    }
    return $newArr;
}

function vpc_woocommerce_version_check( $version = '3.0.0' ) {
   if ( function_exists( "WC" ) && ( version_compare( WC()->version, $version, "<")))
       return true;
   return false;
}