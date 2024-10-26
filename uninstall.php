<?php

//if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
global $wpdb;

$value = get_site_option( 'mo_lms_registration_status' );
if ( isset( $value ) || ! empty( $value ) ) {
	delete_site_option( 'mo2f_email' );
}
delete_site_option( 'mo2f_customerKey' );
delete_site_option( 'mo2f_api_key' );
delete_site_option( 'mo2f_customer_token' );
delete_site_option( 'mo_lms_transactionId' );
delete_site_option( 'mo_lms_registration_status' );

delete_site_option( 'molms_donot_show_trial_notice_always' );
delete_site_option( 'mo2f_customerKey' );
delete_site_option( 'mo2f_api_key' );
delete_site_option( 'mo_lms_customer_token' );
delete_site_option( 'mo2f_app_secret' );
delete_site_option( 'mo_lms_message' );
delete_site_option( 'mo_lms_transactionId' );
delete_site_option( 'mo_lms_registration_status' );

delete_site_option( 'mo_lms_company' );
delete_site_option( 'mo_lms_firstName' );
delete_site_option( 'mo_lms_lastName' );
delete_site_option( 'mo_lms_password' );
delete_site_option( 'mo2f_email' );
delete_site_option( 'mo_lms_admin_phone' );
delete_site_option( 'mo2f_tour_started' );

delete_site_option( 'mo_lms_registration_status' );
delete_site_option( 'totalUsersCloud' );
delete_site_option( 'mo2f_inline_registration' );
delete_site_option( 'mo_2factor_user_registration_status' );
delete_site_option( 'mo2f_GA_account_name' );

delete_site_option( 'mo2f_login_option' );
delete_site_option( 'mo2f_planname' );
delete_site_option( 'mo2f_activated_time' );
delete_site_option( 'mo2f_number_of_transactions' );
delete_site_option( 'mo2f_set_transactions' );
delete_site_option( 'molms_sms_transactions' );
delete_site_option( 'mo2f_enable_xmlrpc' );
delete_site_option( 'mo2f_onprem_admin' );
delete_site_option( 'mo2f_two_factor_tour' );
delete_site_option( 'mo2f_attempts_before_redirect' );
delete_site_option( 'molms_register_with_another_email' );
delete_site_option( 'mo_lms_enable_comment_spam_blocking' );
delete_site_option( 'mo_lms_enable_comment_recaptcha' );


delete_site_option( 'mo_lms_enable_2fa' );
delete_site_option( 'molms_activate_plugin' );

delete_site_option( 'mo2f_deviceid_enabled' );

delete_site_option( 'mo_lms_dbversion' );

delete_site_option( 'mo_lms_dbversion' );

delete_site_option( 'mo2f_two_factor' );

if ( get_site_option( 'is_onprem' ) ) {
	$users = get_users( array() );
	foreach ( $users as $user ) {
		delete_user_meta( $user->ID, 'currentMethod' );
		delete_user_meta( $user->ID, 'email' );
		delete_user_meta( $user->ID, 'mo2f_2FA_method_to_configure' );
		delete_user_meta( $user->ID, 'Security Questions' );
		delete_user_meta( $user->ID, 'Email Verification' );
		delete_user_meta( $user->ID, 'mo2f_kba_challenge' );
		delete_user_meta( $user->ID, 'mo2f_2FA_method_to_test' );
		delete_user_meta( $user->ID, 'kba_questions_user' );
		delete_user_meta( $user->ID, 'Google Authenticator' );
		delete_user_meta( $user->ID, 'mo2f_gauth_key' );
		delete_user_meta( $user->ID, 'mo2f_get_auth_rnd_string' );
	}
}

$users = get_users( array() );
foreach ( $users as $user ) {
	delete_user_meta( $user->ID, 'phone_verification_status' );
	delete_user_meta( $user->ID, 'test_2FA' );
	delete_user_meta( $user->ID, 'mo2f_2FA_method_to_configure' );
	delete_user_meta( $user->ID, 'configure_2FA' );
	delete_user_meta( $user->ID, 'mo2f_2FA_method_to_test' );
	delete_user_meta( $user->ID, 'mo2f_phone' );
	delete_user_meta( $user->ID, 'mo_2factor_user_registration_status' );
	delete_user_meta( $user->ID, 'mo2f_external_app_type' );
	delete_user_meta( $user->ID, 'mo2f_user_login_attempts' );
	delete_user_meta( $user->ID, 'mo2f_transactionId' );
	delete_user_meta( $user->ID, 'mo2f_user_phone' );
	delete_user_meta( $user->ID, 'miniorageqr' );
	delete_user_meta( $user->ID, 'mo2f_google_auth' );
	delete_user_meta( $user->ID, 'mo2f_email_miniOrange' );
	delete_user_meta( $user->ID, 'mo2f_kba_challenge' );
	delete_user_meta( $user->ID, 'mo2f_otp_email_code' );
	delete_user_meta( $user->ID, 'mo2f_otp_email_time' );
	delete_user_meta( $user->ID, 'tempRegEmail' );
	delete_user_meta( $user->ID, 'mo2f_EV_txid' );
}


// Remove all values of 2FA on deactivate
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}molms_user_details" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}molms_user_login_info" );

delete_site_option( 'mo2f_email' );
delete_site_option( 'mo2f_MOLMS_HOST_NAME' );
delete_site_option( 'user_phone' );
delete_site_option( 'mo2f_customerKey' );
delete_site_option( 'mo2f_api_key' );
delete_site_option( 'mo2f_customer_token' );
delete_site_option( 'mo2f_message' );
delete_site_option( 'mo_2factor_admin_registration_status' );
delete_site_option( 'mo2f_login_message' );
delete_site_option( 'mo_2f_login_type_enabled' );
delete_site_option( 'mo2f_admin_disabled_status' );
delete_site_option( 'mo2f_disabled_status' );
delete_site_option( 'mo2f_miniorange_admin' );
delete_site_option( 'mo2f_enable_forgotphone' );
delete_site_option( 'mo2f_enable_login_with_2nd_factor' );
delete_site_option( 'molms_activate_plugin' );
delete_site_option( 'mo2f_remember_device' );
delete_site_option( 'mo2f_app_secret' );
delete_site_option( 'mo2f_enable_custom' );
delete_site_option( 'mo2f_show_sms_transaction_message' );
delete_site_option( 'mo2f_admin_first_name' );
delete_site_option( 'mo2_admin_last_name' );
delete_site_option( 'mo2f_admin_company' );
delete_site_option( 'mo2f_proxy_host' );
delete_site_option( 'mo2f_port_number' );
delete_site_option( 'mo2f_proxy_username' );
delete_site_option( 'mo2f_proxy_password' );
delete_site_option( 'mo2f_auth_methods_for_users' );
delete_site_option( 'mo2f_enable_mobile_support' );
delete_site_option( 'mo2f_login_policy' );
delete_site_option( 'mo2f_msg_counter' );
delete_site_option( 'mo2f_modal_display' );
delete_site_option( 'mo2f_disable_poweredby' );
delete_site_option( 'mo2f_new_customer' );
delete_site_option( 'mo2f_enable_2fa_for_users' );
delete_site_option( 'mo2f_phone' );
delete_site_option( 'mo2f_existing_user_values_updated' );
delete_site_option( 'mo2f_login_option_updated' );
delete_site_option( 'molms_dbversion' );
delete_site_option( 'mo2f_bug_fix_done' );
delete_site_option( 'mo2f_feedback_form' );
delete_site_option( 'mo2f_enable_2fa_prompt_on_login_page' );
delete_site_option( 'mo2f_configured_2_factor_method' );
delete_site_option( 'mo2f_enable_2fa' );
delete_site_option( 'kba_questions' );
delete_site_option( 'mo2f_customer_selected_plan' );
delete_site_option( 'mo2f_admin_first_name' );
delete_site_option( 'mo2_admin_last_name' );
delete_site_option( 'mo2f_admin_company' );
delete_site_option( 'mo2f_db_option_updated' );
delete_site_option( 'mo2f_login_option_updated' );
delete_site_option( 'mo2f_encryption_key' );
delete_site_option( 'mo2f_google_appname' );


delete_site_option( 'mo2f_custom_plugin_name' );
delete_site_option( 'skip_tour' );
delete_site_option( 'mo_lms_new_registration' );
delete_site_option( 'mo2f_is_NC' );

delete_site_option( 'mo_lms_enable_log_requests' );
delete_site_option( 'mo2f_data_storage' );
delete_site_option( 'mo_lms_enable_rename_login_url' );
delete_site_option( 'login_page_url' );
delete_site_option( 'recovery_mode_email_last_sent' );
delete_site_option( 'mo2f_is_NNC' );


//delete all stored key-value pairs for the roles
global $wp_roles;
if ( ! isset( $wp_roles ) ) {
	$wp_roles = new WP_Roles();
}
foreach ( $wp_roles->role_names as $id => $name ) {
	delete_site_option( 'mo2fa_' . $id );
	delete_site_option( 'mo2fa_' . $id . '_login_url' );
}

//delete previous version key-value pairs
delete_site_option( 'mo_2factor_admin_mobile_registration_status' );
delete_site_option( 'mo_2factor_registration_status' );
delete_site_option( 'mo_2factor_temp_status' );
delete_site_option( 'mo2f_login_username' );
delete_site_option( 'mo2f-login-qrCode' );
delete_site_option( 'mo2f_transactionId' );
delete_site_option( 'mo_2factor_login_status' );
delete_site_option( 'mo2f_configured_2_factor_method' );
delete_site_option( 'mo2f_enable_2fa' );
delete_site_option( 'kba_questions' );
delete_site_option( 'mo2f_customerKey' );
delete_site_option( 'mo2f_user_sync' );
