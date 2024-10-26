<?php
/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/
global $molms_dirName;
require_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-mo2f-api.php';

class Molms_Customer_Setup extends Molms_Customer_Cloud_Setup {
	public function send_otp_token( $u_key, $auth_type, $c_key, $api_key, $currentuser = null ) {
		global $molms_dirName;
		$cloud_methods = array( 'MOBILE AUTHENTICATION', 'PUSH NOTIFICATIONS', 'SMS' );
		if ( MOLMS_IS_ONPREM and ! in_array( $auth_type, $cloud_methods ) ) {
			include_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-onprem-redirect.php';
			$mo2fOnPremRedirect = new Molms_Onprem_Redirect();
			if ( is_null( $currentuser ) or ! isset( $currentuser ) ) {
				$currentuser = wp_get_current_user();
			}
			$content = $mo2fOnPremRedirect->OnpremSendRedirect( $u_key, $auth_type, $currentuser );
		} else {
			$content = parent::send_otp_token( $u_key, $auth_type, $c_key, $api_key, $currentuser = null );
		}

		return $content;
	}


	public function validate_otp_token( $auth_type, $username, $transaction_id, $otpToken, $c_key, $customerApiKey, $current_user = null ) {
		global $molms_dirName;
		$content = '';
		if ( MOLMS_IS_ONPREM and $auth_type != 'SOFT TOKEN' and $auth_type != 'OTP Over Email' and $auth_type != 'SMS' and $auth_type != 'OTP Over SMS' ) {
			include_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-onprem-redirect.php';
			$mo2fOnPremRedirect = new Molms_Onprem_Redirect();
			if ( ! isset( $current_user ) or is_null( $current_user ) ) {
				$current_user = wp_get_current_user();
			}
			$content = $mo2fOnPremRedirect->OnpremValidateRedirect( $auth_type, $otpToken, $current_user );
		} else {
			$content = parent::validate_otp_token( $auth_type, $username, $transaction_id, $otpToken, $c_key, $customerApiKey, $current_user = null );
		}

		return $content;
	}
}
