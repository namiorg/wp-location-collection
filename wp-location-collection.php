<?php
/**
 * Plugin Name: Location Data Collection
 * Plugin URI:
 * Description: Location data collection plugin for Gravity Forms
 * Author: NAMI InfoSys Team
 * Author URI:	nami.org
 * Text Domain: wp-location-collection
 * Domain Path: /languages
 * Version: {{VERSION}}
 *
 * @package         Nami_Location_Collection
 */
namespace Nami\LocationData;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once  plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// NB: do not gate this on ! wp_doing_ajax(). The plugin's work happens during
// AJAX requests, not just normal page loads: WordPress runs the "Update now"
// button through admin-ajax (wp_ajax_update_plugin -> wp_update_plugins), so the
// self-hosted update filter must be attached then or the update reports
// "already at the latest version"; and Gravity Forms validates and submits
// AJAX-enabled forms over admin-ajax, so the zip validation and Radar field
// population must be hooked then too. Every init() below only registers hooks
// for the contexts they belong to, so loading during AJAX is safe.
if ( class_exists( 'Nami\LocationData\Bootstrap' ) ) {
	Nami_Location_Collection::set_plugin_path( plugin_dir_path( __FILE__ ) );
	Nami_Location_Collection::set_plugin_basename( plugin_basename( __FILE__ ) );

	load_plugin_textdomain( 'wp-location-collection', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	\Nami\LocationData\Bootstrap::init();

	add_action( 'wp_enqueue_scripts', [ 'Nami\LocationData\Nami_Location_Collection', 'register_assets' ] );
}

register_activation_hook(
	__FILE__,
	[ 'Nami\LocationData\Nami_Location_Collection', 'activate' ]
);

register_deactivation_hook(
	__FILE__,
	[ 'Nami\LocationData\Nami_Location_Collection', 'deactivate' ]
);

register_uninstall_hook(
	__FILE__,
	[ 'Nami\LocationData\Nami_Location_Collection', 'uninstall' ]
);

/**
 * Main Plugin Class
 */
class Nami_Location_Collection {

	/**
	 * Plugin version
	 */
	const string VERSION = '{{VERSION}}';

	protected static string $plugin_path;

	protected static string $plugin_basename;

	/**
	 * Plugin activation hook
	 *
	 * @return void
	 */
	public static function activate() {
		// check for GravityForms and if not present, deactivate?
		// delete the update_plugins transient to force refresh of plugin update info
		delete_site_transient( 'update_plugins' );
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


	/**
	 * Register plugin assets
	 *
	 * Register both the Radar JS and CSS files for enqueuing later on.
	 * Handle asset registration here so we don't have to do weird path
	 * checks in other parts of the code.
	 *
	 * @return void
	 */
	public static function register_assets(): void {
		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.NotInFooter
		// WordPress needs to modernize their phpcs rules...
		wp_register_script(
			handle: 'radar',
			src: plugins_url( 'assets/radar.js', __FILE__ ),
			ver: self::VERSION,
			args: [ 'in_footer' => true ],
		);

		wp_register_script(
			handle: 'nami-location-collection',
			src: plugins_url( 'assets/location.js', __FILE__ ),
			deps: [ 'radar' ],
			ver: self::VERSION,
			args: [ 'in_footer' => true ],
		);

		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.NotInFooter

		wp_register_style(
			handle: 'radar',
			src: plugins_url( 'assets/radar.css', __FILE__ ),
			ver: self::VERSION
		);

		wp_register_style(
			handle: 'radar-frontend',
			src: plugins_url( 'assets/frontend.css', __FILE__ ),
			deps: [ 'radar' ],
			ver: self::VERSION
		);
	}

	public static function set_plugin_path( $path ): void {
		self::$plugin_path = $path;
	}

	public static function set_plugin_basename( $basename ): void {
		self::$plugin_basename = $basename;
	}

	public static function get_plugin_path(): string {
		return self::$plugin_path;
	}

	public static function get_plugin_basename(): string {
		return self::$plugin_basename;
	}
}
