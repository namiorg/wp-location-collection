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
	}
}
