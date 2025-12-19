<?php

namespace Nami\LocationData\Api;

class UpdateChecker
{
	protected const string RELEASE_ENDPOINT = 'https://api.github.com/repos/namiorg/wp-location-collection/releases';
	protected const int|float CACHE_TIME = 12 * HOUR_IN_SECONDS;

	protected static ?self $instance = null;

	public static function get_instance(): self
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void
	{
		add_filter( 'plugins_api', [ $this, 'get_plugin_info' ], 20, 3 );
		add_filter( 'site_transient_update_plugins', [ $this, 'check_for_update' ] );
		add_action( 'upgrader_process_complete', [ $this, 'purge' ], 10, 2 );
	}

	public function get_plugin_info( $false, $action, $args ) {}
	public function check_for_update( $transient, $plugin_data, $wp_filesystem ) {}

	public function purge( $transient, $plugin_data, $network_wide ) {}
}
