<?php

namespace Nami\LocationData\Form;

use Nami\LocationData\Api\RadarAutocomplete;
use Nami\LocationData\Options\ApiSettings;
use GF_Field_Address;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * Radar keys for Gravity Forms Address sub-inputs.
	 *
	 * Address sub-input IDs are fixed by Gravity Forms regardless of label or
	 * language — <field>.1 street, .2 line 2, .3 city, .4 state, .5 zip,
	 * .6 country — so mapping by sub-input ID is translation-proof. (.2 has no
	 * Radar equivalent and is intentionally omitted.)
	 *
	 * @var array|string[]
	 */
	protected array $address_subfield_map = [
		'1' => 'addressLabel',
		'3' => 'city',
		'4' => 'state',
		'5' => 'postalCode',
		'6' => 'country',
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
			add_action( 'gform_pre_submission_' . $this->form_id, [$this, 'check_fields'], 10 );
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
		// the field ID can be constructed as 'input_' . $form_id . '_' . $field_id

		$fields       = $form['fields'] ?? [];
		$radar_fields = $this->find_radar_field( $fields, (int) $form['id'] );

		// Enqueue custom scripts here
		wp_enqueue_script( 'nami-location-collection' );
		wp_enqueue_style( 'radar-frontend' );

		// This data is visible to every visitor, so only ever expose a
		// publishable key — a secret key must not leave the server.
		$api_key = (string) ApiSettings::get_option( 'api_key', '' );

		wp_localize_script(
			'nami-location-collection',
			'nami_location_collection',
			[
				'apiKey'      => ApiSettings::is_publishable_key( $api_key ) ? $api_key : '',
				'radarFields' => $radar_fields,
				'countryCode' => ApiSettings::get_option( 'limit_to_country', 'null' ),
			]
		);
	}

	/**
	 * Find Radar fields in Gravity Forms form fields
	 *
	 * @param array $fields Form fields
	 * @param int   $form_id Form ID
	 *
	 * @return array
	 */
	public function find_radar_field( array $fields, int $form_id, bool $exclude_form_id = false ): array {
		$radar_fields = [];
		$form_id_str  = $exclude_form_id ? '' : '_' . $form_id;

		foreach ( $fields as $field ) {
			// Gravity Forms Address fields expose their parts as sub-inputs with
			// fixed IDs (.1 street, .3 city, .4 state, .5 zip, .6 country), so map
			// them by sub-input ID — independent of the label's language. Forms
			// built from standalone text fields fall through to the label /
			// admin-label / CSS-class resolution below.
			if ( 'address' === $field->type && $field instanceof GF_Field_Address ) {
				foreach ( $field->inputs as $subfield ) {
					$parts     = explode( '.', (string) ( $subfield['id'] ?? '' ) );
					$field_key = $this->address_subfield_map[ end( $parts ) ] ?? '';
					if ( empty( $field_key ) ) {
						continue;
					}

					// GF renders sub-inputs as input_<form>_<field>_<sub> (DOM id)
					// or input_<field>_<sub> (POST name, when form id excluded);
					// either way a separator precedes the field/sub id.
					$input                      = 'input' . $form_id_str . '_' . str_replace( '.', '_', $subfield['id'] );
					$radar_fields[ $field_key ] = $input;

					// Anchor the autocomplete on the visible zip sub-input.
					if ( 'postalCode' === $field_key && empty( $subfield['isHidden'] ) ) {
						$radar_fields['autocomplete'] = $input;
					}
				}
				continue;
			}

			$field_key = $this->resolve_field_key( $field );
			// if $field_key is empty, continue — this isn't an address field
			if ( empty( $field_key ) ) {
				continue;
			}

			$input                      = 'input' . $form_id_str . '_' . $field->id;
			$radar_fields[ $field_key ] = $input;

			// Anchor the Radar autocomplete on the zip field specifically.
			// location.js searches `layers: postalCode`, so the visible zip
			// input is the correct host. The previous logic anchored on the
			// first address-ish field by document order, which on some forms is
			// a hidden City/State field — Radar then attaches to an invisible
			// element and the autocomplete silently never appears. Hidden fields
			// stay in the map (they are auto-filled on selection) but are never
			// used as the anchor.
			if ( 'postalCode' === $field_key && ! $this->is_hidden_field( $field ) ) {
				$radar_fields['autocomplete'] = $input;
			}
		}

		return $radar_fields;
	}

	/**
	 * Determine whether a Gravity Forms field is hidden from the visitor.
	 *
	 * Covers both hidden-type fields and fields set to hidden/administrative
	 * visibility. Such fields can't host a visible autocomplete UI.
	 *
	 * @param object $field The Gravity Forms field.
	 * @return bool True if the field is not visible to the visitor.
	 */
	private function is_hidden_field( object $field ): bool {
		return 'hidden' === ( $field->type ?? '' ) || 'visible' !== ( $field->visibility ?? 'visible' );
	}

	/**
	 * Resolve a field's Radar key from the most translation-stable source.
	 *
	 * Visitor-facing labels are translated (e.g. English vs Spanish), which
	 * breaks label matching. The Admin Field Label and a CSS class are admin-set
	 * and never translated, so they are checked first; the visitor label stays a
	 * fallback so existing English forms keep working with no reconfiguration.
	 *
	 * To tag a field on a translated form, set its Admin Field Label (e.g. "Zip",
	 * "City", "State") or add a CSS class containing the same keyword (e.g.
	 * "radar-zip").
	 *
	 * @param object $field The Gravity Forms field.
	 * @return string The Radar field key, or '' if nothing matched.
	 */
	private function resolve_field_key( object $field ): string {
		$sources = [
			$field->adminLabel ?? '',
			$field->cssClass ?? '',
			$field->label ?? '',
		];

		foreach ( $sources as $source ) {
			$field_key = $this->get_field( (string) $source );
			if ( ! empty( $field_key ) ) {
				return $field_key;
			}
		}

		return '';
	}

	/**
	 * Get field key from label using field map
	 *
	 * @param string $field Field label to parse
	 *
	 * @return string
	 */
	public function get_field( string $field ): mixed {
		$field_key = '';
		foreach ( $this->field_map as $key => $mapped ) {
			// Case-insensitive so labels like "ZIP Code" or "zip code" match
			// the "Zip" key — case-sensitive matching missed them, leaving the
			// zip field undetected and the autocomplete with no field to anchor.
			if ( stripos( $field, $key ) !== false ) {
				$field_key = $mapped;
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
		$form = $validation_result['form'];

		foreach ( $form['fields'] as $index => $field ) {
			// look for the zip code field
			$field_key = $this->resolve_field_key( $field );
			if ( 'postalCode' !== $field_key ) {
				continue;
			}
			$field_id = 'input_' . $field->id;

			$field_value = rgpost($field_id);
			$field_length = is_string($field_value) ? strlen($field_value) : 0;
			if ($field_length < 5 || $field_length > 10) {
				$validation_result['is_valid'] = false;
				$form['fields'][ $index ]['failed_validation'] = true;
				$form['fields'][ $index ]['validation_message'] = __('Please enter a valid zip code.', 'wp-location-collection');
				continue; // already invalid — don't relay the value to the Radar API
			}

			$zipcode_data = RadarAutocomplete::search($field_value);
			if (empty($zipcode_data)) {
				$validation_result['is_valid'] = false;
				$form['fields'][ $index ]['failed_validation'] = true;
				$form['fields'][ $index ]['validation_message'] = __('Unable to validate the zip code. Please enter a valid zip code.', 'wp-location-collection');
			}
		}

		$validation_result['form'] = $form;
		return $validation_result;
	}

	public function check_fields( array $form ): void {

		// find radar fields in the form
		// check which fields are empty
		$radar_fields = $this->find_radar_field($form['fields'], $form['id'], true);
		$empty_fields = [];
		foreach($radar_fields as $radar_field => $field_id_raw) {
			$field = str_replace( '_' . $form['id'] . '_', '_', $field_id_raw );
			$field_value = rgpost($field);
			// Do something with $field_value
			if (empty($field_value)) {
				$empty_fields[] = $radar_field;
			}
		}
		// if no empty fields, bail
		if (empty($empty_fields)) {
			return;
		}

		// if we have empty fields, prepare an api call to Radar to fill them
		if ( empty( $radar_fields['autocomplete'] ) ) {
			return; // no autocomplete field mapped, nothing to look up
		}

		$autocomplete_value = rgpost( $radar_fields['autocomplete'] );
		$address = RadarAutocomplete::search( $autocomplete_value );

		if ( empty( $address ) ) {
			return; // if for some reason we get no addresses back, bail
		}

		// Radar will return multiple addresses, we just want the first one as this one is the most relevant
		// Use the first address to populate the empty fields
		foreach ($empty_fields as $empty_field) {
			$field = $radar_fields[ $empty_field ];
			// modify the $_POST global to set the value
			$_POST[ $field ] = sanitize_text_field( (string) ( $address[ $empty_field ] ?? '' ) ); // sanitize the value in case GravityForms doesn't
		}
	}
}
