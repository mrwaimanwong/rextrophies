<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

/**
* Description of class-vpc-default-skin
*
* @author HL
*/
class VPC_Default_Skin {

  public $product;
  public $product_id;
  public $settings;
  public $config;

  public function __construct($product_id = false, $config = false) {
    if ($product_id) {
      if(vpc_woocommerce_version_check())
      $this->product = new WC_Product($product_id);
      else
      $this->product = wc_get_product($product_id);
      $this->product_id = $product_id;

      $this->config = get_product_config($product_id);
    } else if ($config) {
      $this->config = new VPC_Config($config);
    }
  }

  public function display($config_to_load = array()) {
    $this->enqueue_styles_scripts();
    ob_start();

    if (!$this->config || empty($this->config))
    return __("No valid configuration linked to this product.", "vpc");

    $skin_name = get_class($this);

    $config = $this->config->settings;

    $options_style = "";
    $components_aspect = get_proper_value($config, "components-aspect", "closed");
    if ($components_aspect == "closed")
    $options_style = "display: none";
    $product_id="";
    if(class_exists('Woocommerce')){
      if(vpc_woocommerce_version_check())
      $product_id = $this->product->id;
      else
      $product_id = $this->product->get_id();
      do_action("vpc_before_container", $config, $product_id, $this->config->id);
    }
    $conf_desc=get_configurator_description($config);
    $conf_desc=apply_filters('vpc_configurator_description',$conf_desc,$config, $product_id);

    ?>
    <div id="vpc-container" class="o-wrap <?php echo $skin_name; ?>" data-curr="<?php echo (class_exists('Woocommerce'))? get_woocommerce_currency_symbol(): ''; ?>">
      <?php
      do_action("vpc_before_inside_container", $config, $product_id, $this->config->id);
      ?>
      <div class="o-col conf_desc"><?php echo $conf_desc;?></div>
      <div class="o-col xl-1-3 lg-1-3 md-1-1 sm-1-1" id="vpc-components">
        <?php
      
        do_action("vpc_before_components", $config,$product_id);
        if(isset($config["components"] )){
          foreach ($config["components"] as $component_index => $component) {
            $this->get_components_block($component, $options_style, $config_to_load);
          }
        }
        do_action("vpc_after_components", $config);
        ?>
      </div>
      <div class="o-col xl-2-3 lg-2-3 md-1-1 sm-1-1" id="vpc-preview-wrap">
        <!-- <div > -->
        <!--<div class="vpc-preview-toggle">
        <i class="fa fa-eye" ></i>
      </div>-->
      <?php vpc_get_price_container($this->product->get_id());
      $preview_html='<div id="vpc-preview"></div>';
      $preview_html=  apply_filters("vpc_preview_container",$preview_html, $this->product->get_id(), $this->config->id);
      echo  $preview_html;
      ?>

      <?php do_action("vpc_after_preview_area", $config, $this->product->get_id(), $this->config->id) ?>
      <!-- </div> -->
    </div>
    <div id="vpc-form-builder-wrap">
      <?php
      if(class_exists('Ofb')){
        if(isset($config['ofb_id'])){
          $form_builder_id = $config['ofb_id'];
          $form = display_form_builder($form_builder_id,$config_to_load);
          echo $form;
        }
      }
      ?>
    </div>
    <div id="vpc-bottom-limit"></div>
    <div class="vpc-debug">
      <?php do_action("vpc_container_end", $config) ?>
    </div>
  </div>

  <?php echo vpc_get_action_buttons($this->product_id); ?>
  <div id="debug"></div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}

private function get_components_block($component, $options_style, $config_to_load = array()) {
  global $vpc_settings,$WOOCS;
  $skin_name = get_class($this);
  $c_icon = "";
  $options="";
  if(isset($component["options"]))
  $options = $component["options"];
  if ($options) {
    $options = sort_options_by_group($options);
  }
  $component_id = "component_" . sanitize_title(str_replace(' ', '', $component["cname"]));
  $component_id = get_proper_value($component, "component_id", $component_id);

  //We make sure we have an usable behaviour
  $handlable_behaviours = vpc_get_behaviours();
  //        var_dump($handlable_behaviours);
  if (!isset($handlable_behaviours[$component["behaviour"]]))
  $component["behaviour"] = "radio";

  if ($component["cimage"])
  $c_icon = "<img src='" . o_get_proper_image_url($component["cimage"]) . "'>";

  $components_attributes_string = apply_filters("vpc_component_attributes", "data-component_id = '$component_id'", $this->product_id, $component);
  ?>
  <div id = '<?php echo $component_id; ?>' class="vpc-component" <?php echo $components_attributes_string ?>>

    <div class="vpc-component-header">
      <?php
      echo "$c_icon<span style='display: inline-block;'><span>" . $component["cname"] . "</span>";
      ?>
      <span class="vpc-selected txt"><?php _e('none', 'vpc'); ?></span></span>
      <span class="vpc-selected-icon"><img width="24" src="" alt="..."></span>

    </div>
    <div class="vpc-options" style="<?php echo $options_style; ?>">
      <?php
      do_action('vpc_' . $component["behaviour"] . "_begin", $component, $skin_name);
      $current_group = "";
      if(!is_array($options)||empty($options))
      _e("No option detected for the component. You need at least one option per component.", "vpc");
      else
      {
        $product_id = $this->product_id;//get_query_var("vpc-pid", false);
        //WAD compatibility
        $discount_rate=0;
        if(function_exists("vpc_get_discount_rate"))
        $discount_rate=  vpc_get_discount_rate($product_id);

        foreach ($options as $option_index => $option) {
          if ($option["name"] == "") {
            if ($option_index == count($options) - 1) {
              echo "</div>";
            }
            continue;
          }
          $o_name = $component["cname"];
          if (($option["group"] != $current_group) || ($option_index == 0)) {
            if ($option_index !== 0){
              if($component["behaviour"]=="dropdown")
              echo "</select>";
              echo "</div>";
            }
            echo "<div class='vpc-group'><div class='vpc-group-name'>" . $option["group"] . "</div>"; //."</div>";// . "<br>";
            if($component["behaviour"]=="dropdown"){
              ?>
              <select name="<?php echo $component["cname"]; ?>" id="<?php echo $component_id; ?>">
                <option value=''>Choose an option ...</option>
                <?php
              }
            }
            $current_group = $option["group"];
            $opt_img_id=get_proper_value($option, "image");
            $o_image = o_get_proper_image_url($opt_img_id);
            $opt_icon_id=get_proper_value($option, "icon");
            $o_icon = o_get_proper_image_url($opt_icon_id);
            $name_tooltip = get_proper_value($vpc_settings, "view-name");
            $price_tooltip = get_proper_value($vpc_settings, "view-price");




            //                    $input_id = uniqid();
            //                    $label_id = "cb$input_id";

            $checked = "";
            if ($config_to_load && isset($config_to_load[$component["cname"]])) {
              $saved_options = $config_to_load[$component["cname"]];
              if ((is_array($saved_options) && in_array($option["name"], $saved_options)) || ($option["name"] == $saved_options)
              )
              $checked = "checked='checked'";
            }
            else if (isset($option["default"]) && $option["default"] == 1)
            $checked = "checked='checked' data-default='1'";

            $price = get_proper_value($option, "price", 0);

            if(strpos($price,','))
            $price=floatval(str_replace(',', '.',$price));
            if(""==$price)
            $price=0;
            $price=$price-$price*$discount_rate;
            $price= vpc_apply_taxes_on_price_if_needed($price, $this->product);

            $linked_product = get_proper_value($option, "product", false);
            $formated_price_raw=0;
            if ($linked_product) {
              $price=0;
              if(class_exists('Woocommerce')){
                if(vpc_woocommerce_version_check())
                $product = new WC_Product($linked_product);
                else
                $product = wc_get_product($linked_product);
                if (!$product->is_purchasable() || ($product->managing_stock() && !$product->is_in_stock())) {
                  if ($option_index == count($options) - 1) {
                    echo "</div>";
                  }
                  continue;
                }
                $price = $product->get_price();
                $price= vpc_apply_taxes_on_price_if_needed($price, $product);
              }
            }

            if ($WOOCS) {
              $currencies = $WOOCS->get_currencies();
              $price = $price * $currencies[$WOOCS->current_currency]['rate'];
            }

            $price=  apply_filters("vpc_options_price",$price, $option, $component, $this);

            $formated_price_raw = wc_price($price);

            if (apply_filters("vpc_option_visibility", 1, $option) != 1) {
              if ($option_index == count($options) - 1) {
                echo "</div>";
              }
              continue;
            }

            $formated_price = strip_tags($formated_price_raw);
            $option_id = "component_" . sanitize_title(str_replace(' ', '', $component["cname"])) . "_group_" . sanitize_title(str_replace(' ', '', $option["group"])) . "_option_" . sanitize_title(str_replace(' ', '', $option["name"]));
            $option_id = get_proper_value($option, "option_id", $option_id);
            $comp_index = get_proper_value($component, "c_index", 0);
            $customs_datas=" data-index='$comp_index'";
            $customs_datas =  apply_filters("vpc_options_customs_datas",$customs_datas,$option,$component);

            switch ($component["behaviour"]) {
              case 'radio':
              case 'checkbox':
              $input_type = "radio";
              if ($component["behaviour"] == "checkbox") {
                $o_name.="[]";
                $input_type = "checkbox";
              }

              if($name_tooltip == "Yes")
              $tooltip = $option["name"];
              else
              $tooltip = '';
              if($price_tooltip == "Yes"){
                if(strpos($formated_price, '-')!== false|| strpos($formated_price, '+')!== false)
                $tooltip.=" $formated_price";
                else
                $tooltip.=" +$formated_price";
              }
              if (!empty($option["desc"]))
              $tooltip.= " (" . $option["desc"] . ")";

              $label_id = "cb$option_id";
              $tooltip =  apply_filters("vpc_options_tooltip",$tooltip,$price,$option,$component);

              ?>
              <div class="vpc-single-option-wrap" data-oid="<?php echo $option_id; ?>" >
                <input id="<?php echo $option_id; ?>" type="<?php echo $input_type; ?>" name="<?php echo esc_attr($o_name); ?>" value="<?php echo esc_attr($option["name"]); ?>" data-img="<?php echo $o_image; ?>" data-icon="<?php echo $o_icon; ?>" data-price="<?php echo $price; ?>" data-product="<?php echo isset($option["product"])?$option["product"]:""; ?>" data-oid="<?php echo $option_id; ?>" <?php echo $checked.' '.$customs_datas; ?>>
                <label id="<?php echo $label_id; ?>" for="<?php echo $option_id; ?>" data-o-title="<?php echo esc_attr($tooltip); ?>" class="custom"></label>
                <style>
                #<?php echo $label_id; ?>:before
                {
                  background-image: url("<?php echo $o_icon; ?>");
                }
                </style>
              </div>
              <?php
              break;
              case 'dropdown':
              $selected = "";
              if ($config_to_load && isset($config_to_load[$component["cname"]])) {
                $saved_options = $config_to_load[$component["cname"]];
                if ((is_array($saved_options) && in_array($option["name"], $saved_options)) || ($option["name"] == $saved_options)
                )
                $selected = "selected";
              }
              else if (isset($option["default"]) && $option["default"] == 1)
              $selected = "selected";
              ?>
              <option id="<?php echo $option_id; ?>" value="<?php echo $option["name"]; ?>" data-img="<?php echo $o_image; ?>" data-icon="<?php echo $o_icon; ?>" data-price="<?php echo $price; ?>" data-product="<?php echo isset($option["product"])?$option["product"]:""; ?>" data-oid="<?php echo $option_id; ?>" <?php echo $selected.' '.$customs_datas; ?>>
                <?php echo $option["name"]; ?>
              </option>
              <?php
              break;
              default:
              //                            do_action('vpc_'.$skin_name.'_' . $component["behaviour"], $component);
              do_action('vpc_' . $component["behaviour"], $option, $o_image, $price, $option_id, $component, $skin_name, $config_to_load,$this->config->settings);
              break;
            }
            do_action('vpc_each_' . $component["behaviour"].'_end', $option, $o_image, $price, $option_id, $component, $skin_name, $config_to_load);
            if ($option_index == count($options) - 1) {
              if($component["behaviour"]=="dropdown")
              echo "</select>";
              echo "</div>";
            }
            $current_group = $option["group"];
          }
        }

        do_action('vpc_' . $component["behaviour"] . '_end', $component, $this->config, $skin_name);
        ?>
      </div>
    </div>
    <?php
  }

  public function enqueue_styles_scripts() {
    if (is_admin())
    vpc_enqueue_core_scripts();
    wp_enqueue_style("vpc-default-skin", VPC_URL . 'public/css/vpc-default-skin.css', array(), VPC_VERSION, 'all');
    wp_enqueue_style("o-flexgrid", VPC_URL . 'admin/css/flexiblegs.css', array(), VPC_VERSION, 'all');
    wp_enqueue_style("FontAwesome", VPC_URL . 'public/css/font-awesome.min.css', array(), VPC_VERSION, 'all');
    wp_enqueue_style("o-tooltip", VPC_URL . 'public/css/tooltip.min.css', array(), VPC_VERSION, 'all');

    wp_enqueue_script("o-tooltip", VPC_URL . 'public/js/tooltip.min.js', array('jquery'), VPC_VERSION, false);
    wp_enqueue_script("vpc-default-skin", VPC_URL . 'public/js/vpc-default-skin.js', array('jquery', 'vpc-public'), VPC_VERSION, false);
    wp_localize_script("vpc-default-skin", 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_script("o-serializejson", VPC_URL . 'public/js/jquery.serializejson.min.js', array('jquery'), VPC_VERSION, false);

    // wp_enqueue_script("jquery-scroll-follow", VPC_URL . 'public/js/jquery.simple-scroll-follow.min.js', array('jquery'), VPC_VERSION, true);
    // wp_enqueue_script("vpc-scroll-follow", VPC_URL . 'public/js/vpc-follow-scroll.js', array('jquery', 'vpc-public'), VPC_VERSION, true);
  }

}
