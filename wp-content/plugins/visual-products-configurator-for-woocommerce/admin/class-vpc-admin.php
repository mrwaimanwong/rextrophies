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
        wp_enqueue_style('vpc-admin', plugin_dir_url(__FILE__) . 'css/vpc-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vpc-admin.min.css', array(), $this->version, 'all');
        wp_enqueue_style("o-flexgrid", plugin_dir_url(__FILE__) . 'css/flexiblegs.css', array(), $this->version, 'all');
        wp_enqueue_style("o-ui", plugin_dir_url(__FILE__) . 'css/UI.css', array(), $this->version, 'all');
        wp_enqueue_style("o-tooltip", VPC_URL . 'public/css/tooltip.min.css', array(), $this->version, 'all');
        wp_enqueue_style("o-bs-modal-css", VPC_URL . 'admin/js/modal/modal.min.css', array(), $this->version, 'all');
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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/vpc-admin.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script("o-admin", plugin_dir_url(__FILE__) . 'js/o-admin.min.js', array('jquery', 'jquery-ui-sortable'), $this->version, false);
        wp_localize_script("o-admin", 'home_url', Orion_Library::get_medias_root_url("/"));
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

    /**
     * Initialize the plugin sessions
     */
    function init_sessions() {
        if (!session_id()) {
            session_start();
        }
    }

    public function get_vpc_screen_layout_columns($columns) {
        $columns['vpc-config'] = 1;
        return $columns;
    }

    public function get_vpc_config_screen_layout() {
        return 1;
    }

    public function metabox_order($order) {
        $order["advanced"] = "vpc-config-preview-box,vpc-config-settings-box,vpc-config-conditional-rules-box,submitdiv";
        return $order;
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function get_menu() {
        $parent_slug = "edit.php?post_type=vpc-config";
        add_submenu_page($parent_slug, __('Settings', 'vpc'), __('Settings', 'vpc'), 'manage_product_terms', 'vpc-manage-settings', array($this, 'get_vpc_settings_page'));
        add_submenu_page($parent_slug, __('Getting Started', 'vpc'), __('Getting Started', 'vpc'), 'manage_product_terms', 'vpc-getting-started', array($this, 'get_vpc_getting_started_page'));
        //add_submenu_page($parent_slug, __('Add-ons', 'vpc'), __('Add-ons & Support', 'vpc'), 'manage_product_terms', 'vpc-manage-add-ons', array($this, 'get_vpc_addons_page'));
    }

    public function get_vpc_settings_page() {
        if ( isset($_REQUEST['vpc-options_nonce'] ) &&  wp_verify_nonce($_REQUEST['vpc-options_nonce'], 'vpc-options_nonce' ) ) 
        {
            $posts_datas  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            if ((isset($posts_datas["vpc-options"]) && !empty($posts_datas["vpc-options"])) && current_user_can('manage_product_terms')) {
                $options=vpc_array_sanitize($posts_datas["vpc-options"]);
                if(is_array($options)){
                    $esc_vpc_options=array();
                    $vpc_options=$options;
                    foreach($vpc_options as $key=>$vpc_option)
                        $esc_vpc_options[$key]=sanitize_text_field(esc_html($vpc_option));
                    update_option("vpc-options", $esc_vpc_options);
                }
                global $wp_rewrite;
                $wp_rewrite->flush_rules(); 
            }
	} 
        ?>
        <div class="wrap cf">
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
                    
                 

                    $action_in_cart = array(
                        'title' => __('Action after addition to cart', 'vpc'),
                        'name' => 'vpc-options[action-after-add-to-cart]',
                        'type' => 'select',
                        'options' => $cart_actions_arr,
                        'default' => '',
                        'class' => 'chosen_select_nostd',
                        'desc' => __('What should happen once the customer adds the configured product to the cart.', 'vpc'),
                    );

                    $end = array('type' => 'sectionend');
                    $settings = apply_filters("vpc_global_settings", array(
                        $begin,
                        $configuration_page,
                        $hide_qty_box,
                        $action_in_cart,
                        $end
                    ));
                    echo Orion_Library::o_admin_fields($settings);
                    ?>
                </div>
                <input type="hidden" name="vpc-options_nonce" value="<?php echo wp_create_nonce( 'vpc-options_nonce' ); ?>" />
                <input type="submit" class="button button-primary button-large" value="Save">
            </form>
        </div>
        <?php
    }

    public function get_vpc_addons_page() {
    ?>
        <div class="wrap cf"></div>
    <?php
    }
    
    public function get_vpc_getting_started_page() {
    ?>
         <div class="wrap cf"> 
            <h1 class="">
                <?php _e("About Visual Products Configurator", "vpc"); ?>
            </h1>
            <div class="vpc-getting-started">
                <div class="postbox vpc-gs-half" id="vpc-presentation">
                    <div class="vpc-addon-section-title-container vpc-addon-section-description">
                        <h2 class="vpc-addon-section-title"><?php _e("Visual Products Configurator", "vpc"); ?></h2>
                        <p class="vpc-addon-section-subtitle">
                            <?php _e("A smart and flexible extension <br> which lets you setup any customizable <br> product your customers can configure <br> visually prior to purchase.", "vpc");?>
                        </p>
                        <a class="button" href="https://www.woocommerceproductconfigurator.com/"><?php _e("From: $60", "vpc"); ?></a>
                    </div>
                </div>
                <div class="postbox vpc-gs-half" id="youtube-video-container">
                    <div class="videos_youtube">
                        <iframe src="https://www.youtube.com/embed/2auCs0EBqjE?list=PLC9GLMXokPgXW3mYmXYJc-QstNGgF173d" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div class="vpc-getting-started">
                <div class="vpc-getting-started-body">
                    <h2 class="vpc-addon-section-title"><?php _e("Pro version features", "vpc"); ?></h2>
                    <span class="vpc-addon-section-title"><?php _e("From services to content, there’s no limit to what you can sell with Visual Product Configurator", "vpc"); ?></span>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/logic.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Conditional logic", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Allows you to automatically show or hide some options or components based on the customer selection.", "vpc"); ?></span>
                    </div>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/multiple.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Multiple options selection", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Allows the selection of multiple options within the same component.", "vpc"); ?></span>
                    </div>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/linked.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Linked products", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Allows you to link existing products to an option in order to trigger everything related to the linked products once the order is made.", "vpc"); ?></span>
                    </div>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/priority.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Priority support", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Get help from our support team within the next two hours after submitting your ticket..", "vpc"); ?></span>
                    </div>


                </div>
            </div>

            <div class="clearfix"></div>

            <div class="vpc-getting-started pubs">
                <div class="pub-theme">
                    <img src="<?php echo VPC_URL; ?>/admin/images/gp.png">
                    
                    <div>
                        <h2 class="vpc-addon-section-title"><?php _e("Grand Popo is the official shop theme for Visual Product Configurator", "vpc"); ?></h2>
                        
                        <p class="vpc-addon-section-subtitle">
                            <?php _e("Grand-Popo is a powerful ecommerce solution for creating large scale online stores, complete with advanced e-commerce marketing & up selling solutions for WordPress. 
                                Perfect for any electronic store, drones shop, fashion & clothing megastore, food markets and any other WordPress shop you can think of.", "vpc");?>
                        </p>

                        <div>
                            <a class="button" href="http://demos.orionorigin.com/grand-popo/01/free-trial/?utm_source=VPC%20Free&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php _e("Get free version", "vpc"); ?></a>
                            
                            <a class="button" href="http://demos.orionorigin.com/grand-popo/?utm_source=VPC%20Free&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php _e("Live preview", "vpc"); ?></a>
                        </div>
                    </div>
                </div>

                <div class="pub-plugin">
                    <div class="vpc-addon-section-title-container vpc-addon-section-description">
                        <h2 class="vpc-addon-section-title"><?php _e("Woocommerce All Discounts", "vpc"); ?></h2>
                        <p class="vpc-addon-section-subtitle">
                            <?php _e("Woocommerce All Discounts is a groundbreaking extension that helps you manage bulk
                                or wholesale pricing, customers roles or groups <br>
                                based offers, or....", "vpc");?>
                        </p>

                        <a class="button" href="https://www.orionorigin.com/plugins/woocommerce-all-discounts/?utm_source=VPC%20Free&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_content=Getting%20Started"><?php _e("From: $32", "vpc"); ?></a>
                    </div>
                </div>
            </div>

            <a href="https://wordpress.org/support/plugin/visual-products-configurator-for-woocommerce/reviews/#new-post">
                <span class="rating">
                    <?php _e("If you like <span>Visual Product Configurator</span> please leave us a <img src='" . VPC_URL . "/admin/images/rating.png'> rating. A huge thanks in advance!", "vpc"); ?>
                </span>
            </a>
        </div>
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

}
