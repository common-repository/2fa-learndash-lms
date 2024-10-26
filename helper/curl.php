<?php

class molms_cURL {
	public static function create_customer( $email, $company, $password, $phone = '', $first_name = '', $last_name = '' ) {
		$url      = molms_Constants::MOLMS_HOST_NAME . '/moas/rest/customer/add';
		$fields   = array(
			'companyName'     => $company,
			'areaOfInterest'  => 'WordPress 2 Factor Authentication Plugin',
			'productInterest' => 'API_2FA',
			'firstname'       => $first_name,
			'lastname'        => $last_name,
			'email'           => $email,
			'phone'           => $phone,
			'password'        => $password
		);
		$json     = wp_json_encode( $fields );
		$mo2f_api = new Molms_Mo2f_Api();
		$response = $mo2f_api->make_curl_call( $url, $json );

		return $response;
	}

	public static function get_customer_key( $email, $password ) {
		$url      = molms_Constants::MOLMS_HOST_NAME . "/moas/rest/customer/key";
		$fields   = array(
			'email'    => $email,
			'password' => $password
		);
		$json     = wp_json_encode( $fields );
		$mo2f_api = new Molms_Mo2f_Api();
		$response = $mo2f_api->make_curl_call( $url, $json );

		return $response;
	}

	public function submit_contact_us( $q_email, $q_phone, $query ) {
		$current_user = wp_get_current_user();
		$url          = molms_Constants::MOLMS_HOST_NAME . "/moas/rest/customer/contact-us";

		$is_nc_with_1_user = molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' ) && molms_Utility::get_mo2f_db_option( 'mo2f_is_NNC', 'get_site_option' );
		$is_ec_with_1_user = ! molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' );
		$onprem            = MOLMS_IS_ONPREM ? 'O' : 'C';

		$customer_feature = "";

		if ( $is_ec_with_1_user ) {
			$customer_feature = "V1";
		} elseif ( $is_nc_with_1_user ) {
			$customer_feature = "V3";
		}
		global $molms_utility;
		$query = '[WordPress 2FA for LMS Plugin: ' . $onprem . $customer_feature . ' - V ' . MOLMS_VERSION . ']: ' . $query;

		$fields       = array(
			'firstName' => $current_user->user_firstname,
			'lastName'  => $current_user->user_lastname,
			'company'   => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '',
			'email'     => $q_email,
			'ccEmail'   => '2fasupport@xecurify.com',
			'phone'     => $q_phone,
			'query'     => $query
		);
		$field_string = wp_json_encode( $fields );
		$mo2f_api     = new Molms_Mo2f_Api();
		$response     = $mo2f_api->make_curl_call( $url, $field_string );

		return true;
	}

	//CHECK
	public function send_otp_token( $auth_type, $phone, $email ) {
		$url          = molms_Constants::MOLMS_HOST_NAME . '/moas/api/auth/challenge';
		$customer_key = molms_Constants::DEFAULT_CUSTOMER_KEY;
		$api_key      = molms_Constants::DEFAULT_API_KEY;

		$fields     = array(
			'customerKey'     => $customer_key,
			'email'           => $email,
			'phone'           => $phone,
			'authType'        => $auth_type,
			'transactionName' => 'miniOrange 2-Factor'
		);
		$json       = wp_json_encode( $fields );
		$authHeader = $this->createAuthHeader( $customer_key, $api_key );
		$mo2f_api   = new Molms_Mo2f_Api();
		$response   = $mo2f_api->make_curl_call( $url, $json, $authHeader );

		return $response;
	}

	private static function createAuthHeader( $customer_key, $api_key ) {
		$currentTimestampInMillis = round( microtime( true ) * 1000 );
		$currentTimestampInMillis = number_format( $currentTimestampInMillis, 0, '', '' );

		$string_to_hash = $customer_key . $currentTimestampInMillis . $api_key;
		$authHeader     = hash( "sha512", $string_to_hash );

		$header = [
			"Content-Type"  => "application/json",
			"Customer-Key"  => $customer_key,
			"Timestamp"     => $currentTimestampInMillis,
			"Authorization" => $authHeader
		];

		return $header;
	}

	public function validate_otp_token( $transaction_id, $otpToken ) {
		$url          = molms_Constants::MOLMS_HOST_NAME . '/moas/api/auth/validate';
		$customer_key = molms_Constants::DEFAULT_CUSTOMER_KEY;
		$api_key      = molms_Constants::DEFAULT_API_KEY;

		$fields = array(
			'txId'  => $transaction_id,
			'token' => $otpToken,
		);

		$json       = wp_json_encode( $fields );
		$authHeader = $this->createAuthHeader( $customer_key, $api_key );
		$mo2f_api   = new Molms_Mo2f_Api();
		$response   = $mo2f_api->make_curl_call( $url, $json, $authHeader );

		return $response;
	}

	public function check_customer( $email ) {
		$url      = molms_Constants::MOLMS_HOST_NAME . "/moas/rest/customer/check-if-exists";
		$fields   = array(
			'email' => $email,
		);
		$json     = wp_json_encode( $fields );
		$mo2f_api = new Molms_Mo2f_Api();
		$response = $mo2f_api->make_curl_call( $url, $json );

		return $response;
	}

	public function mo_lms_forgot_password() {
		$url          = molms_Constants::MOLMS_HOST_NAME . '/moas/rest/customer/password-reset';
		$email        = get_site_option( 'mo2f_email' );
		$customer_key = get_site_option( 'mo2f_customerKey' );
		$api_key      = get_site_option( 'mo2f_api_key' );

		$fields = array(
			'email' => $email
		);

		$json       = wp_json_encode( $fields );
		$authHeader = $this->createAuthHeader( $customer_key, $api_key );
		$mo2f_api   = new Molms_Mo2f_Api();
		$response   = $mo2f_api->make_curl_call( $url, $json, $authHeader );

		return $response;
	}

	//added for feedback

	public function send_notification( $toEmail, $subject, $content, $from_email, $fromName, $toName ) {
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		$headers .= 'From: ' . $fromName . '<' . $from_email . '>' . "\r\n";

		mail( $toEmail, $subject, $content, $headers );

		return wp_json_encode( array( "status" => 'SUCCESS', 'statusMessage' => 'SUCCESS' ) );
	}

	public function send_email_alert( $email, $phone, $message, $feedback_option ) {
		global $molms_utility;
		global $user;
		$url          = molms_Constants::MOLMS_HOST_NAME . '/moas/api/notify/send';
		$customer_key = molms_Constants::DEFAULT_CUSTOMER_KEY;
		$api_key      = molms_Constants::DEFAULT_API_KEY;
		$from_email   = 'no-reply@xecurify.com';
		if ( $feedback_option == 'molms_skip_feedback' ) {
			$subject = "Deactivate [Feedback Skipped]: WordPress miniOrange 2FA for LMS Plugin";
		} elseif ( $feedback_option == 'molms_feedback' ) {
			$subject = "Feedback: WordPress miniOrange 2FA for LMS Plugin - " . $email;
		} elseif ( $feedback_option == 'molms_rating' ) {
			$subject = "Feedback: WordPress miniOrange 2FA for LMS Plugin - " . $email;
		}
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$user    = wp_get_current_user();

		$is_nc_with_1_user = molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' ) && molms_Utility::get_mo2f_db_option( 'mo2f_is_NNC', 'get_site_option' );
		$is_ec_with_1_user = ! molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' );
		$onprem            = MOLMS_IS_ONPREM ? 'O' : 'C';

		$customer_feature = "";


		if ( $is_ec_with_1_user ) {
			$customer_feature = "V1";
		} elseif ( $is_nc_with_1_user ) {
			$customer_feature = "V3";
		}

		$query = '[WordPress 2FA for LMS Plugin: ' . $onprem . $customer_feature . ' - V ' . MOLMS_VERSION . ']: ' . $message;

		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$server = sanitize_text_field( $_SERVER['SERVER_NAME'] );
		} else {
			$server = '';
		}

		$content = '<div >Hello, <br><br>First Name :' . $user->user_firstname . '<br><br>Last  Name :' . $user->user_lastname . '<br><br>Company :<a href="' . $server . '" target="_blank">' . $server . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . $email . '" target="_blank">' . $email . '</a><br><br>Query :' . $query . '</div>';


		$fields       = array(
			'customerKey' => $customer_key,
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $customer_key,
				'fromEmail'   => $from_email,
				'fromName'    => 'Xecurify',
				'toEmail'     => '2fasupport@xecurify.com',
				'toName'      => '2fasupport@xecurify.com',
				'subject'     => $subject,
				'content'     => $content
			),
		);
		$field_string = wp_json_encode( $fields );
		$result       = wp_mail( $email, $subject, $content, $headers );
		if ( $result ) {
			$message = 'Email has been successfully sent to notify user.';

			return json_encode( array( "status" => 'SUCCESS', "message" => $message ) );
		} else {
			$authHeader = $this->createAuthHeader( $customer_key, $api_key );
			$response   = self::callAPI( $url, $field_string, $authHeader );

			return $response;
		}
	}

	private static function callAPI( $url, $json_string, $http_header_array = array( "Content-Type"  => "application/json",
	                                                                                 "charset"       => "UTF-8",
	                                                                                 "Authorization" => "Basic"
	)
	) {

		$args = array(
			'method'      => 'POST',
			'body'        => $json_string,
			'timeout'     => '5',
			'redirection' => '5',
			'sslverify'   => true,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $http_header_array
		);

		$molms_api = new Molms_Mo2f_Api();
		$content   = $molms_api->mo2f_wp_remote_post( $url, $args );

		return $content;
	}
}
