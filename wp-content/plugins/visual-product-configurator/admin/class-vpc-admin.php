<?php
/**
* The admin-specific functionality of the plugin.
*
* @link       http://www.orionorigin.com
* @since      1.0.0
*
* @package    Vpc
* @subpackage Vpc/admin
*/

/**
* The admin-specific functionality of the plugin.
*
* Defines the plugin name, version, and two examples hooks for how to
* enqueue the admin-specific stylesheet and JavaScript.
*
* @package    Vpc
* @subpackage Vpc/admin
* @author     ORION <help@orionorigin.com>
*/
class VPC_Admin {

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
  * @param      string    $plugin_name       The name of this plugin.
  * @param      string    $version    The version of this plugin.
  */
  public function __construct($plugin_name, $version) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
  * Register the stylesheets for the admin area.
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
    wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vpc-admin.css', array(), $this->version, 'all');
    wp_enqueue_style("o-flexgrid", plugin_dir_url(__FILE__) . 'css/flexiblegs.css', array(), $this->version, 'all');
    wp_enqueue_style("o-ui", plugin_dir_url(__FILE__) . 'css/UI.css', array(), $this->version, 'all');
    wp_enqueue_style("o-tooltip", VPC_URL . 'public/css/tooltip.min.css', array(), $this->version, 'all');
    wp_enqueue_style("o-bs-modal-css", VPC_URL . 'admin/js/modal/modal.min.css', array(), $this->version, 'all');

    wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
  }

  /**
  * Register the JavaScript for the admin area.
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
    if(is_vpc_admin_screen()){
      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/vpc-admin.js', array('jquery'), $this->version, false);
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script("o-admin", plugin_dir_url(__FILE__) . 'js/o-admin.js', array('jquery', 'jquery-ui-sortable'), $this->version, false);
      wp_localize_script("o-admin", 'home_url', home_url("/"));
      wp_enqueue_script("o-tooltip", VPC_URL . 'public/js/tooltip.min.js', array('jquery'), $this->version, false);
      //        wp_enqueue_script("o-lazyload", VPC_URL . 'admin/js/jquery.lazyload.min.js', array('jquery'), $this->version, false);
      wp_enqueue_script('o-modal-js', VPC_URL . 'admin/js/modal/modal.min.js', array('jquery'), false, false);
      wp_enqueue_script("jquery-serializejson", VPC_URL . 'public/js/jquery.serializejson.min.js', array('jquery'), $this->version, false);

      //Set string translation for js scripts
      $string_translations = array(
        "reverse_cb_label" => __("Enable reverse rule", 'vpc'),
        "group_conditions_relation" => __("Conditions relationship", "vpc"),
      );
      wp_localize_script($this->plugin_name, 'string_translations', $string_translations);
    }
  }

  /**
  * Initialize the plugin sessions
  */
  function init_sessions() {
    if (!session_id()) {
      session_start();
    }
  }

  /**
  * added a custom column
  *
  */

  public function get_vpc_screen_layout_columns($columns) {
    $columns['vpc-config'] = 1;
    return $columns;
  }


  public function get_vpc_config_screen_layout() {
    return 1;
  }

  /**
  * added a metabox order
  *
  */

  public function metabox_order($order) {
    $order["advanced"] = "vpc-config-preview-box,vpc-config-settings-box,vpc-config-conditional-rules-box,submitdiv";
    return $order;
  }

  /**
  * Builds all the plugin menu and submenu
  */
  public function get_menu() {
    $parent_slug = "edit.php?post_type=vpc-config";
    if(class_exists('Ofb')){
      add_submenu_page('edit.php?post_type=vpc-config', __('Form Builder', 'vpc'), __('Form Builder', 'vpc'), 'manage_product_terms', 'edit.php?post_type=ofb', false);
    }
    add_submenu_page($parent_slug, __('Settings', 'vpc'), __('Settings', 'vpc'), 'manage_product_terms', 'vpc-manage-settings', array($this, 'get_vpc_settings_page'));
    add_submenu_page($parent_slug, __('Getting Started', 'vpc'), __('Getting Started', 'vpc'), 'manage_product_terms', 'vpc-getting-started', array($this, 'get_vpc_getting_started_page'));
  }

  /**
  * create settings tabs
  *
  */

  function create_tabs_by_addon($base_settings)
  {
    $section_begin = array();
    $section_end = array();

    foreach ($base_settings as $key => $value) {
      if ($value['type'] === 'sectionbegin') {
        array_push($section_begin,$key);
      }else {
        if ($value['type'] === 'sectionend') {
          array_push($section_end,$key);
        }
      }
    }
    $tabs_group = array();
    foreach ($section_begin as $key => $value) {
      if ( ! isset($section_end[$key])) {
        $section_end[$key] = 0;
      }
      $length = ($section_end[$key] - $value) + 1;
      $new = array_slice($base_settings,$value,$length);
      if(!empty($new))
      $tabs_group[$new[0]['id']] = $new;
    }

    return $tabs_group;
  }

  /**
  * add settings tabs contents
  *
  */

  function vpc_create_settings_tabs_contents($group,$active_tab,$active_onglet)
  {
    if (isset($group) && !empty($group) && $active_tab == $active_onglet) {
      ?> <div class="vpc-addons"> <?php
      echo o_admin_fields($group);
      ?> </div> <?php
    }
  }

  /**
  * create settings tabs headers
  *
  */

  function vpc_create_settings_tabs_header($tabs_group,$active_tab)
  {
    if (isset($tabs_group) && !empty($tabs_group)) {
      foreach ($tabs_group as $group_key => $group_value) {
        if ($group_key === 'vpc-options-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-manage-settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Visual Products Configurator', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-email-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-email-container' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-email-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Request a quote Addon', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-cta-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-cta-container') ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-cta-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Custom Text Add On', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-mva-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-mva-container') ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-mva-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Multiple Views Addon', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-sfla-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-sfla-container') ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-sfla-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Save For Later Addon', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-upload-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-upload-container') ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-upload-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Upload Image', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-sci-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-sci-container') ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-sci-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Save Configuration Image Add-on', 'vpc' ); ?></a><?php
        }elseif ($group_key === 'vpc-dcod-container') {
          ?><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-manage-settings&section=vpc-dcod-container') ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-dcod-container' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Defined Currencies and Options Details', 'vpc' ); ?></a><?php
        }else {
          do_action('vpc_add_settings_onglet',$tabs_group,$group_key,$group_value,$active_tab);
        }
      }
    }
  }

  /**
  * create of body settings tabs
  *
  */

  function vpc_create_settings_tabs_body($tabs_group,$active_tab)
  {
    if (isset($tabs_group) && !empty($tabs_group)) {

      foreach ($tabs_group as $group_key => $group_value) {
        if (strstr($group_key,'vpc-options')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-manage-settings');
        }elseif (strstr($group_key,'vpc-cta')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-cta-container');
        }elseif (strstr($group_key,'vpc-email') || strstr($group_key,'vpc-rqa')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-email-container');
        }elseif (strstr($group_key,'vpc-mva')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-mva-container');
        }elseif (strstr($group_key,'vpc-sfla')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-sfla-container');
        }elseif (strstr($group_key,'vpc-upload')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-upload-container');
        }elseif (strstr($group_key,'vpc-sci')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-sci-container');
        }elseif (strstr($group_key,'vpc-dcod')) {
          $this->vpc_create_settings_tabs_contents($group_value,$active_tab,'vpc-dcod-container');
        }else{
          do_action('vpc_add_settings_body',$tabs_group,$group_key,$group_value,$active_tab);
        }
      }
    }
    global $o_row_templates;
  }

  /**
  * settings page
  *
  */

  public function get_vpc_settings_page() {
    $old_data = get_option("vpc-options");

    //Get the section or the page name
    $session;
    if(isset($_GET['section'])){
      $session = $_GET['section'];
    }elseif (isset($_GET['page'])) {
      $session = $_GET['page'];
    }

    if ((isset($_POST["vpc-options"]) && !empty($_POST["vpc-options"]))) {
      $datas = $_POST["vpc-options"];

      //Affecter une valeur par défaut lorsqu'une case des options social share est décochée
      if (isset($session) && $session === "ssa-global-container") {
        if (!isset($datas["facebook"])) {
          $datas["facebook"] = "0";
        }
        if (!isset($datas["twitter"])) {
          $datas["twitter"] = "0";
        }
        if (!isset($datas["pinterest"])) {
          $datas["pinterest"] = "0";
        }
        if (!isset($datas["googleplus"])) {
          $datas["googleplus"] = "0";
        }
        if (!isset($datas["whatsapp"])) {
          $datas["whatsapp"] = "0";
        }
        if (!isset($datas["mail"])) {
          $datas["mail"] = "0";
        }
      }

      if (is_array($old_data))
      $new_datas = array_merge($old_data,$datas);
      else
      $new_datas=$datas;
      update_option("vpc-options", $new_datas);
      global $wp_rewrite;
      $wp_rewrite->flush_rules();
    }
    ?>
    <div class="wrap woocommerce wc_addons_wrap">
      <h1><?php _e("Visual Products Configurator Settings", "vpc"); ?></h1>
      <form method="POST" action="" class="mg-top">
        <div class="postbox" id="vpc-options-container">
          <?php
          $begin = array(
            'type' => 'sectionbegin',
            'id' => 'vpc-options-container',
            'table' => 'options',
          );
          $args = array(
            "post_type" => "page",
            "nopaging" => true,
          );
          $pages = get_posts($args);
          $pages_ids = array();
          foreach ($pages as $page) {
            $pages_ids[$page->ID] = $page->post_title;
          }
          $configuration_page = array(
            'title' => __('Configuration page', 'vpc'),
            'name' => 'vpc-options[config-page]',
            'type' => 'select',
            'options' => $pages_ids,
            'default' => '',
            'class' => 'chosen_select_nostd',
            'desc' => __('Page where all products are configured.', 'vpc'),
          );

          $automatically_append = array(
            'title' => __('Manage the configuration page', 'vpc'),
            'name' => 'vpc-options[manage-config-page]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            // 'class' => 'chosen_select_nostd',
            'desc' => __('If Yes, the plugin will handle the content of the configuration page. If No, use the shortcode [wpb_builder] to display the configurator INSIDE the configuration page.', 'vpc'),
          );

          $cart_actions_arr = array(
            "none" => __("None", "vpc"),
            "refresh" => __("Refresh", "vpc"),
            "redirect" => __("Redirect to cart page", "vpc"),
            "redirect_to_product_page" => __("Redirect to product page", "vpc"),
          );

          $hide_qty_box = array(
            'title' => __('Hide quantity box', 'vpc'),
            'name' => 'vpc-options[hide-qty]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Hide quantity box on configurator page?', 'vpc'),
          );

          $hide_wc_add_to_cart_btn = array(
            'title' => __('Hide woocommerce add to cart button ', 'vpc'),
            'name' => 'vpc-options[hide-wc-add-to-cart]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Should the plugin hide the default woocommerce add to cart button on configurables products?', 'vpc'),
          );

          $hide_build_your_own_btn = array(
            'title' => __('Hide Build your own button on shop page', 'vpc'),
            'name' => 'vpc-options[hide-build-your-own]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'No',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Should the plugin hide the build your own button on shop page?', 'vpc'),
          );

          $hide_wc_add_to_cart_btn_on_shop_page = array(
            'title' => __('Hide woocommerce add to cart button on shop page', 'vpc'),
            'name' => 'vpc-options[hide-wc-add-to-cart-on-shop-page]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'No',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Should the plugin hide the default woocommerce add to cart button on shop page?', 'vpc'),
          );

          $hide_secondary_product_in_cart = array(
            'title' => __('Hide linked products cart page ', 'vpc'),
            'name' => 'vpc-options[hide-wc-secondary-product-in-cart]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Should the plugin hide the products linked to the options in the cart page?', 'vpc'),
          );

          $hide_options_selected_in_cart = array(
            'title' => __('Hide options selected in cart', 'vpc'),
            'name' => 'vpc-options[hide-options-selected-in-cart]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'No',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Should the plugin hide the options selected in the cart page?', 'vpc'),
          );

          $action_in_cart = array(
            'title' => __('Action after addition to cart', 'vpc'),
            'name' => 'vpc-options[action-after-add-to-cart]',
            'type' => 'select',
            'options' => $cart_actions_arr,
            'default' => '',
            'class' => 'chosen_select_nostd',
            'desc' => __('What should happen once the customer adds the configured product to the cart.', 'vpc'),
          );

          $ajax_load = array(
            'title' => __('Ajax Loading', 'vpc'),
            'name' => 'vpc-options[ajax-loading]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'No',
            'desc' => __('Load the editor via ajax. If enabled, this will speed up configuration page load by building configurator after the configuration page is fully loaded.', 'vpc'),
          );

          $active_follow_scroll_desktop = array(
            'title' => __('Scroll follow', 'vpc'),
            'name' => 'vpc-options[follow-scroll-desktop]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            'desc' => __('Gives the preview the ability to follow scroll so that it can always remain visible.', 'vpc'),
          );

          $active_follow_scroll_mobile = array(
            'title' => __('Scroll follow on mobile', 'vpc'),
            'name' => 'vpc-options[follow-scroll-mobile]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'No',
            'desc' => __('If enabled, the preview scroll follow will remain active on mobile.', 'vpc'),
          );

          $option_view_name_tooltip = array(
            'title' => __('View option name on tooltip', 'vpc'),
            'name' => 'vpc-options[view-name]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            'desc' => __('If enabled, the option name will be seen on the option tooltip', 'vpc'),
          );

          $option_view_price_tooltip = array(
            'title' => __('View option price on tooltip', 'vpc'),
            'name' => 'vpc-options[view-price]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            'desc' => __('If enabled, the option price will be seen on the option tooltip', 'vpc'),
          );
          $product_link = array(
            'title' => __('Links products options', 'vpc'),
            'name' => 'vpc-options[product-link]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'No',
            //'class' => 'chosen_select_nostd',
            'desc' => __('Do you want to link options to products?', 'vpc'),
          );

          $store_original_config = array(
            'title' => __('Store original configuration data in orders', 'vpc'),
            'name' => 'vpc-options[store-original-configs]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            'desc' => __('If enabled, the plugin will store a snapshot of the original configuration data in the orders table everytime an order is made.', 'vpc'),
          );
          $image_configured_in_mail = array(
            'title' => __('Display the configured image in the mail', 'vpc'),
            'name' => 'vpc-options[img-merged-mail]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            'desc' => __('Do you want to add the configured image to the mail', 'vpc'),
          );
          $select_first_option = array(
            'title' => __('Select automatically the first element of the component when rules are applied', 'vpc'),
            'name' => 'vpc-options[select-first-elem]',
            'type' => 'radio',
            'options' => array("Yes" => "Yes", "No" => "No"),
            'default' => 'Yes',
            'desc' => __('Enable/Disable the automatic selection of the first element of the component when the rules are applied', 'vpc'),
          );
          $envato_username = array(
            'title' => __('Envato Username', 'vpc'),
            'name' => 'vpc-options[envato-username]',
            'type' => 'text',
            'desc' => __('Your envato username', 'vpc'),
            'default' => '',
          );

          $envato_api_key = array(
            'title' => __('Secret API Key', 'vpc'),
            'name' => 'vpc-options[envato-api-key]',
            'type' => 'text',
            'desc' => __('You can find your secret api key by following the instructions <a href="https://www.youtube.com/watch?v=KnwumvnWAIM" target="blank">here</a>.', 'vpc'),
            'default' => '',
          );

          $envato_purchase_code = array(
            'title' => __('Purchase Code', 'vpc'),
            'name' => 'vpc-options[purchase-code]',
            'type' => 'text',
            'desc' => __('You can find your purchase code by following the instructions <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="blank">here</a>.', 'vpc'),
            'default' => '',
          );

          $end = array('type' => 'sectionend');
          $base_settings = apply_filters("vpc_global_settings", array(
            $begin,
            $configuration_page,
            $hide_qty_box,
            $hide_wc_add_to_cart_btn,
            $hide_wc_add_to_cart_btn_on_shop_page,
            $hide_secondary_product_in_cart,
            $hide_options_selected_in_cart,
            $hide_build_your_own_btn,
            $automatically_append,
            $active_follow_scroll_desktop,
            $active_follow_scroll_mobile,
            $action_in_cart,
            $ajax_load,
            $option_view_name_tooltip,
            $product_link,
            $option_view_price_tooltip,
            $store_original_config,
            $image_configured_in_mail,
            $select_first_option,
            $envato_username,
            $envato_api_key,
            $envato_purchase_code,
            $end
          ));

          $tabs_group = $this->create_tabs_by_addon($base_settings);
          $active_tab = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : 'vpc-manage-settings';
          global $o_row_templates;
          ?>
          <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php
            $this->vpc_create_settings_tabs_header($tabs_group,$active_tab);
            ?>
          </nav>
          <div class="vpc-getting-started addons-featured">
            <?php
            $this->vpc_create_settings_tabs_body($tabs_group,$active_tab);
            ?>
          </div>
        </div>
        <script>
        var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
        ?>
        <input type="submit" class="button button-primary button-large" value="Save">
      </form>
    </div>
    <?php
  }


  /**
  * Checks if the database needs to be upgraded
  */
  function run_vpc_db_updates_requirements() {
    //Checks db structure for v2.0
    $old_configs = get_option("product_configurator");
    if (!empty($old_configs)) {
      ?>
      <div class="updated" id="vpc-updater-container">
        <strong><?php echo _e("Woocommerce Visual Product Configurator database update required.", "vpc"); ?></strong>
        <div>
          <?php echo _e("Hi! This version of the Woocommerce Visual Product Configuratormade some changes in the way it's data are stored. So in order to work properly, we just need you to click on the \"Run Updater\" button to move your old settings to the new structure. ", "vpc"); ?><br>
          <input type="button" value="<?php echo _e("Run the updater", "vpc"); ?>" id="vpc-run-updater" class="button button-primary"/>
          <div class="loading" style="display:none;"></div>
        </div>
      </div>
      <style>
      #vpc-updater-container
      {
        padding: 3px 17px;
        /*font-size: 13px;*/
        line-height: 36px;
        margin-left: 0px;
        border-left: 5px solid #e14d43 !important;
      }
      #vpc-updater-container.done
      {
        border-color: #7ad03a !important;
      }
      #vpc-run-updater {
        background: #e14d43;
        border-color: #d02a21;
        color: #fff;
        -webkit-box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
        box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
      }

      #vpc-run-updater:focus, #vpc-run-updater:hover {
        background: #dd362d;
        border-color: #ba251e;
        color: #fff;
        -webkit-box-shadow: inset 0 1px 0 #e8756f;
        box-shadow: inset 0 1px 0 #e8756f;
      }
      .loading
      {
        background: url("<?php echo VPC_URL; ?>/admin/images/spinner.gif") 10% 10% no-repeat transparent;
        background-size: 111%;
        width: 32px;
        height: 40px;
        display: inline-block;
      }
      </style>
      <script>
      //jQuery('.loading').hide();
      jQuery('#vpc-run-updater').click('click', function () {
        var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
        if (confirm("It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now")) {
          jQuery('.loading').show();
          jQuery.post(
            ajax_url,
            {
              action: 'run_updater'
            },
            function (data) {
              jQuery('.loading').hide();
              jQuery('#vpc-updater-container').html(data);
              jQuery('#vpc-updater-container').addClass("done");
            }
          );
        }

      });
      </script>
      <?php
    }
  }

  /**
  * run the updater
  */

  public function run_vpc_updater() {
    ob_start();
    $old_configs = get_option("product_configurator");
    if (!empty($old_configs))
    $old_configs = stripcslashes($old_configs);
    //            var_dump($old_configs);
    if (!empty($old_configs)) {
      $decoded_configs = json_decode($old_configs);
      //                var_dump($decoded_configs);
      $config_matches = array();
      foreach ($decoded_configs as $old_config_id => $config) {
        $error_occured = false;
        $new_config_meta = array(
          "components" => array(),
          "conditional_rules" => array(
            "enable_rules" => "",
            "groups" => array()
          )
        );
        $ids_matches = array();
        $cid = 0; //Component count
        foreach ($config->data as $old_component_id => $component) {
          //                        var_dump($old_component_id);

          $oid = 0; //Option count
          $new_component["cname"] = $component->name;
          $new_component["cimage"] = $this->get_attachment_id_from_url($component->layer_icon);
          $ids_matches["layer_wrap_$old_component_id"] = "component_" . sanitize_title(str_replace(' ', '', $new_component["cname"]));
          if (!$new_component["cimage"]) {
            //                        $error_occured = true;
            $new_component["cimage"] = "";
            echo "Can't retrieve image $component->layer_icon in component $component->name for configuration $config->name. <strong>$config->name</strong>.<br>";
          }
          $new_component["behaviour"] = "radio";
          $new_component["options"] = array();
          //Not using the groups
          $default_img = $component->defaul_img;
          if (property_exists($component, 'img_s')) {
            $this->extract_old_options($component->img_s, $new_component, $old_component_id, $ids_matches, $default_img, $cid, $oid);
          } else {
            foreach ($component->category as $group) {
              $this->extract_old_options($group->img, $new_component, $old_component_id, $ids_matches, $default_img, $cid, $oid, $group->name);
            }
          }
          array_push($new_config_meta["components"], $new_component);
          $cid++;
        }

        if ($config->conditional_rules) {
          $this->extract_old_conditionnal_rules($config, $new_config_meta, $ids_matches);
        }

        // Create post object
        if (!$error_occured) {
          $post_args = array(
            'post_title' => $config->name,
            'post_type' => 'vpc-config',
            'post_status' => 'publish',
          );

          $new_config_id = wp_insert_post($post_args);
          if (!is_wp_error($new_config_id)) {
            update_post_meta($new_config_id, "vpc-config", $new_config_meta, true);
            $config_matches[$old_config_id] = $new_config_id;
          }

          echo "<strong>$config->name</strong> successfully imported.<br>";
        }
      }

      $this->run_products_migration($config_matches);
      $this->run_options_migration();

      delete_option("product_configurator");
      update_option("product_configurator_old", $old_configs);
    }
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;
    die();
  }

  /**
  * run  Options migrations
  */

  private function run_options_migration() {
    $old_options = get_option("wvpc_options");
    $action_after_add_to_cart = get_proper_value($old_options, "action_after_add_to_cart", "none");
    $custom_page = get_proper_value($old_options, "customizer_page", "");

    $new_options = array(
      "config-page" => $custom_page,
      "manage-config-page" => "No",
      "action-after-add-to-cart" => $action_after_add_to_cart,
    );
    update_option("vpc-options", $new_options);
  }

  /**
  * run  products migrations
  */

  private function run_products_migration($config_matches) {
    $args = array(
      'post_type' => array('product', 'product_variation'),
      'meta_key' => 'wvpc-meta',
      'post_status' => 'any',
    );
    $custom_products = get_posts($args);
    foreach ($custom_products as $custom_product) {
      $product_obj = wc_get_product($custom_product->ID);
      $product_class = get_class($product_obj);
      if ($product_class == "WC_Product_Simple") {
        $root_pid = $product_obj->get_id();
        $variable_pid = $product_obj->get_id();
      } else {
        $root_pid = $product_obj->get_id();
        $variable_pid = $custom_product->ID;
      }
      $meta = get_post_meta($custom_product->ID, "wvpc-meta", true);
      $old_config = isset($meta["product_config"]) ? $meta["product_config"] : array();

      //We don't handle the configs with failure in migration
      if (!isset($config_matches[$old_config]))
      continue;
      $new_config_id = $config_matches[$old_config];

      $new_meta = get_post_meta($root_pid, "vpc-config", true);
      if (empty($new_meta))
      $new_meta = array();

      $new_meta[$variable_pid] = array("config-id" => $new_config_id);
      update_post_meta($root_pid, "vpc-config", $new_meta);
    }
  }

  /**
  * extract old options to new options
  */

  private function extract_old_options($group, &$new_component, $old_component_id, &$ids_matches, $default_img, $cid, &$oid, $group_name = "") {
    foreach ($group as $old_option) {

      $ids_matches["wvpc_img_$old_component_id" . "_$old_option->img_id"] = "component_" . sanitize_title(str_replace(' ', '', $new_component["cname"])) . "_group_" . sanitize_title(str_replace(' ', '', $group_name)) . "_option_" . sanitize_title(str_replace(' ', '', $old_option->img_name));
      $linked_product = "";
      if (( property_exists($old_option, 'linked_to_product') && !empty($old_option->linked_to_product) && $old_option->linked_to_product == "checked") && ( property_exists($old_option, 'linked_product') && !empty($old_option->linked_product))) {
        $linked_product = $old_option->linked_product;
      }

      $new_option = array(
        "group" => $group_name,
        "name" => $old_option->img_name,
        "desc" => "",
        "icon" => $old_option->icon_id,
        "image" => $old_option->img_id,
        "price" => $old_option->img_price,
        "product" => $linked_product,
      );
      if ($default_img == $old_option->img_id)
      $new_option["default"] = 1;
      array_push($new_component["options"], $new_option);
      $oid++;
    }
  }

  /**
  * extract conditionnal rules
  */

  private function extract_old_conditionnal_rules($config, &$new_config_meta, $ids_matches) {
    $new_config_meta["conditional_rules"]["enable_rules"] = (property_exists($config->conditional_rules, 'enable_rules'));
    foreach ($config->conditional_rules->groups as $group_index => $rules_group) {
      $new_config_meta["conditional_rules"]["groups"][$group_index]["result"] = array(
        "action" => $rules_group->result->action,
        "apply_on" => $ids_matches[$rules_group->result->apply_on],
        "scope" => $rules_group->result->scope
      );
      if (property_exists($rules_group, 'apply_reverse'))
      $new_config_meta["conditional_rules"]["groups"][$group_index]["apply_reverse"] = $rules_group->apply_reverse;
      $new_config_meta["conditional_rules"]["groups"][$group_index]["rules"] = array();
      foreach ($rules_group->rules as $old_rule) {
        $new_rule = array(
          "option" => $ids_matches[$old_rule->option],
          "trigger" => $old_rule->trigger);
          array_push($new_config_meta["conditional_rules"]["groups"][$group_index]["rules"], $new_rule);
        }
      }
    }

    /**
    *  get attachment id from url
    */

    private function get_attachment_id_from_url($url) {
      $info = pathinfo($url);
      $attachment_id = $this->wp_get_attachment_by_post_name($info["filename"]);
      //If the attachment does not exist on that server, we download and register it
      if (!$attachment_id) {
        $attachment_id = $this->import_attachment($url);
      }

      return $attachment_id;
    }

    /**
    *  import attachment
    */

    private function import_attachment($file_url) {
      if (!$file_url)
      return false;
      $upload_dir = wp_upload_dir();
      $filename = $upload_dir["path"] . "/" . basename($file_url);
      $res = file_put_contents($filename, file_get_contents($file_url));
      if (!$res) {
        return false;
      }

      // Check the type of file. We'll use this as the 'post_mime_type'.
      $filetype = wp_check_filetype(basename($filename), null);

      // Get the path to the upload directory.
      $wp_upload_dir = wp_upload_dir();

      // Prepare an array of post data for the attachment.
      $attachment = array(
        'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content' => '',
        'post_status' => 'inherit'
      );

      // Insert the attachment.
      $attach_id = wp_insert_attachment($attachment, $filename);
      //
      //            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
      require_once( ABSPATH . 'wp-admin/includes/image.php' );
      //
      //            // Generate the metadata for the attachment, and update the database record.
      $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
      wp_update_attachment_metadata($attach_id, $attach_data);
      return $attach_id;
    }

    /**
    *  get attachment by post name
    */

    private function wp_get_attachment_by_post_name($post_name) {
      $args = array(
        'post_per_page' => 1,
        'post_type' => 'attachment',
        'name' => trim($post_name),
      );
      $get_posts = new Wp_Query($args);

      if (isset($get_posts->posts[0]))
      return $get_posts->posts[0]->ID;
      else
      return false;
    }

    /**
    *  add configurable product column in  product tables
    */


    function get_product_columns($defaults) {
      $defaults['configuration'] = __('Configurable', 'vpc');
      return $defaults;
    }


    function get_products_columns_values($column_name, $id) {
      if ($column_name === 'configuration') {
        $is_configurable = vpc_product_is_configurable($id);
        if ($is_configurable)
        _e("Yes", "vpc");
        else
        _e("No", "vpc");
      }
    }

    public function get_max_input_vars_php_ini() {
      $total_max_normal = ini_get('max_input_vars');
      $msg = __("Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", "vpc");
      ?>
      <script type="text/javascript">
      var o_max_input_vars = <?php echo $total_max_normal; ?>;
      var o_max_input_msg = "<?php echo $msg; ?>";
      </script>
      <?php
    }

    /**
    *  getting started page
    */


    public function get_vpc_getting_started_page() {
      ?>
        <h1 class="">
            <?php _e("About Visual Products Configurator", "vpc"); ?>
        </h1>
        <?php
            $active_tab = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : 'vpc-getting-started';
        ?>
        <div class="wrap woocommerce wc_addons_wrap">
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-getting-started' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-getting-started' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Browse our extensions', 'vpc' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-getting-started&section=vpc-tutorials' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-tutorials' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Videos tutorials', 'vpc' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-getting-started&section=vpc-about-orion' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-about-orion' ? 'nav-tab-active' : ''; ?>"><?php _e( 'About us', 'vpc' ); ?></a>
            </nav>
            <div class="vpc-getting-started addons-featured">
            <?php
            if( $active_tab == 'vpc-getting-started' ) {
            ?>
            <div class="vpc-addons">
                <div class="vpc-getting-started-title">
                    <h3>Add more features to your product configurator to create the sales machine</h3>
                </div>
                <div class="addons-banner-block-items">
                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                          <img  class="addons-img" src="<?php echo VPC_URL; ?>/admin/images/vpc-ct.png" alt="Custom Text addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>Custom Text</h3>
                            <p>Allows the customer to add a custom text with a custom color and font to the preview area which will be sent with his order.</p>
                            <div class="vpc-addons-buttons">
                                <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/demo/custom-text-configuration/" target="_blank">
                                  Live preview
                                </a>
                                <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-custom-text-add-on/21098606?s_rank=4?ref=orionorigin" target="_blank">
                                  $25
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                            <img src="<?php echo VPC_URL; ?>/admin/images/vpc-mv.png" alt="multi views addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>Multiple Views</h3>
                            <p>Allow the customer to see his custom product under multiple views and angles, which are configured by the shop manager.</p>

                            <div class="vpc-addons-buttons">
                              <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/demo/multi-views-configuration/" target="_blank">
                                Live preview
                              </a>

                              <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-multiple-views-addon/21098558?s_rank=5?ref=orionorigin" target="_blank">
                                $28
                              </a>

                            </div>
                        </div>
                    </div>

                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                            <img src="<?php echo VPC_URL; ?>/admin/images/vpc-sfl.png" alt="Save for Later addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>Save For Later</h3>
                            <p>Gives the users the possibility to save their personalized products for future usage in their account.</p>
                            <div class="vpc-addons-buttons">
                                <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/demo/save-for-later-configuration/" target="_blank">
                                  Live preview
                                </a>
                                <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-save-for-later-addon/21098722?s_rank=1?ref=orionorigin" target="_blank">
                                  $25
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                            <img src="<?php echo VPC_URL; ?>/admin/images/vpc-raq.png" alt="Request a quote addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>Request A Quote</h3>
                            <p>Allows the customer to request a quote about a customized product and purchase later if needed.</p>
                            <div class="vpc-addons-buttons">
                              <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/demo/request-a-quote-configuration/" target="_blank">
                                Live preview
                              </a>
                              <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-products-configurator-request-a-quote-addon/21098694?s_rank=2?ref=orionorigin" target="_blank">
                                $25
                              </a>
                            </div>
                        </div>
                    </div>

                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                            <img src="<?php echo VPC_URL; ?>/admin/images/vpc-uci.png" alt="Upload image addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>Upload Image</h3>
                            <p>Allows the customer to upload one or multiple pictures on his custom product which will show up on the preview area.</p>

                            <div class="vpc-addons-buttons">
                                <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/demo/upload-image-configuration/" target="_blank">
                                  Live preview
                                </a>

                                <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-upload-image/21098653?s_rank=3?ref=orionorigin" target="_blank">
                                  $28
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                          <img src="<?php echo VPC_URL; ?>/admin/images/save_preview.png" alt="Save preview addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                          <h3>Save preview</h3>
                          <p>Allow your customers to download the flattened image of their designs for use outside the product builder.</p>

                          <div class="vpc-addons-buttons">
                            <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/headphones-save-output/" target="_blank">
                              Live preview
                            </a>

                            <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-save-preview-addon/21881361?s_rank=1" target="_blank">
                              $25
                            </a>
                          </div>
                        </div>
                    </div>
                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                            <img src="<?php echo VPC_URL; ?>/admin/images/form_builder.png" alt="Form builder addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                            <h3>Form Builder</h3>
                            <p>A form builder designed to work as add-on for ORION extensions only, not as an independant form builder plugin.</p>

                            <div class="vpc-addons-buttons">
                              <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/demo/configuration-with-form/" target="_blank">
                                Live preview
                              </a>

                              <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-form-builder-addon/21872047?s_rank=1" target="_blank">
                                $28
                              </a>
                            </div>
                        </div>
                    </div>
                    <div class="addons-banner-block-item vpc-addon">
                        <div class="addons-banner-block-item-icon">
                          <img src="<?php echo VPC_URL; ?>/admin/images/vpc-ss.png" alt="Social Share addon" />
                        </div>
                        <div class="addons-banner-block-item-content">
                          <h3>Social Share</h3>
                          <p>Allows your customers to share their configured products to facebook; twitter, pinterest, google,whatsapp and by mail.</p>

                          <div class="vpc-addons-buttons">
                            <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/configuration-with-social-share-demo/">
                              Live preview
                            </a>
                            <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-social-sharing-addon/22094775?s_rank=1">
                              $25
                            </a>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <br><br>

              <div class="vpc-addons">
                <div class="vpc-getting-started-title">
                  <h3>Make your configurator more beautiful than ever using new layouts</h3>
                </div>
                  
                <div class="addons-banner-block-items">
                    <div class="addons-banner-block-item vpc-addon">
                      <div class="addons-banner-block-item-icon">
                        <img  class="addons-img" src="<?php echo VPC_URL; ?>/admin/images/vpc-lomnava.png" alt="Lom-nava skins" />
                      </div>
                      <div class="addons-banner-block-item-content">
                        <h3>Skins Lom-nava</h3>
                        <p>A beautiful mutliple steps skin that will instantly enhance the look and feel of your configurator.</p>
                        <div class="vpc-addons-buttons">
                          <a class="addons-button addons-button-solid live-preview" href="https://configuratorsuiteforwp.com/demo/lom-nava-configurator" target="_blank">
                            Live preview
                          </a>

                          <a class="addons-button addons-button-solid" href="https://codecanyon.net/item/lom-nava-skin-for-visual-product-configurator/21124537?s_rank=3" target="_blank">
                            $25
                          </a>
                        </div>
                      </div>
                    </div>
                    <div class="addons-banner-block-item vpc-addon">
                    <div class="addons-banner-block-item-icon">
                      <img  class="addons-img" src="<?php echo VPC_URL; ?>/admin/images/Ouando.png" alt="Ouando skins" />
                    </div>
                    <div class="addons-banner-block-item-content">
                      <h3>Skins Ouando</h3>
                      <p>A beautiful slideshows skin that will instantly reveal and complete the look and feel of your configurator.</p>
                      <div class="vpc-addons-buttons">
                        <a class="addons-button addons-button-solid live-preview" href="https://demos.configuratorsuiteforwp.com/ouando-skin-demo/" target="_blank">
                          Live preview
                        </a>

                        <a class="addons-button addons-button-solid" href="https://codecanyon.net/user/orionorigin/portfolio" target="_blank">
                          $28
                        </a>
                      </div>
                    </div>
                  </div>  
                </div>
              </div>

              <?php

            }

            if( $active_tab == 'vpc-tutorials' ) {
              ?>
              <div class="vpc-tutorials">
                <div class="postbox" id="youtube-video-container">
                  <div class="videos_youtube">
                    <!--<iframe src="https://www.youtube.com/embed/2auCs0EBqjE?list=PLC9GLMXokPgXW3mYmXYJc-QstNGgF173d" frameborder="0" allowfullscreen></iframe>-->
                    <iframe width="1440" height="480" src="https://www.youtube.com/embed/kvq9yD2IKX0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>


                  </div>
                </div>
              </div>
              <?php
            }

            if( $active_tab == 'vpc-about-orion' ) {
              ?>
              <div>
                <h3>Our other plugins</h3>
              </div>

              <div class="vpc-about-us pubs">
                <div class="pub-plugin vpc-gs-half vpc-block">
                  <div class="vpc-addon-section-title-container vpc-addon-section-description">
                    <h2 class="vpc-addon-section-title"><?php _e("Woocommerce Product Designer", "vpc"); ?></h2>
                    <p class="vpc-addon-section-subtitle">
                      <?php _e("A powerful web to print solution which helps your customers design or customize logos, shirts, business cards and any prints before the order.", "vpc"); ?>
                    </p>

                    <a class="button" href="https://designersuiteforwp.com/products/woocommerce-product-designer/"><?php _e("From: $61", "vpc"); ?></a>
                  </div>
                </div>

                <div class="pub-plugin vpc-gs-half wad-block">
                  <div class="vpc-addon-section-title-container vpc-addon-section-description">
                    <h2 class="vpc-addon-section-title"><?php _e("Woocommerce All Discounts", "vpc"); ?></h2>
                    <p class="vpc-addon-section-subtitle">
                      <?php _e("Woocommerce All Discounts is a groundbreaking extension <br> that helps you manage bulk
                      or wholesale pricing, customers roles or groups based offers, or....", "vpc"); ?>
                    </p>

                    <a class="button" href="https://discountsuiteforwp.com/"><?php _e("From: $33", "vpc"); ?></a>
                  </div>
                </div>

              </div>

              <div class="clearfix"></div>

              <br><br><br>
              <div>
                <h3>Our themes</h3>
              </div>

              <div class="vpc-about-us pubs">
                <div class="pub-theme popo-block">
                  <img src="<?php echo VPC_URL; ?>/admin/images/gp.png">

                  <div>
                    <h2 class="vpc-addon-section-title"><?php _e("Grand Popo is the official shop theme for Visual Product Configurator", "vpc"); ?></h2>

                    <p class="vpc-addon-section-subtitle">
                      <?php _e("Grand-Popo is a powerful ecommerce solution for creating large scale online stores, complete with advanced e-commerce marketing & up selling solutions for WordPress.
                      Perfect for any electronic store, drones shop, fashion & clothing megastore, food markets and any other WordPress shop you can think of.", "vpc"); ?>
                    </p>

                    <div>
                      <a class="button" href="http://demos.orionorigin.com/grand-popo/01/free-trial/?utm_source=VPC%20Pro&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php _e("Get free version", "vpc"); ?></a>

                      <a class="button" href="http://demos.orionorigin.com/grand-popo/?utm_source=VPC%20Pro&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php _e("Live preview", "vpc"); ?></a>
                    </div>
                  </div>
                </div>

                <div class="pub-theme porto-block">
                  <div>
                    <h2 class="vpc-addon-section-title"><?php _e("Porto Novo is the official shop theme for <br> Woocommerce Product Designer", "vpc"); ?></h2>

                    <p class="vpc-addon-section-subtitle">
                      <?php _e("Clean, modern, responsive and highly customizable theme built for any web to print WooCommerce store. It allows your clients to design any customizable product online such as their ideal logo, business cards, greeting cards, shirts, photo frames, calendars and any item which can be personalized online preceeding purchase..", "vpc"); ?>
                    </p>

                    <div>
                      <!--a class="button" href="http://demos.orionorigin.com/grand-popo/01/free-trial/?utm_source=VPC%20Pro&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php //_e("Get free version", "vpc"); ?></a-->

                      <a class="button" href="https://themeforest.net/item/portonovo-portfolio-woocommerce-creative-wordpress-theme/full_screen_preview/19192592?_ga=2.222542175.537187201.1511283225-1782336826.1511283225/?utm_source=VPC%20Pro&utm_medium=cpc&utm_campaign=Porto-Novo&utm_content=Getting%20Started"><?php _e("Live preview", "vpc"); ?></a>
                    </div>
                  </div>

                  <img src="<?php echo VPC_URL; ?>/admin/images/pn.png">
                </div>
              </div>

              <?php

            }

            ?>

            <!---->
            <div class="rating-block">
              <a href="https://wordpress.org/support/plugin/visual-products-configurator-for-woocommerce/reviews/#new-post">
                <span class="rating">
                  <?php _e("If you like <span>Visual Product Configurator</span> please leave us a <img src='" . VPC_URL . "/admin/images/rating.png'> rating. A huge thanks in advance!", "vpc"); ?>
                </span>
              </a>
            </div>

          </div> <!--End first container-->

        </div> <!--End global getting-started-page container-->

        <?php
      }

      /**
      * Redirects the plugin to the about page after the activation
      */
      function vpc_redirect() {
        if (get_option('vpc_do_activation_redirect', false)) {
          delete_option('vpc_do_activation_redirect');
          wp_redirect(admin_url('edit.php?post_type=vpc-config&page=vpc-getting-started'));
        }
      }

      //afficher le bouton d'activation de la license
      function get_license_activation_notice() {
        if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
          if (!get_option('vpc-license-key')) {
            ?>
            <div class="notice notice-error">
              <p><?php _e('<strong>Visual products configurator</strong>: You have not activated your license yet. Please activate it in order to get the plugin working.', 'sample-text-domain'); ?></p>
              <a class="button" id="vpc-activate"><?php _e("Activate", "vpc"); ?></a><img style="display:none;" id="spinner" src="<?php echo plugin_dir_url(__FILE__) . 'images/spinner.gif'; ?> ">
              <p></p>
              <div id="license-message"></div>
            </div>
            <?php
          }
        }
      }

      /**
      *  checks if the license is active
      */
      function activate_license() {
        $options = get_option('vpc-options');
        if (isset($options['purchase-code'])) {
          $purchase_code = $options['purchase-code'];
          $site_url = get_site_url();
          $code = $_POST['code'];
          $plugin_name = ORION_PLUGIN_NAME;
          $url = "https://www.orionorigin.com/service/olicenses/v1/license/?purchase-code=" . $purchase_code . "&siteurl=" . urlencode($site_url) . "&name=" . $plugin_name . "&code=" . $code;
          $args=array('timeout' => 60);
          $response = wp_remote_get($url, $args);

          if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
            die();
          }
          if (isset($response["body"])) {
            $answer = $response["body"];
          }

          if (is_array(json_decode($answer, true))) {
            $data = json_decode($answer, true);
            update_option('vpc-license-key', $data['key']);
            echo "200";
          } else {
            echo $answer;
          }
        } else {
          echo ("Purchase code not found. Please, set your purchase code in the plugin's settings. ");
        }


        die();
      }

      /**
      *  checks if the license is valide
      */
      function o_verify_validity() {
        $options = get_option('vpc-options');
        if (isset($options['purchase-code']) && $options['purchase-code'] != ""  ) {
          $purchase_code = $options['purchase-code'];
          $site_url = get_site_url();
          $url = "https://www.orionorigin.com/service/olicenses/v1/checking/?purchase-code=" . $purchase_code . "&siteurl=" . urlencode($site_url);
          $args=array('timeout' => 60);
          $response = wp_remote_get($url, $args);

          if (!is_wp_error($response)) {
            if (isset($response["body"]) && intval($response["body"]) != 200) {
              delete_option("vpc-license-key");
            }
          }
        } else {
          if (get_option("vpc-license-key")) {
            delete_option("vpc-license-key");
          }
        }
      }

      /**
      *  checks suscribe email
      */

      function vpc_subscribe(){
        $email = $_POST['email'];

        if (preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $email)) {
          $url = "https://configuratorsuiteforwp.com/service/osubscribe/v1/subscribe/?email=" . $email;
          $args=array('timeout' => 60);
          $response = wp_remote_get($url, $args);

          if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
            die();
          }
          if (isset($response["body"])) {
            $answer = $response["body"];
            if($answer == "true" ){
              update_option('o-vpc-subscribe', "subscribed");
              echo $answer;
            }else{
              echo $answer;
            }

            die();
          }
        } else {
          echo 'Please enter a valid email address';
          die();
        }
      }

      /**
      *  get subscribe notice
      */

      function vpc_get_subscription_notice() {
        $screen = get_current_screen();
        if (isset($screen->base) &&
        (
          false !== strpos($screen->base, 'vpc') || false !== strpos($screen->post_type, 'vpc') || 'product' === $screen->post_type || 'shop_order' === $screen->post_type || 'vpc-config' === $screen->post_type
          )
        ) {
          delete_transient("vpc-hide-notice");
          if (!get_option('o-vpc-subscribe') && get_transient("vpc-hide-notice") != "hide") {
            ?>
            <div id="subscription-notice" class="notice notice-info">
              <div id="plug-logo-text" >
                <img id="plug-logo" style="height:50px; width: 50px"src="<?php echo VPC_URL; ?>/admin/images/vpc-logo.png">
                <p>
                  <?php _e('<strong>Visual products configurator</strong>: Sign up now to receive new releases notices and important bugs fixes directly<br> into your inbox! ', 'vpc'); ?>
                </p>
              </div>
              <div id="plug-sucribe-form">
                <input type="email" id="o_user_email" name="usermail" placeholder="your email here">
                <img id="vpc-subscribe-loader" style="display:none;" src="<?php echo VPC_URL; ?>/admin/images/loader.gif" >
                <button id="vpc-subscribe"><?php _e("Subscribe", "vpc"); ?></button>
                <a id="vpc-dismiss"><?php _e("Not now", "vpc"); ?></a>
              </div>
            </div>
            <?php
          }
          ?>
          <div id="subscription-success-notice" class="notice notice-info is-dismissible" style="display:none;">
            <img src="<?php echo VPC_URL; ?>/admin/images/vpc-logo.png">
            <div> <?php _e('<strong>Visual products configurator</strong>: Thank you for subscribing! ', 'vpc'); ?></div>
          </div>
          <?php
        }
      }

      /**
      * hide notice
      */

      function vpc_hide_notice(){
        set_transient('vpc-hide-notice', "hide", 2 * WEEK_IN_SECONDS);
        echo 'ok';
        die();
      }

      /**
      * Runs the new version check and upgrade process
      * @return \VPC_Updater
      */
      function vpc_get_updater() {
        do_action('vpc_before_init_updater');
        require_once( VPC_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'updaters' . DIRECTORY_SEPARATOR . 'class-vpc-updater.php' );
        $updater = new VPC_Updater();
        $updater->init();
        require_once( VPC_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'updaters' . DIRECTORY_SEPARATOR . 'class-vpc-updating-manager.php' );
        $updater->setUpdateManager(new VPC_Updating_Manager(VPC_VERSION, $updater->versionUrl(), VPC_MAIN_FILE));
        do_action('vpc_after_init_updater');
        return $updater;
      }

    }
