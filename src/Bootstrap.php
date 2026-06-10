<?php
/**
 * Bootstrap class for Location Data Collection plugin
 *
 * @package Nami_Location_Collection
 */

namespace Nami\LocationData;

use Nami\LocationData\Admin\SettingsPage;
use Nami\LocationData\Api\UpdateChecker;
use Nami\LocationData\Form\GravityForms;
use Nami\LocationData\Options\ApiSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap Class
 */
class Bootstrap {

	/**
	 * Run on plugin init
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'plugins_loaded', [ __CLASS__, 'plugin_loaded' ] );
	}

	/**
	 * Do things when the plugin is loaded
	 *
	 * @return void
	 */
	public static function plugin_loaded(): void {
		ApiSettings::get_instance()->init();
		SettingsPage::get_instance()->init();
		GravityForms::get_instance()->init();
		UpdateChecker::get_instance()->init();
	}
}
