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
		add_action( 'gform_after_submission', [ $this, 'after_submission' ], 10, 2 );
	}

	/**
	 * Handle after submission of Gravity Form
	 *
	 * @param array $submission Submission data.
	 * @param array $form_data Form data.
	 * @return void
	 */
	#[NoReturn]
	public function after_submission( $submission, $form_data ): void {
		// @todo: Add conditional check for specific form ID from settings, or update the hook to only target that form.
		dump( $submission, $form_data );
		die();
	}
}
