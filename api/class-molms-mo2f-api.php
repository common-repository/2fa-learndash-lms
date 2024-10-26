<?php

class Molms_Mo2f_Api {
	public function make_curl_call(
		$url, $fields, $http_header_array = array(
		"Content-Type"  => "application/json",
		"charset"       => "UTF-8",
		"Authorization" => "Basic"
	)
	) {
		if ( gettype( $fields ) !== 'string' ) {
			$fields = wp_json_encode( $fields );
		}

		$args = array(
			'method'      => 'POST',
			'body'        => $fields,
			'timeout'     => '5',
			'redirection' => '5',
			'sslverify'   => true,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $http_header_array
		);


		$response = Molms_Mo2f_Api::mo2f_wp_remote_post( $url, $args );

		return $response;
	}

	public function mo2f_wp_remote_post( $url, $args = array() ) {
		$response = wp_remote_post( $url, $args );
		if ( ! is_wp_error( $response ) ) {
			return $response['body'];
		} else {
			$message = 'Please enable curl extension. <a href="admin.php?page=molms_troubleshooting">Click here</a> for the steps to enable curl.';

			return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
		}
	}

	public function get_http_header_array() {
		$customer_key = get_site_option( 'mo2f_customerKey' );
		$api_key      = get_site_option( 'mo2f_api_key' );

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$current_time_in_millis = Molms_Mo2f_Api::get_timestamp();

		/* Creating the Hash using SHA-512 algorithm */
		$string_to_hash = $customer_key . $current_time_in_millis . $api_key;
		$hash_value     = hash( "sha512", $string_to_hash );

		$headers = array(
			"Content-Type"  => "application/json",
			"Customer-Key"  => $customer_key,
			"Timestamp"     => $current_time_in_millis,
			"Authorization" => $hash_value
		);

		return $headers;
	}

	public function get_timestamp() {
		$current_time_in_millis = round( microtime( true ) * 1000 );
		$current_time_in_millis = number_format( $current_time_in_millis, 0, '', '' );

		return $current_time_in_millis;
	}
}
