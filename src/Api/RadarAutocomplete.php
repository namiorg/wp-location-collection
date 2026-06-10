<?php

namespace Nami\LocationData\Api;

use Nami\LocationData\Options\ApiSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server-side client for the Radar address autocomplete API.
 */
class RadarAutocomplete {

	const API_URL = 'https://api.radar.io/v1/search/autocomplete';

	/**
	 * Search terms come from unauthenticated form submissions, so cap what
	 * gets relayed to the Radar API.
	 */
	const MAX_TERM_LENGTH = 32;

	const CACHE_TIME = 15 * \MINUTE_IN_SECONDS;

	/**
	 * Search the Radar autocomplete API and return the most relevant address.
	 *
	 * @param mixed $term Search term (typically a zip/postal code from a form submission).
	 * @return array The first matching address, or an empty array.
	 */
	public static function search( $term ) {
		if ( ! is_string( $term ) ) {
			return [];
		}

		$term = trim( $term );
		if ( '' === $term || strlen( $term ) > self::MAX_TERM_LENGTH ) {
			return [];
		}

		$api_key = ApiSettings::get_option( 'api_key' );
		if ( empty( $api_key ) ) {
			return [];
		}

		$country = (string) ApiSettings::get_option( 'limit_to_country', 'US' );

		// Cache lookups (including empty results) so repeated submissions of
		// the same term don't each trigger a billable Radar API call.
		static $memo = [];
		$cache_key   = 'nami_radar_' . md5( $term . '|' . $country );
		if ( array_key_exists( $cache_key, $memo ) ) {
			return $memo[ $cache_key ];
		}

		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			$memo[ $cache_key ] = $cached;
			return $cached;
		}

		$args = [
			'headers' => [
				'Authorization' => $api_key,
			],
		];

		$query_args = [
			'layers'      => 'postalCode',
			'query'       => $term,
			'countryCode' => $country,
		];

		// add_query_arg() does not encode values, so encode them here.
		$api_url  = add_query_arg( array_map( 'rawurlencode', $query_args ), self::API_URL );
		$response = wp_remote_get( $api_url, $args );

		if ( is_wp_error( $response ) ) {
			// Memoize for this request only — the transient is skipped so a
			// transient network failure isn't cached for 15 minutes.
			$memo[ $cache_key ] = [];
			return [];
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$result = [];
		if ( isset( $data['addresses'][0] ) && is_array( $data['addresses'][0] ) ) {
			$result = $data['addresses'][0];
		}

		set_transient( $cache_key, $result, self::CACHE_TIME );
		$memo[ $cache_key ] = $result;

		return $result;
	}
}
