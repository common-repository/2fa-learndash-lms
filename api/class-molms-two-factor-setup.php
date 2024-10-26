<?php

/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/
global $molms_dirName;
require_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-mo2f-api.php';

class Molms_Two_Factor_Setup {
	public $email;
	private $auth_mode = 2;    //  miniorange test or not
	private $https_mode = false; // website http or https

	public function check_mobile_status( $tId ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url               = MOLMS_HOST_NAME . '/moas/api/auth/auth-status';
		$fields            = array(
			'txId' => $tId
		);
		$mo2f_api          = new Molms_Mo2f_Api();
		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $fields, $http_header_array );
	}


	public function get_curl_error_message() {
		$message = molms_lt( 'Please enable curl extension.' ) .
		           ' <a href="admin.php?page=molms_troubleshooting">' .
		           molms_lt( 'Click here' ) .
		           ' </a> ' .
		           molms_lt( 'for the steps to enable curl.' );

		return wp_json_encode( array( "status" => 'ERROR', "message" => $message ) );
	}

	public function register_mobile( $useremail ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url          = MOLMS_HOST_NAME . '/moas/api/auth/register-mobile';
		$customer_key = get_site_option( 'mo2f_customerKey' );
		$fields       = array(
			'customerId' => $customer_key,
			'username'   => $useremail
		);
		$mo2f_api     = new Molms_Mo2f_Api();

		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $fields, $http_header_array );
	}

	public function mo_check_user_already_exist( $email ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url               = MOLMS_HOST_NAME . '/moas/api/admin/users/search';
		$customer_key      = get_site_option( 'mo2f_customerKey' );
		$fields            = array(
			'customerKey' => $customer_key,
			'username'    => $email,
		);
		$mo2f_api          = new Molms_Mo2f_Api();
		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $fields, $http_header_array );
	}

	public function mo_create_user( $currentuser, $email ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url               = MOLMS_HOST_NAME . '/moas/api/admin/users/create';
		$customer_key      = get_site_option( 'mo2f_customerKey' );
		$fields            = array(
			'customerKey' => $customer_key,
			'username'    => $email,
			'firstName'   => $currentuser->user_firstname,
			'lastName'    => $currentuser->user_lastname
		);
		$mo2f_api          = new Molms_Mo2f_Api();
		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $fields, $http_header_array );
	}

	public function mo2f_get_userinfo( $email ) {
		if ( ! molms_2f_Utility::is_curl_installed() ) {
			return $this->get_curl_error_message();
		}

		$url               = MOLMS_HOST_NAME . '/moas/api/admin/users/get';
		$customer_key      = get_site_option( 'mo2f_customerKey' );
		$fields            = array(
			'customerKey' => $customer_key,
			'username'    => $email,
		);
		$mo2f_api          = new Molms_Mo2f_Api();
		$http_header_array = $mo2f_api->get_http_header_array();

		return $mo2f_api->make_curl_call( $url, $fields, $http_header_array );
	}

	public function mo2f_update_userinfo( $email, $auth_type, $phone, $tname, $enableAdminSecondFactor ) {
		$cloud_methods = array( 'MOBILE AUTHENTICATION', 'PUSH NOTIFICATIONS', 'SMS', 'SOFT TOKEN' );
		if ( MOLMS_IS_ONPREM and ! in_array( $auth_type, $cloud_methods ) ) {
			$response = wp_json_encode( array( "status" => 'SUCCESS' ) );
		} else {
			if ( ! molms_2f_Utility::is_curl_installed() ) {
				return $this->get_curl_error_message();
			}

			$url          = MOLMS_HOST_NAME . '/moas/api/admin/users/update';
			$customer_key = get_site_option( 'mo2f_customerKey' );


			$fields = array(
				'customerKey'            => $customer_key,
				'username'               => $email,
				'phone'                  => $phone,
				'authType'               => $auth_type,
				'transactionName'        => $tname,
				'adminLoginSecondFactor' => $enableAdminSecondFactor
			);

			$mo2f_api = new Molms_Mo2f_Api();

			$http_header_array = $mo2f_api->get_http_header_array();

			$response = $mo2f_api->make_curl_call( $url, $fields, $http_header_array );
		}

		return $response;
	}

	public function register_kba_details( $email, $question1, $answer1, $question2, $answer2, $question3, $answer3, $user_id = null ) {
		if ( MOLMS_IS_ONPREM ) {
			$answer1         = md5( $answer1 );
			$answer2         = md5( $answer2 );
			$answer3         = md5( $answer3 );
			$question_answer = array( $question1 => $answer1, $question2 => $answer2, $question3 => $answer3 );
			update_user_meta( $user_id, 'mo2f_kba_challenge', $question_answer );
			global $molms_db_queries;
			$molms_db_queries->update_user_details( $user_id, array( 'mo2f_configured_2FA_method' => 'Security Questions' ) );
			$response = wp_json_encode( array( "status" => 'SUCCESS' ) );
		} else {
			if ( ! molms_2f_Utility::is_curl_installed() ) {
				return $this->get_curl_error_message();
			}

			$url          = MOLMS_HOST_NAME . '/moas/api/auth/register';
			$customer_key = get_site_option( 'mo2f_customerKey' );
			$q_and_a_list = "[{\"question\":\"" . $question1 . "\",\"answer\":\"" . $answer1 . "\" },{\"question\":\"" . $question2 . "\",\"answer\":\"" . $answer2 . "\" },{\"question\":\"" . $question3 . "\",\"answer\":\"" . $answer3 . "\" }]";
			$field_string = "{\"customerKey\":\"" . $customer_key . "\",\"username\":\"" . $email . "\",\"questionAnswerList\":" . $q_and_a_list . "}";

			$mo2f_api          = new Molms_Mo2f_Api();
			$http_header_array = $mo2f_api->get_http_header_array();

			$response = $mo2f_api->make_curl_call( $url, $field_string, $http_header_array );
		}

		return $response;
	}
}
