<?php

/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/
global $molms_dirName;
require_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-mo2f-api.php';

class Molms_Miniorange_Rba_Attributes {
	private $auth_mode = 2;    //  miniorange test or not
	private $https_mode = false; // website http or https

	public function mo2f_collect_attributes( $useremail, $rba_attributes ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url               = MOLMS_HOST_NAME . '/moas/rest/rba/acs';
		$customer_key      = get_site_option( 'mo2f_customerKey' );
		$field_string      = "{\"customerKey\":\"" . $customer_key . "\",\"userKey\":\"" . $useremail . "\",\"attributes\":" . $rba_attributes . "}";
		$mo2f_api          = new Molms_Mo2f_Api();
		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
	}

	public function get_curl_error_message() {
		$message = esc_html( molms_lt( 'Please enable curl extension.' ) ) .
		           ' <a href="admin.php?page=molms_troubleshooting">' .
		           esc_html( molms_lt( 'Click here' ) ) .
		           ' </a> ' .
		           esc_html( molms_lt( 'for the steps to enable curl.' ) );

		return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
	}

	public function mo2f_evaluate_risk( $useremail, $sessionUuid ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url          = MOLMS_HOST_NAME . '/moas/rest/rba/evaluate-risk';
		$customer_key = get_site_option( 'mo2f_customerKey' );
		$field_string = array(
			'customerKey' => $customer_key,
			'appSecret'   => get_site_option( 'mo2f_app_secret' ),
			'userKey'     => $useremail,
			'sessionUuid' => $sessionUuid
		);
		$mo2f_api     = new Molms_Mo2f_Api();

		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
	}

	public function mo2f_register_rba_profile( $useremail, $sessionUuid ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url               = MOLMS_HOST_NAME . '/moas/rest/rba/register-profile';
		$customer_key      = get_site_option( 'mo2f_customerKey' );
		$field_string      = array(
			'customerKey' => $customer_key,
			'userKey'     => $useremail,
			'sessionUuid' => $sessionUuid
		);
		$mo2f_api          = new Molms_Mo2f_Api();
		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
	}

	public function mo2f_get_app_secret() {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$mo2f_api = new Molms_Mo2f_Api();

		$url          = MOLMS_HOST_NAME . '/moas/rest/customer/getapp-secret';
		$customer_key = get_site_option( 'mo2f_customerKey' );
		$field_string = array(
			'customerId' => $customer_key
		);

		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
	}

	public function mo2f_google_auth_service( $useremail, $googleAuthenticatorName = "" ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}
		$mo2f_api     = new Molms_Mo2f_Api();
		$url          = MOLMS_HOST_NAME . '/moas/api/auth/google-auth-secret';
		$customer_key = get_site_option( 'mo2f_customerKey' );
		$field_string = array(
			'customerKey'             => $customer_key,
			'username'                => $useremail,
			'googleAuthenticatorName' => $googleAuthenticatorName
		);

		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
	}

	public function mo2f_validate_google_auth( $useremail, $otptoken, $secret ) {
		global $molms_dirName;
		if ( MOLMS_IS_ONPREM ) {
			include_once $molms_dirName . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'gaonprem.php';
			$gauth_obj = new molms_Google_auth_onpremise();
			$secret    = isset( $_SESSION['secret_ga'] ) ? sanitize_text_field( $_SESSION['secret_ga'] ) : $secret;
			$content   = $gauth_obj->verifyCode( $secret, $otptoken );
			$value     = json_decode( $content, true );
			if ( $value['status'] == 'SUCCESS' ) {
				$user    = wp_get_current_user();
				$user_id = $user->ID;
				$gauth_obj->mo_GAuth_set_secret( $user_id, $secret );
				update_user_meta( $user_id, 'mo2f_2FA_method_to_configure', 'Google Authenticator' );
				update_user_meta( $user_id, 'mo2f_external_app_type', "Google Authenticator" );
				global $molms_db_queries;//might not need this
				$molms_db_queries->update_user_details( $user_id, array( 'mo2f_configured_2FA_method' => 'Google Authenticator' ) );
			}
		} else {
			if ( ! molms_2f_Utility::is_curl_installed() ) {
				return $this->get_curl_error_message();
			}
			$url      = MOLMS_HOST_NAME . '/moas/api/auth/validate-google-auth-secret';
			$mo2f_api = new Molms_Mo2f_Api();

			$customer_key = get_site_option( 'mo2f_customerKey' );
			$field_string = array(
				'customerKey'       => $customer_key,
				'username'          => $useremail,
				'secret'            => $secret,
				'otpToken'          => $otptoken,
				'authenticatorType' => 'GOOGLE AUTHENTICATOR',
			);

			$http_header_array = $mo2f_api->get_http_header_array();
			$content           = $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
		}

		return $content;
	}
}
