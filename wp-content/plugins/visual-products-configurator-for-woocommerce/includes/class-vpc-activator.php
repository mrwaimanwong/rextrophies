<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.orionorigin.com
 * @since      1.0.0
 *
 * @package    Vpc
 * @subpackage Vpc/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Vpc
 * @subpackage Vpc/includes
 * @author     ORION <help@orionorigin.com>
 */
class Vpc_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
            GLOBAL $wp_rewrite;
            add_option('vpc_do_activation_redirect', true);            
            $wp_rewrite->flush_rules(false);
	}

}
