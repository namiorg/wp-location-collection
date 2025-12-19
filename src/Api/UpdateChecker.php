<?php

namespace Nami\LocationData\Api;

class UpdateChecker
{
	const string MANIFEST_JSON_URL = 'https://api.github.com/repos/namiorg/wp-location-collection/blob/main/manifest.json';

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
		// Implementation for update checking goes here
	}
}
