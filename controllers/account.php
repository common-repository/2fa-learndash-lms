<?php
global $molms_utility, $molms_dirName, $molms_db_queries;

if ( current_user_can( 'manage_options' ) and isset( $_POST['option'] ) ) {
	$option = sanitize_text_field( $_POST['option'] );
	switch ( $option ) {
		case "molms_register_customer":
			molms_register_customer();
			break;
		case "molms_verify_customer":
			molms_verify_customer();
			break;
		case "molms_cancel":
			molms_revert_back_registration();
			break;
		case "molms_reset_password":
			molms_reset_password();
			break;
		case "molms_verifycustomer":
			molms_goto_sign_in_page();
			break;
	}
}

$user                             = wp_get_current_user();
$mo2f_current_registration_status = get_site_option( 'mo_2factor_user_registration_status' );
if ( ( get_site_option( 'molms_verify_customer' ) == 'true' || ( get_site_option( 'mo2f_email' ) && ! get_site_option( 'mo2f_customerKey' ) ) ) && $mo2f_current_registration_status == "MO_2_FACTOR_VERIFY_CUSTOMER" ) {
	$admin_email = get_site_option( 'mo2f_email' ) ? get_site_option( 'mo2f_email' ) : "";
	include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'login.php';
} elseif ( ! $molms_utility->icr() ) {
	delete_site_option( 'password_mismatch' );
	update_site_option( 'mo_lms_new_registration', 'true' );
	update_site_option( 'mo_2factor_user_registration_status', 'REGISTRATION_STARTED' );
	include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'register.php';
} else {
	$email             = get_site_option( 'mo2f_email' );
	$key               = get_site_option( 'mo2f_customerKey' );
	$api               = get_site_option( 'mo2f_api_key' );
	$token             = get_site_option( 'mo2f_customer_token' );
	$EmailTransactions = molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' );
	$EmailTransactions = $EmailTransactions ? $EmailTransactions : 0;
	$SMSTransactions   = get_site_option( 'molms_sms_transactions' ) ? get_site_option( 'molms_sms_transactions' ) : 0;
	include $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'profile.php';
}

/* REGISTRATION RELATED FUNCTIONS */

//Function to register new customer
function molms_register_customer() {
	//validate and sanitize
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mo2f-account-nonce' ) ) {
		do_action( 'molms_show_message', 'ERROR', 'ERROR' );

		return;
	}

	global $molms_utility, $molms_db_queries;
	$user    = wp_get_current_user();
	$email   = sanitize_email( $_POST['email'] );
	$company = sanitize_text_field( $_SERVER["SERVER_NAME"] );

	$password        = sanitize_text_field( $_POST['password'] );
	$confirmPassword = sanitize_text_field( $_POST['confirmPassword'] );

	if ( strlen( $password ) < 6 || strlen( $confirmPassword ) < 6 ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'PASS_LENGTH' ), 'ERROR' );

		return;
	}

	if ( $password != $confirmPassword ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'PASS_MISMATCH' ), 'ERROR' );

		return;
	}
	if ( molms_Utility::check_empty_or_null( $email ) || molms_Utility::check_empty_or_null( $password )
	     || molms_Utility::check_empty_or_null( $confirmPassword )
	) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'REQUIRED_FIELDS' ), 'ERROR' );

		return;
	}

	update_site_option( 'mo2f_email', $email );

	update_site_option( 'mo_lms_company', $company );

	update_site_option( 'mo_lms_password', $password );

	$customer = new molms_cURL();
	$content  = json_decode( $customer->check_customer( $email ), true );
	$molms_db_queries->insert_user( $user->ID );
	switch ( $content['status'] ) {
		case 'CUSTOMER_NOT_FOUND':
			$customer_key = json_decode( $customer->create_customer( $email, $company, $password, $phone = '', $first_name = '', $last_name = '' ), true );

			if ( strcasecmp( $customer_key['status'], 'SUCCESS' ) == 0 ) {
				update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );
				update_site_option( 'mo2f_email', $email );
				molms_save_success_customer_config( $email, $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], $customer_key['appSecret'] );
				molms_get_current_customer( $email, $password );
			}

			break;
		default:
			molms_get_current_customer( $email, $password );
			break;
	}
}


function molms_goto_sign_in_page() {
	$nonce = isset( $_POST['molms_verifycustomer_nonce'] ) ? sanitize_text_field( $_POST['molms_verifycustomer_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mo2f-goto-verifycustomer-nonce' ) ) {
		do_action( 'molms_show_message', 'ERROR', 'ERROR' );

		return;
	}
	global $molms_db_queries;
	$user = wp_get_current_user();
	update_site_option( 'molms_verify_customer', 'true' );
	update_site_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_VERIFY_CUSTOMER' );
}

//Function to go back to the registration page
function molms_revert_back_registration() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mo2f-account-nonce' ) ) {
		do_action( 'molms_show_message', 'ERROR', 'ERROR' );

		return;
	}
	global $molms_db_queries;
	$user = wp_get_current_user();
	delete_site_option( 'mo2f_email' );
	delete_site_option( 'mo_lms_registration_status' );
	delete_site_option( 'molms_verify_customer' );
	update_site_option( 'mo_2factor_user_registration_status', '' );
}


//Function to reset customer's password
function molms_reset_password() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mo2f-account-nonce' ) ) {
		do_action( 'molms_show_message', 'ERROR', 'ERROR' );

		return;
	}
	$customer                 = new molms_cURL();
	$forgot_password_response = json_decode( $customer->mo_lms_forgot_password() );
	if ( $forgot_password_response->status == 'SUCCESS' ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'RESET_PASS' ), 'SUCCESS' );
	}
}


//Function to verify customer
function molms_verify_customer() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mo2f-account-nonce' ) ) {
		do_action( 'molms_show_message', 'ERROR', 'ERROR' );

		return;
	}
	global $molms_utility;
	$email    = sanitize_email( $_POST['email'] );
	$password = sanitize_text_field( $_POST['password'] );

	if ( $molms_utility->check_empty_or_null( $email ) || $molms_utility->check_empty_or_null( $password ) ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'REQUIRED_FIELDS' ), 'ERROR' );

		return;
	}
	molms_get_current_customer( $email, $password );
}


//Function to get customer details
function molms_get_current_customer( $email, $password ) {
	global $molms_db_queries;
	$user         = wp_get_current_user();
	$customer     = new molms_cURL();
	$content      = $customer->get_customer_key( $email, $password );
	$customer_key = json_decode( $content, true );
	if ( json_last_error() == JSON_ERROR_NONE ) {
		if ( isset( $customer_key['phone'] ) ) {
			update_site_option( 'mo_lms_admin_phone', $customer_key['phone'] );
		}
		update_site_option( 'mo2f_email', $email );

		molms_save_success_customer_config( $email, $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], $customer_key['appSecret'] );
		do_action( 'molms_show_message', molms_Messages::showMessage( 'REG_SUCCESS' ), 'SUCCESS' );
		update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );
		$customerT = new Molms_Customer_Cloud_Setup();
		$content   = json_decode( $customerT->get_customer_transactions( get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
		if ( isset( $content['smsRemaining'] ) ) {
			update_site_option( 'molms_sms_transactions', $content['smsRemaining'] );
		} else {
			update_site_option( 'molms_sms_transactions', 0 );
		}
	} else {
		update_site_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_VERIFY_CUSTOMER' );
		update_site_option( 'molms_verify_customer', 'true' );
		delete_site_option( 'mo_lms_new_registration' );
		do_action( 'molms_show_message', molms_Messages::showMessage( 'ACCOUNT_EXISTS' ), 'ERROR' );
	}
}


//Save all required fields on customer registration/retrieval complete.
function molms_save_success_customer_config( $email, $id, $api_key, $token, $appSecret ) {
	global $molms_db_queries;

	$user = wp_get_current_user();
	update_site_option( 'mo2f_customerKey', $id );
	update_site_option( 'mo2f_api_key', $api_key );
	update_site_option( 'mo2f_customer_token', $token );
	update_site_option( 'mo2f_app_secret', $appSecret );
	update_site_option( 'mo_lms_enable_log_requests', true );
	update_site_option( 'mo2f_miniorange_admin', $user->ID );
	update_site_option( 'mo_2factor_admin_registration_status', 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' );
	update_site_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_PLUGIN_SETTINGS' );

	$molms_db_queries->update_user_details(
		$user->ID,
		array(
			'mo2f_user_email'                   => $email,
			'user_registration_with_miniorange' => 'SUCCESS'
		)
	);
	$enduser  = new Molms_Two_Factor_Setup();
	$userinfo = json_decode( $enduser->mo2f_get_userinfo( $email ), true );

	$mo2f_second_factor = 'NONE';
	if ( json_last_error() == JSON_ERROR_NONE ) {
		if ( $userinfo['status'] == 'SUCCESS' ) {
			$mo2f_second_factor = molms_update_and_sync_user_two_factor( $user->ID, $userinfo );
		}
	}
	$configured_2FA_method = '';
	if ( $mo2f_second_factor == 'EMAIL' ) {
		$enduser->mo2f_update_userinfo( $email, 'NONE', null, '', true );
		$configured_2FA_method = 'NONE';
	} elseif ( $mo2f_second_factor != 'NONE' ) {
		$configured_2FA_method = molms_2f_Utility::molms_decode_2_factor( $mo2f_second_factor, "servertowpdb" );
		if ( molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' ) == 0 ) {
			$auth_method_abr = str_replace( ' ', '', $configured_2FA_method );
		} else {
			if ( in_array(
				$configured_2FA_method,
				array(
					'Email Verification',
					'Authy Authenticator',
					'OTP over SMS'
				)
			)
			) {
				$enduser->mo2f_update_userinfo( $email, 'NONE', null, '', true );
			}
		}
	}

	$mo2f_message = molms_2fConstants:: langTranslate( "ACCOUNT_RETRIEVED_SUCCESSFULLY" );
	if ( $configured_2FA_method != 'NONE' && molms_Utility::get_mo2f_db_option( 'mo2f_is_NC', 'get_site_option' ) == 0 ) {
		$mo2f_message .= ' <b>' . $configured_2FA_method . '</b> ' . molms_2fConstants:: langTranslate( "DEFAULT_2ND_FACTOR" ) . '. ';
	}
	$mo2f_message .= '<a href=\"admin.php?page=molms_two_fa\" >' . molms_2fConstants:: langTranslate( "CLICK_HERE" ) . '</a> ' . molms_2fConstants:: langTranslate( "CONFIGURE_2FA" );

	delete_user_meta( $user->ID, 'register_account' );

	$mo2f_customer_selected_plan = get_site_option( 'mo2f_customer_selected_plan' );
	if ( ! empty( $mo2f_customer_selected_plan ) ) {
		delete_site_option( 'mo2f_customer_selected_plan' );

		if ( molms_Utility::get_mo2f_db_option( 'mo2f_planname', 'get_site_option' ) == 'addon_plan' ) {
			?>
            <script>window.location.href = "admin.php?page=molms_addons";</script><?php
		} else {
			?>
            <script>window.location.href = "admin.php?page=molms_upgrade";</script><?php
		}
	} elseif ( $mo2f_second_factor == 'NONE' ) {
		if ( get_user_meta( $user->ID, 'register_account_popup', true ) ) {
			update_user_meta( $user->ID, 'configure_2FA', 1 );
		}
	}

	update_site_option( 'mo2f_message', $mo2f_message );
	delete_user_meta( $user->ID, 'register_account_popup' );
	delete_site_option( 'molms_verify_customer' );
	delete_site_option( 'mo_lms_registration_status' );
	delete_site_option( 'mo_lms_password' );
}
