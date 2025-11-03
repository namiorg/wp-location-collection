<?php
/**
 * API Settings class for Location Data Collection plugin
 *
 * @package Nami_Location_Collection
 */

namespace Nami\LocationData\Options;

/**
 * API Settings Class
 */
class ApiSettings {

	/**
	 * Options group name
	 *
	 * @var string
	 */
	const string OPTIONS_GROUP = 'nami_location_data_api_settings';

	/**
	 * Default options
	 *
	 * @var array
	 */
	protected array $default_options = [
		'api_key'      => '',
		'api_endpoint' => 'https://api.example.com',
	];

	/**
	 * Register settings
	 *
	 * @return void
	 */
	public function register_settings(): void {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		register_setting(
			self::OPTIONS_GROUP,
			self::OPTIONS_GROUP,
			[ $this, 'sanitize_options' ]
		);

		add_settings_section(
			'api_settings_section',
			'API Settings',
			function () {
				echo '<p>Configure the API settings for location data collection.</p>';
			},
			self::OPTIONS_GROUP
		);
	}

	/**
	 * Sanitize options
	 *
	 * @param array $input Input options.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( array $input ): array {
		$sanitized                 = [];
		$sanitized['api_key']      = sanitize_text_field( $input['api_key'] ?? '' );
		$sanitized['api_endpoint'] = esc_url_raw( $input['api_endpoint'] ?? '' );
		return array_merge( $this->default_options, $sanitized );
	}


	/**
	 * Initialize API settings
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}
}
