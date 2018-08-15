<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin .
 *
 * @link              https://configuratorsuiteforwp.com/
 * @since             1.0.0
 * @package           Vpc
 *
 * @wordpress-plugin
 * Plugin Name:       Visual Products Configurator
 * Plugin URI:        https://configuratorsuiteforwp.com/
 * Description:       A smart and flexible extension which lets you setup any customizable product your customers can configure visually prior to purchase.
 * Version:           5.6.1
 * Author:            ORION
 * Author URI:        https://configuratorsuiteforwp.com/
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
define('VPC_VERSION', '5.6.1' );
define('ORION_PLUGIN_NAME', 'woocommerce Visual Products Configurator' );
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
require plugin_dir_path( __FILE__ ) . 'skins/class-vpc-right-sidebar-skin.php';

if(!function_exists("o_admin_fields"))
{
    require plugin_dir_path(__FILE__) . 'includes/utils.php';
}

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
