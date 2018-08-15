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
    wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vpc-public.css', array(), $this->version, 'all');

    wp_enqueue_style("o-flexgrid", plugin_dir_url(__FILE__) . '../admin/css/flexiblegs.css', array(), $this->version, 'all');

    // wp_enqueue_style('vpc-follow-scroll', plugin_dir_url(__FILE__) . 'css/vpc-follow-scroll.css', array(), $this->version, 'all');
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
    add_shortcode('vpc', array($this, 'get_vpc_editor_handlers'));
  }

  public function get_vpc_editor_handlers($atts) {
    $product_id = get_query_var("vpc-pid", false);

    //Maybe the product ID is included in the shortcode
    if (isset($atts["product"]))
    $product = $atts["product"];
    if (!$product_id)
    $product_id = $product;

    if (!$product_id)
    $output = __("Looks like you're trying to access the configuration page directly. This page can only be accessed by clicking on the Build your own button from the product or the shop page.", "vpc");
    else
    $output = $this->get_vpc_editor($product_id);

    return $output;
  }

  public function get_vpc_editor($product_id, $config_id = false) {
    global $vpc_settings,$wp_query,$woocommerce;
    if (class_exists('Woocommerce')) {
      global $woocommerce;
      if ($product_id) {
        if (vpc_woocommerce_version_check())
        $product = new WC_Product($product_id);
        else
        $product = wc_get_product($product_id);
        $config = get_product_config($product_id);
      }else if ($config_id) {
        $config = new VPC_Config($config_id);
      }
    } else {
      $product_id = '';
      $config = new VPC_Config($config_id);
    }

    ?>
    <div style='display: none;'>
      <?php
      $settings = $config->settings;
      if(isset($settings['components'])){
        foreach($settings['components'] as $components){
          if(isset($components['options'])){
            foreach($components['options'] as $option){
              if(!empty($option['image'])){
                $opt_image = o_get_proper_image_url($option['image']);
                echo "<img src='".$opt_image."'>";
              }
              if(class_exists( 'Vpc_Mva')){
                if(isset($option['view_options'])){
                  foreach($option['view_options'] as $view){
                    if(!empty($view['image'])){
                      $view_image = o_get_proper_image_url($view['image']);
                      echo "<img src='".$view_image."'>";
                    }
                  }
                }
              }
            }
          }
        }
      }
      ?>
    </div>
    <?php


    $skin = get_proper_value($config->settings, "skin", "VPC_Default_Skin");
    $wvpc_conditional_rules = array();
    $reverse_triggers = array();
    $rules_structure = get_proper_value($config->settings, "conditional_rules", array());
    $rules_enabled = get_proper_value($rules_structure, "enable_rules", false);
    if ($rules_enabled == "enabled") {
      $rules_groups = get_proper_value($rules_structure, "groups", array());
      $reorganized_rules = get_reorganized_rules($rules_groups);
      $wvpc_conditional_rules = $reorganized_rules["per-option"];
      $reverse_triggers = $reorganized_rules["reverse-triggers"];
    }
    $cart_url = "";
    $product_url = "";
    $price_format = '';
    $decimal_separator = '';
    $symbol = '';
    $price_separator = '';
    $price_unit = '';
    $to_load=array();
    $cart_item_key="";
    if (is_admin() && !is_ajax()) {
      $cart_url = "";
      $product_url = "";
    } else {
      //Déclenche une erreur lorsqu'utilisée dans l'interface de conception d'un template
      if (class_exists('Woocommerce')) {
        //$cart_url = $woocommerce->cart->get_cart_url();
        if (vpc_woocommerce_version_check()) {
          $cart_url = $woocommerce->cart->get_cart_url();
          $prod_id = $product->id;
          $product_obj = wc_get_product($prod_id);
          $product_type = $product_obj->product_type;
          if ($product_type == 'variation')
          $prod_id = $product_obj->parent->id;
        }
        else {
          $cart_url = wc_get_cart_url();
          $prod_id = $product->get_id();
          $product_obj = wc_get_product($prod_id);
          $product_type = $product_obj->get_type();
          if ($product_type == 'variation')
          $prod_id = $product_obj->get_parent_id();
        }
        $product_url = get_permalink($prod_id);
      }
    }

    if (class_exists('Woocommerce')) {
      $price_format = vpc_get_price_format(); //str_replace(html_entity_decode(htmlentities(get_woocommerce_currency_symbol())), "$", $raw_price_format);
      $decimal_separator = wc_get_price_decimal_separator();
      $symbol = get_woocommerce_currency_symbol();
      $price_separator = wc_get_price_thousand_separator();
      $price_unit = wc_get_price_decimals();
    }

    $editor = new $skin($product_id, $config_id);

    if (isset($wp_query->query_vars["edit"])) {
      $cart_item_key = $wp_query->query_vars["edit"];
      $cart_content=$woocommerce->cart->cart_contents;
      $item_content=$cart_content[$cart_item_key];
      $to_load=get_recap_from_cart_item($item_content);
    }

    // if(class_exists('Ofb')){
    //     if (isset($wp_query->query_vars["edit"])) {
    //         if (!empty($_SESSION["files_saved_name"])) {
    //             foreach($_SESSION["files_saved_name"] as $name => $data){
    //                 $to_load[$name] = $data;
    //             }
    //             var_dump($to_load);
    //         }
    //     }
    // }

    $to_load = apply_filters("vpc_config_to_load", $to_load, $product_id);
    if (isset($wp_query->query_vars["edit"]))
    $cart_item_key = $wp_query->query_vars["edit"];

    $ajax_loading = get_proper_value($vpc_settings, "ajax-loading", "No");
    $action_after_add_to_cart = get_proper_value($vpc_settings, "action-after-add-to-cart", "Yes");
    $vpc_parameters = apply_filters("vpc_data", array(
      "preload" => $to_load,
      "product" => $product_id,
      'action_after_add_to_cart' => $action_after_add_to_cart,
      'wvpc_conditional_rules' => $wvpc_conditional_rules,
      'reverse_triggers' => $reverse_triggers,
      'cart_url' => $cart_url,
      'current_product_page' => $product_url,
      'vpc_selected_items_selector' => apply_filters("vpc_selected_items_selector", ".vpc-options input:checked, .vpc-options select > option:selected"),
      'currency' => html_entity_decode(htmlentities($symbol)),
      'decimal_separator' => $decimal_separator,
      'thousand_separator' => $price_separator,
      'decimals' => $price_unit,
      'price_format' => $price_format,
      'config' => $config->settings,
      'trigger' => true,
      'enable_rules'=>get_proper_value($rules_structure, "enable_rules", false),
      'select_first_elt'=>get_proper_value($vpc_settings, "select-first-elem", "Yes"),
      'query_vars' => array('edit' => $cart_item_key)
    ));
    ?>
    <script>
    var vpc =<?php echo html_entity_decode(json_encode($vpc_parameters)); ?>;
    </script>
    <?php
    if ($ajax_loading == "Yes" && !is_admin()) {
      $editor->enqueue_styles_scripts();
      /* if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
      if (!get_option('vpc-license-key')) {
      $output = "<h2>You have not activated your license yet. Please, activate it in order to use this plugin.</h2>";
    } else {
    $output = "<div id='vpc-ajax-container' class='vpc-loading'><img src='" . VPC_URL . "public/images/preloader.gif'></div>";
  }
}else{*/
$output = "<div id='vpc-ajax-container' class=''><div id='vpc-ajax-loader-container' class='vpc-ajax-loader'><img src='".VPC_URL."/public/images/preloader.gif'></div></div>";
// }
} else {
  /* if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
  if (!get_option('vpc-license-key')) {
  $raw_output = "<h2>You have not activated your license yet. Please, activate it in order to use this plugin.</h2>";
} else {
$raw_output = $editor->display($to_load);
}
}else{*/
$raw_output = $editor->display($to_load);
// }

$output = apply_filters("vpc_output_editor", $raw_output, $product_id, $config->id);
}

return $output;
}

public function get_vpc_editor_ajax() {
  $vpc = $_POST["vpc"];
  $to_load = get_proper_value($vpc, "preload", false);
  $product_id = $_POST["vpc"]["product"];
  $config = get_product_config($product_id);
  $skin = get_proper_value($config->settings, "skin", "VPC_Default_Skin");
  $editor = new $skin($product_id);
  $raw_output = $editor->display($to_load);
  $output = apply_filters("vpc_output_editor", $raw_output, $product_id, $config->id);
  echo $output;
  //var_dump($vpc);
  die();
}

public function add_query_vars($aVars) {
  //        $aVars[] = "vpc-pid";
  $aVars[] = "edit";
  $aVars[] = "qty";
  return $aVars;
}

public function add_rewrite_rules($param) {
  GLOBAL $vpc_settings;
  GLOBAL $wp_rewrite;
  add_rewrite_tag('%vpc-pid%', '([^&]+)');

  $config_page_id = get_proper_value($vpc_settings, "config-page", false);
  if (!$config_page_id)
  return;

  $rule_str = "";
  $rule_match = 1;
  $languages = array();
  /* if (function_exists('icl_get_languages')) {
  $languages_obj = apply_filters('wpml_active_languages', null, array('skip_missing' => false ));
  $languages = array_column($languages_obj, 'language_code');

  if(function_exists('pll_languages_list'))
  {
  $languages_str = implode("|", $languages);
  $rule_str = "($languages_str)/";
  $rule_match = 2;
}
} */

if (function_exists('icl_get_languages')) {

  if (function_exists('pll_languages_list')) {
    $languages = pll_languages_list();
    $languages_str = implode("|", $languages);
    $rule_str = "($languages_str)/";
    $rule_match = 2;
  } else {
    if (function_exists('icl_object_id')) {
      $languages_obj = apply_filters('wpml_active_languages', null, array('skip_missing' => 1));
      $languages = array_column($languages_obj, 'language_code');
    }
  }
}

if (is_array($languages) && !empty($languages)) {
  foreach ($languages as $language_code) {
    $translation_id = icl_object_id($config_page_id, 'page', true, $language_code);
    $translated_page = get_post($translation_id);
    $slug = $translated_page->post_name;
    if (function_exists('pll_languages_list'))
    $wp_rewrite->add_rule("$language_code/" . $slug . '/configure/([^/]+)', 'index.php?pagename=' . $slug . '&vpc-pid=$matches[1]', 'top');
    else
    $wp_rewrite->add_rule($slug . '/configure/([^/]+)', 'index.php?pagename=' . $slug . '&vpc-pid=$matches[1]', 'top');
  }
} else {
  $wpc_page = get_post($config_page_id);
  if (is_object($wpc_page)) {
    $slug = $wpc_page->post_name;
    if (function_exists('pll_languages_list'))
    $wp_rewrite->add_rule($rule_str . $slug . '/configure/([^/]+)', 'index.php?pagename=' . $slug . '&vpc-pid=$matches[' . $rule_match . ']', 'top');
    else
    $wp_rewrite->add_rule($slug . '/configure/([^/]+)', 'index.php?pagename=' . $slug . '&vpc-pid=$matches[' . $rule_match . ']', 'top');

    $wp_rewrite->flush_rules();
  }
}
}

function init_globals() {
  global $vpc_settings;
  $vpc_settings = get_option("vpc-options");
  $_SESSION["vpc_calculated_totals"] = false;
}

function get_configure_btn() {
  $post_id = get_the_ID();
  $button = $this->get_configuration_button($post_id, true);
  if ($button)
  echo $button;
}

private function hide_add_to_cart_button_on_shop_page($product_id) {
  ?>
  <script type="text/javascript">
  jQuery.each(jQuery('[data-product_id= "<?php echo $product_id;?>"]'),function () {
    if (jQuery(this).is('.button.product_type_simple.add_to_cart_button.ajax_add_to_cart')) {
      jQuery(this).hide();
    }
  });
  </script>
  <?php
}


private function hide_add_to_cart_button($product_id,$product_type) {
  if ($product_type == "variable"){
    ?>
    <script type="text/javascript">
    jQuery('form [value="<?php echo $product_id; ?>"].single_add_to_cart_button').hide();
    </script>
    <?php
  }
  ?>
  <script type="text/javascript">
  setTimeout(function () {
    jQuery('form [data-product_id="<?php echo $product_id;?>"]').hide();
    jQuery('[value="<?php echo $product_id; ?>"]').parent().find('.add_to_cart_button').hide();
    jQuery('[value="<?php echo $product_id; ?>"]').parent().find('.single_add_to_cart_button').hide();
  },500);
  </script>
  <?php
}

private function get_configuration_button($product_id, $wrap = false) {
  global $vpc_settings;
  $output = "";
  if (class_exists('Woocommerce')) {
    ob_start();
    $metas = get_post_meta($product_id, 'vpc-config', true);

    $hide_wc_add_to_cart = get_proper_value($vpc_settings, "hide-wc-add-to-cart", "Yes");
    $hide_add_to_cart_on_shop_page = get_proper_value($vpc_settings, "hide-wc-add-to-cart-on-shop-page","Yes");
    $vpc_product_is_configurable = vpc_product_is_configurable($product_id);
    if ($vpc_product_is_configurable && $hide_add_to_cart_on_shop_page === "Yes") {
      $this->hide_add_to_cart_button_on_shop_page($product_id);
    }

    $product = wc_get_product($product_id);
    if (vpc_woocommerce_version_check())
    $product_type = $product->product_type;
    else
    $product_type = $product->get_type();
    if ($product_type == "variable") {
      $variations = $product->get_available_variations();
      if(isset($variations) && is_array($variations )){
        foreach ($variations as $variation) {
          echo $this->get_button($variation["variation_id"], $metas, $wrap, false);
          if ($vpc_product_is_configurable && $hide_wc_add_to_cart == 'Yes') {
            if (vpc_woocommerce_version_check()) {
              ?>
              <script type="text/javascript">
              jQuery('form [data-product_id="<?php echo $variation["variation_id"]; ?>"]').hide();
              jQuery('[value="<?php echo $variation["variation_id"]; ?>"]').parent().find('.add_to_cart_button').hide();
              jQuery('[value="<?php echo $variation["variation_id"]; ?>"]').parent().find('.single_add_to_cart_button').hide();
              </script>
              <?php
            } else
            $this->hide_add_to_cart_button($product_id,$product_type);
          }
        }
      }
    } else {
      echo $this->get_button($product_id, $metas, $wrap);
      if ($vpc_product_is_configurable && $hide_wc_add_to_cart == 'Yes') {
        $this->hide_add_to_cart_button($product_id,$product_type);
      }
    }
    $output = ob_get_contents();
    ob_end_clean();
  }
  return $output;
}

private function get_button($id, $metas, $wrap, $display = true) {

  $configs = get_proper_value($metas, $id, array());
  $config_id = get_proper_value($configs, "config-id", false);

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

function get_configure_btn_loop($html, $product) {
  global $vpc_settings;
  $hide_build_your_own_btn = get_proper_value($vpc_settings, "hide-build-your-own", "No");
  if ($hide_build_your_own_btn == "No") {
    $button = $this->get_configuration_button($product->get_id(), true);
    if ($button)
    $html.=$button;
  }
  return $html;
}

function set_variable_action_filters() {
  global $vpc_settings;
  $append_content_filter = get_proper_value($vpc_settings, "manage-config-page", "Yes");

  if ($append_content_filter === "Yes" && !is_admin()) {

    add_filter("the_content", array($this, "filter_content"), 99);
  }
}

function filter_content($content) {
  global $vpc_settings;
  $vpc_page_id = get_proper_value($vpc_settings, "config-page", false);
  $vpc_page_id = apply_filters("vpc_configurator_page_id", $vpc_page_id);
  if (!$vpc_page_id)
  return $content;
  if (function_exists("icl_object_id"))
  $vpc_page_id = icl_object_id($vpc_page_id, 'page', true, ICL_LANGUAGE_CODE);

  $current_page_id = get_the_ID();
  if ($vpc_page_id == $current_page_id) {
    $product_id = get_query_var("vpc-pid", false);
    $product_id = apply_filters("vpc_configurator_product_id", $product_id);
    if (class_exists('Woocommerce'))
    $product_object = wc_get_product($product_id);
    else {
      $product_id = $product_object = "";
    }
    if (!$product_id)
    $content.=__("Looks like you're trying to access the configuration page directly. This page can only be accessed by clicking on Build your own button from the product or the shop page.", "vpc");
    elseif (!$product_object->is_purchasable() || !$product_object->is_in_stock())
    $content.=__("This product is not purchasable.", "vpc");
    else
    $content.= $this->get_vpc_editor($product_id);
  }
  return $content;
}

//    public function get_design_price() {
//        $price = $_POST["total_price"];
//        echo wc_price($price);
//        die();
//    }

function vpc_get_image_url($id,$imageData)
{
  $upload_dir = wp_upload_dir();
  $generation_path = $upload_dir["basedir"] . "/VPC";
  $generation_url = $upload_dir["baseurl"] . "/VPC";
  $final_file_url="";
  if (wp_mkdir_p($generation_path)) {
    $final_file_path = $generation_path . '/canvas_' . $id . '.png';
    $final_file_url = $generation_url . '/canvas_' . $id . '.png';
    $unencoded = base64_decode($imageData);
    $fp = fopen($final_file_path, 'w');
    fwrite($fp, $unencoded);
    fclose($fp);
  }
  return $final_file_url;
}

public function add_vpc_configuration_to_cart() {
  global $woocommerce;
  $message = "";
  if (vpc_woocommerce_version_check())
  $cart_url = $woocommerce->cart->get_cart_url();
  else
  $cart_url = wc_get_cart_url();
  $cart_content=$woocommerce->cart->cart_contents;
  $product_id = $_POST["product_id"];
  if (isset($_POST["quantity"])) {
    $quantity = $_POST["quantity"];
  } else {
    $quantity = 1;
  }

  $recap = $_POST["recap"];
  //Remove empty compenents
  if (isset($recap) && !empty($recap)) {
    foreach ($recap as $key => $value) {
      if ($value === "") {
        unset($recap[$key]);
      }
    }
  }

  $custom_vars ="";
  if($_POST["custom_vars"])
  $custom_vars =$_POST["custom_vars"];

  $id = uniqid();
  $imageData = $custom_vars['preview_saved'];
  $final_file_url = "";
  if (isset($id) && isset($imageData) && !empty($id) && !empty($imageData)) {
    $final_file_url = $this->vpc_get_image_url($id,$imageData);
  }

  if (isset($final_file_url) && !empty($final_file_url)) {
    $custom_vars['preview_saved'] = $final_file_url;
  }

  if(class_exists('Ofb')){
    if (isset($_POST["form_data"])) {
      $form_data = $_POST["form_data"];
    }
  }
  $alt_products = array();
  if (isset($_POST["alt_products"]))
  $alt_products = $_POST["alt_products"];
  if (!is_array($alt_products))
  $alt_products = array();

  $proceed_addition_to_cart = apply_filters("vpc_proceed_add_to_cart", true, $_POST);
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

  $ids = get_product_root_and_variations_ids($product_id);
  $variation_id = $ids["variation-id"];
  if (isset($_SESSION['attributes'][$variation_id]) && !empty($_SESSION['attributes'][$variation_id])) {
    $custom_vars['attributes'] = $_SESSION['attributes'][$variation_id];
    unset($_SESSION['attributes'][$variation_id]);
  }
  if ($proceed_addition_to_cart && $products_are_availables) {
    if(isset($custom_vars['item_key']) && !empty($custom_vars['item_key'])){
      $newly_added_cart_item_key=$custom_vars['item_key'];
      $variation_datas=$cart_content[$newly_added_cart_item_key]['visual-product-configuration'];
      $old_custom_vars=$cart_content[$newly_added_cart_item_key]['vpc-custom-vars'];
      $new_data=  array_replace($variation_datas, $recap);
      $new_custom_data = array_replace($old_custom_vars, $custom_vars);

      $woocommerce->cart->cart_contents[$newly_added_cart_item_key]['visual-product-configuration']=$new_data;
      $woocommerce->cart->cart_contents[$newly_added_cart_item_key]['vpc-custom-vars']=$new_custom_data;
      if(class_exists('Ofb')){
        if(isset($form_data)){
          $old_form_data=$cart_content[$newly_added_cart_item_key]['form_data'];
          $new_form_data=  array_replace($old_form_data, $form_data);
          $woocommerce->cart->cart_contents[$newly_added_cart_item_key]['form_data']=$new_form_data;
        }
      }
      $woocommerce->cart ->calculate_totals();
    }
    else{
      if (vpc_woocommerce_version_check()){
        if(isset($form_data)){
          $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], $ids["variation"], array('visual-product-configuration' => $recap, 'vpc-custom-vars' => $custom_vars, 'form_data' => $form_data));
        }else{
          $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], $ids["variation"], array('visual-product-configuration' => $recap, 'vpc-custom-vars' => $custom_vars));
        }
      }
      else{
        if(isset($form_data)){
          $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], "", array('visual-product-configuration' => $recap, 'vpc-custom-vars' => $custom_vars,'form_data' => $form_data));
        }else{
          $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], "", array('visual-product-configuration' => $recap, 'vpc-custom-vars' => $custom_vars));
        }
      }
    }
    // print_r($woocommerce->cart->cart_contents);
    do_action("vpc_add_to_cart_main", $ids["product-id"], $quantity, $ids["variation-id"]);
    if (method_exists($woocommerce->cart, "maybe_set_cart_cookies"))
    $woocommerce->cart->maybe_set_cart_cookies();
    if ($newly_added_cart_item_key) {
      //Alternate products
      foreach ($alt_products as $alt_product_id) {
        $ids = get_product_root_and_variations_ids($alt_product_id);
        if (vpc_woocommerce_version_check()){
          $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], $ids["variation"], array('vpc-is-secondary-product' => true, 'main_product_cart_item_key' => $newly_added_cart_item_key));
          if (method_exists($woocommerce->cart, "maybe_set_cart_cookies"))
          $woocommerce->cart->maybe_set_cart_cookies();
        }
        else{
          $woocommerce->cart->add_to_cart($ids["product-id"], $quantity, $ids["variation-id"], "", array('vpc-is-secondary-product' => true, 'main_product_cart_item_key' => $newly_added_cart_item_key));
          if (method_exists($WC_Cart, "maybe_set_cart_cookies"))
          $WC_Cart->maybe_set_cart_cookies();
        }
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
    do_action("vpc_add_to_cart_processing", $_POST);
  }
  die();
}

function get_vpc_data_image($product_image_code, $values, $cart_item_key) {
  if ($values["variation_id"])
  $product_id = $values["variation_id"];
  else
  $product_id = $values["product_id"];
  $config = get_product_config($product_id);
  //We extract the recap from the cart item key
  $recap = get_recap_from_cart_item($values);
  if (!empty($recap)) {
    $config_image = $this->get_config_image($recap, $config->settings, $values);
    $product_image_code = $config_image;
  }
  return $product_image_code;
}

function get_vpc_data($thumbnail_code, $values, $cart_item_key) {
    global $woocommerce,$vpc_settings;
    if ($values["variation_id"])
        $product_id = $values["variation_id"];
    else
        $product_id = $values["product_id"];
    $config = get_product_config($product_id);
    //We extract the recap from the cart item key
    $recap = get_recap_from_cart_item($values);

    if (!empty($recap)) {
        if (isset($values['vpc-custom-vars']['attributes']) && !empty($values['vpc-custom-vars']['attributes'])) {
          $details = '';
            foreach ($values['vpc-custom-vars']['attributes'] as $key => $value) {
              $name = explode("_",$key);
              $details .= '<dt class="variation-'.ucfirst(end($name)).'">'.ucfirst(end($name)).':</dt>
              <dd class="variation-'.ucfirst(end($name)).'"><p>'.ucfirst($value).'</p></dd>';
            }
            $thumbnail_code .= '<dl class="variation">'.$details.'</dl>';
        }
        if(get_proper_value($vpc_settings, "hide-options-selected-in-cart","No")=="No"){
            $formatted_config = $this->get_formatted_config_data($recap, $config->settings, $values);
            $thumbnail_code.= "<div class='vpc-cart-config o-wrap'><div class='o-col xl-1-1'>" . $formatted_config . "</div> </div>";
        }
  }

  $config_url=vpc_get_configuration_url($product_id);
  $cart_content=$woocommerce->cart->cart_contents;
  $item_content=$cart_content[$cart_item_key];

  if (get_option('permalink_structure'))
  $edit_url=$config_url."?edit=$cart_item_key&qty=".$item_content['quantity'];
  else
  $edit_url=$config_url."&edit=$cart_item_key&qty=".$item_content['quantity'];
  if(isset($item_content['visual-product-configuration']))
  $thumbnail_code.='<br><a class="button alt" href="' . $edit_url . '">'.__("Edit", "vpc").'</a>';

  $thumbnail_code = apply_filters("vpc_get_config_data", $thumbnail_code, $recap,$config, $values, $cart_item_key);
  return $thumbnail_code;
}

private function get_config_image($recap, $config, $item) {
  $output = "";
  if (is_array($recap)) {
    if (isset($item['vpc-custom-vars']['preview_saved']) && !empty($item['vpc-custom-vars']['preview_saved'])) {
      $url = $item['vpc-custom-vars']['preview_saved'];
      $output="<img src='".$url."' />";
    }
    else if (isset($item['vpc-custom-data']['preview_saved']) && !empty($item['vpc-custom-data']['preview_saved'])) {
      $url = $item['vpc-custom-data']['preview_saved'];
      $output="<img src='".$url."' />";
    }
    else{
      $output = $output="<img src='".$this->get_config_image_by_image_merged($recap, $config, $item)."' />";
    }
    $output ="<div class='vpc-cart-config-image o-wrap'>$output</div>";
  }
  return $output;
}


private function get_config_image_by_image_merged($recap, $config, $item) {
  $output = "";
  $imgs = array();
  if (is_array($recap)) {
    foreach ($recap as $component => $raw_options) {
      if (is_array($raw_options)) {
        //$options=  implode (", ", $raw_options);
        foreach ($raw_options as $options) {
          $image = $this->extract_option_field_from_config($options, $component, $config, "image");
          $img_src = o_get_proper_image_url($image);
          $title = $raw_options;
          if (is_array($raw_options))
          $title = implode(", ", $raw_options);
          if (isset($img_src) && !empty($img_src)) {
            //$img_code = "<img src='$img_src' data-tooltip-title='$title'>";
            // $output.=$img_code;
            array_push($imgs, $img_src);
          }
        }
      } else {
        $options = $raw_options;
        $image = $this->extract_option_field_from_config($raw_options, $component, $config, "image");
        $img_src = o_get_proper_image_url($image);
        $title = $raw_options;
        if (is_array($raw_options))
        $title = implode(", ", $raw_options);
        if (isset($img_src) && !empty($img_src)) {
          //$img_code = "<img src='$img_src' data-tooltip-title='$title'>";
          // $output.=$img_code;
          array_push($imgs, $img_src);
        }
      }
    }
    $img_url = merge_pictures($imgs, false, true);
  }

  return $img_url;
}

public function get_formatted_config_data($recap, $config, $show_icons = true) {
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
            $img_src = o_get_proper_image_url($icon);
            $img_code = "<div>" . stripslashes($option) . "<img src='$img_src' data-tooltip-title='$option'></div>";
            //                        $img_code = "<img src='$img_src' data-tooltip-title='$option'>";
            $options_html.=$img_code;
          } else{
            $options_html.=stripslashes($option); //To escape quotes in the name
          }
        }
      } else{
        $options_html = implode(", ", $options_arr);

      }
      $option = stripslashes($option);
      $component = stripslashes($component);

      $output.="<div><strong>$component: </strong>$options_html</div>";
    }
  }
  $output.="</div>";
  return $output;
}


/*private function validate_url($url) {
$path = parse_url($url, PHP_URL_PATH);
$encoded_path = array_map('urlencode', explode('/', $path));
$url = str_replace($path, implode('/', $encoded_path), $url);
return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
}
*/

public function extract_option_field_from_config($searched_option, $searched_component, $config, $field = "icon") {
  $unslashed_searched_option = stripslashes($searched_option);
  $unslashed_searched_component = stripslashes($searched_component);
  $field = apply_filters("extracted_option_field_from_config", $field);
  if (!is_array($config))
  $config = unserialize($config);
  if(isset($config["components"])){
    foreach ($config["components"] as $i => $component) {
      if (stripslashes($component["cname"]) == $unslashed_searched_component) {
        foreach ($component["options"] as $component_option) {
          if (stripslashes($component_option["name"]) == $unslashed_searched_option) {
            if (isset($component_option[$field]))
            return $component_option[$field];
          }
        }
      }
    }
  }
  return false;
}

function save_customized_item_meta($item_id, $values, $cart_item_key) {
  global $vpc_settings;
  $store_original_config = get_proper_value($vpc_settings, "store-original-configs", "Yes");

  if ($values["variation_id"])
  $product_id = $values["variation_id"];
  else
  $product_id = $values["product_id"];

  //We extract the recap from the cart item key
  $recap = get_recap_from_cart_item($values);
  $original_config = get_product_config($product_id);
  /*if (isset($values['vpc-is-secondary-product']))
  wc_add_order_item_meta($item_id, 'vpc-is-secondary-product', $values['vpc-is-secondary-product']);*/
  if (!empty($recap) && $original_config != false) {
    wc_add_order_item_meta($item_id, 'vpc-cart-data', $recap);
    if (!empty($values['vpc-custom-vars']))
    wc_add_order_item_meta($item_id, 'vpc-custom-data', $values['vpc-custom-vars']);
    if ($store_original_config == "Yes")
    wc_add_order_item_meta($item_id, 'vpc-original-config', $original_config->settings);
  }
  // if(class_exists('Ofb')){
  //     $form_data = get_form_data_from_cart_item($values);
  //     if(!empty($form_data) && $original_config != false)
  //     wc_add_order_item_meta($item_id, 'form_data', $form_data);
  // }
}

function get_user_account_products_meta($output, $item) {
  if (isset($item["vpc-cart-data"])) {
    $original_config = vpc_get_order_item_configuration($item);
    $output.="<br>";

    if (vpc_woocommerce_version_check())
    $recap = unserialize($item["vpc-cart-data"]);
    else
    $recap = $item["vpc-cart-data"];
    if (!empty($recap)) {
      if (isset($item['vpc-custom-data']['attributes']) && !empty($item['vpc-custom-data']['attributes'])) {
        $details = '';
        foreach ($item['vpc-custom-data']['attributes'] as $key => $value) {

          $name = explode("_",$key);
          $details .= '<dt class="variation-'.ucfirst(end($name)).'">'.ucfirst(end($name)).':</dt>
          <dd class="variation-'.ucfirst(end($name)).'"><p>'.ucfirst($value).'</p></dd>';
        }
        $output .= '<dl class="variation">'.$details.'</dl>';
      }
    }

    //            foreach ($data_arr as $recap) {
    $config_image = $this->get_config_image($recap, $original_config, $item);
    $formatted_config = $this->get_formatted_config_data($recap, $original_config);
    $output.= "<div class='vpc-cart-config o-wrap'>" . $config_image . "<div class='o-col xl-2-3'>" . $formatted_config . "</div></div>";
    //            }
  }
  // if(class_exists('Ofb')){
  //     if (isset($item["form_data"])) {
  //         // var_dump($item["form_data"]);
  //         $form_data = $item["form_data"];
  //         $form_html =  $this->get_form_build_data($form_data);
  //         $output.= "<div><div class='o-col xl-2-3'>" . $form_html . "</div> </div>";
  //     }
  // }

  return $output;
}

function get_admin_products_metas($item_id, $item, $_product) {
  $output = "";
  if (isset($item["vpc-cart-data"])) {
    $original_config = vpc_get_order_item_configuration($item);
    $output.="<br>";
    if (vpc_woocommerce_version_check())
    $recap = unserialize(strip_tags($item["vpc-cart-data"]));
    else
    $recap = $item["vpc-cart-data"];
    if (!empty($recap)) {
      if (isset($item['vpc-custom-data']['attributes']) && !empty($item['vpc-custom-data']['attributes'])) {
        $details = '';
        foreach ($item['vpc-custom-data']['attributes'] as $key => $value) {

          $name = explode("_",$key);
          $details .= '<dt class="variation-'.ucfirst(end($name)).'">'.ucfirst(end($name)).':</dt>
          <dd class="variation-'.ucfirst(end($name)).'"><p>'.ucfirst($value).'</p></dd>';
        }
        $output .= '<dl class="variation">'.$details.'</dl>';
      }
    }
    //            foreach ($data_arr as $recap) {
    $config_image = $this->get_config_image($recap, $original_config, $item);
    $formatted_config = $this->get_formatted_config_data($recap, $original_config);
    $output.= "<div class='vpc-order-config o-wrap xl-gutter-8'>" . $config_image . "<div class='o-col xl-2-3'>" . $formatted_config . "</div></div>";
    //            }
  }
  echo $output;
}

private function get_product_linked_price($linked_product) {
  global $vpc_settings;
  $hide_secondary_product_in_cart = get_proper_value($vpc_settings, "hide-wc-secondary-product-in-cart", "Yes");
  if ($hide_secondary_product_in_cart == "Yes") {
    $_product = wc_get_product($linked_product);
    if (function_exists("wad_get_product_price")) {
      $option_price = wad_get_product_price($_product);
    } else {
      $option_price = $_product->get_price();
      if (strpos($option_price, ','))
      $option_price = floatval(str_replace(',', '.', $option_price));
    }
  } else
  $option_price = 0;
  return $option_price;
}

private function get_config_price($product_id, $config, $cart_item) {
  $original_config = get_product_config($product_id);
  $total_price = 0;
  $product = wc_get_product($product_id);
  if(is_array($config)){
    foreach ($config as $component => $raw_options) {
      $options_arr = $raw_options;
      if (!is_array($raw_options))
      $options_arr = array($raw_options);
      foreach ($options_arr as $option) {
        $linked_product = $this->extract_option_field_from_config($option, $component, $original_config->settings, "product");
        $option_price = $this->extract_option_field_from_config($option, $component, $original_config->settings, "price");

        if (strpos($option_price, ','))
        $option_price = floatval(str_replace(',', '.', $option_price));
        if ($linked_product) {
          $option_price = $this->get_product_linked_price($linked_product);
        }

        //We make sure we're not handling any empty priced option
        if (empty($option_price))
        $option_price = 0;

        $total_price+=$option_price;
      }
    }
  }
  return apply_filters("vpc_config_price", $total_price, $product_id, $config, $cart_item);
}

function get_cart_item_price($cart) {

  global $vpc_settings,$WOOCS;
  $hide_secondary_product_in_cart = get_proper_value($vpc_settings, "hide-wc-secondary-product-in-cart", "Yes");
  if ($_SESSION["vpc_calculated_totals"] == TRUE)
  return;
  if(is_array($cart->cart_contents)){
    foreach ($cart->cart_contents as $cart_item_key => $cart_item) {
      if ($cart_item["variation_id"])
      $product_id = $cart_item["variation_id"];
      else
      $product_id = $cart_item["product_id"];

      $recap = get_recap_from_cart_item($cart_item);
      if (isset($cart_item['vpc-is-secondary-product']) && $cart_item['vpc-is-secondary-product'] && $hide_secondary_product_in_cart == "Yes") {
        if (vpc_woocommerce_version_check())
        $cart_item['data']->price = 0;
        else
        $cart_item['data']->set_price(0);
      }

      if (vpc_woocommerce_version_check()){
        $price = $cart_item['data']->price;
      }else {
        $price = $cart_item['data']->get_price();
      }
      if($WOOCS){
        $currencies = $WOOCS->get_currencies();
        if (empty($currencies[$WOOCS->current_currency]['rate'])) {
          $price = null;
        }else {
          $price= $price / $currencies[$WOOCS->current_currency]['rate'];
        }
      }

      if (vpc_woocommerce_version_check())
      $tax_status = $cart_item['data']->tax_status;
      else
      $tax_status = $cart_item['data']->get_tax_status();

      $a_price = 0;
      if (!empty($recap)) {
        $a_price = $this->get_config_price($product_id, $recap, $cart_item);
        if(isset($tax_status) &&  $tax_status!= "taxable"){
          $a_price=vpc_apply_taxes_on_price_if_needed($a_price,$cart_item['data']);
        }
      }
      if(class_exists('Ofb')){
        if(isset($cart_item['form_data']) && !empty($cart_item['form_data'])){
          $form_data = $cart_item['form_data'];
          $a_price += get_form_data($form_data['id_ofb'],$form_data);
        }
      }
      $total =  $price + $a_price;
      if (vpc_woocommerce_version_check()){
        $cart_item['data']->price = $total;
      }
      else {
        $cart_item['data']->set_price($total);
      }
    }
  }
  $_SESSION["vpc_calculated_totals"] = TRUE;
}

function update_vpc_cart_item_price($price, $cart_item, $cart_item_key) {
  global $vpc_settings,$WOOCS;
  $hide_secondary_product_in_cart = get_proper_value($vpc_settings, "hide-wc-secondary-product-in-cart", "Yes");

  if ($cart_item["variation_id"])
  $product_id = $cart_item["variation_id"];
  else
  $product_id = $cart_item["product_id"];
  if (isset($cart_item['vpc-is-secondary-product']) && $cart_item['vpc-is-secondary-product'] && $hide_secondary_product_in_cart == "Yes")
  $price = 0;
  else {
    if (!is_cart()) {
      $_product = wc_get_product($product_id);
      $price = $_product->get_price();

      if (vpc_woocommerce_version_check())
      $tax_status = $cart_item['data']->tax_status;
      else
      $tax_status = $cart_item['data']->get_tax_status();

      $recap = get_recap_from_cart_item($cart_item);
      if (!empty($recap)) {
        $a_price = $this->get_config_price($product_id, $recap, $cart_item);
        if(isset($tax_status) &&  $tax_status!= "taxable"){
          $a_price=vpc_apply_taxes_on_price_if_needed($a_price,$_product);
        }
      }
      if(class_exists('Ofb')){
        if(isset($cart_item['form_data']) && !empty($cart_item['form_data'])){
          $form_data = $cart_item['form_data'];
          $a_price += get_form_data($form_data['id_ofb'],$form_data);
        }
      }

      if($WOOCS){
        $currencies = $WOOCS->get_currencies();
        if(isset($a_price) && !empty($a_price)){
          $a_price = $a_price * $currencies[$WOOCS->current_currency]['rate'];
        }
      }

      if(isset($a_price) && !empty($a_price)){
        $price = $price + $a_price;
      }

      $price = wc_price($price);
    }
  }
  return $price;
}

public function set_email_order_item_meta($item_id, $item, $order) {
  global $vpc_settings;
  $show_image_configured = get_proper_value($vpc_settings, "img-merged-mail", "Yes");
  $output = $config_image="";
  //if (is_order_received_page())
  //return;
  if (isset($item["vpc-cart-data"])) {
    $original_config = vpc_get_order_item_configuration($item);
    //            $output.="<br>";
    if (vpc_woocommerce_version_check())
    $recap = unserialize(strip_tags($item["vpc-cart-data"]));
    else
    $recap = $item["vpc-cart-data"];
    if (!empty($recap)) {
      if (isset($item['vpc-custom-data']['attributes']) && !empty($item['vpc-custom-data']['attributes'])) {
        $details = '';
        foreach ($item['vpc-custom-data']['attributes'] as $key => $value) {

          $name = explode("_",$key);
          $details .= '<dt class="variation-'.ucfirst(end($name)).'">'.ucfirst(end($name)).':</dt>
          <dd class="variation-'.ucfirst(end($name)).'"><p>'.ucfirst($value).'</p></dd>';
        }
        $output .= '<dl class="variation">'.$details.'</dl>';
      }
    }
    //            foreach ($data_arr as $recap) {
    if( $show_image_configured=="Yes")
    $config_image = $this->get_config_image($recap, $original_config, $item);
    $formatted_config = $this->get_formatted_config_data($recap, $original_config, false);
    $output .= "<style type='text/css'> .vpc-order-config img{width:150px;height:auto;margin:20px 0;}</style>
    <div class='vpc-order-config'>$config_image <div class='vpc-order-config-option'>" . $formatted_config . "</div></div>";

    //            }
  }
  
  echo $output;
}

/*private function get_config_image_merged($recap, $config, $item) {
$output = "";
$imgs = array();
if (is_array($recap)) {
foreach ($recap as $component => $raw_options) {
if (is_array($raw_options)) {
//$options=  implode (", ", $raw_options);
foreach ($raw_options as $options) {
$image = $this->extract_option_field_from_config($options, $component, $config, "image");
$img_src = o_get_proper_image_url($image);
$title = $raw_options;
if (is_array($raw_options))
$title = implode(", ", $raw_options);
if ($img_src) {
array_push($imgs, $img_src);
}
}
} else {
$options = $raw_options;
$image = $this->extract_option_field_from_config($raw_options, $component, $config, "image");
$img_src = o_get_proper_image_url($image);
$title = $raw_options;
if (is_array($raw_options))
$title = implode(", ", $raw_options);
if ($img_src) {
array_push($imgs, $img_src);
}
}
}


$imgs=apply_filters('vpc_get_recap_images',$imgs, $recap, $config, $item);
$img_url = merge_pictures($imgs, false, true);

}

return $img_url;
}*/

public function add_class_to_body($classes) {
  $wpc_options_settings=get_option('vpc-options');
  $active_follow_scroll_desktop = get_proper_value($wpc_options_settings, 'follow-scroll-desktop',"Yes");
  $active_follow_scroll_mobile = get_proper_value($wpc_options_settings, 'follow-scroll-mobile',"No");
  $current_ID = get_the_ID();
  $configurable = vpc_product_is_configurable($current_ID);
  if ($configurable) {
    $classes[] = 'vpc-is-configurable';
  }
  if($active_follow_scroll_desktop=="No")
  $classes[] = 'vpc-desktop-follow-scroll-disabled';
  if($active_follow_scroll_mobile=="No")
  $classes[] = 'vpc-mobile-follow-scroll-disabled';
  return $classes;
}

/*public function set_order_again_cart_item_data($datas, $item, $order) {
$item_metas = $item['item_meta'];
$recap =array();
if(isset($item_metas["vpc-cart-data"])){
if (vpc_woocommerce_version_check())
$recap = unserialize($item["vpc-cart-data"]);
else
$recap = $item["vpc-cart-data"];
}
$cart_datas['visual-product-configuration']=$recap;
return  $cart_datas;
}
*/
public function get_switcher_proper_url($url, $slug, $locale) {
  $product_id = get_query_var("vpc-pid", false);
  if ($product_id) {
    $translation_id = icl_object_id($product_id, 'product', true, $locale);
    $url.="configure/$translation_id/";
  }
  return $url;
}

public function check_product_availability($product_id, $quantity) {
  $product = wc_get_product($product_id);
  $numleft = $product->get_stock_quantity();

  if ($numleft === NULL || $numleft >= $quantity) {
    return true;
  } else {
    return false;
  }
}

public function hide_cart_item($cart_item_visible, $cart_item, $cart_item_key) {
  global $vpc_settings;
  $hide_secondary_product_in_cart = get_proper_value($vpc_settings, "hide-wc-secondary-product-in-cart", "Yes");
  if (isset($cart_item['vpc-is-secondary-product']) && $cart_item['vpc-is-secondary-product'] && $hide_secondary_product_in_cart == "Yes") {
    return false;
  } else {
    return $cart_item_visible;
  }
}

public function hide_order_item($true, $item) {
  global $vpc_settings;
  $hide_secondary_product_in_cart = get_proper_value($vpc_settings, "hide-wc-secondary-product-in-cart", "Yes");
  if (isset($item['vpc-is-secondary-product']) && $item['vpc-is-secondary-product'] && $hide_secondary_product_in_cart == "Yes") {
    return false;
  } else {
    return $true;
  }
}

function vpc_remove_secondary_products($cart_item_key) {
  global $woocommerce;
  if(is_array(WC()->cart->cart_contents)){
    foreach (WC()->cart->cart_contents as $key => $values) {
      if ((isset($values['main_product_cart_item_key'])) && (($values['main_product_cart_item_key'] == $cart_item_key) || !isset(WC()->cart->cart_contents[$values['main_product_cart_item_key']]))) {
        unset(WC()->cart->cart_contents[$key]);
      }
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

public function get_switcher_proper_url_wpml($w_active_languages) {
  $product_id = get_query_var("vpc-pid", false);
  $use_pretty_url = apply_filters("vpc_use_pretty_url", true);
  if ($product_id && is_array($w_active_languages )) {
    foreach ($w_active_languages as $lang => $element) {
      $translation_id = icl_object_id($product_id, 'product', true, $lang);
      if ($use_pretty_url)
      $w_active_languages[$lang]['url'] .= "configure/$translation_id/";
      else
      $w_active_languages[$lang]['url'] .= "?vpc-pid=$translation_id";
    }
  }
  return $w_active_languages;
}

function get_vpc_product_qty_ajax() {
  $prod_id = $_POST['prod_id'];
  $_SESSION['attributes'] = $_POST['new_variation_attributes'];
  $qty = $_POST['qty'];
  $design_url = vpc_get_configuration_url($prod_id);
  if ($qty > 1)
  $design_url = add_query_arg('qty', $qty, $design_url);
  echo $design_url;
  die();
}

public function get_vpc_config_details($item_id, $item, $order){
  $output ="";
  $original_config = vpc_get_order_item_configuration($item);
  $output.="<br>";
  if(!is_array($item["vpc-cart-data"])){
    $recap = unserialize($item["vpc-cart-data"]);
  }
  else{
    $recap = $item["vpc-cart-data"];
  }

  if (!empty($recap)) {
    if (isset($item['vpc-custom-data']['attributes']) && !empty($item['vpc-custom-data']['attributes'])) {
      $details = '';
      foreach ($item['vpc-custom-data']['attributes'] as $key => $value) {

        $name = explode("_",$key);
        $details .= '<dt class="variation-'.ucfirst(end($name)).'">'.ucfirst(end($name)).':</dt>
        <dd class="variation-'.ucfirst(end($name)).'"><p>'.ucfirst($value).'</p></dd>';
      }
      $output .= '<dl class="variation">'.$details.'</dl>';
    }
  }
  $formatted_config = VPC_Public::get_formatted_config_data($recap, $original_config, false);
  $output .= "<div class='vpc-order-config o-wrap xl-gutter-8'><div class='o-col xl-2-3'>" . $formatted_config . "</div></div>";
  echo $output;

}

public function get_form_build_data($form_data){
  $form_html = '<strong>Your form data</strong><br>';
  foreach($form_data as $index=>$data){
    if(is_array($data)){
      foreach($data as $opt => $opt_data){
        $form_html .= $index. ' :<br>' . $opt. ' :'. $opt_data.'<br>';
      }
    }else{
      if('id_ofb' === $index ){
        $form_html .= ' ';
      }elseif(!empty($data)){
        $form_html .= $index. ' :' .$data.'<br>';
      }
    }
  }
  return $form_html;
}

public function update_price_from_form(){
  if(isset($_POST['form_data'])){
    $form_data = $_POST['form_data'];
    $form_id = $form_data['id_ofb'];
    $price = get_form_data($form_data['id_ofb'],$form_data);
  }
  echo $price;
  die();
}

}
