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

// Your code starts here.

register_activation_hook(
	__FILE__,
	array( 'Nami_Location_Collection', 'activate' )
);

register_deactivation_hook(
	__FILE__,
	array( 'Nami_Location_Collection', 'deactivate' )
);

register_uninstall_hook(
	__FILE__,
	array( 'Nami_Location_Collection', 'uninstall' )
);

class Nami_Location_Collection {

	public static function activate() {
		// check for GravityForms and if not present, deactivate?
	}

	public static function deactivate() {
	}

	public static function uninstall() {
	}
}
