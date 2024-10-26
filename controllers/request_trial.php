<?php

if ( current_user_can( 'manage_options' ) && isset( $_POST['option'] ) ) {
	switch ( sanitize_text_field( $_POST['option'] ) ) {
		case "molms_trial_request_form":
			molms_handle_trial_request_form( $_POST );
			break;
	}
}
global $molms_dirName;
$current_user = wp_get_current_user();
$email        = isset( $current_user->user_email ) ? $current_user->user_email : null;
$url          = get_site_url();

echo '<link rel="stylesheet" type="text/css" href="' . esc_url( plugins_url( 'includes/css/style_settings.css', dirname( __FILE__ ) ) ) . '" />';
require $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'request_trial.php';

function molms_handle_trial_request_form( $post ) {
	$nonce = isset( $post['nonce'] ) ? sanitize_text_field( $post['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'molms_request_trial-nonce' ) ) {
		return;
	}

	$email      = isset( $post['molms_trial_email'] ) ? $post['molms_trial_email'] : "";
	$trial_plan = isset( $post['molms_trial_plan'] ) ? $post['molms_trial_plan'] : "";
	if ( get_site_option( 'molms_trial_query_sent' ) ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'TRIAL_REQUEST_ALREADY_SENT' ), 'ERROR' );

		return;
	}

	if ( empty( $email ) ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'REQUIRED_FIELDS' ), 'ERROR' );

		return;
	}
	if ( empty( $trial_plan ) ) {
		do_action( 'molms_show_message', molms_Messages::showMessage( 'SELECT_A_PLAN' ), 'ERROR' );

		return;
	} else {
		$email        = filter_var( $email, FILTER_VALIDATE_EMAIL );
		$trial_plan   = sanitize_text_field( $trial_plan );
		$query        = 'REQUEST FOR TRIAL';
		$query        .= ' [ Plan Name => ';
		$query        .= $trial_plan;
		$query        .= ' | Email => ';
		$query        .= get_option( 'mo2f_email' ) . ' ]';
		$current_user = wp_get_current_user();


		$url = MOLMS_HOST_NAME . "/moas/rest/customer/contact-us";
		global $mowafutility;
		$query = '[WordPress 2 Factor LMS Plugin: OV3 - ' . MOLMS_VERSION . ']: ' . $query;

		$fields       = array(
			'firstName' => $current_user->user_firstname,
			'lastName'  => $current_user->user_lastname,
			'company'   => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '',
			'email'     => $email,
			'ccEmail'   => '2fasupport@xecurify.com',
			'phone'     => '',
			'query'     => $query
		);
		$field_string = json_encode( $fields );
		$response     = make_curl_call( $url, $field_string );


		$submitted = $response;

		if ( json_last_error() == JSON_ERROR_NONE && $submitted ) {
			update_site_option( 'molms_trial_query_sent', true );
			do_action( 'molms_show_message', molms_Messages::showMessage( 'SUPPORT_FORM_SENT' ), 'SUCCESS' );

			return;
		} else {
			do_action( 'molms_show_message', molms_Messages::showMessage( 'SUPPORT_FORM_ERROR' ), 'ERROR' );
		}

	}
}

function make_curl_call(
	$url, $fields, $http_header_array = array(
	"Content-Type"  => "application/json",
	"charset"       => "UTF-8",
	"Authorization" => "Basic"
)
) {
	if ( gettype( $fields ) !== 'string' ) {
		$fields = json_encode( $fields );
	}

	$args = array(
		'method'      => 'POST',
		'body'        => $fields,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $http_header_array
	);

	$response = wp_remote_post( $url, $args );

	return $response;

}

?>