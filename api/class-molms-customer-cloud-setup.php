<?php
/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/

global $molms_dirName;
require_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-mo2f-api.php';

class Molms_Customer_Cloud_Setup {
	public $email;
	public $phone;
	public $customer_key;
	public $transaction_id;

	private $auth_mode = 2; //  miniorange test or not
	private $https_mode = false; // website http or https


	public function check_customer() {
		$url          = MOLMS_HOST_NAME . "/moas/rest/customer/check-if-exists";
		$email        = get_site_option( "mo2f_email" );
		$mo2f_api     = new Molms_Mo2f_Api();
		$fields       = array(
			'email' => $email
		);
		$field_string = wp_json_encode( $fields );

		$headers = array( "Content-Type" => "application/json", "charset" => "UTF-8", "Authorization" => "Basic" );

		$response = $mo2f_api->make_curl_call( $url, $field_string );

		return $response;
	}

	public function send_email_alert( $email, $phone, $message ) {
		$url = MOLMS_HOST_NAME . '/moas/api/notify/send';

		$mo2f_api     = new Molms_Mo2f_Api();
		$customer_key = "16555";
		$api_key      = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

		$current_time_in_millis = $mo2f_api->get_timestamp();
		$string_to_hash         = $customer_key . $current_time_in_millis . $api_key;
		$hash_value             = hash( "sha512", $string_to_hash );
		$from_email             = $email;
		$subject                = "WordPress 2FA Plugin Feedback - " . $email;

		global $user;
		$user = wp_get_current_user();

		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$server = sanitize_text_field( $_SERVER['SERVER_NAME'] );
		} else {
			$server = '';
		}

		$query = '[WordPress 2FA for LMS Plugin: - V ' . MOLMS_VERSION . ']: ' . $message;

		$content = '<div >First Name :' . $user->user_firstname . '<br><br>Last  Name :' . $user->user_lastname . '   <br><br>Company :<a href="' . $server . '" target="_blank" >' . $server . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . $from_email . '" target="_blank">' . $from_email . '</a><br><br>Query :' . $query . '</div>';

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

		$headers = $mo2f_api->get_http_header_array();

		$response = $mo2f_api->make_curl_call( $url, $field_string, $headers );

		return $response;
	}

	public function create_customer() {
		global $molms_db_queries;
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			$message = 'Please enable curl extension. <a href="admin.php?page=molms_troubleshooting">Click here</a> for the steps to enable curl.';

			return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
		}

		$url      = MOLMS_HOST_NAME . '/moas/rest/customer/add';
		$mo2f_api = new Molms_Mo2f_Api();
		global $user;
		$user        = wp_get_current_user();
		$this->email = get_site_option( 'mo2f_email' );
		$this->phone = $molms_db_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
		$password    = get_site_option( 'mo2f_password' );

		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$server = sanitize_text_field( $_SERVER['SERVER_NAME'] );
		} else {
			$server = '';
		}

		$company = get_site_option( 'mo2f_admin_company' ) != '' ? get_site_option( 'mo2f_admin_company' ) : $server;

		$fields       = array(
			'companyName'     => $company,
			'areaOfInterest'  => 'WordPress 2 Factor Authentication Plugin',
			'productInterest' => 'API_2FA',
			'email'           => $this->email,
			'phone'           => $this->phone,
			'password'        => $password
		);
		$field_string = wp_json_encode( $fields );
		$headers      = array( "Content-Type" => "application/json", "charset" => "UTF-8", "Authorization" => "Basic" );

		$content = $mo2f_api->make_curl_call( $url, $field_string );

		return $content;
	}


	public function get_customer_key() {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			$message = 'Please enable curl extension. <a href="admin.php?page=molms_troubleshooting">Click here</a> for the steps to enable curl.';

			return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
		}

		$url = MOLMS_HOST_NAME . "/moas/rest/customer/key";

		$email        = get_site_option( "mo2f_email" );
		$password     = get_site_option( "mo2f_password" );
		$mo2f_api     = new Molms_Mo2f_Api();
		$fields       = array(
			'email'    => $email,
			'password' => $password
		);
		$field_string = wp_json_encode( $fields );

		$headers = array( "Content-Type" => "application/json", "charset" => "UTF-8", "Authorization" => "Basic" );

		$content = $mo2f_api->make_curl_call( $url, $field_string );

		return $content;
	}


	public function send_otp_token( $u_key, $auth_type, $c_key, $api_key, $currentuser = null ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			$message = 'Please enable curl extension. <a href="admin.php?page=molms_troubleshooting">Click here</a> for the steps to enable curl.';

			return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
		}

		$url      = MOLMS_HOST_NAME . '/moas/api/auth/challenge';
		$mo2f_api = new Molms_Mo2f_Api();
		/* The customer Key provided to you */
		$customer_key = $c_key;

		/* The customer API Key provided to you */
		$api_key = $api_key;

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$current_time_in_millis = $mo2f_api->get_timestamp();

		/* Creating the Hash using SHA-512 algorithm */
		$string_to_hash = $customer_key . $current_time_in_millis . $api_key;
		$hash_value     = hash( "sha512", $string_to_hash );

		$headers = $mo2f_api->get_http_header_array();

		$fields = '';
		if ( $auth_type == 'EMAIL' || $auth_type == 'OTP Over Email' || $auth_type == 'OUT OF BAND EMAIL' ) {
			$fields = array(
				'customerKey'     => $customer_key,
				'email'           => $u_key,
				'authType'        => $auth_type,
				'transactionName' => 'WordPress 2 Factor Authentication Plugin'
			);
		} elseif ( $auth_type == 'SMS' ) {
			$auth_type = "SMS";
			$fields    = array(
				'customerKey' => $customer_key,
				'phone'       => $u_key,
				'authType'    => $auth_type
			);
		} else {
			$fields = array(
				'customerKey'     => $customer_key,
				'username'        => $u_key,
				'authType'        => $auth_type,
				'transactionName' => 'WordPress 2 Factor Authentication Plugin'
			);
		}

		$field_string = wp_json_encode( $fields );

		$content = $mo2f_api->make_curl_call( $url, $field_string, $headers );

		return $content;
	}


	public function get_customer_transactions( $c_key, $api_key ) {
		$url = MOLMS_HOST_NAME . '/moas/rest/customer/license';

		$customer_key           = $c_key;
		$api_key                = $api_key;
		$mo2f_api               = new Molms_Mo2f_Api();
		$current_time_in_millis = $mo2f_api->get_timestamp();
		$string_to_hash         = $customer_key . $current_time_in_millis . $api_key;
		$hash_value             = hash( "sha512", $string_to_hash );

		$fields = '';
		$fields = array(
			'customerId'      => $customer_key,
			'applicationName' => 'wp_2fa',
			'licenseType'     => 'DEMO'
		);

		$field_string = wp_json_encode( $fields );

		$headers = $mo2f_api->get_http_header_array();

		$content = $mo2f_api->make_curl_call( $url, $field_string, $headers );


		return $content;
	}


	public function validate_otp_token( $auth_type, $username, $transaction_id, $otpToken, $c_key, $customerApiKey, $current_user = null ) {
		$content = '';
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			$message = 'Please enable curl extension. <a href="admin.php?page=molms_troubleshooting">Click here</a> for the steps to enable curl.';

			return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
		}

		$url      = MOLMS_HOST_NAME . '/moas/api/auth/validate';
		$mo2f_api = new Molms_Mo2f_Api();
		/* The customer Key provided to you */
		$customer_key = $c_key;

		/* The customer API Key provided to you */
		$api_key = $customerApiKey;

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$current_time_in_millis = $mo2f_api->get_timestamp();

		/* Creating the Hash using SHA-512 algorithm */
		$string_to_hash = $customer_key . $current_time_in_millis . $api_key;
		$hash_value     = hash( "sha512", $string_to_hash );

		$headers = $mo2f_api->get_http_header_array();
		$fields  = '';
		if ( $auth_type == 'SOFT TOKEN' || $auth_type == 'GOOGLE AUTHENTICATOR' ) {
			/*check for soft token*/
			$fields = array(
				'customerKey' => $customer_key,
				'username'    => $username,
				'token'       => $otpToken,
				'authType'    => $auth_type
			);
		} elseif ( $auth_type == 'KBA' ) {
			$fields = array(
				'txId'    => $transaction_id,
				'answers' => array(
					array(
						'question' => $otpToken[0],
						'answer'   => $otpToken[1]
					),
					array(
						'question' => $otpToken[2],
						'answer'   => $otpToken[3]
					)
				)
			);
		} else {
			//*check for otp over sms/email
			$fields = array(
				'txId'  => $transaction_id,
				'token' => $otpToken
			);
		}
		$field_string = wp_json_encode( $fields );


		$content = $mo2f_api->make_curl_call( $url, $field_string, $headers );

		return $content;
	}
}
