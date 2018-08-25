<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.orionorigin.com
 * @since             1.0.0
 * @package           Vpc
 *
 * @wordpress-plugin
 * Plugin Name:       Visual Products Configurator - Free
 * Plugin URI:        http://www.orionorigin.com/plugins/woo-visual-product-configurator/
 * Description:       Configure, build, design and customize any product visually prior to purchase.
 * Version:           3.3.2
 * Author:            ORION
 * Author URI:        http://www.orionorigin.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vpc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('VPC_URL', plugins_url('/', __FILE__));
define('VPC_DIR', dirname(__FILE__));
define('VPC_MAIN_FILE', 'visual-product-configurator/vpc.php' );
define('VPC_VERSION', '3.3.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vpc-activator.php
 */
function activate_vpc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vpc-activator.php';
	Vpc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vpc-deactivator.php
 */
function deactivate_vpc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vpc-deactivator.php';
	Vpc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_vpc' );
register_deactivation_hook( __FILE__, 'deactivate_vpc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vpc.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-vpc-config.php';
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'skins/class-vpc-default-skin.php';
/*if(!function_exists("o_admin_fields"))
{
    require plugin_dir_path(__FILE__) . 'includes/utils.php';
}*/
if (!class_exists('Orion_Library'))
    require plugin_dir_path( __FILE__ ) . 'includes/class-orion-library.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vpc() {

	$plugin = new Vpc();
	$plugin->run();

}
run_vpc();
