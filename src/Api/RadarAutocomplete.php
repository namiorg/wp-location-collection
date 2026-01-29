<?php

namespace Nami\LocationData\Api;

class RadarAutocomplete
{
	const API_URL = 'https://api.radar.io/v1/search/autocomplete';

	public static function search($term){
		$api_key = \Nami\LocationData\Options\ApiSettings::get_option('api_key');
		if (empty($api_key)) {
			return [];
		}

		$args = [
			'headers' => [
				'Authorization' => $api_key,
			],
		];

		$query_args = [
			'layers' => 'postalCode',
			'query' => $term,
			'countryCode' => \Nami\LocationData\Options\ApiSettings::get_option('limit_to_country', 'US'),
		];

		$api_url = add_query_arg( $query_args, self::API_URL );
		$response = wp_remote_get($api_url , $args);

		if (is_wp_error($response)) {
			return [];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data['addresses'])) {
			return $data['addresses'];
		}

		return [];
	}
}
