<?php
/**
 * API Settings class for Location Data Collection plugin
 *
 * @package Nami_Location_Collection
 */

namespace Nami\LocationData\Options;

use GFAPI;
use Nami\LocationData\Admin\SettingsPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		'api_key'          => '',
		'gravity_forms_id' => '',
		'limit_to_country' => 'US',
	];

	/**
	 * Singleton instance
	 *
	 * @var ApiSettings|null
	 */
	protected static ?self $instance = null;


	/**
	 * Get singleton instance
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


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
			self::OPTIONS_GROUP,
			esc_html__( 'API Settings', 'nami-location-collection' ),
			function () {
				echo '<p>' . esc_html__( 'Add API key and endpoint.', 'nami-location-collection' ) . '</p>';
			},
			self::OPTIONS_GROUP
		);

		add_settings_field(
			'api_key',
			esc_html__( 'API Key', 'nami-location-collection' ),
			function () {
				$options = get_option( self::OPTIONS_GROUP, $this->default_options );
				$api_key = $options['api_key'] ?? '';
				?>
				<input type="text" name="<?php echo esc_attr( self::OPTIONS_GROUP ); ?>[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Radar publishable key (prj_live_pk_… or prj_test_pk_…). Never use a secret (sk) key — this key is sent to visitors’ browsers.', 'nami-location-collection' ); ?></p>
				<?php
			},
			self::OPTIONS_GROUP,
			self::OPTIONS_GROUP
		);

		add_settings_field(
			'gravity_forms_id',
			esc_html__( 'Gravity Forms Form ID', 'nami-location-collection' ),
			function () {
				$options = get_option( self::OPTIONS_GROUP, $this->default_options );
				$gravity_forms_id = $options['gravity_forms_id'] ?? '';
				$forms   = GFAPI::get_forms();
				?>
				<select name="<?php echo esc_attr( self::OPTIONS_GROUP ); ?>[gravity_forms_id]" id="">
					<option value=""><?php esc_html_e( 'Select a form', 'nami-location-collection' ); ?></option>
					<?php foreach ( $forms as $form ) : ?>
						<option value="<?php echo esc_attr( $form['id'] ); ?>" <?php selected( $gravity_forms_id, $form['id'] ); ?>>
							<?php echo esc_html( $form['title'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<?php
			},
			self::OPTIONS_GROUP,
			self::OPTIONS_GROUP
		);

		add_settings_field(
			'limit_to_country',
			esc_html__( 'Limit to Country', 'nami-location-collection' ),
			function () {
				$options = get_option( self::OPTIONS_GROUP, $this->default_options );
				$limit_to_country = $options['limit_to_country'] ?? '';
				?>
				<input type="text" name="<?php echo esc_attr( self::OPTIONS_GROUP ); ?>[limit_to_country]" value="<?php echo esc_attr( $limit_to_country ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Enter a comma-delineated list of country codes (e.g., US) to limit location autocomplete to that country. Leave blank to turn limiting off.', 'nami-location-collection' ); ?></p>
				<?php
			},
			self::OPTIONS_GROUP,
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
		$api_key = $input['api_key'] ?? '';

		$sanitized                     = [];
		$sanitized['api_key']          = $this->sanitize_api_key( is_string( $api_key ) ? $api_key : '' );
		$sanitized['gravity_forms_id'] = absint( $input['gravity_forms_id'] ?? 0 );
		$sanitized['limit_to_country'] = sanitize_text_field( $input['limit_to_country'] ?? $this->default_options['limit_to_country'] );

		return array_merge( $this->default_options, $sanitized );
	}


	/**
	 * Sanitize the API key, allowing only Radar publishable keys.
	 *
	 * The key is sent to visitors' browsers via wp_localize_script(), so a
	 * secret (sk) key must never be stored here.
	 *
	 * @param string $api_key Submitted API key.
	 * @return string The validated key, or the previously saved key if invalid.
	 */
	protected function sanitize_api_key( string $api_key ): string {
		$api_key = sanitize_text_field( $api_key );

		if ( '' === $api_key || self::is_publishable_key( $api_key ) ) {
			return $api_key;
		}

		add_settings_error(
			self::OPTIONS_GROUP,
			'invalid_api_key',
			esc_html__( 'The API key was not saved: it must be a Radar publishable key (prj_live_pk_… or prj_test_pk_…). This key is sent to visitors’ browsers, so a secret (sk) key must never be used here.', 'nami-location-collection' )
		);

		return (string) self::get_option( 'api_key', '' );
	}


	/**
	 * Check whether a key is a Radar publishable key.
	 *
	 * @param string $api_key API key to check.
	 * @return bool
	 */
	public static function is_publishable_key( string $api_key ): bool {
		return 1 === preg_match( '/^prj_(test|live)_pk_/', $api_key );
	}


	/**
	 * Initialize API settings
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}


	/**
	 * Get option value by key
	 *
	 * @param string $key Option key.
	 * @param mixed  $default_value Default value if none assigned.
	 *
	 * @return mixed Option value or null if not set.
	 */
	public static function get_option( string $key, mixed $default_value = null ): mixed {
		$options = get_option( self::OPTIONS_GROUP );
		return $options[ $key ] ?? $default_value;
	}
}
