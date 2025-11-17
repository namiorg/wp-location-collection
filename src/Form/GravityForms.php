<?php

namespace Nami\LocationData\Form;

use JetBrains\PhpStorm\NoReturn;
use Nami\LocationData\Options\ApiSettings;

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
	public function enqueue_scripts( $form ): void {
		$options          = get_option( ApiSettings::OPTIONS_GROUP );
		$selected_form_id = (int) $options['gravity_forms_id'] ?? null;
		$current_form_id  = (int) $form['id'] ?? null;

		if ( $selected_form_id !== $current_form_id ) {
			return;
		}
		// Enqueue custom scripts here
		wp_enqueue_script( 'nami-location-collection-radar' );
		wp_enqueue_style( 'nami-location-collection-radar' );
	}
}
