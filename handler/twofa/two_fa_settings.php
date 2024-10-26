<?php
require 'class-molms-password-2factor-login.php';
global $molms_dirName;
require $molms_dirName . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_setup_notification.php';

class molms_Miniorange_Authentication {
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	public function __construct() {
		add_action( 'admin_init', array( $this, 'miniorange_auth_save_settings' ) );

		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		if ( molms_Utility::get_mo2f_db_option( 'molms_activate_plugin', 'get_site_option' ) == 1 ) {
			$mo2f_rba_attributes = new Molms_Miniorange_Rba_Attributes();
			$pass2fa_login       = new Molms_Password_2Factor_Login();
			$mo2f_2factor_setup  = new Molms_Two_Factor_Setup();
			add_action( 'init', array( $pass2fa_login, 'miniorange_pass2login_redirect' ) );
			//for shortcode addon
			$mo2f_ns_config = new molms_Utility();

			add_filter( 'mo2f_shortcode_rba_gauth', array( $mo2f_rba_attributes, 'mo2f_validate_google_auth' ), 10, 3 );
			add_filter( 'mo2f_shortcode_kba', array( $mo2f_2factor_setup, 'register_kba_details' ), 10, 7 );
			add_filter( 'mo2f_update_info', array( $mo2f_2factor_setup, 'mo2f_update_userinfo' ), 10, 5 );
			add_action(
				'mo2f_shortcode_form_fields',
				array(
					$pass2fa_login,
					'miniorange_pass2login_form_fields'
				),
				10,
				5
			);
			add_filter( 'mo2f_gauth_service', array( $mo2f_rba_attributes, 'mo2f_google_auth_service' ), 10, 1 );
			if ( molms_Utility::get_mo2f_db_option( 'mo2f_login_option', 'get_site_option' ) ) { //password + 2nd factor enabled
				if ( get_site_option( 'mo_2factor_admin_registration_status' ) == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' or MOLMS_IS_ONPREM ) {
					remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );

					add_filter( 'authenticate', array( $pass2fa_login, 'mo2f_check_username_password' ), 99999, 4 );
					add_action( 'init', array( $pass2fa_login, 'miniorange_pass2login_redirect' ) );
					add_action(
						'login_form',
						array(
							$pass2fa_login,
							'mo_2_factor_pass2login_show_wp_login_form'
						),
						10
					);

					if ( get_site_option( 'mo2f_remember_device' ) ) {
						add_action( 'login_footer', array( $pass2fa_login, 'miniorange_pass2login_footer_form' ) );
						add_action(
							'woocommerce_before_customer_login_form',
							array(
								$pass2fa_login,
								'miniorange_pass2login_footer_form'
							)
						);
					}
					add_action(
						'login_enqueue_scripts',
						array(
							$pass2fa_login,
							'mo_2_factor_enable_jquery_default_login'
						)
					);

					if ( get_site_option( 'mo2f_woocommerce_login_prompt' ) ) {
						add_action(
							'woocommerce_login_form',
							array(
								$pass2fa_login,
								'mo_2_factor_pass2login_show_wp_login_form'
							)
						);
					} elseif ( ! get_site_option( 'mo2f_woocommerce_login_prompt' ) && molms_Utility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'site_option' ) ) {
						add_action(
							'woocommerce_login_form_end',
							array(
								$pass2fa_login,
								'mo_2_factor_pass2login_woocommerce'
							)
						);
					}
					add_action(
						'wp_enqueue_scripts',
						array(
							$pass2fa_login,
							'mo_2_factor_enable_jquery_default_login'
						)
					);

					//Actions for other plugins to use miniOrange 2FA plugin
					add_action(
						'miniorange_pre_authenticate_user_login',
						array(
							$pass2fa_login,
							'mo2f_check_username_password'
						),
						1,
						4
					);
					add_action(
						'miniorange_post_authenticate_user_login',
						array(
							$pass2fa_login,
							'miniorange_initiate_2nd_factor'
						),
						1,
						3
					);
					add_action(
						'miniorange_collect_attributes_for_authenticated_user',
						array(
							$pass2fa_login,
							'mo2f_collect_device_attributes_for_authenticated_user'
						),
						1,
						2
					);
				}
			} else { //login with phone enabled
				if ( get_site_option( 'mo_2factor_admin_registration_status' ) == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' or MOLMS_IS_ONPREM ) {
					$mobile_login = new molms_Miniorange_Mobile_Login();
					add_action( 'login_form', array( $mobile_login, 'miniorange_login_form_fields' ), 99999, 10 );
					add_action( 'login_footer', array( $mobile_login, 'miniorange_login_footer_form' ) );

					remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
					add_filter( 'authenticate', array( $mobile_login, 'mo2fa_default_login' ), 99999, 3 );
					add_action( 'login_enqueue_scripts', array( $mobile_login, 'custom_login_enqueue_scripts' ) );
				}
			}
		}
	}

	public function mo_auth_success_message() {
		$message = get_site_option( 'mo2f_message' ); ?>
        <script>
            jQuery(document).ready(function () {
                var message = "<?php echo esc_html( $message ); ?>";
                jQuery('#messages').append("<div  style='padding:5px;'><div class='error notice is-dismissible mo2f_error_container' style='position: fixed;left: 60.4%;top: 6%;width: 37%;z-index: 99999;background-color: bisque;font-weight: bold;'> <p class='mo2f_msgs'>" + message + "</p></div></div>");
            });
        </script>
		<?php
	}

	public function mo_auth_error_message() {
		$message = get_site_option( 'mo2f_message' ); ?>

        <script>
            jQuery(document).ready(function () {
                var message = "<?php echo esc_html( $message ); ?>";
                jQuery('#messages').append("<div  style='padding:5px;'><div class='updated notice is-dismissible mo2f_success_container' style='position: fixed;left: 60.4%;top: 6%;width: 37%;z-index: 9999;background-color: #bcffb4;font-weight: bold;'> <p class='mo2f_msgs'>" + message + "</p></div></div>");
            });
        </script>
		<?php
	}

	public function miniorange_auth_save_settings() {

		if ( get_site_option( 'molms_plugin_redirect' ) ) {
			delete_site_option( 'molms_plugin_redirect' );
			wp_safe_redirect( admin_url() . 'admin.php?page=molms_two_fa' );
			exit;
		}
		if ( array_key_exists( 'page', $_REQUEST ) && sanitize_text_field( $_REQUEST['page'] ) == 'molms_two_fa' ) {
			if ( ! session_id() || session_id() == '' || ! isset( $_SESSION ) ) {
				session_start();
			}
		}

		global $user;
		global $molms_db_queries;
		$defaultCustomerKey = $this->defaultCustomerKey;
		$defaultApiKey      = $this->defaultApiKey;

		$user    = wp_get_current_user();
		$user_id = $user->ID;

		if ( current_user_can( 'manage_options' ) ) {

			if ( isset( $_POST['molms_register_to_upgrade_nonce'] ) ) { //registration with miniOrange for upgrading
				$nonce = isset( $_POST['molms_register_to_upgrade_nonce'] ) ? sanitize_text_field( $_POST['molms_register_to_upgrade_nonce'] ) : '';
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-lms-user-reg-to-upgrade-nonce' ) ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
				} else {
					$requestOrigin = sanitize_text_field( $_POST['requestOrigin'] );
					update_site_option( 'mo2f_customer_selected_plan', $requestOrigin );
					header( 'Location: admin.php?page=molms_account' );
				}
			}
			if ( strlen( get_site_option( 'mo2f_encryption_key' ) ) > 17 ) {
				$get_encryption_key = molms_2f_Utility::random_str( 16 );
				update_site_option( 'mo2f_encryption_key', $get_encryption_key );
			}
			if ( isset( $_POST['option'] ) ) {
				switch ( sanitize_text_field( $_POST['option'] ) ) {
					case 'mo2f_skiplogin':
						$this->mo2f_skiplogin();
						break;
					case 'mo2f_userlogout':
						$this->mo2f_userlogout();
						break;
					case 'molms_verifycustomer':
						$this->molms_verifycustomer();
						break;
					case 'mo2f_registration_closed':
						$this->mo2f_registration_closed();
						break;
					case 'woocommerce_disable_login_prompt':
						$this->woocommerce_disable_login_prompt();
						break;
					case 'mo_auth_sync_sms_transactions':
						$this->mo_auth_sync_sms_transactions();
						break;
					case 'mo2f_configure_miniorange_authenticator_validate':
						$this->mo2f_configure_miniorange_authenticator_validate();
						break;
					case 'mo2f_mobile_authenticate_success':
						$this->mo2f_mobile_authenticate_success();
						break;
					case 'mo2f_mobile_authenticate_error':
						$this->mo2f_mobile_authenticate_error();
						break;
					case 'mo_auth_refresh_mobile_qrcode':
						$this->mo_auth_refresh_mobile_qrcode();
						break;
					case 'mo2f_validate_soft_token':
						$this->mo2f_validate_soft_token();
						break;
					case 'mo2f_validate_otp_over_sms':
						$this->mo2f_validate_otp_over_sms();
						break;
					case 'mo2f_out_of_band_success':
						$this->mo2f_out_of_band_success();
						break;
					case 'mo2f_out_of_band_error':
						$this->mo2f_out_of_band_error();
						break;
					case 'mo2f_validate_google_authy_test':
						$this->mo2f_validate_google_authy_test();
						break;
					case 'mo2f_validate_otp_over_email':
						$this->mo2f_validate_otp_over_email();
						break;
					case 'mo2f_google_appname':
						$this->mo2f_google_appname();
						break;
					case 'mo2f_configure_google_authenticator_validate':
						$this->mo2f_configure_google_authenticator_validate();
						break;
					case 'mo2f_save_kba':
						$this->mo2f_save_kba();
						break;
					case 'mo2f_validate_kba_details':
						$this->mo2f_validate_kba_details();
						break;
					case 'mo2f_configure_otp_over_sms_send_otp':
						$this->mo2f_configure_otp_over_sms_send_otp();
						break;
					case 'mo2f_configure_otp_over_sms_validate':
						$this->mo2f_configure_otp_over_sms_validate();
						break;
					case 'mo2f_save_free_plan_auth_methods':
						$this->mo2f_save_free_plan_auth_methods();
						break;
					case 'mo2f_save_free_plan_auth_methods':
						$this->mo2f_save_free_plan_auth_methods();
						break;
					case 'mo2f_enable_2FA_for_users_option':
						$this->mo2f_enable_2FA_for_users_option();
						break;
					case 'mo2f_enable_2FA_on_login_page_option':
						$this->mo2f_enable_2FA_on_login_page_option();
						break;
					case 'mo_2factor_test_authentication_method':
						$this->mo_2factor_test_authentication_method();
						break;
					case 'mo2f_go_back':
						$this->mo2f_go_back();
						break;
					case 'mo2fa_register_to_upgrade':
						$this->mo2fa_register_to_upgrade();
						break;
					default:
						// code...
						break;
				}
			}
		}
	}

	public function mo2f_skiplogin() {
		$nonce = isset( $_POST['mo2f_skiplogin_nonce'] ) ? sanitize_text_field( $_POST['mo2f_skiplogin_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-skiplogin-failed-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			update_site_option( 'mo2f_tour_started', 2 );
		}
	}

	public function mo2f_userlogout() {
		$nonce = isset( $_POST['mo2f_userlogout_nonce'] ) ? sanitize_text_field( $_POST['mo2f_userlogout_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-userlogout-failed-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			update_site_option( 'mo2f_tour_started', 2 );
			wp_logout();
			wp_safe_redirect( admin_url() );
		}
	}

	public function molms_verifycustomer() {
		$nonce = isset( $_POST['molms_verifycustomer_nonce'] ) ? sanitize_text_field( $_POST['molms_verifycustomer_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-goto-verifycustomer-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			$molms_db_queries->insert_user( $user_id, array( 'user_id' => $user_id ) );
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ENTER_YOUR_EMAIL_PASSWORD" ) );
			update_site_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_VERIFY_CUSTOMER' );
		}
	}

	public function mo2f_registration_closed() {
		$nonce = isset( $_POST['mo2f_registration_closed_nonce'] ) ? sanitize_text_field( $_POST['mo2f_registration_closed_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-registration-closed-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			delete_user_meta( $user->ID, 'register_account_popup' );
			$mo2f_message = 'Please set up the second-factor by clicking on Configure button.';
			update_site_option( 'mo2f_message', $mo2f_message );
			$this->mo_auth_show_success_message();
		}
	}

	public function mo_auth_show_success_message() {
		do_action( 'molms_show_message', get_site_option( 'mo2f_message' ), 'SUCCESS' );
	}

	public function woocommerce_disable_login_prompt() {
		$nonce = isset( $_POST['molms_woocommerce_login_prompt_nonce'] ) ? sanitize_text_field( $_POST['molms_woocommerce_login_prompt_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'molms-woocommerce-login-prompt-nonce' ) ) {
			return;
		}
		if ( isset( $_POST['woocommerce_login_prompt'] ) ) {
			update_site_option( 'mo2f_woocommerce_login_prompt', true );
		} else {
			update_site_option( 'mo2f_woocommerce_login_prompt', false );
		}
	}

	public function mo_auth_sync_sms_transactions() {
		$customer = new Molms_Customer_Setup();
		$content  = json_decode( $customer->get_customer_transactions( get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
		if ( ! array_key_exists( 'smsRemaining', $content ) ) {
			$smsRemaining = 0;
		} else {
			$smsRemaining = $content['smsRemaining'];
			if ( $smsRemaining == null ) {
				$smsRemaining = 0;
			}
		}
		update_site_option( 'mo2f_number_of_transactions', $smsRemaining );
	}

	public function mo2f_configure_miniorange_authenticator_validate() {
		$nonce = isset( $_POST['mo2f_configure_miniorange_authenticator_validate_nonce'] ) ? sanitize_text_field( $_POST['mo2f_configure_miniorange_authenticator_validate_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-configure-miniorange-authenticator-validate-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			delete_site_option( 'mo2f_transactionId' );
			$session_variables = array( 'mo2f_qrCode', 'mo2f_transactionId', 'mo2f_show_qr_code' );
			molms_2f_Utility::unset_session_variables( $session_variables );

			$email                     = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			$TwoFA_method_to_configure = get_user_meta( $user->ID, 'mo2f_2FA_method_to_configure', true );
			$enduser                   = new Molms_Two_Factor_Setup();
			$current_method            = molms_2f_Utility::molms_decode_2_factor( $TwoFA_method_to_configure, "server" );

			$response = json_decode( $enduser->mo2f_update_userinfo( $email, $current_method, null, null, null ), true );

			if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
				if ( $response['status'] == 'ERROR' ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );

					$this->mo_auth_show_error_message();
				} elseif ( $response['status'] == 'SUCCESS' ) {
					$selectedMethod = $TwoFA_method_to_configure;

					delete_user_meta( $user->ID, 'mo2f_2FA_method_to_configure' );


					$molms_db_queries->update_user_details(
						$user->ID,
						array(
							'mo2f_configured_2FA_method'                        => $selectedMethod,
							'mobile_registration_status'                        => true,
							'mo2f_miniOrangeQRCodeAuthentication_config_status' => true,
							'mo2f_miniOrangeSoftToken_config_status'            => true,
							'mo2f_miniOrangePushNotification_config_status'     => true,
							'user_registration_with_miniorange'                 => 'SUCCESS',
							'mo_2factor_user_registration_status'               => 'MO_2_FACTOR_PLUGIN_SETTINGS'
						)
					);

					delete_user_meta( $user->ID, 'configure_2FA' );
					molms_display_test_2fa_notification( $user );
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
					$this->mo_auth_show_error_message();
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo_auth_show_error_message() {
		do_action( 'molms_show_message', get_site_option( 'mo2f_message' ), 'ERROR' );
	}

	public function mo2f_mobile_authenticate_success() {
		$nonce = isset( $_POST['mo2f_mobile_authenticate_success_nonce'] ) ? sanitize_text_field( $_POST['mo2f_mobile_authenticate_success_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-mobile-authenticate-success-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			if ( current_user_can( 'manage_options' ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
			}

			$session_variables = array( 'mo2f_qrCode', 'mo2f_transactionId', 'mo2f_show_qr_code' );
			molms_2f_Utility::unset_session_variables( $session_variables );

			delete_user_meta( $user->ID, 'test_2FA' );
			$this->mo_auth_show_success_message();
		}
	}

	public function mo2f_mobile_authenticate_error() {
		$nonce = isset( $_POST['mo2f_mobile_authenticate_error_nonce'] ) ? sanitize_text_field( $_POST['mo2f_mobile_authenticate_error_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-mobile-authenticate-error-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "AUTHENTICATION_FAILED" ) );
			molms_2f_Utility::unset_session_variables( 'mo2f_show_qr_code' );
			$this->mo_auth_show_error_message();
		}
	}

	public function mo_auth_refresh_mobile_qrcode() {
		$nonce = isset( $_POST['mo_auth_refresh_mobile_qrcode_nonce'] ) ? sanitize_text_field( $_POST['mo_auth_refresh_mobile_qrcode_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo-auth-refresh-mobile-qrcode-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id                = $user->ID;
			$twofactor_transactions = new molms_DB;
			$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );

			if ( $exceeded ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "USER_LIMIT_EXCEEDED" ) );
				$this->mo_auth_show_error_message();

				return;
			}

			$mo_2factor_user_registration_status = get_site_option( 'mo_2factor_user_registration_status' );
			if ( in_array(
				$mo_2factor_user_registration_status,
				array(
					'MO_2_FACTOR_INITIALIZE_TWO_FACTOR',
					'MO_2_FACTOR_molms_initialize_mobile_registration',
					'MO_2_FACTOR_PLUGIN_SETTINGS'
				)
			)
			) {
				$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
				$this->mo2f_get_qr_code_for_mobile( $email, $user->ID );
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "REGISTER_WITH_MO" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_get_qr_code_for_mobile( $email, $id ) {
		$register_mobile = new Molms_Two_Factor_Setup();
		$content         = $register_mobile->register_mobile( $email );

		$response = json_decode( $content, true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $response['status'] == 'ERROR' ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
				$session_variables = array( 'mo2f_qrCode', 'mo2f_transactionId', 'mo2f_show_qr_code' );
				molms_2f_Utility::unset_session_variables( $session_variables );
				delete_site_option( 'mo2f_transactionId' );
				$this->mo_auth_show_error_message();
			} else {
				if ( $response['status'] == 'IN_PROGRESS' ) {
					update_site_option( 'mo2f_message', molms_2fConstants::langTranslate( "SCAN_QR_CODE" ) );
					$_SESSION['mo2f_qrCode']        = $response['qrCode'];
					$_SESSION['mo2f_transactionId'] = $response['txId'];
					update_site_option( 'mo2f_transactionId', $response['txId'] );
					$_SESSION['mo2f_show_qr_code'] = 'MO_2_FACTOR_SHOW_QR_CODE';
					$this->mo_auth_show_success_message();
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
					$session_variables = array( 'mo2f_qrCode', 'mo2f_transactionId', 'mo2f_show_qr_code' );
					molms_2f_Utility::unset_session_variables( $session_variables );
					delete_site_option( 'mo2f_transactionId' );
					$this->mo_auth_show_error_message();
				}
			}
		}
	}

	public function mo2f_validate_soft_token() {
		$nonce = isset( $_POST['mo2f_validate_soft_token_nonce'] ) ? sanitize_text_field( $_POST['mo2f_validate_soft_token_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-validate-soft-token-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id   = $user->ID;
			$otp_token = '';
			if ( molms_2f_Utility::mo2f_check_empty_or_null( sanitize_text_field( $_POST['otp_token'] ) ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ENTER_VALUE" ) );
				$this->mo_auth_show_error_message();

				return;
			} else {
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}
			$email    = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			$customer = new Molms_Customer_Setup();
			$content  = json_decode( $customer->validate_otp_token( 'SOFT TOKEN', $email, null, $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
			if ( $content['status'] == 'ERROR' ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $content['message'] ) );
				$this->mo_auth_show_error_message();
			} else {
				if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) { //OTP validated and generate QRCode
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );

					delete_user_meta( $user->ID, 'test_2FA' );
					$this->mo_auth_show_success_message();
				} else {  // OTP Validation failed.
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_OTP" ) );
					$this->mo_auth_show_error_message();
				}
			}
		}
	}

	public function mo2f_validate_otp_over_sms() {
		$nonce = isset( $_POST['mo2f_validate_otp_over_sms_nonce'] ) ? sanitize_text_field( $_POST['mo2f_validate_otp_over_sms_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-validate-otp-over-sms-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id   = $user->ID;
			$otp_token = '';
			if ( molms_2f_Utility::mo2f_check_empty_or_null( sanitize_text_field( $_POST['otp_token'] ) ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ENTER_VALUE" ) );
				$this->mo_auth_show_error_message();

				return;
			} else {
				$otp_token = sanitize_text_field( sanitize_text_field( $_POST['otp_token'] ) );
			}

			//if the php session folder has insufficient permissions, temporary options to be used
			$mo2f_transactionId        = isset( $_SESSION['mo2f_transactionId'] ) && ! empty( $_SESSION['mo2f_transactionId'] ) ? sanitize_text_field( $_SESSION['mo2f_transactionId'] ) : get_site_option( 'mo2f_transactionId' );
			$email                     = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			$selected_2_2factor_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
			$customer                  = new Molms_Customer_Setup();
			$content                   = json_decode( $customer->validate_otp_token( $selected_2_2factor_method, $email, $mo2f_transactionId, $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );

			if ( $content['status'] == 'ERROR' ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $content['message'] ) );
				$this->mo_auth_show_error_message();
			} else {
				if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) { //OTP validated
					if ( current_user_can( 'manage_options' ) ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
					} else {
						update_site_option( 'mo2f_message', molms_2fConstants::langTranslate( "COMPLETED_TEST" ) );
					}

					delete_user_meta( $user->ID, 'test_2FA' );
					$this->mo_auth_show_success_message();
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_OTP" ) );
					$this->mo_auth_show_error_message();
				}
			}
		}
	}

	public function mo2f_out_of_band_success() {
		$nonce = isset( $_POST['mo2f_out_of_band_success_nonce'] ) ? sanitize_text_field( $_POST['mo2f_out_of_band_success_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-out-of-band-success-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			$show    = 1;
			if ( MOLMS_IS_ONPREM ) {
				$txid   = isset( $_POST['TxidEmail'] ) ? sanitize_text_field( $_POST['TxidEmail'] ) : null;
				$status = get_site_option( $txid );
				if ( $status != '' ) {
					if ( $status != 1 ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_EMAIL_VER_REQ" ) );
						$show = 0;
						$this->mo_auth_show_error_message();
					}
				}
			}
			$mo2f_configured_2FA_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
			if ( MOLMS_IS_ONPREM and $mo2f_configured_2FA_method == 'OUT OF BAND EMAIL' ) {
				$mo2f_configured_2FA_method = 'Email Verification';
			}

			$mo2f_EmailVerification_config_status = $molms_db_queries->get_user_detail( 'mo2f_EmailVerification_config_status', $user->ID );
			if ( ! current_user_can( 'manage_options' ) && $mo2f_configured_2FA_method == 'OUT OF BAND EMAIL' ) {
				if ( $mo2f_EmailVerification_config_status ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
				} else {
					$email    = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
					$enduser  = new Molms_Two_Factor_Setup();
					$response = json_decode( $enduser->mo2f_update_userinfo( $email, $mo2f_configured_2FA_method, null, null, null ), true );
					update_site_option( 'mo2f_message', '<b> ' . molms_2fConstants:: langTranslate( "EMAIL_VERFI" ) . '</b> ' . molms_2fConstants:: langTranslate( "SET_AS_2ND_FACTOR" ) );
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
			}
			delete_user_meta( $user->ID, 'test_2FA' );
			$molms_db_queries->update_user_details(
				$user->ID,
				array(
					'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					'mo2f_EmailVerification_config_status' => true
				)
			);
			if ( $show ) {
				$this->mo_auth_show_success_message();
			}
		}
	}

	public function mo2f_out_of_band_error() {
		$nonce = isset( $_POST['mo2f_out_of_band_error_nonce'] ) ? sanitize_text_field( $_POST['mo2f_out_of_band_error_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-out-of-band-error-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "DENIED_REQUEST" ) );
			delete_user_meta( $user->ID, 'test_2FA' );
			$molms_db_queries->update_user_details(
				$user->ID,
				array(
					'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					'mo2f_EmailVerification_config_status' => true
				)
			);
			$this->mo_auth_show_error_message();
		}
	}

	public function mo2f_validate_google_authy_test() {
		$nonce = isset( $_POST['mo2f_validate_google_authy_test_nonce'] ) ? sanitize_text_field( $_POST['mo2f_validate_google_authy_test_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-validate-google-authy-test-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id   = $user->ID;
			$otp_token = '';
			if ( molms_2f_Utility::mo2f_check_empty_or_null( sanitize_text_field( $_POST['otp_token'] ) ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ENTER_VALUE" ) );
				$this->mo_auth_show_error_message();

				return;
			} else {
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}
			$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );

			$customer = new Molms_Customer_Setup();
			$content  = json_decode( $customer->validate_otp_token( 'GOOGLE AUTHENTICATOR', $email, null, $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) { //Google OTP validated
					if ( current_user_can( 'manage_options' ) ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
					} else {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
					}

					delete_user_meta( $user->ID, 'test_2FA' );
					$this->mo_auth_show_success_message();
				} else {  // OTP Validation failed.
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_OTP" ) );
					$this->mo_auth_show_error_message();
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_WHILE_VALIDATING_OTP" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_validate_otp_over_email() {
		$nonce = isset( $_POST['mo2f_validate_otp_over_email_test_nonce'] ) ? sanitize_text_field( $_POST['mo2f_validate_otp_over_email_test_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-validate-otp-over-email-test-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id   = $user->ID;
			$otp_token = '';
			if ( molms_2f_Utility::mo2f_check_empty_or_null( sanitize_text_field( $_POST['otp_token'] ) ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ENTER_VALUE" ) );
				$this->mo_auth_show_error_message();

				return;
			} else {
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}
			$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );

			$customer = new Molms_Customer_Setup();
			$content  = json_decode( $customer->validate_otp_token( 'OTP_OVER_EMAIL', $email, $_SESSION['mo2f_transactionId'], $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) { //Google OTP validated
					if ( current_user_can( 'manage_options' ) ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
						delete_user_meta( $user->ID, 'configure_2FA' );
						$molms_db_queries->update_user_details( $user->ID, array( 'mo2f_configured_2FA_method' => 'OTP Over Email' ) );
					} else {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
					}

					delete_user_meta( $user->ID, 'test_2FA' );
					$this->mo_auth_show_success_message();
				} else {  // OTP Validation failed.
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_OTP" ) );
					$this->mo_auth_show_error_message();
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_WHILE_VALIDATING_OTP" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_google_appname() {
		$nonce = isset( $_POST['mo2f_google_appname_nonce'] ) ? sanitize_text_field( $_POST['mo2f_google_appname_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-google-appname-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			update_site_option( 'mo2f_google_appname', ( ( isset( $_POST['mo2f_google_auth_appname'] ) && sanitize_text_field( $_POST['mo2f_google_auth_appname'] ) != '' ) ? sanitize_text_field( $_POST['mo2f_google_auth_appname'] ) : 'miniOrangeAu' ) );
		}
	}

	public function mo2f_configure_google_authenticator_validate() {
		$nonce = isset( $_POST['mo2f_configure_google_authenticator_validate_nonce'] ) ? sanitize_text_field( $_POST['mo2f_configure_google_authenticator_validate_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-configure-google-authenticator-validate-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id   = $user->ID;
			$otpToken  = sanitize_text_field( $_POST['google_token'] );
			$ga_secret = isset( $_POST['google_auth_secret'] ) ? sanitize_text_field( $_POST['google_auth_secret'] ) : null;

			if ( molms_2f_Utility::mo2f_check_number_length( $otpToken ) ) {
				$email                  = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
				$twofactor_transactions = new molms_DB;
				$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );

				if ( $exceeded ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "USER_LIMIT_EXCEEDED" ) );
					$this->mo_auth_show_error_message();

					return;
				}
				$google_auth     = new Molms_Miniorange_Rba_Attributes();
				$google_response = json_decode( $google_auth->mo2f_validate_google_auth( $email, $otpToken, $ga_secret ), true );
				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( $google_response['status'] == 'SUCCESS' ) {
						$enduser  = new Molms_Two_Factor_Setup();
						$response = json_decode( $enduser->mo2f_update_userinfo( $email, "GOOGLE AUTHENTICATOR", null, null, null ), true );
						if ( json_last_error() == JSON_ERROR_NONE ) {
							if ( $response['status'] == 'SUCCESS' ) {
								delete_user_meta( $user->ID, 'mo2f_2FA_method_to_configure' );

								delete_user_meta( $user->ID, 'configure_2FA' );

								$molms_db_queries->update_user_details(
									$user->ID,
									array(
										'mo2f_GoogleAuthenticator_config_status' => true,
										'mo2f_AuthyAuthenticator_config_status'  => false,
										'mo2f_configured_2FA_method'             => "Google Authenticator",
										'user_registration_with_miniorange'      => 'SUCCESS',
										'mo_2factor_user_registration_status'    => 'MO_2_FACTOR_PLUGIN_SETTINGS'
									)
								);

								update_user_meta( $user->ID, 'mo2f_external_app_type', "Google Authenticator" );
								molms_display_test_2fa_notification( $user );
								unset( $_SESSION['secret_ga'] );
							} else {
								update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
								$this->mo_auth_show_error_message();
							}
						} else {
							update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
							$this->mo_auth_show_error_message();
						}
					} else {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_IN_SENDING_OTP_CAUSES" ) . '<br>1. ' . molms_2fConstants:: langTranslate( "INVALID_OTP" ) . '<br>2. ' . molms_2fConstants:: langTranslate( "APP_TIME_SYNC" ) . '<br>3.' . molms_2fConstants::langTranslate( "SERVER_TIME_SYNC" ) );
						$this->mo_auth_show_error_message();
					}
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_WHILE_VALIDATING_USER" ) );
					$this->mo_auth_show_error_message();
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ONLY_DIGITS_ALLOWED" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_save_kba() {
		$nonce = isset( $_POST['mo2f_save_kba_nonce'] ) ? sanitize_text_field( $_POST['mo2f_save_kba_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mo2f-save-kba-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		}
		global $molms_db_queries, $user;
		$user_id                = $user->ID;
		$twofactor_transactions = new molms_DB;
		$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );
		if ( $exceeded ) {
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "USER_LIMIT_EXCEEDED" ) );
			$this->mo_auth_show_error_message();

			return;
		}

		$kba_q1 = sanitize_text_field( $_POST['mo2f_kbaquestion_1'] );
		$kba_a1 = sanitize_text_field( $_POST['mo2f_kba_ans1'] );
		$kba_q2 = sanitize_text_field( $_POST['mo2f_kbaquestion_2'] );
		$kba_a2 = sanitize_text_field( $_POST['mo2f_kba_ans2'] );
		$kba_q3 = sanitize_text_field( $_POST['mo2f_kbaquestion_3'] );
		$kba_a3 = sanitize_text_field( $_POST['mo2f_kba_ans3'] );

		if ( molms_2f_Utility::mo2f_check_empty_or_null( $kba_q1 ) || molms_2f_Utility::mo2f_check_empty_or_null( $kba_a1 ) || molms_2f_Utility::mo2f_check_empty_or_null( $kba_q2 ) || molms_2f_Utility::mo2f_check_empty_or_null( $kba_a2 ) || molms_2f_Utility::mo2f_check_empty_or_null( $kba_q3 ) || molms_2f_Utility::mo2f_check_empty_or_null( $kba_a3 ) ) {
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_ENTRY" ) );
			$this->mo_auth_show_error_message();

			return;
		}

		if ( strcasecmp( $kba_q1, $kba_q2 ) == 0 || strcasecmp( $kba_q2, $kba_q3 ) == 0 || strcasecmp( $kba_q3, $kba_q1 ) == 0 ) {
			update_site_option( 'mo2f_message', 'The questions you select must be unique.' );
			$this->mo_auth_show_error_message();

			return;
		}
		$kba_q1 = addcslashes( stripslashes( $kba_q1 ), '"\\' );
		$kba_q2 = addcslashes( stripslashes( $kba_q2 ), '"\\' );
		$kba_q3 = addcslashes( stripslashes( $kba_q3 ), '"\\' );

		$kba_a1 = addcslashes( stripslashes( $kba_a1 ), '"\\' );
		$kba_a2 = addcslashes( stripslashes( $kba_a2 ), '"\\' );
		$kba_a3 = addcslashes( stripslashes( $kba_a3 ), '"\\' );

		$email            = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
		$kba_registration = new Molms_Two_Factor_Setup();
		$kba_reg_reponse  = json_decode( $kba_registration->register_kba_details( $email, $kba_q1, $kba_a1, $kba_q2, $kba_a2, $kba_q3, $kba_a3, $user->ID ), true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $kba_reg_reponse['status'] == 'SUCCESS' ) {
				if ( isset( $_POST['mobile_kba_option'] ) && sanitize_text_field( $_POST['mobile_kba_option'] ) == 'mo2f_request_for_kba_as_emailbackup' ) {
					molms_2f_Utility::unset_session_variables( 'mo2f_mobile_support' );

					delete_user_meta( $user->ID, 'configure_2FA' );
					delete_user_meta( $user->ID, 'mo2f_2FA_method_to_configure' );

					$message = molms_lt( 'Your KBA as alternate 2 factor is configured successfully.' );
					update_site_option( 'mo2f_message', $message );
					$this->mo_auth_show_success_message();
				} else {
					$enduser  = new Molms_Two_Factor_Setup();
					$response = json_decode( $enduser->mo2f_update_userinfo( $email, 'KBA', null, null, null ), true );
					if ( json_last_error() == JSON_ERROR_NONE ) {
						if ( $response['status'] == 'ERROR' ) {
							update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
							$this->mo_auth_show_error_message();
						} elseif ( $response['status'] == 'SUCCESS' ) {
							delete_user_meta( $user->ID, 'configure_2FA' );

							$molms_db_queries->update_user_details(
								$user->ID,
								array(
									'mo2f_SecurityQuestions_config_status' => true,
									'mo2f_configured_2FA_method'           => "Security Questions",
									'mo_2factor_user_registration_status'  => "MO_2_FACTOR_PLUGIN_SETTINGS"
								)
							);
							molms_display_test_2fa_notification( $user );
						} else {
							update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
							$this->mo_auth_show_error_message();
						}
					} else {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
						$this->mo_auth_show_error_message();
					}
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_WHILE_SAVING_KBA" ) );
				$this->mo_auth_show_error_message();


				return;
			}
		} else {
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_WHILE_SAVING_KBA" ) );
			$this->mo_auth_show_error_message();


			return;
		}
	}

	public function mo2f_validate_kba_details() {
		$nonce = isset( $_POST['mo2f_validate_kba_details_nonce'] ) ? sanitize_text_field( $_POST['mo2f_validate_kba_details_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-validate-kba-details-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id   = $user->ID;
			$kba_ans_1 = sanitize_text_field( $_POST['mo2f_answer_1'] );
			$kba_ans_2 = sanitize_text_field( $_POST['mo2f_answer_2'] );
			if ( molms_2f_Utility::mo2f_check_empty_or_null( $kba_ans_1 ) || molms_2f_Utility::mo2f_check_empty_or_null( $kba_ans_2 ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_ENTRY" ) );
				$this->mo_auth_show_error_message();

				return;
			}

			//if the php session folder has insufficient permissions, temporary options to be used
			if ( isset( $_SESSION['mo_2_factor_kba_questions'] ) && ! empty( $_SESSION['mo_2_factor_kba_questions'] ) ) {
				$kba_questions[0]['question'] = sanitize_text_field( $_SESSION['mo_2_factor_kba_questions'][0]['question'] );
				$kba_questions[1]['question'] = sanitize_text_field( $_SESSION['mo_2_factor_kba_questions'][1]['question'] );
			} else {
				$kba_questions = get_site_option( 'kba_questions' );
			}

			$kbaAns = array();
			if ( ! MOLMS_IS_ONPREM ) {
				$kbaAns[0] = sanitize_text_field( $kba_questions[0]['question'] );
				$kbaAns[1] = $kba_ans_1;
				$kbaAns[2] = sanitize_text_field( $kba_questions[1]['question'] );
				$kbaAns[3] = $kba_ans_2;
			}
			//if the php session folder has insufficient permissions, temporary options to be used
			$mo2f_transactionId    = isset( $_SESSION['mo2f_transactionId'] ) && ! empty( $_SESSION['mo2f_transactionId'] ) ? sanitize_text_field( $_SESSION['mo2f_transactionId'] ) : get_site_option( 'mo2f_transactionId' );
			$kba_validate          = new Molms_Customer_Setup();
			$kba_validate_response = json_decode( $kba_validate->validate_otp_token( 'KBA', null, $mo2f_transactionId, $kbaAns, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				if ( strcasecmp( $kba_validate_response['status'], 'SUCCESS' ) == 0 ) {
					unset( $_SESSION['mo_2_factor_kba_questions'] );
					unset( $_SESSION['mo2f_transactionId'] );
					delete_site_option( 'mo2f_transactionId' );
					delete_site_option( 'kba_questions' );
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "COMPLETED_TEST" ) );
					delete_user_meta( $user->ID, 'test_2FA' );
					$this->mo_auth_show_success_message();
				} else {  // KBA Validation failed.
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_ANSWERS" ) );
					$this->mo_auth_show_error_message();
				}
			}
		}
	}

	public function mo2f_configure_otp_over_sms_send_otp() {
		// sendin otp for configuring OTP over SMS

		$nonce = isset( $_POST['mo2f_configure_otp_over_sms_send_otp_nonce'] ) ? sanitize_text_field( $_POST['mo2f_configure_otp_over_sms_send_otp_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-configure-otp-over-sms-send-otp-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			$phone   = sanitize_text_field( $_POST['verify_phone'] );

			if ( molms_2f_Utility::mo2f_check_empty_or_null( $phone ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_ENTRY" ) );
				$this->mo_auth_show_error_message();

				return;
			}

			$phone                  = str_replace( ' ', '', $phone );
			$_SESSION['user_phone'] = $phone;
			update_site_option( 'user_phone_temp', $phone );
			$customer      = new Molms_Customer_Setup();
			$currentMethod = "SMS";

			$content = json_decode( $customer->send_otp_token( $phone, $currentMethod, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );

			if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate otp token */
				if ( $content['status'] == 'ERROR' ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $content['message'] ) );
					$this->mo_auth_show_error_message();
				} elseif ( $content['status'] == 'SUCCESS' ) {
					$_SESSION['mo2f_transactionId'] = $content['txId'];
					update_site_option( 'mo2f_transactionId', $content['txId'] );
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "OTP_SENT" ) . ' ' . $phone . ' .' . molms_2fConstants:: langTranslate( "ENTER_OTP" ) );
					update_site_option( 'mo2f_number_of_transactions', molms_Utility::get_mo2f_db_option( 'mo2f_number_of_transactions', 'get_site_option' ) - 1 );
					update_site_option( 'molms_sms_transactions', get_site_option( 'molms_sms_transactions' ) - 1 );
					$this->mo_auth_show_success_message();
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants::langTranslate( $content['message'] ) );
					$this->mo_auth_show_error_message();
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_configure_otp_over_sms_validate() {
		$nonce = isset( $_POST['mo2f_configure_otp_over_sms_validate_nonce'] ) ? sanitize_text_field( $_POST['mo2f_configure_otp_over_sms_validate_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-configure-otp-over-sms-validate-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id                = $user->ID;
			$twofactor_transactions = new molms_DB;
			$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );

			if ( $exceeded ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "USER_LIMIT_EXCEEDED" ) );
				$this->mo_auth_show_error_message();

				return;
			}
			$otp_token = '';
			if ( molms_2f_Utility::mo2f_check_empty_or_null( sanitize_text_field( $_POST['otp_token'] ) ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_ENTRY" ) );
				$this->mo_auth_show_error_message();

				return;
			} else {
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}

			//if the php session folder has insufficient permissions, temporary options to be used
			$mo2f_transactionId         = isset( $_SESSION['mo2f_transactionId'] ) && ! empty( $_SESSION['mo2f_transactionId'] ) ? sanitize_text_field( $_SESSION['mo2f_transactionId'] ) : get_site_option( 'mo2f_transactionId' );
			$user_phone                 = isset( $_SESSION['user_phone'] ) && $_SESSION['user_phone'] != 'false' ? sanitize_text_field( $_SESSION['user_phone'] ) : get_site_option( 'user_phone_temp' );
			$mo2f_configured_2FA_method = get_user_meta( $user->ID, 'mo2f_2FA_method_to_configure', true );
			$phone                      = $molms_db_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
			$customer                   = new Molms_Customer_Setup();
			$content                    = json_decode( $customer->validate_otp_token( $mo2f_configured_2FA_method, null, $mo2f_transactionId, $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );

			if ( $content['status'] == 'ERROR' ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $content['message'] ) );
			} elseif ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) { //OTP validated
				if ( $phone && strlen( $phone ) >= 4 ) {
					if ( $user_phone != $phone ) {
						$molms_db_queries->update_user_details( $user->ID, array( 'mobile_registration_status' => false ) );
					}
				}
				$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );

				$enduser                   = new Molms_Two_Factor_Setup();
				$TwoFA_method_to_configure = get_user_meta( $user->ID, 'mo2f_2FA_method_to_configure', true );
				$current_method            = molms_2f_Utility::molms_decode_2_factor( $TwoFA_method_to_configure, "server" );
				$response                  = array();
				if ( MOLMS_IS_ONPREM ) {
					$response['status'] = 'SUCCESS';
					if ( $current_method == 'SMS' ) {
						$molms_db_queries->update_user_details( $user->ID, array( 'mo2f_configured_2FA_method' => 'OTP Over SMS' ) );
					} else {
						$molms_db_queries->update_user_details( $user->ID, array( 'mo2f_configured_2FA_method' => $current_method ) );
					}
				} else {
					$response = json_decode( $enduser->mo2f_update_userinfo( $email, $current_method, $user_phone, null, null ), true );
				}

				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( $response['status'] == 'ERROR' ) {
						molms_2f_Utility::unset_session_variables( 'user_phone' );
						delete_site_option( 'user_phone_temp' );

						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
						$this->mo_auth_show_error_message();
					} elseif ( $response['status'] == 'SUCCESS' ) {
						$molms_db_queries->update_user_details(
							$user->ID,
							array(
								'mo2f_configured_2FA_method'          => 'OTP Over SMS',
								'mo2f_OTPOverSMS_config_status'       => true,
								'user_registration_with_miniorange'   => 'SUCCESS',
								'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
								'mo2f_user_phone'                     => $user_phone
							)
						);

						delete_user_meta( $user->ID, 'configure_2FA' );
						delete_user_meta( $user->ID, 'mo2f_2FA_method_to_configure' );

						unset( $_SESSION['user_phone'] );
						molms_2f_Utility::unset_session_variables( 'user_phone' );
						delete_site_option( 'user_phone_temp' );

						molms_display_test_2fa_notification( $user );
					} else {
						molms_2f_Utility::unset_session_variables( 'user_phone' );
						delete_site_option( 'user_phone_temp' );
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
						$this->mo_auth_show_error_message();
					}
				} else {
					molms_2f_Utility::unset_session_variables( 'user_phone' );
					delete_site_option( 'user_phone_temp' );
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
					$this->mo_auth_show_error_message();
				}
			} else {  // OTP Validation failed.
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_OTP" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_save_free_plan_auth_methods() {
		// user clicks on Set 2-Factor method
		$nonce = isset( $_POST['miniorange_save_form_auth_methods_nonce'] ) ? sanitize_text_field( $_POST['miniorange_save_form_auth_methods_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'miniorange-save-form-auth-methods-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id          = $user->ID;
			$configuredMethod = sanitize_text_field( $_POST['mo2f_configured_2FA_method_free_plan'] );
			$selectedAction   = sanitize_text_field( $_POST['mo2f_selected_action_free_plan'] );
			$cloud_methods    = array(
				'OTPOverSMS',
				'miniOrangeQRCodeAuthentication',
				'miniOrangePushNotification',
				'miniOrangeSoftToken'
			);

			if ( $configuredMethod == 'OTPOverSMS' ) {
				$configuredMethod = 'OTP Over SMS';
			}

			//limit exceed check
			$exceeded = $molms_db_queries->check_alluser_limit_exceeded( $user_id );
			if ( $exceeded ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "USER_LIMIT_EXCEEDED" ) );
				$this->mo_auth_show_error_message();

				return;
			}

			$selected_2FA_method = isset( $_POST['mo2f_configured_2FA_method_free_plan'] ) ? sanitize_text_field( $_POST['mo2f_configured_2FA_method_free_plan'] ) : sanitize_text_field( $_POST['mo2f_selected_action_standard_plan'] );

			$selected_2FA_method = molms_2f_Utility::molms_decode_2_factor( $selected_2FA_method, "wpdb" );
			$onprem_methods      = array( 'Google Authenticator', 'Security Questions' );
			$molms_db_queries->insert_user( $user->ID );
			if ( MOLMS_IS_ONPREM && ! in_array( $selected_2FA_method, $onprem_methods ) ) {
				foreach ( $cloud_methods as $cloud_method ) {
					$is_end_user_registered = $molms_db_queries->get_user_detail( 'mo2f_' . $cloud_method . '_config_status', $user->ID );
					if ( ! is_null( $is_end_user_registered ) && $is_end_user_registered == 1 ) {
						break;
					}
				}
			} else {
				$is_end_user_registered = $molms_db_queries->get_user_detail( 'user_registration_with_miniorange', $user->ID );
			}
			$is_customer_registered = false;

			if ( ! MOLMS_IS_ONPREM or $configuredMethod == 'miniOrangeSoftToken' or $configuredMethod == 'miniOrangeQRCodeAuthentication' or $configuredMethod == 'miniOrangePushNotification' or $configuredMethod == 'OTPOverSMS' or $configuredMethod == 'OTP Over SMS' ) {
				$is_customer_registered = get_site_option( 'mo2f_api_key' ) ? true : false;
			}

			$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			if ( ! isset( $email ) or is_null( $email ) or $email == '' ) {
				$email = $user->user_email;
			}
			$is_end_user_registered = $is_end_user_registered ? $is_end_user_registered : false;
			$allowed                = false;
			if ( get_site_option( 'mo2f_miniorange_admin' ) ) {
				$allowed = wp_get_current_user()->ID == get_site_option( 'mo2f_miniorange_admin' );
			}

			if ( $is_customer_registered && ! $is_end_user_registered and ! $allowed ) {
				$enduser    = new Molms_Two_Factor_Setup();
				$check_user = json_decode( $enduser->mo_check_user_already_exist( $email ), true );
				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( $check_user['status'] == 'ERROR' ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $check_user['message'] ) );
						$this->mo_auth_show_error_message();

						return;
					} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND' ) == 0 ) {
						$molms_db_queries->update_user_details(
							$user->ID,
							array(
								'user_registration_with_miniorange' => 'SUCCESS',
								'mo2f_user_email'                   => $email
							)
						);
						update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );
					} elseif ( strcasecmp( $check_user['status'], 'USER_NOT_FOUND' ) == 0 ) {
						$content = json_decode( $enduser->mo_create_user( $user, $email ), true );
						if ( json_last_error() == JSON_ERROR_NONE ) {
							if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
								update_site_option( 'totalUsersCloud', get_site_option( 'totalUsersCloud' ) + 1 );
								$molms_db_queries->update_user_details(
									$user->ID,
									array(
										'user_registration_with_miniorange' => 'SUCCESS',
										'mo2f_user_email'                   => $email
									)
								);
							}
						}
					} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) == 0 ) {
						$mo2fa_login_message = __( 'The email associated with your account is already registered in miniOrnage. Please Choose another email or contact miniOrange.', '2fa-learndash-lms' );
						update_site_option( 'mo2f_message', $mo2fa_login_message );
						$this->mo_auth_show_error_message();
					}
				}
			}

			update_user_meta( $user->ID, 'mo2f_2FA_method_to_configure', $selected_2FA_method );
			if ( MOLMS_IS_ONPREM ) {
				if ( $selected_2FA_method == 'EmailVerification' ) {
					$selected_2FA_method = 'Email Verification';
				}
				if ( $selected_2FA_method == 'OTPOverEmail' ) {
					$selected_2FA_method = 'OTP Over Email';
				}
				if ( $selected_2FA_method == 'OTPOverSMS' ) {
					$selected_2FA_method = 'OTP Over SMS';
				}
			}

			if ( MOLMS_IS_ONPREM and ( $selected_2FA_method == 'Google Authenticator' or $selected_2FA_method == 'Security Questions' or $selected_2FA_method == 'OTP Over Email' or $selected_2FA_method == 'Email Verification' ) ) {
				$is_customer_registered = 1;
			}

			if ( $is_customer_registered ) {
				$selected_2FA_method = molms_2f_Utility::molms_decode_2_factor( isset( $_POST['mo2f_configured_2FA_method_free_plan'] ) ? sanitize_text_field( $_POST['mo2f_configured_2FA_method_free_plan'] ) : sanitize_text_field( $_POST['mo2f_selected_action_standard_plan'] ), "wpdb" );
				$selected_2FA_method = sanitize_text_field( $selected_2FA_method );
				$selected_action     = isset( $_POST['mo2f_selected_action_free_plan'] ) ? sanitize_text_field( $_POST['mo2f_selected_action_free_plan'] ) : sanitize_text_field( $_POST['mo2f_selected_action_standard_plan'] );
				$user_phone          = '';
				if ( isset( $_SESSION['user_phone'] ) ) {
					$user_phone = $_SESSION['user_phone'] != 'false' ? sanitize_text_field( $_SESSION['user_phone'] ) : $molms_db_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
				}

				// set it as his 2-factor in the WP database and server
				$enduser = new Molms_Customer_Setup();
				if ( $selected_action == "select2factor" ) {
					if ( $selected_2FA_method == 'OTP Over SMS' && $user_phone == 'false' ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "PHONE_NOT_CONFIGURED" ) );
						$this->mo_auth_show_error_message();
					} else {
						// update in the Wordpress DB
						$email         = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
						$customer_key  = get_site_option( 'mo2f_customerKey' );
						$api_key       = get_site_option( 'mo2f_api_key' );
						$customer      = new Molms_Customer_Setup();
						$cloud_method1 = array(
							'miniOrange QR Code Authentication',
							'miniOrange Push Notification',
							'miniOrange Soft Token'
						);
						if ( ( $selected_2FA_method == "OTP Over Email" ) and MOLMS_IS_ONPREM ) {
							$check = 1;
							if ( molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' ) <= 0 ) {
								update_site_option( "molms_zero_email_transactions", 1 );
								$check = 0;
							}


							if ( $check == 1 ) {
								$response = json_decode( $customer->send_otp_token( $email, $selected_2FA_method, $customer_key, $api_key ), true );
							} else {
								$response['status'] = 'FAILED';
							}
							if ( strcasecmp( $response['status'], 'SUCCESS' ) == 0 ) {
								$molms_email_transactions = molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' );
								update_site_option( "molms_email_transactions", $molms_email_transactions - 1 );
								update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "OTP_SENT" ) . ( $email ) . '. ' . molms_2fConstants:: langTranslate( "ENTER_OTP" ) );
								update_site_option( 'mo2f_number_of_transactions', molms_Utility::get_mo2f_db_option( 'mo2f_number_of_transactions', 'get_site_option' ) - 1 );

								$_SESSION['mo2f_transactionId'] = $response['txId'];
								update_site_option( 'mo2f_transactionId', $response['txId'] );
								$this->mo_auth_show_success_message();
							} else {
								update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_IN_SENDING_OTP_ONPREM" ) );
								$this->mo_auth_show_error_message();
							}
							update_user_meta( $user->ID, 'configure_2FA', 1 );
						} elseif ( $selected_2FA_method == "Email Verification" ) {
							$enduser->send_otp_token( $email, 'OUT OF BAND EMAIL', $customer_key, $api_key );
						}


						if ( $selected_2FA_method != 'OTP Over Email' ) {
							$molms_db_queries->update_user_details( $user->ID, array( 'mo2f_configured_2FA_method' => $selected_2FA_method ) );
						}

						// update the server
						if ( ! MOLMS_IS_ONPREM ) {
							$this->mo2f_save_2_factor_method( $user, $selected_2FA_method );
						}
						if ( in_array(
							$selected_2FA_method,
							array(
								"miniOrange QR Code Authentication",
								"miniOrange Soft Token",
								"miniOrange Push Notification",
								"Google Authenticator",
								"Security Questions",
								"Authy Authenticator",
								"Email Verification",
								"OTP Over SMS",
								"OTP Over Email",
								"OTP Over SMS and Email",
								"Hardware Token"
							)
						)
						) {
						} else {
							update_site_option( 'mo2f_enable_2fa_prompt_on_login_page', 0 );
						}
					}
				} elseif ( $selected_action == "configure2factor" ) {
					//show configuration form of respective Two Factor method
					update_user_meta( $user->ID, 'configure_2FA', 1 );
					update_user_meta( $user->ID, 'mo2f_2FA_method_to_configure', $selected_2FA_method );
				}
			} else {
				update_site_option( "mo_2factor_user_registration_status", "REGISTRATION_STARTED" );
				update_user_meta( $user->ID, 'register_account_popup', 1 );
				update_site_option( 'mo2f_message', '' );
			}
		}
	}

	public function mo2f_save_2_factor_method( $user, $mo2f_configured_2FA_method ) {
		global $molms_db_queries;
		$email          = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
		$enduser        = new Molms_Two_Factor_Setup();
		$phone          = $molms_db_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
		$current_method = molms_2f_Utility::molms_decode_2_factor( $mo2f_configured_2FA_method, "server" );

		$response = json_decode( $enduser->mo2f_update_userinfo( $email, $current_method, $phone, null, null ), true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $response['status'] == 'ERROR' ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
				$this->mo_auth_show_error_message();
			} elseif ( $response['status'] == 'SUCCESS' ) {
				$configured_2fa_method = '';
				if ( $mo2f_configured_2FA_method == '' ) {
					$configured_2fa_method = $molms_db_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
				} else {
					$configured_2fa_method = $mo2f_configured_2FA_method;
				}
				if ( in_array( $configured_2fa_method, array( "Google Authenticator", "Authy Authenticator" ) ) ) {
					update_user_meta( $user->ID, 'mo2f_external_app_type', $configured_2fa_method );
				}

				$molms_db_queries->update_user_details(
					$user->ID,
					array(
						'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS'
					)
				);
				delete_user_meta( $user->ID, 'configure_2FA' );

				if ( $configured_2fa_method == 'OTP Over Email' or $configured_2fa_method == 'OTP Over SMS' ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $configured_2fa_method ) . ' ' . molms_2fConstants:: langTranslate( "SET_2FA_otp" ) );
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $configured_2fa_method ) . ' ' . molms_2fConstants:: langTranslate( "SET_2FA" ) );
				}


				$this->mo_auth_show_success_message();
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
				$this->mo_auth_show_error_message();
			}
		} else {
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
			$this->mo_auth_show_error_message();
		}
	}

	public function mo2f_enable_2FA_for_users_option() {
		$nonce = isset( $_POST['mo2f_enable_2FA_for_users_option_nonce'] ) ? sanitize_text_field( $_POST['mo2f_enable_2FA_for_users_option_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-enable-2FA-for-users-option-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			update_site_option( 'mo2f_enable_2fa_for_users', isset( $_POST['mo2f_enable_2fa_for_users'] ) ? sanitize_text_field( $_POST['mo2f_enable_2fa_for_users'] ) : 0 );
		}
	}

	public function mo2f_enable_2FA_on_login_page_option() {
		$nonce = isset( $_POST['mo2f_enable_2FA_on_login_page_option_nonce'] ) ? sanitize_text_field( $_POST['mo2f_enable_2FA_on_login_page_option_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-enable-2FA-on-login-page-option-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			update_site_option( 'mo2f_enable_2fa_prompt_on_login_page', isset( $_POST['mo2f_enable_2fa_prompt_on_login_page'] ) ? sanitize_text_field( $_POST['mo2f_enable_2fa_prompt_on_login_page'] ) : 0 );
		}
	}

	public function mo_2factor_test_authentication_method() {
		//network security feature
		$nonce = isset( $_POST['mo_2factor_test_authentication_method_nonce'] ) ? sanitize_text_field( $_POST['mo_2factor_test_authentication_method_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo-2factor-test-authentication-method-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id = $user->ID;
			update_user_meta( $user->ID, 'test_2FA', 1 );

			$selected_2FA_method        = sanitize_text_field( $_POST['mo2f_configured_2FA_method_test'] );
			$selected_2FA_method_server = molms_2f_Utility::molms_decode_2_factor( $selected_2FA_method, "server" );
			$customer                   = new Molms_Customer_Setup();
			$email                      = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			$customer_key               = get_site_option( 'mo2f_customerKey' );
			$api_key                    = get_site_option( 'mo2f_api_key' );

			if ( $selected_2FA_method == 'Security Questions' ) {
				$response = json_decode( $customer->send_otp_token( $email, $selected_2FA_method_server, $customer_key, $api_key ), true );

				if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate KBA Questions*/
					if ( $response['status'] == 'SUCCESS' ) {
						$_SESSION['mo2f_transactionId'] = $response['txId'];
						update_site_option( 'mo2f_transactionId', $response['txId'] );
						$questions = array();

						$questions[0]                          = $response['questions'][0];
						$questions[1]                          = $response['questions'][1];
						$_SESSION['mo_2_factor_kba_questions'] = $questions;
						update_site_option( 'kba_questions', $questions );

						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ANSWER_SECURITY_QUESTIONS" ) );
						$this->mo_auth_show_success_message();
					} elseif ( $response['status'] == 'ERROR' ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_FETCHING_QUESTIONS" ) );
						$this->mo_auth_show_error_message();
					}
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_FETCHING_QUESTIONS" ) );
					$this->mo_auth_show_error_message();
				}
			} elseif ( $selected_2FA_method == 'miniOrange Push Notification' ) {
				$response = json_decode( $customer->send_otp_token( $email, $selected_2FA_method_server, $customer_key, $api_key ), true );
				if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
					if ( $response['status'] == 'ERROR' ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
						$this->mo_auth_show_error_message();
					} else {
						if ( $response['status'] == 'SUCCESS' ) {
							$_SESSION['mo2f_transactionId'] = $response['txId'];
							update_site_option( 'mo2f_transactionId', $response['txId'] );
							$_SESSION['mo2f_show_qr_code'] = 'MO_2_FACTOR_SHOW_QR_CODE';
							update_site_option( 'mo2f_transactionId', $response['txId'] );
							update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "PUSH_NOTIFICATION_SENT" ) );
							$this->mo_auth_show_success_message();
						} else {
							$session_variables = array( 'mo2f_qrCode', 'mo2f_transactionId', 'mo2f_show_qr_code' );
							molms_2f_Utility::unset_session_variables( $session_variables );

							delete_site_option( 'mo2f_transactionId' );
							update_site_option( 'mo2f_message', 'An error occurred while processing your request. Please Try again.' );
							$this->mo_auth_show_error_message();
						}
					}
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
					$this->mo_auth_show_error_message();
				}
			} elseif ( $selected_2FA_method == 'OTP Over SMS' || $selected_2FA_method == 'OTP Over Email' ) {
				$phone = $molms_db_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
				$check = 1;
				if ( $selected_2FA_method == 'OTP Over Email' ) {
					$phone = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
					if ( molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' ) <= 0 ) {
						update_site_option( "molms_zero_email_transactions", 1 );
						$check = 0;
					}
				}

				if ( $check == 1 ) {
					$response = json_decode( $customer->send_otp_token( $phone, $selected_2FA_method_server, $customer_key, $api_key ), true );
				} else {
					$response['status'] = 'FAILED';
				}
				if ( strcasecmp( $response['status'], 'SUCCESS' ) == 0 ) {
					if ( $selected_2FA_method == 'OTP Over Email' ) {
						$molms_email_transactions = molms_Utility::get_mo2f_db_option( 'molms_email_transactions', 'site_option' );
						update_site_option( "molms_email_transactions", $molms_email_transactions - 1 );
					} elseif ( $selected_2FA_method == 'OTP Over SMS' ) {
						update_site_option( 'molms_sms_transactions', get_site_option( 'molms_sms_transactions' ) - 1 );
					}
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "OTP_SENT" ) . ( $phone ) . '. ' . molms_2fConstants:: langTranslate( "ENTER_OTP" ) );
					update_site_option( 'mo2f_number_of_transactions', molms_Utility::get_mo2f_db_option( 'mo2f_number_of_transactions', 'get_site_option' ) - 1 );

					$_SESSION['mo2f_transactionId'] = $response['txId'];
					update_site_option( 'mo2f_transactionId', $response['txId'] );
					$this->mo_auth_show_success_message();
				} else {
					if ( ! MOLMS_IS_ONPREM or $selected_2FA_method == 'OTP Over SMS' ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_IN_SENDING_OTP" ) );
					} else {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_IN_SENDING_OTP_ONPREM" ) );
					}

					$this->mo_auth_show_error_message();
				}
			} elseif ( $selected_2FA_method == 'miniOrange QR Code Authentication' ) {
				$response = json_decode( $customer->send_otp_token( $email, $selected_2FA_method_server, $customer_key, $api_key ), true );

				if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate Qr code */
					if ( $response['status'] == 'ERROR' ) {
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
						$this->mo_auth_show_error_message();
					} else {
						if ( $response['status'] == 'SUCCESS' ) {
							$_SESSION['mo2f_qrCode']        = $response['qrCode'];
							$_SESSION['mo2f_transactionId'] = $response['txId'];
							$_SESSION['mo2f_show_qr_code']  = 'MO_2_FACTOR_SHOW_QR_CODE';
							update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "SCAN_QR_CODE" ) );
							$this->mo_auth_show_success_message();
						} else {
							unset( $_SESSION['mo2f_qrCode'] );
							unset( $_SESSION['mo2f_transactionId'] );
							unset( $_SESSION['mo2f_show_qr_code'] );
							update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
							$this->mo_auth_show_error_message();
						}
					}
				} else {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
					$this->mo_auth_show_error_message();
				}
			} elseif ( $selected_2FA_method == 'Email Verification' ) {
				$this->miniorange_email_verification_call( $user );
			}


			update_user_meta( $user->ID, 'mo2f_2FA_method_to_test', $selected_2FA_method );
		}
	}

	public function miniorange_email_verification_call( $current_user ) {
		global $molms_db_queries;
		$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );

		if ( MOLMS_IS_ONPREM ) {
			$challengeMobile      = new Molms_Customer_Setup();
			$is_flow_driven_setup = ! ( get_user_meta( $current_user->ID, 'current_modal', true ) ) ? 0 : 1;

			$subject   = '2-Factor Authentication(Email verification)';
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$txid      = '';
			$otpToken  = '';
			$otpTokenD = '';
			for ( $i = 1; $i < 7; $i ++ ) {
				$otpToken  .= rand( 0, 9 );
				$txid      .= rand( 100, 999 );
				$otpTokenD .= rand( 0, 9 );
			}
			$otpTokenH            = hash( 'sha512', $otpToken );
			$otpTokenDH           = hash( 'sha512', $otpTokenD );
			$_SESSION['txid']     = $txid;
			$_SESSION['otpToken'] = $otpToken;
			$userID               = hash( 'sha512', $current_user->ID );
			update_site_option( $userID, $otpTokenH );
			update_site_option( $txid, 3 );
			$userIDd = $userID . 'D';
			update_site_option( $userIDd, $otpTokenDH );
			$url     = get_site_option( 'siteurl' ) . '/wp-login.php?'; //login page can change
			$message = '<table cellpadding="25" style="margin:0px auto">
			<tbody>
			<tr>
			<td>
			<table cellpadding="24" width="584px" style="margin:0 auto;max-width:584px;background-color:#f6f4f4;border:1px solid #a8adad">
			<tbody>
			<tr>
			<td><img src="https://ci5.googleusercontent.com/proxy/10EQeM1udyBOkfD2dwxGhIaMXV4lOwCRtUecpsDkZISL0JIkOL2JhaYhVp54q6Sk656rW2rpAFJFEgGQiAOVcYIIKxXYMHHMNSNB=s0-d-e1-ft#https://test.miniorange.in/moas/images/xecurify-logo.png" style="color:#5fb336;text-decoration:none;display:block;width:auto;height:auto;max-height:35px" class="CToWUd"></td>
			</tr>
			</tbody>
			</table>
			<table cellpadding="24" style="background:#fff;border:1px solid #a8adad;width:584px;border-top:none;color:#4d4b48;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:18px">
			<tbody>
			<tr>
			<td>
			<p style="margin-top:0;margin-bottom:20px">Dear Customers,</p>
			<p style="margin-top:0;margin-bottom:10px">You initiated a transaction <b>WordPress 2 Factor LMS Plugin</b>:</p>
			<p style="margin-top:0;margin-bottom:10px">To accept, <a href="' . esc_url_raw( $url ) . 'userID=' . esc_html( $userID ) . '&amp;accessToken=' . esc_html( $otpTokenH ) . '&amp;secondFactorAuthType=OUT+OF+BAND+EMAIL&amp;Txid=' . esc_html( $txid ) . '&amp;user=' . esc_html( $email ) . '" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://login.xecurify.com/moas/rest/validate-otp?customerKey%3D182589%26otpToken%3D735705%26secondFactorAuthType%3DOUT%2BOF%2BBAND%2BEMAIL%26user%3D' . esc_html( $email ) . '&amp;source=gmail&amp;ust=1569905139580000&amp;usg=AFQjCNExKCcqZucdgRm9-0m360FdYAIioA">Accept Transaction</a></p>
			<p style="margin-top:0;margin-bottom:10px">To deny, <a href="' . esc_url_raw( $url ) . 'userID=' . esc_html( $userID ) . '&amp;accessToken=' . esc_html( $otpTokenDH ) . '&amp;secondFactorAuthType=OUT+OF+BAND+EMAIL&amp;Txid=' . esc_html( $txid ) . '&amp;user=' . esc_html( $email ) . '" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://login.xecurify.com/moas/rest/validate-otp?customerKey%3D182589%26otpToken%3D735705%26secondFactorAuthType%3DOUT%2BOF%2BBAND%2BEMAIL%26user%3D' . esc_html( $email ) . '&amp;source=gmail&amp;ust=1569905139580000&amp;usg=AFQjCNExKCcqZucdgRm9-0m360FdYAIioA">Deny Transaction</a></p><div><div class="adm"><div id="q_31" class="ajR h4" data-tooltip="Hide expanded content" aria-label="Hide expanded content" aria-expanded="true"><div class="ajT"></div></div></div><div class="im">
			<p style="margin-top:0;margin-bottom:15px">Thank you,<br>miniOrange Team</p>
			<p style="margin-top:0;margin-bottom:0px;font-size:11px">Disclaimer: This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed.</p>
			</div></div></td>
			</tr>
			</tbody>
			</table>
			</td>
			</tr>
			</tbody>
			</table>';
			$result  = wp_mail( $email, $subject, $message, $headers );
			if ( $result ) {
				$time                   = "time" . $txid;
				$current_time_in_millis = round( microtime( true ) * 1000 );
				update_site_option( $time, $current_time_in_millis );
				update_site_option( 'mo2f_message', molms_2fConstants::langTranslate( "VERIFICATION_EMAIL_SENT" ) . '<b> ' . esc_html( $email ) . '</b>. ' . molms_2fConstants::langTranslate( "ACCEPT_LINK_TO_VERIFY_EMAIL" ) );
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants::langTranslate( "ERROR_DURING_PROCESS_EMAIL" ) );
				$this->mo_auth_show_error_message();
			}
		} else {
			global $molms_db_queries;
			$challengeMobile = new Molms_Customer_Setup();
			$email           = $molms_db_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
			$content         = $challengeMobile->send_otp_token( $email, 'OUT OF BAND EMAIL', $this->defaultCustomerKey, $this->defaultApiKey );
			$response        = json_decode( $content, true );
			if ( json_last_error() == JSON_ERROR_NONE ) { /* Generate out of band email */
				if ( $response['status'] == 'ERROR' ) {
					update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( $response['message'] ) );
					$this->mo_auth_show_error_message();
				} else {
					if ( $response['status'] == 'SUCCESS' ) {
						$_SESSION['mo2f_transactionId'] = $response['txId'];
						update_site_option( 'mo2f_transactionId', $response['txId'] );
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "VERIFICATION_EMAIL_SENT" ) . '<b> ' . esc_html( $email ) . '</b>. ' . molms_2fConstants:: langTranslate( "ACCEPT_LINK_TO_VERIFY_EMAIL" ) );
						$this->mo_auth_show_success_message();
					} else {
						unset( $_SESSION['mo2f_transactionId'] );
						update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_PROCESS" ) );
						$this->mo_auth_show_error_message();
					}
				}
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
				$this->mo_auth_show_error_message();
			}
		}
	}

	public function mo2f_go_back() {
		$nonce = isset( $_POST['mo2f_go_back_nonce'] ) ? sanitize_text_field( $_POST['mo2f_go_back_nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-go-back-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );

			return $error;
		} else {
			global $molms_db_queries, $user;
			$user_id           = $user->ID;
			$session_variables = array(
				'mo2f_qrCode',
				'mo2f_transactionId',
				'mo2f_show_qr_code',
				'user_phone',
				'mo2f_google_auth',
				'mo2f_mobile_support',
				'mo2f_authy_keys'
			);
			molms_2f_Utility::unset_session_variables( $session_variables );
			delete_site_option( 'mo2f_transactionId' );
			delete_site_option( 'user_phone_temp' );

			delete_user_meta( $user->ID, 'test_2FA' );
			delete_user_meta( $user->ID, 'configure_2FA' );

			if ( isset( $_SESSION['secret_ga'] ) ) {
				unset( $_SESSION['secret_ga'] );
			}
		}
	}

	public function mo2fa_register_to_upgrade() {
		if ( isset( $_POST['mo2fa_register_to_upgrade_nonce'] ) ) { //registration with miniOrange for upgrading
			$nonce = isset( $_POST['mo2fa_register_to_upgrade_nonce'] ) ? sanitize_text_field( $_POST['mo2fa_register_to_upgrade_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-user-reg-to-upgrade-nonce' ) ) {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "INVALID_REQ" ) );
			} else {
				$requestOrigin = sanitize_text_field( $_POST['requestOrigin'] );
				update_site_option( 'mo2f_customer_selected_plan', $requestOrigin );
				header( 'Location: admin.php?page=molms_account' );
			}
		}
	}

	public function mo_auth_deactivate() {
		global $molms_db_queries;
		$molms_register_with_another_email = get_site_option( 'molms_register_with_another_email' );

		if ( $molms_register_with_another_email ) {
			update_site_option( 'molms_register_with_another_email', 0 );
			$url = admin_url( 'plugins.php' );
			wp_safe_redirect( $url );
		}
	}

	public function mo2f_get_GA_parameters( $user ) {
		global $molms_db_queries;
		$email           = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
		$google_auth     = new Molms_Miniorange_Rba_Attributes();
		$gauth_name      = get_site_option( 'mo2f_google_appname' );
		$gauth_name      = $gauth_name ? $gauth_name : 'miniOrangeAu';
		$google_response = json_decode( $google_auth->mo2f_google_auth_service( $email, $gauth_name ), true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $google_response['status'] == 'SUCCESS' ) {
				$mo2f_google_auth              = array();
				$mo2f_google_auth['ga_qrCode'] = $google_response['qrCodeData'];
				$mo2f_google_auth['ga_secret'] = $google_response['secret'];
				$_SESSION['mo2f_google_auth']  = $mo2f_google_auth;
			} else {
				update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_USER_REGISTRATION" ) );
				do_action( 'mo_auth_show_error_message' );
			}
		} else {
			update_site_option( 'mo2f_message', molms_2fConstants:: langTranslate( "ERROR_DURING_USER_REGISTRATION" ) );
			do_action( 'mo_auth_show_error_message' );
		}
	}
}

function molms_is_customer_registered() {
	$email        = get_site_option( 'mo2f_email' );
	$customer_key = get_site_option( 'mo2f_customerKey' );
	if ( ! $email || ! $customer_key || ! is_numeric( trim( $customer_key ) ) ) {
		return 0;
	} else {
		return 1;
	}
}

new molms_Miniorange_Authentication;
?>