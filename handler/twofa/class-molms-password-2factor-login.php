<?php
/**
 * miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
 * Copyright (C) 2015  miniOrange
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @package miniOrange OAuth
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/

require 'two_fa_login.php';
class Molms_Password_2Factor_Login
{
private $mo2f_kbaquestions;
private $mo2f_user_id;
private $mo2f_rbastatus;
private $mo2f_transactionid;

public function miniorange_pass2login_redirect() {
	do_action( 'mo2f_network_init' );
	global $molms_db_queries;

	if ( ! molms_Utility::get_mo2f_db_option( 'mo2f_login_option', 'get_site_option' ) ) {
		if ( isset( $_POST['miniorange_login_nonce'] ) ) {
			$nonce      = isset( $_POST['miniorange_login_nonce'] ) ? sanitize_text_field( $_POST['miniorange_login_nonce'] ) : '';
			$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;

			if ( is_null( $session_id ) ) {
				$session_id = $this->create_session();
			}


			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' ) ) {
				$this->remove_current_activity( $session_id );
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$mobile_login = new molms_Miniorange_Mobile_Login();
				//validation and sanitization
				$username = '';
				if ( isset( $_POST['mo2fa_username'] ) ) {
					$username = sanitize_text_field( $_POST['mo2fa_username'] );
				}
				if ( molms_2f_Utility::mo2f_check_empty_or_null( $username ) ) {
					molms_2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please enter username to proceed' );
					$mobile_login->mo_auth_show_error_message();

					return;
				}
				if ( username_exists( $username ) ) {    /*if username exists in wp site */
					$user        = new WP_User( $username );
					$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : null;
					molms_2f_Utility::set_user_values( $session_id, 'mo2f_current_user_id', $user->ID );
					molms_2f_Utility::set_user_values( $session_id, 'mo2f_1stfactor_status', 'VALIDATE_SUCCESS' );
					$this->mo2f_userId                   = $user->ID;
					$this->fstfactor                     = 'VALIDATE_SUCCESS';
					$current_roles                       = miniorange_get_user_role( $user );
					$mo2f_configured_2FA_method          = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
					$email                               = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
					$mo_2factor_user_registration_status = $molms_db_queries->get_user_detail( 'mo_2factor_user_registration_status', $user->ID );
					$kba_configuration_status            = $molms_db_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $user->ID );

					if ( MOLMS_IS_ONPREM ) {
						$mo_2factor_user_registration_status = 'MO_2_FACTOR_PLUGIN_SETTINGS';
					}
					if ( $mo2f_configured_2FA_method ) {
						if ( $email && $mo_2factor_user_registration_status == 'MO_2_FACTOR_PLUGIN_SETTINGS' || ( MOLMS_IS_ONPREM && $mo_2factor_user_registration_status == 'MO_2_FACTOR_PLUGIN_SETTINGS' ) ) {
							if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && molms_2f_Utility::check_if_request_is_from_mobile_device( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) ) && $kba_configuration_status ) {
								$this->mo2f_pass2login_kba_verification( $user->ID, $redirect_to, $session_id );
							} else {
								$mo2f_second_factor = '';

								if ( MOLMS_IS_ONPREM ) {
									global $molms_db_queries;
									$mo2f_second_factor = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
									if ( $mo2f_second_factor == 'Security Questions' ) {
										$mo2f_second_factor = 'KBA';
									} elseif ( $mo2f_second_factor == 'Google Authenticator' ) {
										$mo2f_second_factor = 'GOOGLE AUTHENTICATOR';
									} elseif ( $mo2f_second_factor != 'Email Verification' ) {
										$mo2f_second_factor = 'NONE';
									}
								} else {
									$mo2f_second_factor = mo2f_get_user_2ndfactor( $user );
								}

								if ( $mo2f_second_factor == 'MOBILE AUTHENTICATION' ) {
									$this->mo2f_pass2login_mobile_verification( $user, $redirect_to, $session_id );
								} elseif ( $mo2f_second_factor == 'PUSH NOTIFICATIONS' || $mo2f_second_factor == 'OUT OF BAND EMAIL' ) {
									$this->mo2f_pass2login_push_oobemail_verification( $user, $mo2f_second_factor, $redirect_to, $session_id );
								} elseif ( $mo2f_second_factor == 'Email Verification' ) {
									$this->mo2f_pass2login_push_oobemail_verification( $user, $mo2f_second_factor, $redirect_to, $session_id );
								} elseif ( $mo2f_second_factor == 'SOFT TOKEN' || $mo2f_second_factor == 'SMS' || $mo2f_second_factor == 'PHONE VERIFICATION' || $mo2f_second_factor == 'GOOGLE AUTHENTICATOR' ) {
									$this->mo2f_pass2login_otp_verification( $user, $mo2f_second_factor, $redirect_to, $session_id );
								} elseif ( $mo2f_second_factor == 'KBA' ) {
									$this->mo2f_pass2login_kba_verification( $user->ID, $redirect_to, $session_id );
								} else {
									$this->remove_current_activity( $session_id );
									molms_2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please try again or contact your admin.' );
									$mobile_login->mo_auth_show_success_message();
								}
							}
						} else {
							molms_2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please login into your account using password.' );
							$mobile_login->mo_auth_show_success_message( 'Please login into your account using password.' );
							update_user_meta( $user->ID, 'userMessage', 'Please login into your account using password.' );
							$mobile_login->mo2f_redirectto_wp_login();
						}
					} else {
						molms_2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please login into your account using password.' );
						$mobile_login->mo_auth_show_success_message( 'Please login into your account using password.' );
						update_user_meta( $user->ID, 'userMessage', 'Please login into your account using password.' );
						$mobile_login->mo2f_redirectto_wp_login();
					}
				} else {
					$mobile_login->remove_current_activity( $session_id );
					molms_2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Invalid Username.' );
					$mobile_login->mo_auth_show_error_message( 'Invalid Username.' );
				}
			}
		}
	}
	if ( isset( $_GET['Txid'] ) && isset( $_GET['accessToken'] ) ) {
		$userIDGet     = sanitize_text_field( $_GET['userID'] );
		$txIdGet       = sanitize_text_field( $_GET['Txid'] );
		$otpToken      = get_site_option( $userIDGet );
		$txidstatus    = get_site_option( $txIdGet );
		$userIDd       = $userIDGet . 'D';
		$otpTokenD     = get_site_option( $userIDd );
		$molms_dirName = dirname( __FILE__ );
		$molms_dirName = explode( 'wp-content', $molms_dirName );
		$molms_dirName = explode( 'handler', $molms_dirName[1] );

		$head  = 'You are not authorized to perform this action';
		$body  = 'Please contact to your admin';
		$color = 'red';
		if ( 3 == $txidstatus ) {
			$time                   = 'time' . $txIdGet;
			$current_time_in_millis = round( microtime( true ) * 1000 );
			$generatedTimeINMillis  = get_site_option( $time );
			$difference             = ( $current_time_in_millis - $generatedTimeINMillis ) / 1000;
			if ( $difference <= 300 ) {
				$accessTokenGet = sanitize_text_field( $_GET['accessToken'] );
				if ( $accessTokenGet == $otpToken ) {
					update_site_option( $txIdGet, 1 );
					$body  = 'Transaction has been successfully validated.<br><br>Please continue with the transaction.';
					$head  = 'TRANSACTION SUCCESSFUL';
					$color = 'green';
				} elseif ( $accessTokenGet == $otpTokenD ) {
					update_site_option( $txIdGet, 0 );
					$body = 'Transaction has been Canceled.<br><br>Please try Again.';
					$head = 'TRANSACTION DENIED';
				}
			}
			delete_site_option( $userIDGet );
			delete_site_option( $userIDd );
			delete_site_option( $time );
		}

		$this->display_email_verification( $head, $body, $color );
		exit;
	} elseif ( isset( $_POST['emailInlineCloud'] ) ) {
		$nonce = isset( $_POST['miniorange_emailChange_nonce'] ) ? sanitize_text_field( $_POST['miniorange_emailChange_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-email-change-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			$email           = sanitize_text_field( $_POST['emailInlineCloud'] );
			$current_user_id = sanitize_text_field( $_POST['current_user_id'] );
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				global $molms_db_queries;
				$molms_db_queries->update_user_details( $current_user_id, array(
					'mo2f_user_email'            => $email,
					'mo2f_configured_2FA_method' => ''
				) );
				molms_prompt_user_to_select_2factor_mthod_inline( $current_user_id, 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR', '', '', '', null );
			}
		}
	} elseif ( isset( $_POST['txid'] ) ) {
		$txidpost = sanitize_text_field( $_POST['txid'] );
		$status   = get_site_option( $txidpost );
		update_site_option( 'optionVal1', $status );
		if ( $status == 1 || $status == 0 ) {
			delete_site_option( $txidpost );
		}
		echo esc_html( $status );
		exit();
	} else {
		$value = isset( $_POST['option'] ) ? sanitize_text_field( $_POST['option'] ) : false;

		switch ( $value ) {
			case 'miniorange_rba_validate':
				$this->check_rba_validation();
				break;

			case 'miniorange_rba_cancle':
				$this->check_rba_cancalation();
				break;

			case 'miniorange_forgotphone':
				$this->check_miniorange_challenge_forgotphone();
				break;

			case 'miniorange2f_back_to_inline_registration':
				$this->miniorange2f_back_to_inline_registration();
				exit;

			case 'miniorange_alternate_login_kba':
				$this->check_miniorange_alternate_login_kba();
				break;

			case 'miniorange_kba_validate':
				$this->check_kba_validation();

				break;

			case 'miniorange_mobile_validation':
				$this->check_miniorange_mobile_validation();
				break;

			case 'miniorange_mobile_validation_failed':
				$this->check_miniorange_mobile_validation_failed();
				break;

			case 'miniorange_softtoken':
				$this->check_miniorange_softtoken();

				break;


			case 'miniorange_soft_token':
				$this->check_miniorange_soft_token();
				break;

			case 'miniorange_inline_skip_registration':
				$this->check_miniorange_inline_skip_registration();
				break;

			case 'miniorange_attribute_collection':
				$this->check_miniorange_attribute_collection();
				break;

			case 'miniorange_inline_save_2factor_method':
				$this->save_inline_2fa_method();
				break;

			case 'mo2f_skip_2fa_setup':
				$this->mo2f_skip_2fa_setup();
				break;

			case 'miniorange_back_inline':
				$this->back_to_select_2fa();
				break;

			case 'miniorange_inline_ga_validate':
				$this->inline_validate_and_set_ga();
				break;

			case 'miniorange_inline_show_mobile_config':
				$this->inline_mobile_configure();
				break;

			case 'miniorange_inline_complete_mobile':
				$this->mo2f_inline_validate_mobile_authentication();
				break;

			case 'mo2f_inline_kba_option':
				$this->mo2f_inline_validate_kba();
				break;

			case 'miniorange_inline_complete_otp_over_sms':
				$this->mo2f_inline_send_otp();
				break;

			case 'miniorange_inline_complete_otp':
				$this->mo2f_inline_validate_otp();
				break;

			case 'miniorange_inline_login':
				$this->mo2f_inline_login();
				break;
			case 'miniorange_inline_register':
				$this->mo2f_inline_register();
				break;
			default:
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

				return $error;
				break;
		}
	}
}

public function create_session() {
	global $molms_db_queries;
	$session_id = molms_2f_Utility::random_str( 20 );
	$molms_db_queries->insert_user_login_session( $session_id );
	$key                = get_site_option( 'mo2f_encryption_key' );
	$session_id_encrypt = molms_2f_Utility::encrypt_data( $session_id, $key );

	return $session_id_encrypt;
}

public function remove_current_activity( $session_id ) {
	global $molms_db_queries;
	$session_variables = array(
		'mo2f_current_user_id',
		'mo2f_1stfactor_status',
		'mo_2factor_login_status',
		'mo2f-login-qrCode',
		'mo2f_transactionId',
		'mo2f_login_message',
		'mo2f_rba_status',
		'mo_2_factor_kba_questions',
		'mo2f_show_qr_code',
		'mo2f_google_auth',
		'mo2f_authy_keys',
	);

	$cookie_variables = array(
		'mo2f_current_user_id',
		'mo2f_1stfactor_status',
		'mo_2factor_login_status',
		'mo2f-login-qrCode',
		'mo2f_transactionId',
		'mo2f_login_message',
		'mo2f_rba_status_status',
		'mo2f_rba_status_sessionUuid',
		'mo2f_rba_status_decision_flag',
		'kba_question1',
		'kba_question2',
		'mo2f_show_qr_code',
		'mo2f_google_auth',
		'mo2f_authy_keys',
	);

	$temp_table_variables = array(
		'session_id',
		'mo2f_current_user_id',
		'mo2f_login_message',
		'mo2f_1stfactor_status',
		'mo2f_transactionId',
		'mo_2_factor_kba_questions',
		'mo2f_rba_status',
		'ts_created',
	);

	molms_2f_Utility::unset_session_variables( $session_variables );
	molms_2f_Utility::unset_cookie_variables( $cookie_variables );
	$key        = get_site_option( 'mo2f_encryption_key' );
	$session_id = molms_2f_Utility::decrypt_data( $session_id, $key );
	$molms_db_queries->save_user_login_details(
		$session_id,
		array(

			'mo2f_current_user_id'      => '',
			'mo2f_login_message'        => '',
			'mo2f_1stfactor_status'     => '',
			'mo2f_transactionId'        => '',
			'mo_2_factor_kba_questions' => '',
			'mo2f_rba_status'           => '',
			'ts_created'                => '',
		)
	);
}

public function miniorange_pass2login_start_session() {
	if ( ! session_id() || session_id() == '' || ! isset( $_SESSION ) ) {
		$session_path = ini_get( 'session.save_path' );
		if ( is_writable( $session_path ) && is_readable( $session_path ) ) {
			session_start();
		}
	}
}

public function mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id ) {
	global $molms_db_queries, $LoginuserID;
	$LoginuserID = $user_id;
	$user_email  = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user_id );
	if ( is_null( $session_id ) ) {
		$session_id = $this->create_session();
	}
	if ( MOLMS_IS_ONPREM ) {
		$question_answers    = get_user_meta( $user_id, 'mo2f_kba_challenge', true );
		$challenge_questions = array_keys( $question_answers );
		$random_keys         = array_rand( $challenge_questions, 2 );
		$challenge_ques1     = $challenge_questions[ $random_keys[0] ];
		$challenge_ques2     = $challenge_questions[ $random_keys[1] ];
		$questions[0]        = array( 'question' => $challenge_ques1 );
		$questions[1]        = array( 'question' => $challenge_ques2 );
		update_user_meta( $user_id, 'kba_questions_user', $questions );
		$mo2fa_login_message = 'Please answer the following questions:';
		$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
		$mo2f_kbaquestions   = $questions;
		molms_2f_Utility::set_user_values( $session_id, 'mo_2_factor_kba_questions', $questions );
		$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id, $this->mo2f_kbaquestions );
	} else {
		$challengeKba = new Molms_Customer_Setup();
		$content      = $challengeKba->send_otp_token( $user_email, 'KBA', get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) );
		$response     = json_decode( $content, true );
		if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
			if ( $response['status'] == 'SUCCESS' ) {
				molms_2f_Utility::set_user_values( $session_id, 'mo2f_transactionId', $response['txId'] );
				$this->mo2f_transactionid = $response['txId'];
				$questions                = array();
				$questions[0]             = $response['questions'][0];
				$questions[1]             = $response['questions'][1];
				molms_2f_Utility::set_user_values( $session_id, 'mo_2_factor_kba_questions', $questions );
				$this->mo2f_kbaquestions = $questions;
				$mo2fa_login_message     = 'Please answer the following questions:';
				$mo2fa_login_status      = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id, $this->mo2f_kbaquestions );
			} elseif ( $response['status'] == 'ERROR' ) {
				$this->remove_current_activity( $session_id );
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please try again.' ) );

				return $error;
			}
		} else {
			$this->remove_current_activity( $session_id );
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please try again.' ) );

			return $error;
		}
	}
}

public function miniorange_pass2login_form_fields( $mo2fa_login_status = null, $mo2fa_login_message = null, $redirect_to = null, $qrCode = null, $session_id_encrypt = null, $show_back_button = null ) {
	$login_status  = $mo2fa_login_status;
	$login_message = $mo2fa_login_message;
	switch ( $login_status ) {
		case 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION':
			$transaction_id = $this->mo2f_transactionid ? $this->mo2f_transactionid : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );
			molms_get_qrcode_authentication_prompt( $login_status, $login_message, $redirect_to, $qrCode, $session_id_encrypt, $transaction_id );
			exit;
			break;
		case 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
			exit;
			break;
		case 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id, $show_back_button );
			exit;
			break;
		case 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
			exit;
			break;
		case 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
			exit;
			break;
		case 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
			exit;
			break;
		case 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL':
			mo2f_get_forgotphone_form( $login_status, $login_message, $redirect_to, $session_id_encrypt );
			exit;
			break;

		case 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS':
			$transaction_id = $this->mo2f_transactionid ? $this->mo2f_transactionid : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );
			$user_id        = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_push_notification_oobemail_prompt( $user_id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $transaction_id );
			exit;
			break;

		case 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL':
			$transaction_id = $this->mo2f_transactionid ? $this->mo2f_transactionid : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );
			$user_id        = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_get_push_notification_oobemail_prompt( $user_id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $transaction_id );
			exit;
			break;

		case 'MO_2_FACTOR_RECONFIG_GOOGLE':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			$this->mo2f_redirect_shortcode_addon( $user_id, $login_status, $login_message, 'reconfigure_google' );
			exit;
			break;

		case 'MO_2_FACTOR_RECONFIG_KBA':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			$this->mo2f_redirect_shortcode_addon( $user_id, $login_status, $login_message, 'reconfigure_kba' );
			exit;
			break;

		case 'MO_2_FACTOR_SETUP_SUCCESS':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			$this->mo2f_inline_setup_success( $user_id, $redirect_to, $session_id_encrypt );
			break;

		case 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION':
			$kbaquestions = $this->mo2f_kbaquestions ? $this->mo2f_kbaquestions : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo_2_factor_kba_questions', $session_id_encrypt );
			if ( MOLMS_IS_ONPREM ) {
				$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$ques    = get_user_meta( $user_id, 'kba_questions_user' );
				molms_get_kba_authentication_prompt( $login_message, $redirect_to, $session_id_encrypt, $ques[0] );
			} else {
				molms_get_kba_authentication_prompt( $login_message, $redirect_to, $session_id_encrypt, $kbaquestions );
			}
			exit;
			break;

		case 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE':
			mo2f_get_device_form( $redirect_to, $session_id_encrypt );
			exit;
			break;

		case 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS':
			$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			molms_prompt_user_to_select_2factor_mthod_inline( $user_id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $qrCode );
			exit;
			break;

		default:
			$this->mo_2_factor_pass2login_show_wp_login_form();
			if ( MOLMS_IS_ONPREM ) {
				$this->mo_2_factor_pass2login_show_wp_login_form();
			}
			break;
	}
}

public function mo2f_redirect_shortcode_addon( $current_user_id, $login_status, $login_message, $identity ) {
	do_action( 'mo2f_shortcode_addon', $current_user_id, $login_status, $login_message, $identity );
}

public function mo2f_inline_setup_success( $current_user_id, $redirect_to, $session_id ) {
	global $molms_db_queries;
	$molms_db_queries->update_user_details( $current_user_id, array( 'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS' ) );
	$pass2fa = new Molms_Password_2Factor_Login();
	$pass2fa->mo2fa_pass2login( $redirect_to, $session_id );
	exit;
}

public function mo2fa_pass2login( $redirect_to = null, $session_id_encrypted = null ) {
	if ( empty( $this->mo2f_user_id ) && empty( $this->fstfactor ) ) {
		$user_id               = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypted );
		$mo2f_1stfactor_status = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_1stfactor_status', $session_id_encrypted );
	} else {
		$user_id               = $this->mo2f_user_id;
		$mo2f_1stfactor_status = $this->fstfactor;
	}
	if ( $user_id && $mo2f_1stfactor_status && ( $mo2f_1stfactor_status == 'VALIDATE_SUCCESS' ) ) {
		$currentuser = get_user_by( 'id', $user_id );
		wp_set_current_user( $user_id, $currentuser->user_login );
		$mobile_login = new molms_Miniorange_Mobile_Login();
		$mobile_login->remove_current_activity( $session_id_encrypted );
		wp_set_auth_cookie( $user_id, true );
		do_action( 'wp_login', $currentuser->user_login, $currentuser );
		molms_redirect_user_to( $currentuser, $redirect_to );
		exit;
	} else {
		$this->remove_current_activity( $session_id_encrypted );
	}
}

public function mo_2_factor_pass2login_show_wp_login_form()
{
$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
if ( is_null( $session_id_encrypt ) ) {
	$session_id_encrypt = $this->create_session();
}
if ( class_exists( 'Theme_My_Login' ) ) {
	wp_enqueue_script( 'tmlajax_script', plugins_url( 'includes/js/tmlajax.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_localize_script(
		'tmlajax_script',
		'my_ajax_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
	);
} ?>
<p><input type='hidden' name='miniorange_login_nonce'
          value='<?php echo esc_html( wp_create_nonce( 'miniorange-2-factor-login-nonce' ) ); ?>'/>

    <input type='hidden' id='sessid' name='session_id'
           value='<?php echo esc_html( $session_id_encrypt ); ?>'/>

</p>

<?php
if ( get_site_option( 'mo2f_remember_device' ) ) {
?>
<p><input type='hidden' id='miniorange_rba_attribures' name='miniorange_rba_attribures' value='/></p>
                <?php
	wp_enqueue_script( 'jquery_script', plugins_url( 'includes/js/rba/js/jquery-1.9.1.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'flash_script', plugins_url( 'includes/js/rba/js/jquery.flash.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'uaparser_script', plugins_url( 'includes/js/rba/js/ua-parser.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'client_script', plugins_url( 'includes/js/rba/js/client.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'device_script', plugins_url( 'includes/js/rba/js/device_attributes.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'swf_script', plugins_url( 'includes/js/rba/js/swfobject.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'font_script', plugins_url( 'includes/js/rba/js/fontdetect.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'murmur_script', plugins_url( 'includes/js/rba/js/murmurhash3.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	wp_enqueue_script( 'miniorange_script', plugins_url( 'includes/js/rba/js/miniorange-fp.js', dirname( dirname( __FILE__ ) ) ), array(), MOLMS_VERSION, false );
	} else {
		if ( molms_Utility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'get_site_option' ) ) {
			echo "\t<p>\n";
			echo "\t\t<label class=\"mo2f_instuction1\" title=\"" . esc_html__( 'If you don\'t have 2-factor authentication enabled for your WordPress account, leave this field empty.', 'google-authenticator' ) . "\">" . esc_html__( '2 Factor Authentication code*', 'google-authenticator' ) . "<span id=\"google-auth-info\"></span><br />\n";
			echo "\t\t<input type=\"text\" placeholder=\"No soft Token ? Skip\" name=\"mo_softtoken\" id=\"mo2f_2fa_code\" class=\"mo2f_2fa_code\" value=\"\" size=\"20\" style=\"ime-mode: inactive;\" /></label>\n";
			echo "\t<p class=\"mo2f_instuction2\" style='color:red; font-size:12px;padding:5px'>* Skip the authentication code if it doesn't apply.</p>\n";
			echo "\t</p>\n";
			echo " \r\n";
			echo " \r\n";
			echo "\n";
		}
	}
	}

	public function mo2f_pass2login_mobile_verification( $user, $redirect_to, $session_id_encrypt = null ) {
		global $molms_db_queries;
		if ( is_null( $session_id_encrypt ) ) {
			$session_id_encrypt = $this->create_session();
		}
		$user_email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
		$useragent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';
		if ( molms_2f_Utility::check_if_request_is_from_mobile_device( $useragent ) ) {
			$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );

			molms_2f_Utility::unset_session_variables( $session_cookie_variables );
			molms_2f_Utility::unset_cookie_variables( $session_cookie_variables );
			molms_2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );

			$mo2fa_login_message = 'Please enter the one time passcode shown in the miniOrange  Authenticator app.';
			$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
		} else {
			$challengeMobile = new Molms_Customer_Setup();
			$content         = $challengeMobile->send_otp_token( $user_email, 'MOBILE AUTHENTICATION', get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) );
			$response        = json_decode( $content, true );
			if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
				if ( $response['status'] == 'SUCCESS' ) {
					$qrCode = $response['qrCode'];
					molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_transactionId', $response['txId'] );
					$this->mo2f_transactionid = $response['txId'];
					$mo2fa_login_message      = '';
					$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, $qrCode, $session_id_encrypt );
				} elseif ( $response['status'] == 'ERROR' ) {
					$this->remove_current_activity( $session_id_encrypt );
					$error = new WP_Error();
					$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please try again.' ) );

					return $error;
				}
			} else {
				$this->remove_current_activity( $session_id_encrypt );
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please try again.' ) );

				return $error;
			}
		}
	}

	public function mo2f_pass2login_push_oobemail_verification( $current_user, $mo2f_second_factor, $redirect_to, $session_id = null ) {
		global $molms_db_queries, $molms_dirName;
		if ( is_null( $session_id ) ) {
			$session_id = $this->create_session();
		}
		$challengeMobile = new Molms_Customer_Setup();
		$user_email      = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
		if ( MOLMS_IS_ONPREM && $mo2f_second_factor != 'PUSH NOTIFICATIONS' ) {
			include_once $molms_dirName . 'api' . DIRECTORY_SEPARATOR . 'class-molms-onprem-redirect.php';
			$mo2f_onprem_redirect = new Molms_Onprem_Redirect();
			$content              = $mo2f_onprem_redirect->mo2f_pass2login_push_email_onpremise( $current_user, $redirect_to, $session_id );
		} else {
			$content = $challengeMobile->send_otp_token( $user_email, $mo2f_second_factor, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) );
		}
		$response = json_decode( $content, true );
		if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
			if ( $response['status'] == 'SUCCESS' ) {
				molms_2f_Utility::set_user_values( $session_id, 'mo2f_transactionId', $response['txId'] );
				$this->mo2f_transactionid = $response['txId'];

				$mo2fa_login_message = $mo2f_second_factor == 'PUSH NOTIFICATIONS' ? 'A Push Notification has been sent to your phone. We are waiting for your approval.' : 'An email has been sent to ' . molms_2f_Utility::mo2f_get_hidden_email( $user_email ) . '. We are waiting for your approval.';
				$mo2fa_login_status  = $mo2f_second_factor == 'PUSH NOTIFICATIONS' ? 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' : 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
			} elseif ( $response['status'] == 'ERROR' || $response['status'] == 'FAILED' ) {
				molms_2f_Utility::set_user_values( $session_id, 'mo2f_transactionId', $response['txId'] );
				$this->mo2f_transactionid = $response['txId'];
				$mo2fa_login_message      = $mo2f_second_factor == 'PUSH NOTIFICATIONS' ? 'An error occured while sending push notification to your app. You can click on <b>Phone is Offline</b> button to enter soft token from app or <b>Forgot your phone</b> button to receive OTP to your registered email.' : 'An error occured while sending email. Please try again.';
				$mo2fa_login_status       = $mo2f_second_factor == 'PUSH NOTIFICATIONS' ? 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' : 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
			}
		} else {
			$this->remove_current_activity( $session_id );
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please try again.' ) );

			return $error;
		}
	}

	public function mo2f_pass2login_otp_verification( $user, $mo2f_second_factor, $redirect_to, $session_id = null ) {
		global $molms_db_queries;
		if ( is_null( $session_id ) ) {
			$session_id = $this->create_session();
		}
		$mo2f_external_app_type = get_user_meta( $user->ID, 'mo2f_external_app_type', true );
		if ( $mo2f_second_factor == 'EMAIL' ) {
			$mo2f_user_phone = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			$wdewdeqdqq      = get_site_option( base64_encode( 'remainingOTP' ) );
			if ( $wdewdeqdqq > 30 || get_site_option( base64_encode( 'limitReached' ) ) ) {
				update_site_option( base64_encode( 'remainingOTP' ), 0 );
			}
		} else {
			$mo2f_user_phone = $molms_db_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
		}
		if ( $mo2f_second_factor == 'SOFT TOKEN' ) {
			$mo2fa_login_message = 'Please enter the one time passcode shown in the miniOrange Authenticator app.';
			$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
		} elseif ( $mo2f_second_factor == 'GOOGLE AUTHENTICATOR' ) {
			$mo2fa_login_message = 'Please enter the one time passcode shown in the Authenticator app.';
			$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION';
			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
		} else {
			$challengeMobile = new Molms_Customer_Setup();
			$content         = '';
			$response        = [];
			$otpLIMiTE       = 0;
			if ( molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' ) > 0 || $mo2f_second_factor != 'EMAIL' ) {
				if ( $mo2f_second_factor == 'OTP Over SMS' ) {
					$mo2f_second_factor = 'SMS';
				}
				$content  = $challengeMobile->send_otp_token( $mo2f_user_phone, $mo2f_second_factor, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ), $user );
				$response = json_decode( $content, true );
			} else {
				$response['status']  = 'FAILED';
				$response['message'] = '<p style = "color:red;
                ">OTP limit has been exceeded</p>';
				$otpLIMiTE           = 1;
			}
			if ( json_last_error() == JSON_ERROR_NONE ) {
				if ( $response['status'] == 'SUCCESS' ) {
					if ( $mo2f_second_factor == 'EMAIL' ) {
						$molms_email_transactions = molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' );
						update_site_option( 'molms_email_transactions', $molms_email_transactions - 1 );
					} elseif ( $mo2f_second_factor == 'SMS' ) {
						update_site_option( 'molms_sms_transactions', get_site_option( 'molms_sms_transactions' ) - 1 );
					}
					if ( ! isset( $response['phoneDelivery']['contact'] ) ) {
						$response['phoneDelivery']['contact'] = '';
					}
					$message = 'The OTP has been sent to ' . molms_2f_Utility::get_hidden_phone( $response['phoneDelivery']['contact'] ) . '. Please enter the OTP you received to Validate.';
					update_site_option( 'mo2f_number_of_transactions', molms_Utility::get_mo2f_db_option( 'mo2f_number_of_transactions', 'get_site_option' ) - 1 );
					molms_2f_Utility::set_user_values( $session_id, 'mo2f_transactionId', $response['txId'] );
					$this->mo2f_transactionid = $response['txId'];
					$mo2fa_login_message      = $message;
					$currentMethod            = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );

					if ( $currentMethod == 'OTP Over Email' ) {
						$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
					} else {
						$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS';
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				} else {
					if ( $response['message'] == 'TEST FAILED.' ) {
						$response['message'] = 'There is an error in sending the OTP.';
					}

					$last_message = 'or  <a href = "https://login.xecurify.com/moas/login?redirectUrl=https://login.xecurify.com/moas/login?redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=otp_recharge_plan">puchase trascactions</a>';

					if ( $otpLIMiTE == 1 ) {
						$last_message = 'or contact miniOrange';
					} elseif ( MOLMS_IS_ONPREM && ( $mo2f_second_factor == 'OTP Over Email' || $mo2f_second_factor == 'EMAIL' || $mo2f_second_factor == 'Email Verification' ) ) {
						$last_message = 'or check your SMTP Server and remaining transacions.';
					} else {
						$last_message = 'or check your remaining transacions';
					}

					$message = $response['message'] . $last_message;
					if ( ! isset( $response['txId'] ) ) {
						$response['txId'] = '';
					}
					molms_2f_Utility::set_user_values( $session_id, 'mo2f_transactionId', $response['txId'] );
					$this->mo2f_transactionid = $response['txId'];
					$mo2fa_login_message      = $message;
					$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				}
			} else {
				$this->remove_current_activity( $session_id );
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please try again.' ) );

				return $error;
			}
		}
	}

	public function display_email_verification( $head, $body, $color ) {
		echo '<div  style="background-color: #d5e3d9; height:850px;" >
                    <div style="height:350px; background-color: #3CB371; border-radius: 2px; padding:2%;">
                    <div class="mo2f_tamplate_layout" style="background-color: #ffffff;border-radius: 5px;box-shadow: 0 5px 15px rgba(0,0,0,.5); width:850px;height:350px; align-self: center; margin: 180px auto;">
                    <img  alt="logo"  style="margin-left:240px ;
		        margin-top:10px;width=40%;" src="https://auth.miniorange.com/moas/images/logo_large.png" />
                    <div><hr></div>

                    <tbody>
                    <tr>
                    <td>

                    <p style="margin-top:0;margin-bottom:10px">
                    <p style="margin-top:0;margin-bottom:10px"> <h1 style="color:' . esc_html( $color ) . ';text-align:center;font-size:50px">' . esc_html( $head ) . '</h1></p>
                    <p style="margin-top:0;margin-bottom:10px">
                    <p style=margin-top:0;margin-bottom:10px;text-align:center><h2 style=text-align:center>' . esc_html( $body ) . '</h2></p>
                    <p style=margin-top:0;margin-bottom:0px;font-size:11px>

                    </td>
                    </tr>

                    </div>
                    </div>
                    </div>';
	}

	public function check_rba_validation() {
		$nonce = isset( $_POST['mo2f_trust_device_confirm_nonce'] ) ? sanitize_text_field( $_POST['mo2f_trust_device_confirm_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-trust-device-confirm-nonce' ) ) {
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$this->remove_current_activity( $session_id_encrypt );
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR ' ) . '</strong>:' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			try {
				$user_id = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				global $molms_db_queries;
				$email           = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user_id );
				$mo2f_rba_status = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_rba_status', $session_id_encrypt );
				mo2f_register_profile( $email, 'true', $mo2f_rba_status );
			} catch ( Exception $e ) {
				echo esc_html( $e->getMessage() );
			}
			$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
		}
	}

	public function check_rba_cancalation() {
		$nonce = isset( $_POST['mo2f_trust_device_cancel_nonce'] ) ? sanitize_text_field( $_POST['mo2f_trust_device_cancel_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-trust-device-cancel-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
		}
	}

	public function check_miniorange_challenge_forgotphone() {
		/*check kba validation*/
		$nonce = isset( $_POST['miniorange_forgotphone'] ) ? sanitize_text_field( $_POST['miniorange_forgotphone'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-forgotphone' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			$mo2fa_login_status  = isset( $_POST['request_origin_method'] ) ? sanitize_text_field( $_POST['request_origin_method'] ) : null;
			$session_id_encrypt  = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$redirect_to         = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$mo2fa_login_message = '';
			$this->miniorange_pass2login_start_session();
			$customer = new Molms_Customer_Setup();
			$user_id  = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			global $molms_db_queries;
			$user_email               = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user_id );
			$kba_configuration_status = $molms_db_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $user_id );
			if ( $kba_configuration_status ) {
				$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL';
				$pass2fa_login      = new Molms_Password_2Factor_Login();
				$pass2fa_login->mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id_encrypt );
			} else {
				$hidden_user_email = molms_2f_Utility::mo2f_get_hidden_email( $user_email );
				$content           = json_decode( $customer->send_otp_token( $user_email, 'EMAIL', get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				if ( isset( $content['status'] ) && strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
					$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );
					molms_2f_Utility::unset_session_variables( $session_cookie_variables );
					molms_2f_Utility::unset_cookie_variables( $session_cookie_variables );
					molms_2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
					//if the php session folder has insufficient permissions, cookies to be used
					molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_login_message', 'A one time passcode has been sent to <b>' . $hidden_user_email . '</b>. Please enter the OTP to verify your identity.' );
					molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_transactionId', $content['txId'] );
					$this->mo2f_transactionid = $content['txId'];
					$mo2fa_login_message      = 'A one time passcode has been sent to <b>' . $hidden_user_email . '</b>. Please enter the OTP to verify your identity.';
					$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
				} else {
					$mo2fa_login_message = 'Error occurred while sending OTP over email. Please try again.';
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
			$pass2fa_login = new Molms_Password_2Factor_Login();
			$pass2fa_login->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
		}
	}

	public function miniorange2f_back_to_inline_registration() {
		$nonce = isset( $_POST['miniorange_back_inline_reg_nonce'] ) ? sanitize_text_field( $_POST['miniorange_back_inline_reg_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-back-inline-reg-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			$session_id_encrypt  = sanitize_text_field( $_POST['session_id'] );
			$redirect_to         = esc_url_raw( $_POST['redirect_to'] );
			$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$mo2fa_login_message = '';
			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
		}
	}

	public function check_miniorange_alternate_login_kba() {
		$nonce = isset( $_POST['miniorange_alternate_login_kba_nonce'] ) ? sanitize_text_field( $_POST['miniorange_alternate_login_kba_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-alternate-login-kba-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$this->mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id_encrypt );
		}
	}

	public function check_kba_validation() {
		if ( isset( $_POST['miniorange_kba_nonce'] ) ) { /*check kba validation*/
			$nonce = isset( $_POST['miniorange_kba_nonce'] ) ? sanitize_text_field( $_POST['miniorange_kba_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'mo2f-validate-kba-details-nonce' ) ) {
				$error = new WP_Error();
				$error->add(
					'empty_username',
					__(
						'<strong>ERROR</strong>:
                Invalid Request.'
					)
				);

				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				if ( isset( $user_id ) ) {
					$otpToken      = array();
					$kba_questions = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo_2_factor_kba_questions', $session_id_encrypt );
					$otpToken[0]   = $kba_questions[0]['question'];
					$otpToken[1]   = sanitize_text_field( $_POST['mo2f_answer_1'] );
					$otpToken[2]   = $kba_questions[1]['question'];
					$otpToken[3]   = sanitize_text_field( $_POST['mo2f_answer_2'] );
					if ( molms_2f_Utility::mo2f_check_empty_or_null( $otpToken[1] ) || molms_2f_Utility::mo2f_check_empty_or_null( $otpToken[3] ) ) {
						$mo2fa_login_message = 'Please provide both the answers.';
						$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
					$check_trust_device = isset( $_POST['mo2f_trust_device'] ) ? sanitize_text_field( $_POST['mo2f_trust_device'] ) : 'false';
					//if the php session folder has insufficient permissions, cookies to be used
					$mo2f_login_transaction_id = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );
					$mo2f_rba_status           = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_rba_status', $session_id_encrypt );
					$kba_validate              = new Molms_Customer_Setup();
					$kba_validate_response     = json_decode( $kba_validate->validate_otp_token( 'KBA', $user_id, $mo2f_login_transaction_id, $otpToken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ), get_user_by( 'ID', $user_id ) ), true );
					global $molms_db_queries;
					$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user_id );
					if ( isset( $kba_validate_response['status'] ) && strcasecmp( $kba_validate_response['status'], 'SUCCESS' ) == 0 ) {
						if ( get_site_option( 'mo2f_remember_device' ) && $check_trust_device == 'on' ) {
							try {
								mo2f_register_profile( $email, 'true', $mo2f_rba_status );
							} catch ( Exception $e ) {
								echo esc_html( $e->getMessage() );
							}
							$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
						} else {
							$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
						}
					} else {
						$mo2fa_login_message = 'The answers you have provided are incorrect.';
						$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
				} else {
					$this->remove_current_activity( $session_id_encrypt );

					return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again..' ) );
				}
			}
		}
	}

	public function check_miniorange_mobile_validation() {
		/*check mobile validation */

		$nonce = isset( $_POST['miniorange_mobile_validation_nonce'] ) ? sanitize_text_field( $_POST['miniorange_mobile_validation_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			if ( MOLMS_IS_ONPREM && ( isset( $_POST['tx_type'] ) && sanitize_text_field( $_POST['tx_type'] ) != 'PN' ) ) {
				$txid   = sanitize_email( $_POST['TxidEmail'] );
				$status = get_site_option( $txid );
				if ( $status != '' ) {
					if ( $status != 1 ) {
						return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again.' ) );
					}
				}
			}
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			//if the php session folder has insufficient permissions, cookies to be used
			$mo2f_login_transaction_id = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );
			$redirect_to               = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$checkMobileStatus         = new Molms_Two_Factor_Setup();
			$content                   = $checkMobileStatus->check_mobile_status( $mo2f_login_transaction_id );
			$response                  = json_decode( $content, true );
			if ( MOLMS_IS_ONPREM ) {
				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			}
			if ( json_last_error() == JSON_ERROR_NONE ) {
				if ( $response['status'] == 'SUCCESS' ) {
					if ( get_site_option( 'mo2f_remember_device' ) ) {
						$mo2fa_login_status = 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, null, $redirect_to, null, $session_id_encrypt );
					} else {
						$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
					}
				} else {
					$this->remove_current_activity( $session_id_encrypt );

					return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again.' ) );
				}
			} else {
				$this->remove_current_activity( $session_id_encrypt );

				return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again.' ) );
			}
		}
	}

	public function check_miniorange_mobile_validation_failed() {
		/*Back to miniOrange Login Page if mobile validation failed and from back button of mobile challenge, soft token and default login*/
		$nonce = isset( $_POST['miniorange_mobile_validation_failed_nonce'] ) ? sanitize_text_field( $_POST['miniorange_mobile_validation_failed_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-failed-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$this->remove_current_activity( $session_id_encrypt );
		}
	}

	public function check_miniorange_softtoken() {
		/*Click on the link of phone is offline */
		$nonce = isset( $_POST['miniorange_softtoken'] ) ? sanitize_text_field( $_POST['miniorange_softtoken'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-softtoken' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt       = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );
			molms_2f_Utility::unset_session_variables( $session_cookie_variables );
			molms_2f_Utility::unset_cookie_variables( $session_cookie_variables );
			molms_2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
			$redirect_to         = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$mo2fa_login_message = 'Please enter the one time passcode shown in the miniOrange Authenticator app.';
			$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
		}
	}

	public function check_miniorange_soft_token() {
		/*Validate Soft Token,OTP over SMS,OTP over EMAIL,Phone verification */
		$nonce = isset( $_POST['miniorange_soft_token_nonce'] ) ? sanitize_text_field( $_POST['miniorange_soft_token_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-soft-token-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$mo2fa_login_status = isset( $_POST['request_origin_method'] ) ? sanitize_text_field( $_POST['request_origin_method'] ) : null;
			$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$softtoken          = '';
			$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			$attempts           = get_site_option( 'mo2f_attempts_before_redirect', 3 );
			if ( isset( $_POST['mo2fa_softtoken'] ) ) {
				$softtoken = sanitize_text_field( $_POST['mo2fa_softtoken'] );
			}

			if ( molms_2f_Utility::mo2f_check_empty_or_null( $softtoken ) ) {
				if ( $attempts > 1 || $attempts == 'disabled' ) {
					update_site_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
					$mo2fa_login_message = 'Please enter OTP to proceed.';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				} else {
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
					$this->remove_current_activity( $session_id_encrypt );

					return new WP_Error( 'limit_exceeded', '<strong>ERROR</strong>: Number of attempts exceeded.' );
				}
			} else {
				if ( ! molms_2f_Utility::mo2f_check_number_length( $softtoken ) ) {
					if ( $attempts > 1 || $attempts == 'disabled' ) {
						update_site_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
						$mo2fa_login_message = 'Invalid OTP. Only digits within range 4-8 are allowed. Please try again.';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					} else {
						$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
						$this->remove_current_activity( $session_id_encrypt );
						update_site_option( 'mo2f_attempts_before_redirect', 3 );
						if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
							$data = array( 'reload' => 'reload', );
							wp_send_json_success( $data );
						} else {
							return new WP_Error( 'limit_exceeded', '<strong>ERROR</strong>: Number of attempts exceeded.' );
						}
					}
				}
			}

			global $molms_db_queries;
			$user_email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user_id );
			if ( isset( $user_id ) ) {
				$customer     = new Molms_Customer_Setup();
				$content      = '';
				$current_user = get_userdata( $user_id );
				//if the php session folder has insufficient permissions, cookies to be used
				$mo2f_login_transaction_id = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );

				if ( isset( $mo2fa_login_status ) && $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' ) {
					$content = json_decode( $customer->validate_otp_token( 'EMAIL', null, $mo2f_login_transaction_id, $softtoken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ), $current_user ), true );
				} elseif ( isset( $mo2fa_login_status ) && $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' ) {
					$content = json_decode( $customer->validate_otp_token( 'SMS', null, $mo2f_login_transaction_id, $softtoken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				} elseif ( isset( $mo2fa_login_status ) && $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' ) {
					$content = json_decode( $customer->validate_otp_token( 'PHONE VERIFICATION', null, $mo2f_login_transaction_id, $softtoken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				} elseif ( isset( $mo2fa_login_status ) && $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' ) {
					$content = json_decode( $customer->validate_otp_token( 'SOFT TOKEN', $user_email, null, $softtoken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				} elseif ( isset( $mo2fa_login_status ) && $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION' ) {
					$content = json_decode( $customer->validate_otp_token( 'GOOGLE AUTHENTICATOR', $user_email, null, $softtoken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				} else {
					$this->remove_current_activity( $session_id_encrypt );

					return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Invalid Request. Please try again.' ) );
				}
				if ( isset( $content['status'] ) && strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
					update_site_option( 'mo2f_attempts_before_redirect', 3 );
					if ( get_site_option( 'mo2f_remember_device' ) ) {
						$mo2fa_login_status = 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, null, $redirect_to, null, $session_id_encrypt );
					} else {
						if ( $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' ) {
							$molms_db_queries->update_user_details( $user_id, array(
								'mo2f_configured_2FA_method'          => 'OTP Over Email',
								'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS'
							) );
							$enduser = new Molms_Two_Factor_Setup();

							$enduser->mo2f_update_userinfo( $user_email, 'OTP Over Email', null, null, null );
						}
						$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
					}
				} else {
					if ( $attempts > 1 || $attempts == 'disabled' ) {
						update_site_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
						$message = $mo2fa_login_status == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' ? 'You have entered an invalid OTP. Please click on Sync Time in the miniOrange Authenticator app to sync your phone time with the miniOrange servers and try again.' : 'Invalid OTP. Please try again.';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $message, $redirect_to, null, $session_id_encrypt );
					} else {
						$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
						$this->remove_current_activity( $session_id_encrypt );
						update_site_option( 'mo2f_attempts_before_redirect', 3 );
						if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
							$data = array( 'reload' => 'reload', );
							wp_send_json_success( $data );
						} else {
							return new WP_Error( 'limit_exceeded', '<strong>ERROR</strong>: Number of attempts exceeded.' );
						}
					}
				}
			} else {
				$this->remove_current_activity( $session_id_encrypt );

				return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again..' ) );
			}
		}
	}

	public function check_miniorange_inline_skip_registration() {
		$error = new WP_Error();
		$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
	}

	public function check_miniorange_attribute_collection() {
		$nonce = isset( $_POST['miniorange_attribute_collection_nonce'] ) ? sanitize_text_field( $_POST['miniorange_attribute_collection_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-attribute-collection-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );

			return $error;
		} else {
			$this->miniorange_pass2login_start_session();
			$user_id     = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
			$currentuser = get_user_by( 'id', $user_id );
			$attributes  = isset( $_POST['miniorange_rba_attribures'] ) ? sanitize_text_field( $_POST['miniorange_rba_attribures'] ) : null;
			$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
			$session_id  = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
			$this->miniorange_initiate_2nd_factor( $currentuser, $attributes, $redirect_to, $session_id );
		}
	}

	public function miniorange_initiate_2nd_factor( $currentuser, $attributes = null, $redirect_to = null, $otp_token = '', $session_id_encrypt = null ) {
		global $molms_db_queries;
		$this->miniorange_pass2login_start_session();
		if ( is_null( $session_id_encrypt ) ) {
			$session_id_encrypt = $this->create_session();
		}

		if ( class_exists( 'UM_Functions' ) ) {
			if ( ! isset( $_POST['wp-submit'] ) and isset( $_POST['um_request'] ) ) {
				$meta = get_site_option( 'um_role_' . $currentuser->roles[0] . '_meta' );
				if ( isset( $meta ) and $meta != '' ) {
					if ( isset( $meta['_um_login_redirect_url'] ) ) {
						$redirect_to = $meta['_um_login_redirect_url'];
					}
					if ( $redirect_to == '' ) {
						$redirect_to = get_site_url();
					}
				}
				$login_form_url = '';
				if ( isset( $_POST['redirect_to'] ) ) {
					$login_form_url = esc_url_raw( $_POST['redirect_to'] );
				}

				if ( $login_form_url != '' and ! is_null( $login_form_url ) ) {
					$redirect_to = $login_form_url;
				}
			}
		}
		molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_current_user_id', $currentuser->ID );
		molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_1stfactor_status', 'VALIDATE_SUCCESS' );

		$this->mo2f_user_id = $currentuser->ID;
		$this->fstfactor    = 'VALIDATE_SUCCESS';

		$is_customer_admin = true;

		$roles             = ( array ) $currentuser->roles;
		$twofactor_enabled = 0;
		foreach ( $roles as $role ) {
			if ( get_site_option( 'mo2fa_' . $role ) == '1' ) {
				$twofactor_enabled = 1;
			}
		}
		if ( $is_customer_admin && $twofactor_enabled ) {
			$mo_2factor_user_registration_status = $molms_db_queries->get_user_detail( 'mo_2factor_user_registration_status', $currentuser->ID );
			$kba_configuration_status            = $molms_db_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $currentuser->ID );

			if ( molms_Utility::get_mo2f_db_option( 'mo2f_enable_brute_force', 'get_site_option' ) ) {
				$mo2f_allwed_login_attempts = get_site_option( 'mo2f_allwed_login_attempts' );
			} else {
				$mo2f_allwed_login_attempts = 'disabled';
			}
			update_user_meta( $currentuser->ID, 'mo2f_user_login_attempts', $mo2f_allwed_login_attempts );

			$twofactor_transactions = new molms_DB;
			$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $currentuser->ID );
			$tfa_enabled            = $molms_db_queries->get_user_detail( 'mo2f_2factor_enable_2fa_byusers', $currentuser->ID );
			if ( $tfa_enabled == 0 && ( $mo_2factor_user_registration_status != 'MO_2_FACTOR_PLUGIN_SETTINGS' ) && $tfa_enabled != '' ) {
				$exceeded = 1;
			}

			if ( $mo_2factor_user_registration_status == 'MO_2_FACTOR_PLUGIN_SETTINGS' ) { //checking if user has configured any 2nd factor method
				$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $currentuser->ID );
				try {
					$mo2f_rba_status = molms_collect_attributes( $email, stripslashes( $attributes ) ); // Rba flow
					molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_rba_status', $mo2f_rba_status );
					$this->mo2f_rbastatus = $mo2f_rba_status;
				} catch ( Exception $e ) {
					echo esc_html( $e->getMessage() );
				}

				if ( $mo2f_rba_status['status'] == 'SUCCESS' && $mo2f_rba_status['decision_flag'] ) {
					$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
				} elseif ( ( $mo2f_rba_status['status'] == 'DENY' ) && get_site_option( 'mo2f_rba_installed' ) ) {
					$this->mo2f_restrict_access( 'Access_denied' );
					exit;
				} elseif ( ( $mo2f_rba_status['status'] == 'ERROR' ) && get_site_option( 'mo2f_rba_installed' ) ) {
					$this->mo2f_restrict_access( 'Access_denied' );
					exit;
				} else {
					$mo2f_second_factor = '';

					if ( MOLMS_IS_ONPREM ) {
						$mo2f_second_factor = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $currentuser->ID );
					} else {
						$mo2f_second_factor = mo2f_get_user_2ndfactor( $currentuser );
					}
					if ( $mo2f_second_factor == 'miniOrange Soft Token' ) {
						$mo2f_second_factor = 'SOFT TOKEN';
					} elseif ( $mo2f_second_factor == 'miniOrange Push Notification' ) {
						$mo2f_second_factor = 'PUSH NOTIFICATIONS';
					} elseif ( $mo2f_second_factor == 'miniOrange QR Code Authentication' ) {
						$mo2f_second_factor = 'MOBILE AUTHENTICATION';
					} elseif ( $mo2f_second_factor == 'Security Questions' ) {
						$mo2f_second_factor = 'KBA';
					} elseif ( $mo2f_second_factor == 'Google Authenticator' ) {
						$mo2f_second_factor = 'GOOGLE AUTHENTICATOR';
					} elseif ( $mo2f_second_factor == 'OTP Over SMS' ) {
						$mo2f_second_factor = 'SMS';
					} elseif ( $mo2f_second_factor == 'OTP Over Email' || $mo2f_second_factor == 'OTP OVER EMAIL' || $mo2f_second_factor == 'EMAIL' ) {
						$mo2f_second_factor = 'EMAIL';

						if ( molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' ) <= 0 ) {
							update_site_option( 'molms_zero_email_transactions', 1 );
						}
					}


					if ( ( ( $mo2f_second_factor == 'GOOGLE AUTHENTICATOR' ) || ( $mo2f_second_factor == 'SOFT TOKEN' ) || ( $mo2f_second_factor == 'AUTHY AUTHENTICATOR' ) ) && molms_Utility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'get_site_option' ) && ! get_site_option( 'mo2f_remember_device' ) && ! isset( $_POST['mo_woocommerce_login_prompt'] ) ) {
						$error = $this->mo2f_validate_soft_token( $currentuser, $mo2f_second_factor, $otp_token, $session_id_encrypt, $redirect_to );
						if ( is_wp_error( $error ) ) {
							return $error;
						}
					} else {
						if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && molms_2f_Utility::check_if_request_is_from_mobile_device( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) ) && $kba_configuration_status ) {
							$this->mo2f_pass2login_kba_verification( $currentuser->ID, $redirect_to, $session_id_encrypt );
						} else {
							if ( $mo2f_second_factor == 'MOBILE AUTHENTICATION' ) {
								$this->mo2f_pass2login_mobile_verification( $currentuser, $redirect_to, $session_id_encrypt );
							} elseif ( $mo2f_second_factor == 'PUSH NOTIFICATIONS' || $mo2f_second_factor == 'OUT OF BAND EMAIL' || $mo2f_second_factor == 'Email Verification' ) {
								$this->mo2f_pass2login_push_oobemail_verification( $currentuser, $mo2f_second_factor, $redirect_to, $session_id_encrypt );
							} elseif ( $mo2f_second_factor == 'SOFT TOKEN' || $mo2f_second_factor == 'SMS' || $mo2f_second_factor == 'PHONE VERIFICATION' || $mo2f_second_factor == 'GOOGLE AUTHENTICATOR' || $mo2f_second_factor == 'EMAIL' ) {
								$this->mo2f_pass2login_otp_verification( $currentuser, $mo2f_second_factor, $redirect_to, $session_id_encrypt );
							} elseif ( $mo2f_second_factor == 'KBA' || $mo2f_second_factor == 'Security Questions' ) {
								$this->mo2f_pass2login_kba_verification( $currentuser->ID, $redirect_to, $session_id_encrypt );
							} elseif ( $mo2f_second_factor == 'NONE' ) {
								if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
									$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
								} else {
									return $currentuser;
								}
							} else {
								$this->remove_current_activity( $session_id_encrypt );
								$error = new WP_Error();
								if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
									$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Two Factor method has not been configured.', );
									wp_send_json_success( $data );
								} else {
									$error->add( 'empty_username', __( '<strong>ERROR</strong>: Two Factor method has not been configured.' ) );

									return $error;
								}
							}
						}
					}
				}
			} elseif ( ! $exceeded && molms_Utility::get_mo2f_db_option( 'mo2f_inline_registration', 'site_option' ) ) {
				$this->mo2fa_inline( $currentuser, $redirect_to, $session_id_encrypt );
			} else {
				if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
					$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
				} else {
					return $currentuser;
				}
			}
		} else { //plugin is not activated for current role then logged him in without asking 2 factor
			if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			} else {
				return $currentuser;
			}
		}
	}

	public function mo2f_restrict_access( $identity ) {
		apply_filters( 'mo2f_rba_addon', $identity );
		exit;
	}

	public function mo2f_validate_soft_token( $currentuser, $mo2f_second_factor, $softtoken, $session_id_encrypt, $redirect_to = null ) {
		global $molms_db_queries;
		$email    = $molms_db_queries->get_user_detail( 'mo2f_user_email', $currentuser->ID );
		$customer = new Molms_Customer_Setup();
		$content  = json_decode( $customer->validate_otp_token( $mo2f_second_factor, $email, null, $softtoken, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
		if ( isset( $content['status'] ) && strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
			if ( get_site_option( 'mo2f_remember_device' ) ) {
				$mo2fa_login_status = 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, null, $redirect_to, null, $session_id_encrypt );
			} else {
				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			}
		} else {
			if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
				$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Invalid One Time Passcode.', );
				wp_send_json_success( $data );
			} else {
				return new WP_Error( 'invalid_one_time_passcode', '<strong>ERROR</strong>: Invalid One Time Passcode.' );
			}
		}
	}

	public function mo2fa_inline( $currentuser, $redirect_to, $session_id ) {
		global $molms_db_queries;
		$currentUserId = $currentuser->ID;
		$email         = $currentuser->user_email;
		$molms_db_queries->insert_user( $currentUserId, array( 'user_id' => $currentUserId ) );
		$molms_db_queries->update_user_details(
			$currentUserId,
			array(
				'user_registration_with_miniorange'   => 'SUCCESS',
				'mo2f_user_email'                     => $email,
				'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR',
			)
		);

		$mo2fa_login_message = '';
		$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

		$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
	}

	public function save_inline_2fa_method() {
		if ( isset( $_POST['miniorange_inline_save_2factor_method_nonce'] ) ) {
			$nonce = isset( $_POST['miniorange_inline_save_2factor_method_nonce'] ) ? sanitize_text_field( $_POST['miniorange_inline_save_2factor_method_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-2factor-method-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$mo2fa_login_message               = '';
				$mo2fa_login_status                = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$user_id                           = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$redirect_to                       = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$current_user                      = get_user_by( 'id', $user_id );
				$currentUserId                     = $current_user->ID;
				$email                             = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
				$user_registration_with_miniorange = $molms_db_queries->get_user_detail( 'user_registration_with_miniorange', $current_user->ID );
				if ( $user_registration_with_miniorange == 'SUCCESS' ) {
					$selected_method = isset( $_POST['mo2f_selected_2factor_method'] ) ? sanitize_text_field( $_POST['mo2f_selected_2factor_method'] ) : 'NONE';

					if ( $selected_method == 'OUT OF BAND EMAIL' ) {
						if ( ! MOLMS_IS_ONPREM ) {
							$current_user = get_userdata( $currentUserId );
							$email        = $current_user->user_email;
							$response     = $this->create_user_in_miniOrange( $currentUserId, $email, $selected_method );

							if ( $response['status'] == 'ERROR' ) {
								$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
								$mo2fa_login_message = $response['message'] . 'Skip the two-factor for login';
							} else {
								$enduser = new Molms_Two_Factor_Setup();

								$molms_db_queries->update_user_details(
									$currentUserId,
									array(
										'mo2f_email_verification_status' => true,
										'mo2f_configured_2FA_method'     => 'Email Verification',
										'mo2f_user_email'                => $email
									)
								);
								$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
							}
						} else {
							$enduser = new Molms_Two_Factor_Setup();

							$molms_db_queries->update_user_details(
								$currentUserId,
								array(
									'mo2f_email_verification_status' => true,
									'mo2f_configured_2FA_method'     => 'Email Verification',
									'mo2f_user_email'                => $email
								)
							);
							$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
						}
					} elseif ( $selected_method == 'OTP OVER EMAIL' ) {
						$email = $current_user->user_email;
						if ( ! MOLMS_IS_ONPREM ) {
							$current_user = get_userdata( $currentUserId );
							$response     = $this->create_user_in_miniOrange( $currentUserId, $email, $selected_method );
							if ( $response['status'] == 'ERROR' ) {
								$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
								$mo2fa_login_message = $response['message'] . 'Skip the two-factor for login';
							} else {
								$user_email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
								if ( $user_email != '' and ! is_null( $user_email ) ) {
									$email = $user_email;
								}
								$this->mo2f_otp_over_email_send( $email, $redirect_to, $session_id_encrypt, $current_user );
							}
						} else {
							$this->mo2f_otp_over_email_send( $email, $redirect_to, $session_id_encrypt, $current_user );
						}
					} elseif ( $selected_method == 'GOOGLE AUTHENTICATOR' ) {
						$this->miniorange_pass2login_start_session();
						$mo2fa_login_message = '';
						$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
						$google_auth         = new Molms_Miniorange_Rba_Attributes();

						$gauth_name          = get_site_option( 'mo2f_google_appname' );
						$google_account_name = $gauth_name ? $gauth_name : 'miniOrangeAu';

						$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );

						if ( MOLMS_IS_ONPREM ) {
							$molms_db_queries->update_user_details(
								$current_user->ID,
								array(
									'mo2f_configured_2FA_method' => $selected_method,
								)
							);
							global $molms_dirName;
							include_once $molms_dirName . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'gaonprem.php';
							$gauth_obj = new molms_Google_auth_onpremise();

							$onpremise_secret              = $gauth_obj->createSecret();
							$issuer                        = get_site_option( 'mo2f_GA_account_name', 'miniOrangeAu' );
							$url                           = $gauth_obj->geturl( $onpremise_secret, $issuer, $email );
							$mo2f_google_auth              = array();
							$mo2f_google_auth['ga_qrCode'] = $url;
							$mo2f_google_auth['ga_secret'] = $onpremise_secret;
							$_SESSION['mo2f_google_auth']  = $mo2f_google_auth;
							update_user_meta( $current_user->ID, 'mo2f_google_auth', wp_json_encode( $mo2f_google_auth ) );
						} else {
							$current_user = get_userdata( $currentUserId );
							$email        = $current_user->user_email;
							$tempemail    = $molms_db_queries->get_user_detail( 'mo2f_user_email', $currentUserId );

							if ( ! isset( $tempemail ) and ! is_null( $tempemail ) and $tempemail != '' ) {
								$email = $tempemail;
							}

							$response = $this->create_user_in_miniOrange( $currentUserId, $email, $selected_method );
							if ( $response['status'] == 'ERROR' ) {
								$mo2fa_login_message = $response['message'];
								$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
							} else {
								$molms_db_queries->update_user_details(
									$current_user->ID,
									array(
										'mo2f_configured_2FA_method' => $selected_method,
									)
								);
								$google_response = json_decode( $google_auth->mo2f_google_auth_service( $email, $google_account_name ), true );
								if ( json_last_error() == JSON_ERROR_NONE ) {
									if ( $google_response['status'] == 'SUCCESS' ) {
										$mo2f_google_auth              = array();
										$mo2f_google_auth['ga_qrCode'] = $google_response['qrCodeData'];
										$mo2f_google_auth['ga_secret'] = $google_response['secret'];
										$_SESSION['mo2f_google_auth']  = $mo2f_google_auth;
										update_user_meta( $current_user->ID, 'mo2f_google_auth', wp_json_encode( $mo2f_google_auth ) );
									} else {
										$mo2fa_login_message = __( 'Invalid request. Please register with miniOrange to configure 2 Factor plugin.', '2fa-learndash-lms' );
									}
								}
							}
						}
					} else {
						//inline for others
						if ( ! MOLMS_IS_ONPREM || $selected_method == 'MOBILE AUTHENTICATION' || $selected_method == 'PUSH NOTIFICATIONS' || $selected_method == 'SOFT TOKEN' ) {
							$current_user = get_userdata( $currentUserId );
							$email        = $current_user->user_email;
							$response     = $this->create_user_in_miniOrange( $currentUserId, $email, $selected_method );
							if ( ! is_null( $response ) && $response['status'] == 'ERROR' ) {
								$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
								$mo2fa_login_message = $response['message'] . 'Skip the two-factor for login';
							} else {
								$molms_db_queries->update_user_details( $current_user->ID, array( 'mo2f_configured_2FA_method' => $selected_method ) );
							}
						} else {
							$molms_db_queries->update_user_details(
								$current_user->ID,
								array(
									'mo2f_configured_2FA_method' => $selected_method,
								)
							);
						}
					}
				} else {
					$mo2fa_login_message = __( 'Invalid request. Please register with miniOrange to configure 2 Factor plugin.', '2fa-learndash-lms' );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function create_user_in_miniOrange( $current_user_id, $email, $currentMethod ) {
		$tempEmail = get_user_meta( $current_user_id, 'mo2f_email_miniOrange', true );
		if ( isset( $tempEmail ) and $tempEmail != '' ) {
			$email = $tempEmail;
		}
		global $molms_db_queries;

		$enduser = new Molms_Two_Factor_Setup();
		if ( $current_user_id == get_site_option( 'mo2f_miniorange_admin' ) ) {
			$email = get_site_option( 'mo2f_email' );
		}

		$check_user = json_decode( $enduser->mo_check_user_already_exist( $email ), true );

		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $check_user['status'] == 'ERROR' ) {
				return $check_user;
			} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND' ) == 0 ) {
				$molms_db_queries->update_user_details(
					$current_user_id,
					array(
						'user_registration_with_miniorange'   => 'SUCCESS',
						'mo2f_user_email'                     => $email,
						'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR'
					)
				);
				update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );

				$mo2fa_login_status = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

				return $check_user;
			} elseif ( strcasecmp( $check_user['status'], 'USER_NOT_FOUND' ) == 0 ) {
				$current_user = get_user_by( 'id', $current_user_id );
				$content      = json_decode( $enduser->mo_create_user( $current_user, $email ), true );

				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( isset( $content['status'] ) && strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
						update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );
						$molms_db_queries->update_user_details(
							$current_user_id,
							array(
								'user_registration_with_miniorange'   => 'SUCCESS',
								'mo2f_user_email'                     => $email,
								'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR'
							)
						);

						$mo2fa_login_message = '';
						$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

						return $check_user;
					} else {
						$check_user['status']  = 'ERROR';
						$check_user['message'] = 'There is an issue in user creation in miniOrange. Please skip and contact miniorange';

						return $check_user;
					}
				}
			} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) == 0 ) {
				$mo2fa_login_message   = __( 'The email associated with your account is already registered. Please contact your admin to change the email.', '2fa-learndash-lms' );
				$check_user['status']  = 'ERROR';
				$check_user['message'] = $mo2fa_login_message;

				return $check_user;
			}
		}
	}

	public function mo2f_otp_over_email_send( $email, $redirect_to, $session_id_encrypt, $current_user ) {
		$challengeMobile = new Molms_Customer_Setup();
		$content         = '';
		$response        = [];
		$otpLIMiTE       = 0;
		if ( get_site_option( 'molms_email_transactions' ) > 0 ) {
			$content  = $challengeMobile->send_otp_token( $email, 'EMAIL', get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ), $current_user );
			$response = json_decode( $content, true );
			if ( ! MOLMS_IS_ONPREM ) {
				if ( isset( $response['txId'] ) ) {
					molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_transactionId', $response['txId'] );
				}
			}
		} else {
			$response['status']  = 'FAILED';
			$response['message'] = '<p style = "color:red;
            ">OTP limit has been exceeded</p>';
			$otpLIMiTE           = 1;
		}
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $response['status'] == 'SUCCESS' ) {
				$molms_email_transactions = get_site_option( 'molms_email_transactions' );
				update_site_option( 'molms_email_transactions', $molms_email_transactions - 1 );
				$mo2fa_login_message = 'An OTP has been sent to ' . $email . ' please verify to set the two-factor';
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt, 1 );
			} else {
				$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$mo2fa_login_message = 'There was an issue while sending the OTP to ' . $email . '. Please check your remaining transactions and try again.';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function mo2f_skip_2fa_setup() {
		if ( isset( $_POST['miniorange_skip_2fa_nonce'] ) ) {
			$nonce = isset( $_POST['miniorange_skip_2fa_nonce'] ) ? sanitize_text_field( $_POST['miniorange_skip_2fa_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-skip-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				global $molms_db_queries;
				$redirect_to        = esc_url_raw( $_POST['redirect_to'] );
				$session_id_encrypt = sanitize_text_field( $session_id_encrypt );
				$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );

				$molms_db_queries->update_user_details( $user_id, array( 'mo2f_2factor_enable_2fa_byusers' => 0 ) );

				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			}
		}
	}

	public function back_to_select_2fa() {
		if ( isset( $_POST['miniorange_inline_two_factor_setup'] ) ) { /* return back to choose second factor screen */
			$nonce = isset( $_POST['miniorange_inline_two_factor_setup'] ) ? sanitize_text_field( $_POST['miniorange_inline_two_factor_setup'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-setup-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				unset( $_SESSION['mo2f_google_auth'] );
				unset( $_SESSION['mo2f_authy_keys'] );
				unset( $_SESSION['secret_ga'] );
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$current_user       = get_user_by( 'id', $user_id );
				$molms_db_queries->update_user_details( $current_user->ID, array( 'mo2f_configured_2FA_method' => '' ) );
				$mo2fa_login_message = '';
				$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function inline_validate_and_set_ga() {
		if ( isset( $_POST['mo2f_inline_validate_ga_nonce'] ) ) {
			$nonce = isset( $_POST['mo2f_inline_validate_ga_nonce'] ) ? sanitize_text_field( $_POST['mo2f_inline_validate_ga_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-google-auth-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$otpToken            = sanitize_text_field( $_POST['google_auth_code'] );
				$session_id_encrypt  = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$user_id             = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$current_user        = get_user_by( 'id', $user_id );
				$redirect_to         = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$mo2f_google_auth    = json_decode( get_user_meta( $user_id, 'mo2f_google_auth', true ), true );
				$mo2f_google_auth    = isset( $mo2f_google_auth ) ? $mo2f_google_auth : null;
				$ga_secret           = $mo2f_google_auth != null ? $mo2f_google_auth['ga_secret'] : null;
				$mo2fa_login_message = '';
				$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				if ( molms_2f_Utility::mo2f_check_number_length( $otpToken ) ) {
					$email           = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
					$google_auth     = new Molms_Miniorange_Rba_Attributes();
					$google_response = json_decode( $google_auth->mo2f_validate_google_auth( $email, $otpToken, $ga_secret ), true );
					if ( json_last_error() == JSON_ERROR_NONE ) {
						if ( $google_response['status'] == 'SUCCESS' ) {
							$response = $google_response;
							if ( json_last_error() == JSON_ERROR_NONE || MOLMS_IS_ONPREM ) {
								if ( $response['status'] == 'SUCCESS' ) {
									$molms_db_queries->update_user_details(
										$current_user->ID,
										array(
											'mo2f_GoogleAuthenticator_config_status' => true,
											'mo2f_configured_2FA_method'             => 'Google Authenticator',
											'mo2f_AuthyAuthenticator_config_status'  => false,
											'mo_2factor_user_registration_status'    => 'MO_2_FACTOR_PLUGIN_SETTINGS'
										)
									);

									if ( MOLMS_IS_ONPREM ) {
										update_user_meta( $current_user->ID, 'mo2f_2FA_method_to_configure', 'GOOGLE AUTHENTICATOR' );
										$gauth_obj = new molms_Google_auth_onpremise();
										$gauth_obj->mo_GAuth_set_secret( $current_user->ID, $ga_secret );
									}
									update_user_meta( $current_user->ID, 'mo2f_external_app_type', 'GOOGLE AUTHENTICATOR' );
									$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
									unset( $_SESSION['mo2f_google_auth'] );
								} else {
									$mo2fa_login_message = __( 'An error occured while processing your request. Please try again.', '2fa-learndash-lms' );
								}
							} else {
								$mo2fa_login_message = __( 'An error occured while processing your request. Please try again.', '2fa-learndash-lms' );
							}
						} else {
							$mo2fa_login_message = __( 'An error occured while processing your request. Please try again.', '2fa-learndash-lms' );
						}
					} else {
						$mo2fa_login_message = __( 'An error occured while validating the user. Please try again.', '2fa-learndash-lms' );
					}
				} else {
					$mo2fa_login_message = __( 'Only digits are allowed. Please enter again.', '2fa-learndash-lms' );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function inline_mobile_configure() {
		if ( isset( $_POST['miniorange_inline_show_qrcode_nonce'] ) ) {
			$nonce = isset( $_POST['miniorange_inline_show_qrcode_nonce'] ) ? sanitize_text_field( $_POST['miniorange_inline_show_qrcode_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-show-qrcode-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;

				$user_id = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );

				$redirect_to              = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$current_user             = get_user_by( 'id', $user_id );
				$mo2fa_login_message      = '';
				$mo2fa_login_status       = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$user_registration_status = $molms_db_queries->get_user_detail( 'mo_2factor_user_registration_status', $current_user->ID );
				if ( $user_registration_status == 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR' ) {
					$email               = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
					$miniorageqr         = $this->mo2f_inline_get_qr_code_for_mobile( $email, $current_user->ID );
					$mo2fa_login_message = $miniorageqr['message'];
					molms_2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_transactionId', $miniorageqr['mo2f-login-transactionId'] );
					$this->mo2f_transactionid = $miniorageqr['mo2f-login-transactionId'];
				} else {
					$mo2fa_login_message = __( 'Invalid request. Please register with miniOrange before configuring your mobile.', '2fa-learndash-lms' );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, $miniorageqr, $session_id_encrypt );
			}
		}
	}

	public function mo2f_inline_get_qr_code_for_mobile( $email, $id ) {
		$register_mobile = new Molms_Two_Factor_Setup();
		$content         = $register_mobile->register_mobile( $email );
		$response        = json_decode( $content, true );
		$message         = '';
		$miniorageqr     = array();
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $response['status'] == 'ERROR' ) {
				$miniorageqr['message'] = molms_2fConstants::langTranslate( $response['message'] );
				delete_user_meta( $id, 'miniorageqr' );
			} else {
				if ( $response['status'] == 'IN_PROGRESS' ) {
					$miniorageqr['message']                  = '';
					$miniorageqr['mo2f-login-qrCode']        = $response['qrCode'];
					$miniorageqr['mo2f-login-transactionId'] = $response['txId'];
					$miniorageqr['mo2f_show_qr_code']        = 'MO_2_FACTOR_SHOW_QR_CODE';
					update_user_meta( $id, 'miniorageqr', $miniorageqr );
				} else {
					$miniorageqr['message'] = __( 'An error occured while processing your request. Please try again.', '2fa-learndash-lms' );
					delete_user_meta( $id, 'miniorageqr' );
				}
			}
		}

		return $miniorageqr;
	}

	public function mo2f_inline_validate_mobile_authentication() {
		if ( isset( $_POST['mo_auth_inline_mobile_registration_complete_nonce'] ) ) {
			$nonce = isset( $_POST['mo_auth_inline_mobile_registration_complete_nonce'] ) ? sanitize_text_field( $_POST['mo_auth_inline_mobile_registration_complete_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-mobile-registration-complete-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				molms_2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
				$user_id                 = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$redirect_to             = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$selected_2factor_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user_id );
				$email                   = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user_id );
				$mo2fa_login_message     = '';
				$mo2fa_login_status      = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$enduser                 = new Molms_Two_Factor_Setup();
				if ( $selected_2factor_method == 'SOFT TOKEN' ) {
					$selected_2factor_method_onprem = 'miniOrange Soft Token';
				} elseif ( $selected_2factor_method == 'PUSH NOTIFICATIONS' ) {
					$selected_2factor_method_onprem = 'miniOrange Push Notification';
				} elseif ( $selected_2factor_method == 'MOBILE AUTHENTICATION' ) {
					$selected_2factor_method_onprem = 'miniOrange QR Code Authentication';
				}
				$response = json_decode( $enduser->mo2f_update_userinfo( $email, $selected_2factor_method, null, null, null ), true );
				if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
					if ( $response['status'] == 'ERROR' ) {
						$mo2fa_login_message = molms_2fConstants::langTranslate( $response['message'] );
					} elseif ( $response['status'] == 'SUCCESS' ) {
						$molms_db_queries->update_user_details(
							$user_id,
							array(
								'mobile_registration_status'                        => true,
								'mo2f_miniOrangeQRCodeAuthentication_config_status' => true,
								'mo2f_miniOrangeSoftToken_config_status'            => true,
								'mo2f_miniOrangePushNotification_config_status'     => true,
								'mo2f_configured_2FA_method'                        => $selected_2factor_method_onprem,
								'mo_2factor_user_registration_status'               => 'MO_2_FACTOR_PLUGIN_SETTINGS',
							)
						);
						$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
					} else {
						$mo2fa_login_message = __( 'An error occured while validating the user. Please try again.', '2fa-learndash-lms' );
					}
				} else {
					$mo2fa_login_message = __( 'Invalid request. Please try again', '2fa-learndash-lms' );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function mo2f_inline_validate_kba() {
		if ( isset( $_POST['mo2f_inline_save_kba_nonce'] ) ) {
			$nonce = isset( $_POST['mo2f_inline_save_kba_nonce'] ) ? sanitize_text_field( $_POST['mo2f_inline_save_kba_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-kba-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$mo2fa_login_message = '';
				$mo2fa_login_status  = isset( $_POST['mo2f_inline_kba_status'] ) ? 'MO_2_FACTOR_SETUP_SUCCESS' : 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$temp_array          = array(
					sanitize_text_field( $_POST['mo2f_kbaquestion_1'] ),
					sanitize_text_field( $_POST['mo2f_kbaquestion_2'] ),
					sanitize_text_field( $_POST['mo2f_kbaquestion_3'] )
				);
				$kba_questions       = array();
				foreach ( $temp_array as $question ) {
					if ( molms_2f_Utility::mo2f_check_empty_or_null( $question ) ) {
						$mo2fa_login_message = __( 'All the fields are required. Please enter valid entries.', '2fa-learndash-lms' );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message );
					} else {
						$ques = sanitize_text_field( $question );
						$ques = addcslashes( stripslashes( $ques ), '\\' );
						array_push( $kba_questions, $ques );
					}
				}
				if ( ! ( array_unique( $kba_questions ) == $kba_questions ) ) {
					$mo2fa_login_message = __( 'The questions you select must be unique.', '2fa-learndash-lms' );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message );
				}
				$temp_array_ans = array(
					sanitize_text_field( $_POST['mo2f_kba_ans1'] ),
					sanitize_text_field( $_POST['mo2f_kba_ans2'] ),
					sanitize_text_field( $_POST['mo2f_kba_ans3'] )
				);
				$kba_answers    = array();
				foreach ( $temp_array_ans as $answer ) {
					if ( molms_2f_Utility::mo2f_check_empty_or_null( $answer ) ) {
						$mo2fa_login_message = __( 'All the fields are required. Please enter valid entries.', '2fa-learndash-lms' );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message );
					} else {
						$ques   = sanitize_text_field( $answer );
						$answer = strtolower( $answer );
						array_push( $kba_answers, $answer );
					}
				}
				$size         = sizeof( $kba_questions );
				$kba_q_a_list = array();
				for ( $c = 0; $c < $size; $c ++ ) {
					array_push( $kba_q_a_list, $kba_questions[ $c ] );
					array_push( $kba_q_a_list, $kba_answers[ $c ] );
				}

				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$current_user       = get_user_by( 'id', $user_id );
				$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;

				$email              = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
				$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
				$molms_db_queries->update_user_details(
					$current_user->ID,
					array(
						'mo2f_SecurityQuestions_config_status' => true,
						'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS'
					)
				);
				if ( ! MOLMS_IS_ONPREM ) {
					$kba_q1 = sanitize_text_field( $_POST['mo2f_kbaquestion_1'] );
					$kba_a1 = sanitize_text_field( $_POST['mo2f_kba_ans1'] );
					$kba_q2 = sanitize_text_field( $_POST['mo2f_kbaquestion_2'] );
					$kba_a2 = sanitize_text_field( $_POST['mo2f_kba_ans2'] );
					$kba_q3 = sanitize_text_field( $_POST['mo2f_kbaquestion_3'] );
					$kba_a3 = sanitize_text_field( $_POST['mo2f_kba_ans3'] );

					$kba_q1 = addcslashes( stripslashes( $kba_q1 ), '\\' );
					$kba_q2 = addcslashes( stripslashes( $kba_q2 ), '\\' );
					$kba_q3 = addcslashes( stripslashes( $kba_q3 ), '\\' );

					$kba_a1 = addcslashes( stripslashes( $kba_a1 ), '\\' );
					$kba_a2 = addcslashes( stripslashes( $kba_a2 ), '\\' );
					$kba_a3 = addcslashes( stripslashes( $kba_a3 ), '\\' );

					$kba_registration = new Molms_Two_Factor_Setup();
					$kba_reg_reponse  = json_decode( $kba_registration->register_kba_details( $email, $kba_q1, $kba_a1, $kba_q2, $kba_a2, $kba_q3, $kba_a3, $user_id ), true );

					if ( json_last_error() == JSON_ERROR_NONE ) {
						if ( $kba_reg_reponse['status'] == 'SUCCESS' ) {
							$response = json_decode( $kba_registration->mo2f_update_userinfo( $email, 'KBA', null, null, null ), true );
						}
					}
				}
				$kba_q1          = $kba_q_a_list[0];
				$kba_a1          = md5( $kba_q_a_list[1] );
				$kba_q2          = $kba_q_a_list[2];
				$kba_a2          = md5( $kba_q_a_list[3] );
				$kba_q3          = $kba_q_a_list[4];
				$kba_a3          = md5( $kba_q_a_list[5] );
				$question_answer = array( $kba_q1 => $kba_a1, $kba_q2 => $kba_a2, $kba_q3 => $kba_a3 );
				update_user_meta( $current_user->ID, 'mo2f_kba_challenge', $question_answer );
				if ( ! isset( $_POST['mo2f_inline_kba_status'] ) ) {
					update_user_meta( $current_user->ID, 'mo2f_2FA_method_to_configure', 'Security Questions' );
					$molms_db_queries->update_user_details( $current_user->ID, array( 'mo2f_configured_2FA_method' => 'Security Questions' ) );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function mo2f_inline_send_otp() {
		if ( isset( $_POST['miniorange_inline_verify_phone_nonce'] ) ) {
			$nonce = isset( $_POST['miniorange_inline_verify_phone_nonce'] ) ? sanitize_text_field( $_POST['miniorange_inline_verify_phone_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-verify-phone-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt      = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$current_user            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$phone                   = isset( $_POST['verify_phone'] ) ? sanitize_text_field( $_POST['verify_phone'] ) : get_user_meta( $current_user, 'mo2f_user_phone', true );
				$redirect_to             = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$customer                = new Molms_Customer_Setup();
				$selected_2factor_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $current_user );
				$parameters              = array();
				$email                   = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user );

				$mo2fa_login_message = '';
				$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				if ( $selected_2factor_method == 'SMS' || $selected_2factor_method == 'PHONE VERIFICATION' || $selected_2factor_method == 'SMS AND EMAIL' ) {
					$phone = isset( $_POST['verify_phone'] ) ? sanitize_text_field( $_POST['verify_phone'] ) : get_user_meta( $current_user, 'mo2f_user_phone', true );
					if ( molms_2f_Utility::mo2f_check_empty_or_null( $phone ) ) {
						$mo2fa_login_message = __( 'Please enter your phone number.', '2fa-learndash-lms' );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
					$phone = str_replace( ' ', '', $phone );
					update_user_meta( $current_user, 'mo2f_user_phone', $phone );
				}
				if ( $selected_2factor_method == 'OTP_OVER_SMS' || $selected_2factor_method == 'SMS' ) {
					$currentMethod = 'SMS';
				} elseif ( $selected_2factor_method == 'SMS AND EMAIL' ) {
					$currentMethod = 'OTP_OVER_SMS_AND_EMAIL';
					$parameters    = array( 'phone' => $phone, 'email' => $email );
				} elseif ( $selected_2factor_method == 'PHONE VERIFICATION' ) {
					$currentMethod = 'PHONE_VERIFICATION';
				} elseif ( $selected_2factor_method == 'OTP OVER EMAIL' ) {
					$currentMethod = 'OTP_OVER_EMAIL';
					$parameters    = $email;
				}
				if ( $selected_2factor_method == 'SMS AND EMAIL' ) {
					$content = json_decode( $customer->send_otp_token( $parameters, $currentMethod, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				} elseif ( $selected_2factor_method == 'OTP OVER EMAIL' ) {
					$content = json_decode( $customer->send_otp_token( $email, $currentMethod, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				} else {
					$content = json_decode( $customer->send_otp_token( $phone, $currentMethod, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				}
				if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate otp token */
					if ( $content['status'] == 'ERROR' ) {
						$mo2fa_login_message = molms_2fConstants::langTranslate( $content['message'] );
					} elseif ( $content['status'] == 'SUCCESS' ) {
						update_user_meta( $current_user, 'mo2f_transactionId', $content['txId'] );
						if ( $selected_2factor_method == 'SMS' ) {
							update_site_option( 'molms_sms_transactions', get_site_option( 'molms_sms_transactions' ) - 1 );
							$mo2fa_login_message = __( 'The One Time Passcode has been sent to', '2fa-learndash-lms' ) . $phone . '.' . __( 'Please enter the one time passcode below to verify your number.', '2fa-learndash-lms' );
						} elseif ( $selected_2factor_method == 'SMS AND EMAIL' ) {
							$mo2fa_login_message = 'The One Time Passcode has been sent to ' . $parameters['phone'] . ' and ' . $parameters['email'] . '. Please enter the one time passcode sent to your email and phone to verify.';
						} elseif ( $selected_2factor_method == 'OTP OVER EMAIL' ) {
							$mo2fa_login_message = __( 'The One Time Passcode has been sent to ', '2fa-learndash-lms' ) . $parameters . '.' . __( 'Please enter the one time passcode sent to your email to verify.', '2fa-learndash-lms' );
						} elseif ( $selected_2factor_method == 'PHONE VERIFICATION' ) {
							$mo2fa_login_message = __( 'You will receive a phone call on this number ', '2fa-learndash-lms' ) . $phone . '.' . __( 'Please enter the one time passcode below to verify your number.', '2fa-learndash-lms' );
						}
					} elseif ( $content['status'] == 'FAILED' ) {
						$mo2fa_login_message = __( $content['message'], '2fa-learndash-lms' );
					} else {
						$mo2fa_login_message = __( 'An error occured while validating the user. Please Try again.', '2fa-learndash-lms' );
					}
				} else {
					$mo2fa_login_message = __( 'Invalid request. Please try again', '2fa-learndash-lms' );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function mo2f_inline_validate_otp() {
		if ( isset( $_POST['miniorange_inline_validate_otp_nonce'] ) ) {
			$nonce = isset( $_POST['miniorange_inline_validate_otp_nonce'] ) ? sanitize_text_field( $_POST['miniorange_inline_validate_otp_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-otp-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

				return $error;
			} else {
				global $molms_db_queries;
				$this->miniorange_pass2login_start_session();
				$otp_token           = '';
				$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$mo2fa_login_message = '';
				if ( molms_2f_Utility::mo2f_check_empty_or_null( sanitize_text_field( $_POST['otp_token'] ) ) ) {
					$mo2fa_login_message = __( 'All the fields are required. Please enter valid entries.', '2fa-learndash-lms' );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message );
				} else {
					$otp_token = sanitize_text_field( $_POST['otp_token'] );
				}

				$session_id_encrypt      = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$current_user            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
				$redirect_to             = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
				$selected_2factor_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $current_user );
				$user_phone              = $molms_db_queries->get_user_detail( 'mo2f_user_phone', $current_user );
				$customer                = new Molms_Customer_Setup();
				$content                 = json_decode( $customer->validate_otp_token( $selected_2factor_method, null, get_user_meta( $current_user, 'mo2f_transactionId', true ), $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
				if ( $content['status'] == 'ERROR' ) {
					$mo2fa_login_message = molms_2fConstants::langTranslate( $content['message'] );
				} elseif ( isset( $content['status'] ) && strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) { //OTP validated
					$phone = get_user_meta( $current_user, 'mo2f_user_phone', true );
					if ( $user_phone && strlen( $user_phone ) >= 4 ) {
						if ( $phone != $user_phone ) {
							$molms_db_queries->update_user_details(
								$current_user,
								array(
									'mobile_registration_status' => false
								)
							);
						}
					}

					$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user );
					if ( ! ( $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $current_user ) == 'OTP OVER EMAIL' ) ) {
						$molms_db_queries->update_user_details(
							$current_user,
							array(
								'mo2f_OTPOverSMS_config_status' => true,
								'mo2f_user_phone'               => $phone
							)
						);
					} else {
						$molms_db_queries->update_user_details( $current_user, array( 'mo2f_email_otp_registration_status' => true ) );
					}
					$molms_db_queries->update_user_details(
						$current_user,
						array(
							'mo2f_configured_2FA_method'          => 'OTP Over SMS',
							'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
						)
					);
					$TwoF_setup         = new Molms_Two_Factor_Setup();
					$response           = json_decode( $TwoF_setup->mo2f_update_userinfo( $email, 'SMS', null, null, null ), true );
					$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
				} else {  // OTP Validation failed.
					$mo2fa_login_message = __( 'Invalid OTP. Please try again.', '2fa-learndash-lms' );
				}
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
	}

	public function mo2f_inline_login() {
		global $molms_utility;
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange_inline_login' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

			return $error;
		}
		$email              = sanitize_email( $_POST['email'] );
		$password           = sanitize_text_field( $_POST['password'] );
		$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
		$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
		$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
		if ( $molms_utility->check_empty_or_null( $email ) || $molms_utility->check_empty_or_null( $password ) ) {
			$login_message = molms_Messages::showMessage( 'REQUIRED_FIELDS' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );

			return;
		}
		$this->inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt );
	}

	public function inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt ) {
		global $molms_db_queries;
		$customer = new molms_cURL();

		$content      = $customer->get_customer_key( $email, $password );
		$customer_key = json_decode( $content, true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( isset( $customer_key['phone'] ) ) {
				update_site_option( 'mo_lms_admin_phone', $customer_key['phone'] );
				$molms_db_queries->update_user_details( $user_id, array( 'mo2f_user_phone' => $customer_key['phone'] ) );
			}
			update_site_option( 'mo2f_email', $email );
			$this->inline_save_success_customer_config( $user_id, $email, $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], $customer_key['appSecret'] );
			$login_message = molms_Messages::showMessage( 'REG_SUCCESS' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
		} else {
			$molms_db_queries->update_user_details( $user_id, array( 'mo_2factor_user_registration_status' => 'MO_2_FACTOR_VERIFY_CUSTOMER' ) );
			$login_message = molms_Messages::showMessage( 'ACCOUNT_EXISTS' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
		}
	}

	public function inline_save_success_customer_config( $user_id, $email, $id, $api_key, $token, $app_secret ) {
		global $molms_db_queries;
		update_site_option( 'mo2f_customerKey', $id );
		update_site_option( 'mo2f_api_key', $api_key );
		update_site_option( 'mo2f_customer_token', $token );
		update_site_option( 'mo2f_app_secret', $app_secret );
		update_site_option( 'mo_lms_enable_log_requests', true );
		update_site_option( 'mo2f_miniorange_admin', $id );
		update_site_option( 'mo_2factor_admin_registration_status', 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' );
		update_site_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_PLUGIN_SETTINGS' );
		$current_user = get_user_by( 'id', $user_id );
		$molms_db_queries->update_user_details(
			$user_id,
			array(
				'mo2f_user_email' => $current_user->user_email,
			)
		);
	}

	public function mo2f_inline_register() {
		global $molms_utility, $molms_db_queries;
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange_inline_register' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . __( 'ERROR', '2fa-learndash-lms' ) . '</strong>: ' . __( 'Invalid Request.', '2fa-learndash-lms' ) );

			return $error;
		}

		$email              = sanitize_email( $_POST['email'] );
		$company            = sanitize_text_field( $_SERVER['SERVER_NAME'] );
		$password           = sanitize_text_field( $_POST['password'] );
		$confirm_password   = sanitize_text_field( $_POST['confirmPassword'] );
		$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
		$user_id            = molms_2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_current_user_id', $session_id_encrypt );
		$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : null;
		if ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
			$login_message = molms_Messages::showMessage( 'PASS_LENGTH' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
		}
		if ( $password != $confirm_password ) {
			$login_message = molms_Messages::showMessage( 'PASS_MISMATCH' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
		}
		if ( molms_Utility::check_empty_or_null( $email ) || molms_Utility::check_empty_or_null( $password )
		     || molms_Utility::check_empty_or_null( $confirm_password )
		) {
			$login_message = molms_Messages::showMessage( 'REQUIRED_FIELDS' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
		}

		update_site_option( 'mo2f_email', $email );

		update_site_option( 'mo_lms_company', $company );

		update_site_option( 'mo_lms_password', $password );

		$customer = new molms_cURL();
		$content  = json_decode( $customer->check_customer( $email ), true );
		$molms_db_queries->insert_user( $user_id );
		switch ( $content['status'] ) {
			case 'CUSTOMER_NOT_FOUND':
				$customer_key = json_decode( $customer->create_customer( $email, $company, $password, $phone = '', $first_name = '', $last_name = '' ), true );

				if ( strcasecmp( $customer_key['status'], 'SUCCESS' ) == 0 ) {
					$this->inline_save_success_customer_config( $user_id, $email, $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], $customer_key['appSecret'] );
					$this->inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt );
				}

				break;
			default:
				$this->inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt );
				break;
		}
	}

	public function deniedMessage( $message ) {
		if ( empty( $message ) && get_site_option( 'deniedMessage' ) ) {
			delete_site_option( 'deniedMessage' );
		} else {
			return $message;
		}
	}

	public function miniorange_pass2login_check_mobile_status( $login_status ) {
		//mobile authentication
		if ( $login_status == 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_check_otp_status( $login_status, $sso = false ) {
		if ( $login_status == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' || $login_status == 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' || $login_status == 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_check_forgotphone_status( $login_status ) {
		// after clicking on forgotphone link when both kba and email are configured
		if ( $login_status == 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_check_push_oobemail_status( $login_status ) {
		// for push and out of and email
		if ( $login_status == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' || $login_status == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_reconfig_google( $login_status ) {
		if ( $login_status == 'MO_2_FACTOR_RECONFIG_GOOGLE' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_reconfig_kba( $login_status ) {
		if ( $login_status == 'MO_2_FACTOR_RECONFIG_KBA' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_check_kba_status( $login_status ) {
		if ( $login_status == 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION' ) {
			return true;
		}

		return false;
	}

	public function miniorange_pass2login_check_trusted_device_status( $login_status ) {
		if ( $login_status == 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE' ) {
			return true;
		}

		return false;
	}

	public function mo_2_factor_pass2login_woocommerce() {
	?>
    <input type=' hidden' name='mo_woocommerce_login_prompt' value='1'>
	<?php
	}

	public function mo2f_collect_device_attributes_for_authenticated_user( $currentuser, $redirect_to = null ) {
		global $molms_db_queries;
		if ( get_site_option( 'mo2f_remember_device' ) ) {
			$this->miniorange_pass2login_start_session();

			$session_id = $this->create_session();
			molms_2f_Utility::set_user_values( $session_id, 'mo2f_current_user_id', $currentuser->ID );
			$this->mo2f_user_id = $currentuser->ID;

			mo2f_collect_device_attributes_handler( $redirect_to, $session_id );
			exit;
		} else {
			$this->miniorange_initiate_2nd_factor( $currentuser, null, $redirect_to );
		}
	}

	public function mo2f_check_username_password( $user, $username, $password, $redirect_to = null ) {
		if ( is_a( $user, 'WP_Error' ) && ! empty( $user ) ) {
			if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
				$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp;Invalid User Credentials', );
				wp_send_json_success( $data );
			} else {
				return $user;
			}
		}
		if ( $GLOBALS['pagenow'] == 'wp-login.php' && isset( $_POST['mo_woocommerce_login_prompt'] ) ) {
			return new WP_Error( 'Unauthorized Access.', '<strong>ERROR</strong>: Access Denied.' );
		}
		// if an app password is enabled, this is an XMLRPC / APP login ?
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			$currentuser = wp_authenticate_username_password( $user, $username, $password );
			if ( is_wp_error( $currentuser ) ) {
				$this->error = new IXR_Error( 403, __( 'Bad login/pass combination.' ) );

				return false;
			} else {
				return $currentuser;
			}
		} else {
			$currentuser = wp_authenticate_username_password( $user, $username, $password );
			if ( is_wp_error( $currentuser ) ) {
				if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
					$data = array( 'notice' => '<div style= "border-left:3px solid #dc3232;">&nbsp; Invalid User Credentials', );
					wp_send_json_success( $data );
				} else {
					$currentuser->add( 'invalid_username_password', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Username or password.' ) );

					return $currentuser;
				}
			} else {
				if ( get_site_option( 'mo2f_simultaneous_session_enable' ) ) {
					$session_action = apply_filters( 'mo2f_session_addon', 'block', $currentuser );
					if ( $session_action == 'block' ) {
						mo2f_device_exceeded_error();
						exit;
					}
				}
				global $molms_db_queries;

				$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;

				$redirect_to                = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : null;
				$mo2f_configured_2FA_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $currentuser->ID );
				$cloud_methods              = array( 'MOBILE AUTHENTICATION', 'PUSH NOTIFICATIONS', 'SOFT TOKEN' );
				if ( MOLMS_IS_ONPREM && $mo2f_configured_2FA_method == 'Security Questions' ) {
					$this->miniorange_initiate_2nd_factor( $currentuser, null, $redirect_to, '', $session_id );
				} elseif ( MOLMS_IS_ONPREM && $mo2f_configured_2FA_method == 'Email Verification' ) {
					$this->miniorange_initiate_2nd_factor( $currentuser, null, $redirect_to, null, $session_id );
				} else {
					if ( empty( sanitize_text_field( $_POST['mo_softtoken'] ) ) && molms_Utility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'get_site_option' ) && $mo2f_configured_2FA_method && ! get_site_option( 'mo2f_remember_device' ) && ( ( $mo2f_configured_2FA_method == 'Google Authenticator' ) || ( $mo2f_configured_2FA_method == 'miniOrange Soft Token' ) || ( $mo2f_configured_2FA_method == 'Authy Authenticator' ) ) ) {
						if ( isset( $_POST['mo_woocommerce_login_prompt'] ) ) {
							$this->miniorange_initiate_2nd_factor( $currentuser, '', '', '' );
						}
						if ( molms_2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
							$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Please enter the One Time Passcode', );
							wp_send_json_success( $data );
						} else {
							return new WP_Error( 'one_time_passcode_empty', '<strong>ERROR</strong>: Please enter the One Time Passcode.' );
						}
						// Prevent PHP notices when using app password login
					} else {
						$otp_token = isset( $_POST['mo_softtoken'] ) ? trim( sanitize_text_field( $_POST['mo_softtoken'] ) ) : '';
					}
					$attributes = isset( $_POST['miniorange_rba_attribures'] ) ? sanitize_text_field( $_POST['miniorange_rba_attribures'] ) : null;
					$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;

					$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : null;

					if ( is_null( $session_id ) ) {
						$session_id = $this->create_session();
					}


					$error = $this->miniorange_initiate_2nd_factor( $currentuser, $attributes, $redirect_to, $otp_token, $session_id );


					if ( is_wp_error( $error ) ) {
						return $error;
					}

					return $error;
				}
			}
		}
	}

	public function mo_2_factor_enable_jquery_default_login() {
		wp_enqueue_script( 'jquery' );
	}

	public function miniorange_pass2login_footer_form() {
		?>
        <script>
            jQuery(document).ready(function () {
                if (document.getElementById(' loginform') != null) {
                    jQuery('#loginform').on('submit', function (e) {
                        jQuery('#miniorange_rba_attribures').val(JSON.stringify(rbaAttributes.attributes));
                    });
                } else {
                    if (document.getElementsByClassName('login') != null) {
                        jQuery('.login').on('submit', function (e) {
                            jQuery('#miniorange_rba_attribures').val(JSON.stringify(rbaAttributes.attributes));
                        });
                    }
                }
            });
        </script>
		<?php
	}
	}

	?>
