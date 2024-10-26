<?php

$current_user = wp_get_current_user();
if ( current_user_can( 'manage_options' ) ) {

	if ( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == 'mo2f_session_settings_save' ) {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'molms-session-nonce' ) ) {
			return;
		}
		$mo2f_factor  = new molms_Miniorange_Authentication();
		$mo2f_success = true;
		update_site_option( 'mo2f_message', 'Your settings are saved successfully.' );
		if ( isset( $_POST['mo2f_simultaneous_session_enable'] ) ) {
			$sessions_allowed = sanitize_text_field( $_POST['mo2f_number_of_sessions'] );
			if ( $sessions_allowed < 1 ) {
				update_site_option( 'mo2f_message', 'Number of simultaneous session should be greater than 1' );
				$mo2f_success = false;
				$mo2f_factor->mo_auth_show_error_message();
			} elseif ( $sessions_allowed < 4 ) {
				update_site_option( 'mo2f_message', 'Please upgrade to apply session limit less than 4' );
				$mo2f_success = false;
				$mo2f_factor->mo_auth_show_error_message();
			} else {
				update_site_option( 'mo2f_simultaneous_session_enable', 1 );
				update_site_option( 'mo2f_simultaneous_session_allowed', $sessions_allowed );
				update_site_option( 'mo2f_simultaneous_session_give_entry', sanitize_text_field( $_POST['mo2f_allow_access'] ) );
			}
		} else {
			update_site_option( 'mo2f_simultaneous_session_enable', 0 );
		}
		if ( isset( $_POST['mo2f_idle_session_logout_enable'] ) ) {
			$idle_hours = sanitize_text_field( $_POST['mo2f_number_of_idle_hours'] );
			if ( $idle_hours < 1 ) {
				update_site_option( 'mo2f_message', 'Minimum number of idle hours should be greater than 1' );
				$mo2f_success = false;
				$mo2f_factor->mo_auth_show_error_message();
			} elseif ( $idle_hours < 4 ) {
				update_site_option( 'mo2f_message', 'Please upgrade to apply idle session limit less than 4' );
				$mo2f_success = false;
				$mo2f_factor->mo_auth_show_error_message();
			} else {
				update_site_option( 'mo2f_idle_session_logout_enable', 1 );
				update_site_option( 'mo2f_number_of_idle_hours', $idle_hours );
			}
		} else {
			update_site_option( 'mo2f_idle_session_logout_enable', 0 );
		}
		if ( isset( $_POST['mo2f_session_logout_time_enable'] ) ) {
			$timeout_hours = sanitize_text_field( $_POST['mo2f_number_of_timeout_hours'] );
			if ( $timeout_hours < 1 ) {
				update_site_option( 'mo2f_message', 'Minimum number of session timeout hours should be greater than 1' );
				$mo2f_success = false;
				$mo2f_factor->mo_auth_show_error_message();
			} elseif ( $timeout_hours < 4 ) {
				update_site_option( 'mo2f_message', 'Please upgrade to apply timeout session limit less than 4' );
				$mo2f_success = false;
				$mo2f_factor->mo_auth_show_error_message();
			} else {
				update_site_option( 'mo2f_session_logout_time_enable', 1 );
				update_site_option( 'mo2f_number_of_timeout_hours', $timeout_hours );
			}
		} else {
			update_site_option( 'mo2f_session_logout_time_enable', 0 );
		}
		if ( $mo2f_success == true ) {
			$mo2f_factor->mo_auth_show_success_message();
		}
	}
}
$session_addon              = 1;
$session_settings_disabled  = "";
$limit_simultaneous_session = get_site_option( 'mo2f_simultaneous_session_enable' );
$sessions_allowed           = get_site_option( 'mo2f_simultaneous_session_allowed', 10 );
$allow_deny_access          = get_site_option( 'mo2f_simultaneous_session_give_entry' );
$idle_session_enable        = get_site_option( 'mo2f_idle_session_logout_enable' );
$idle_hours                 = get_site_option( 'mo2f_number_of_idle_hours', 10 );
$session_logout_time_enable = get_site_option( 'mo2f_session_logout_time_enable' );
$session_timeout_hours      = get_site_option( 'mo2f_number_of_timeout_hours', 10 );

require $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_session_control.php';
