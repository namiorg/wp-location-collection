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
			self::OPTIONS_GROUP . '-main',
			'API Settings',
			null,
			self::OPTIONS_GROUP . '-main'
		);

		add_settings_field(
			'api_key',
			'API Key',
			function () {
				$options = get_option( self::OPTIONS_GROUP, $this->default_options );
				?>
				<input type="text" name="<?php echo esc_attr( self::OPTIONS_GROUP ); ?>[api_key]" value="<?php echo esc_attr( $options['api_key'] ); ?>" class="regular-text" />
				<?php
			},
			self::OPTIONS_GROUP,
			self::OPTIONS_GROUP . '-main'
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
