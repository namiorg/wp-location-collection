<?php
/**
 * Plugin Name:     Location Data Collection
 * Plugin URI:
 * Description:     Location data collection plugin for Gravity Forms
 * Author:          NAMI.org
 * Author URI:		NAMI InfoSys Team
 * Text Domain:     nami-location-collection
 * Domain Path:     /languages
 * Version:         {{VERSION}}
 *
 * @package         Nami_Location_Collection
 */

require_once 'vendor/autoload.php';

if ( class_exists( 'Nami\LocationData\Bootstrap' ) && ! wp_doing_ajax() ) {
	\Nami\LocationData\Bootstrap::init();
	add_action( 'wp_enqueue_scripts', [ 'Nami_Location_Collection', 'register_assets' ] );
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

	const string VERSION = '{{VERSION}}';

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
}
