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
		add_action( 'gform_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue Gravity Forms scripts
	 *
	 * @param array $form The Gravity Forms form array
	 *
	 * @return void
	 */
	public function enqueue_scripts( array $form ): void {
		$options          = get_option( ApiSettings::OPTIONS_GROUP );
		$selected_form_id = (int) $options['gravity_forms_id'] ?? null;
		$current_form_id  = (int) $form['id'] ?? null;

		if ( is_admin() ) {
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

		// Enqueue custom scripts here
		wp_enqueue_script( 'nami-location-collection' );
		wp_enqueue_style( 'radar-frontend' );

		wp_localize_script(
			'nami-location-collection',
			'nami_location_collection',
			[
				'apiKey'      => $options['api_key'] ?? '',
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

		foreach ( $fields as $field ) {
			$css_class = $field->cssClass ?? '';

			if ( 'address' === $field->type && $field instanceof GF_Field_Address ) {

				foreach ( $field->inputs as $subfield ) {
					$input = 'input_' . $id . '_' . str_replace( '.', '_', $subfield['id'] );
				}
				continue;
			}

			$field_key = $this->kabob_case_to_camel_case( sanitize_title( $field->label ) );

			// normal zip_code to postal_code mapping
			if ( 'zipCode' === $field_key ) {
				$field_key = 'postalCode';
			}

			if ( str_contains( $css_class, 'radar_autocomplete_field' ) ) {
				$radar_fields['autocomplete'] = 'input_' . $id . '_' . $field->id;
				// also add as a sub-field for adding data
				$radar_fields[ $field_key ] = 'input_' . $id . '_' . $field->id;
				continue;
			}

			if ( 'hidden' === ( $field->type ?? '' ) ) {
				if ( in_array( $field->label, [ 'Zip', 'Postal', 'City', 'State', 'Province', 'Country' ], true ) ) {
					$radar_fields[ $field_key ] = 'input_' . $id . '_' . $field->id;
				}
				continue;
			}

			if ( str_contains( $css_class, 'radar_address_field' ) && 'hidden' !== ( $field->type ?? '' ) ) {
				$radar_fields[ $field_key ] = 'input_' . $id . '_' . $field->id;
			}
		}
//		dd( 'ops' );
		return $radar_fields;
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
}
