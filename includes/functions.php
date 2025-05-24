<?php
/**
 * General functions for ITTO Companion plugin.
 *
 * @package itto-companion
 */

/**
 * Send a check-in ping to the ITTO service.
 *
 * @param string $event_key The event created at the ITTO website.
 * @return bool True if check-in was successful, false otherwise.
 */
function itto_send_checkin( string $event_key ): bool {
	if ( ! $event_key ) {
		return false;
	}

	$api_key = get_option( 'itto_api_key', '' ) ?? false;

	if ( ! $api_key ) {
		return false;
	}

	$api_url = 'https://itto.vegasgeek.com/wp-json/itto/v1/checkin';

	$response = wp_remote_post(
		$api_url,
		array(
			'method' => 'POST',
			'body'   => array(
				'api_key'   => $api_key,
				'event_key' => $event_key,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	return true;
}

/**
 * Send a test connection ping to the ITTO service.
 *
 * @return bool True if test connection was successful, false otherwise.
 */
function itto_send_test_connection(): bool {
	$api_key = get_option( 'itto_api_key', '' ) ?? false;

	if ( ! $api_key ) {
		return false;
	}

	$api_url = 'https://itto.vegasgeek.com/wp-json/itto/v1/test-connection';

	$response = wp_remote_post(
		$api_url,
		array(
			'method' => 'POST',
			'body'   => array( 'api_key' => $api_key ),
		)
	);

	if ( is_wp_error( $response ) ) {
		ray( $response );
		return false;
	}

	return true;
}
