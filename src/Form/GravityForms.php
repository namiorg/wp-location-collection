<?php

namespace Nami\LocationData\Form;

use JetBrains\PhpStorm\NoReturn;

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
		// @todo: use gform_enqueue_scripts_{form_id} to only load on our selected form
		// this will need to be done by calling get_option(...) to get the form ID from settings
		add_action( 'gform_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue Gravity Forms scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		// Enqueue custom scripts here
		wp_enqueue_script( 'nami-location-collection-radar' );
		wp_enqueue_style( 'nami-location-collection-radar' );
	}
}
