<?php

namespace Nami\LocationData\Form;

use Nami\LocationData\Options\ApiSettings;
use GF_Field_Address;
/**
 * Gravity Forms Integration Class
 */
class GravityForms {


	/**
	 * Singleton Instance
	 *
	 * @var GravityForms|null Singleton instance
	 */
	protected static ?GravityForms $instance = null;

	/**
	 * Selected Gravity Forms Form ID
	 *
	 * @var int
	 */
	protected int $form_id = 0;


	/**
	 * Fields to be used for Radar API
	 * Map Radar API response fields to GravityForms fields. Used for populating fields with data from Radar
	 *
	 * @var array
	 */
	protected array $radar_fields = [];

	/**
	 * Field-mapping values
	 * Map field based on label to Radar field
	 *
	 * @var array|string[]
	 */
	protected array $field_map = [
		'Zip'         => 'postalCode',
		'Postal'      => 'postalCode',
		'Street'      => 'addressLabel',
		'City'        => 'city',
		'State'       => 'state',
		'Province'    => 'state',
		'stateCode'   => 'stateCode',
		'Country'     => 'country',
		'countryCode' => 'countryCode',
		'Latitude'    => 'latitude',
		'Longitude'   => 'longitude',
	];


	/**
	 * Get Singleton Instance
	 *
	 * @return GravityForms
	 */
	public static function get_instance(): GravityForms {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Initialize Gravity Forms integration
	 *
	 * @return void
	 */
	public function init(): void {
		// only proceed if Gravity Forms is active and a form ID is set
		$this->form_id = ApiSettings::get_option( 'gravity_forms_id', 0 );

		add_action( 'gform_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );

		if ( ! empty( $this->form_id ) ) {
			add_filter( 'gform_validation_' . $this->form_id, [ $this, 'validate_zip_code' ], 10, 2 );
		}
	}

	/**
	 * Enqueue Gravity Forms scripts
	 *
	 * @param array $form The Gravity Forms form array
	 *
	 * @return void
	 */
	public function enqueue_scripts( array $form ): void {
		$selected_form_id = (int) $this->form_id ?? null;
		$current_form_id  = (int) $form['id'] ?? null;

		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // no enqueueing in admin area
		}

		if ( $selected_form_id !== $current_form_id ) {
			return;
		}

		// CSS Classes to look for in the form array. These are needed to pass
		// to the frontend to initialize Radar.
		// Check if field has the hidden type.
		// If so, check label index for Zip, Postal, City, State, Province, or Country.
		// Yes, this is hacky but Gravity Forms does not provide a better way to identify fields.
		// Check 'cssClass' in the $form array for:
		// radar_autocomplete_field
		// radar_address_field if the field isn't hidden
		// the field ID can be constructed as 'input_' . $form_id . '_' . $field_id

		$fields       = $form['fields'] ?? [];
		$radar_fields = $this->find_radar_field( $fields, (int) $form['id'] );

		$api_key = ApiSettings::get_option( 'api_key' );
		// Enqueue custom scripts here
		wp_enqueue_script( 'nami-location-collection' );
		wp_enqueue_style( 'radar-frontend' );

		wp_localize_script(
			'nami-location-collection',
			'nami_location_collection',
			[
				'apiKey'      => $api_key,
				'radarFields' => $radar_fields,
			]
		);
	}

	/**
	 * Find Radar fields in Gravity Forms form fields
	 *
	 * @param array $fields Form fields
	 * @param int   $id Form ID
	 *
	 * @return array
	 */
	public function find_radar_field( array $fields, int $id ): array {
		$radar_fields = [];

		// we use this to check labels for the data we need
		$field_position = 0;
		foreach ( $fields as $field ) {
			// we need to handle GF_Field_Address fields differently
			if ( 'address' === $field->type && $field instanceof GF_Field_Address ) {
				foreach ( $field->inputs as $index => $subfield ) {
					$input     = 'input_' . $id . '_' . str_replace( '.', '_', $subfield['id'] );
					$field_key = $this->get_field( $subfield['label'] );
					// first input should have the autocomplete class
					if ( 0 === $index ) {
						$radar_fields['autocomplete'] = $input;
					}

					$radar_fields[ $field_key ] = $input;
				}
				continue;
			}

			$field_key = $this->get_field( $field->label );

			// if $field_key is empty, continue — this isn't an address field
			if ( empty( $field_key ) ) {
				continue;
			}

			$field_position++; // use to find which text address field is first
			if ( 1 === $field_position ) {
				$radar_fields['autocomplete'] = 'input_' . $id . '_' . $field->id;
				// also add as a sub-field for adding data
				$radar_fields[ $field_key ] = 'input_' . $id . '_' . $field->id;
				continue;
			}

			$radar_fields[ $field_key ] = 'input_' . $id . '_' . $field->id;
		}

		return $radar_fields;
	}

	/**
	 * Get field key from label using field map
	 *
	 * @param string $field Field label to parse
	 *
	 * @return string
	 */
	public function get_field( string $field ): mixed {
		$field_map      = $this->field_map;
		$field_map_keys = array_keys( $this->field_map );
		$field_key      = '';
		foreach ( $field_map_keys as $key ) {
			if ( str_contains( $field, $key ) ) {
				$field_key = $field_map[ $key ];
				break;
			}
		}

		return $field_key;
	}


	/**
	 * Convert kabob-case string to camelCase
	 *
	 * @param string $string_to_convert String to convert
	 *
	 * @return string
	 */
	private function kabob_case_to_camel_case( string $string_to_convert ): string {

		$str = str_replace( '-', '', ucwords( $string_to_convert, '-' ) );

		return lcfirst( $str );
	}

	public function validate_zip_code( array $validation_result, string $form_context ): array {
		// Example validation logic for zip code
		$form = $validation_result['form'];
		foreach ( $form['fields'] as $field ) {
			// look for the zip code field
		}

		$validation_result['form'] = $form;
		return $validation_result;
	}
}
