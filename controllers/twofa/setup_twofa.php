<?php

$email_registered = 1;
global $molms_db_queries;
$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', get_current_user_id() );
if ( $email == '' or ! isset( $email ) ) {
	$email = wp_get_current_user()->user_email;
}

if ( isset( $email ) ) {
	$email_registered = 1;
} else {
	$email_registered = 0;
}

$upgrade_url = add_query_arg( array( 'page' => 'molms_upgrade' ), esc_url_raw( $_SERVER['REQUEST_URI'] ) );

if ( current_user_can( 'manage_options' ) && isset( $_POST['option'] ) ) {
	switch ( sanitize_text_field( $_POST['option'] ) ) {
		case "mo2f_enable_2FA_on_login_page_option":
			molms_handle_enable_2fa_login_prompt();
			break;
	}
}

require $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'setup_twofa.php';

function molms_handle_enable_2fa_login_prompt() {
	$nonce = isset( $_POST['mo2f_enable_2FA_on_login_page_option_nonce'] ) ? sanitize_text_field( $_POST['mo2f_enable_2FA_on_login_page_option_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'mo2f-enable-2FA-on-login-page-option-nonce' ) ) {
		$error = new WP_Error();
		$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );
		do_action( 'molms_show_message', molms_Messages::showMessage( 'ERROR_OCCURED' ), 'ERROR' );

		return $error;
	}
	if ( molms_Utility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'get_site_option' ) == 1 ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'TWO_FA_ON_LOGIN_PROMPT_ENABLED' ), 'SUCCESS' );
	} else {
		if ( isset( $_POST['mo2f_enable_2fa_prompt_on_login_page'] ) ) {
			do_action( 'molms_show_message', molms_Messages::showMessage( 'TWO_FA_PROMPT_LOGIN_PAGE' ), 'ERROR' );
		} else {
			do_action( 'molms_show_message', molms_Messages::showMessage( 'TWO_FA_ON_LOGIN_PROMPT_DISABLED' ), 'ERROR' );
		}
	}
}
