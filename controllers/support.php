<?php

global $molms_dirName;

if ( current_user_can( 'manage_options' ) && isset( $_POST['option'] ) ) {
	switch ( sanitize_text_field( $_POST['option'] ) ) {
		case "molms_send_query":
			molms_handle_support_form();
			break;
	}
}

$current_user = wp_get_current_user();
$email        = get_site_option( "mo2f_email" );
$phone        = get_site_option( "mo_lms_admin_phone" );


if ( empty( $email ) ) {
	$email = $current_user->user_email;
}

require $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'support.php';


/* SUPPORT FORM RELATED FUNCTIONS */

//Function to handle support form submit
function molms_handle_support_form() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mo2f-support-nonce' ) ) {
		do_action( 'molms_show_message', 'ERROR', 'ERROR' );

		return;
	}
	$query = sanitize_text_field( $_POST['query'] );
	$email = sanitize_email( $_POST['query_email'] );
	$phone = sanitize_text_field( $_POST['query_phone'] );

	if ( empty( $email ) || empty( $query ) ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'SUPPORT_FORM_VALUES' ), 'SUCCESS' );

		return;
	}
	$contact_us = new molms_cURL();
	$submited   = json_decode( $contact_us->submit_contact_us( $email, $phone, $query ), true );

	if ( json_last_error() == JSON_ERROR_NONE && $submited ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'SUPPORT_FORM_SENT' ), 'SUCCESS' );

		return;
	}

	do_action( 'molms_show_message', molms_Messages::showMessage( 'SUPPORT_FORM_ERROR' ), 'ERROR' );
}
