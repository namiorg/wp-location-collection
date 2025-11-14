<?php
/**
 * API Settings class for Location Data Collection plugin
 *
 * @package Nami_Location_Collection
 */

namespace Nami\LocationData\Options;

use GFAPI;
use Nami\LocationData\Admin\SettingsPage;

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
			'API Settings',
			function () {
				echo '<p>Add API key and endpoint.</p>';
			},
			self::OPTIONS_GROUP
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
			self::OPTIONS_GROUP
		);

		add_settings_field(
			'gravity_forms_id',
			'Gravity Forms Form ID',
			function () {
				$options = get_option( self::OPTIONS_GROUP, $this->default_options );
				$forms   = GFAPI::get_forms();
				?>
				<select name="<?php echo esc_attr( self::OPTIONS_GROUP ); ?>[gravity_forms_id]" id="">
					<option value=""><?php esc_html_e( 'Select a form', 'wp-location-collection' ); ?></option>
					<?php foreach ( $forms as $form ) : ?>
						<option value="<?php echo esc_attr( $form['id'] ); ?>" <?php selected( $options['gravity_forms_id'], $form['id'] ); ?>>
							<?php echo esc_html( $form['title'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>

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
		$sanitized                     = [];
		$sanitized['api_key']          = sanitize_text_field( $input['api_key'] ?? '' );
		$sanitized['gravity_forms_id'] = absint( $input['gravity_forms_id'] ?? 0 );
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
