<?php
/**
 * Plugin Name:     Location Data Collection
 * Plugin URI:
 * Description:     Location data collection plugin for Gravity Forms
 * Author:          NAMI.org
 * Author URI:
 * Text Domain:     wp-location-collection
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Nami_Location_Collection
 */

require_once 'vendor/autoload.php';

if ( class_exists( 'Nami\LocationData\Bootstrap' ) && ! wp_doing_ajax() ) {
	\Nami\LocationData\Bootstrap::init();
}

register_activation_hook(
	__FILE__,
	[ 'Nami_Location_Collection', 'activate' ]
);

register_deactivation_hook(
	__FILE__,
	[ 'Nami_Location_Collection', 'deactivate' ]
);

register_uninstall_hook(
	__FILE__,
	[ 'Nami_Location_Collection', 'uninstall' ]
);

/**
 * Main Plugin Class
 */
class Nami_Location_Collection {

	/**
	 * Plugin activation hook
	 *
	 * @return void
	 */
	public static function activate() {
		// check for GravityForms and if not present, deactivate?
	}

	/**
	 * Plugin deactivation hook
	 *
	 * @return void
	 */
	public static function deactivate() {
	}

	/**
	 * Plugin uninstall hook
	 *
	 * @return void
	 */
	public static function uninstall() {
	}
}

