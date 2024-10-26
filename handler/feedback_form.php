<?php

class molms_FeedbackHandler {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'molms_feedback_actions' ) );
	}

	public function molms_feedback_actions() {
		global $molms_utility, $molms_dirName;

		if ( current_user_can( 'manage_options' ) && isset( $_POST['option'] ) ) {
			switch ( sanitize_text_field( $_REQUEST['option'] ) ) {
				case "molms_skip_feedback":
				case "molms_rating":
				case "molms_feedback":
					$this->molms_lms_handle_feedback();
					break;
			}
		}
	}


	public function molms_lms_handle_feedback() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-feedback-nonce' ) ) {
			do_action( 'molms_show_message', 'ERROR', 'ERROR' );

			return;
		}
		if ( MOLMS_TEST_MODE ) {
			deactivate_plugins( dirname( dirname( __FILE__ ) ) . "\\miniorange_lms_2_factor_settings.php" );

			return;
		}

		$user            = wp_get_current_user();
		$feedback_option = sanitize_text_field( $_POST['option'] );
		if ( $feedback_option != "molms_rating" ) {
			$message = 'Plugin Deactivated';
		}


		$deactivate_reason_message = array_key_exists( 'molms_query_feedback', $_POST ) ? sanitize_text_field( $_POST['molms_query_feedback'] ) : false;
		$activation_date           = get_site_option( 'mo2f_activated_time' );
		$current_date              = time();
		$diff                      = $activation_date - $current_date;
		if ( $activation_date == false ) {
			$days = 'NA';
		} else {
			$days = abs( round( $diff / 86400 ) );
		}

		if ( $feedback_option != "molms_rating" ) {
			$reply_required = '';
			if ( isset( $_POST['get_reply'] ) ) {
				$reply_required = sanitize_text_field( $_POST['get_reply'] );
			}

			if ( empty( $reply_required ) ) {
				$reply_required = "don't reply";
				$message        .= ' &nbsp; [Reply:<b style="color:red";>' . $reply_required . '</b>,';
			} else {
				$reply_required = "yes";
				$message        .= '[Reply:' . $reply_required . ',';
			}
		} else {
			$message = '[';
		}
		$message .= 'D:' . $days . ',';
		if ( molms_Utility::get_mo2f_db_option( 'mo_lms_2fa_with_network_security', 'get_site_option' ) ) {
			$message .= '2FA+NS]';
		} else {
			$message .= '2FA]';
		}

		$message .= ', Feedback : ' . $deactivate_reason_message . '';

		if ( isset( $_POST['rate'] ) ) {
			$rate_value = sanitize_text_field( $_POST['rate'] );
		} else {
			$rate_value = "--";
		}
		$message .= ', [Rating :' . $rate_value . ']';

		$email = isset( $_POST['query_mail'] ) ? sanitize_email( $_POST['query_mail'] ) : '';
		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$email = get_site_option( 'mo2f_email' );
			if ( empty( $email ) ) {
				$email = $user->user_email;
			}
		}
		$phone            = get_site_option( 'mo_lms_admin_phone' );
		$feedback_reasons = new molms_cURL();
		if ( ! is_null( $feedback_reasons ) ) {
			if ( ! molms_2f_Utility::is_curl_installed() ) {
				deactivate_plugins( dirname( dirname( __FILE__ ) ) . "\\miniorange_lms_2_factor_settings.php" );
				wp_safe_redirect( 'plugins.php' );
			} else {
				$submited = json_decode( $feedback_reasons->send_email_alert( $email, $phone, $message, $feedback_option ), true );
				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && $submited['status'] == 'ERROR' ) {
						do_action( 'molms_show_message', $submited['message'], 'ERROR' );
					} else {
						if ( $submited == false ) {
							do_action( 'molms_show_message', 'Error while submitting the query.', 'ERROR' );
						}
					}
				}

				if ( $feedback_option == 'molms_feedback' || $feedback_option == 'molms_skip_feedback' ) {
					deactivate_plugins( dirname( dirname( __FILE__ ) ) . "\\miniorange_lms_2_factor_settings.php" );
				}
				do_action( 'molms_show_message', 'Thank you for the feedback.', 'SUCCESS' );
			}
		}
	}
}

new molms_FeedbackHandler();
