<?php

class molms_ajax {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'molms_two_factor' ) );
	}

	public function molms_two_factor() {
		add_action( 'wp_ajax_molms_two_factor_ajax', array( $this, 'molms_two_factor_ajax' ) );
		add_action( 'wp_ajax_mo2f_ajax', array( $this, 'mo2f_ajax' ) );
		add_action( 'wp_ajax_nopriv_mo2f_ajax', array( $this, 'mo2f_ajax' ) );
	}

	public function molms_two_factor_ajax() {
		switch ( sanitize_text_field( $_POST['mo_2f_two_factor_ajax'] ) ) {
			case 'mo2f_save_email_verification':
				$this->mo2f_save_email_verification();
				break;
			case 'mo2f_unlimitted_user':
				$this->mo2f_unlimitted_user();
				break;
			case 'mo2f_check_user_exist_miniOrange':
				$this->mo2f_check_user_exist_miniOrange();
				break;
			case 'mo2f_single_user':
				$this->mo2f_single_user();
				break;
			case 'molms_CheckEVStatus':
				$this->molms_CheckEVStatus();
				break;
			case 'mo2f_role_based_2_factor':
				$this->mo2f_role_based_2_factor();
				break;
			case 'mo2f_enable_disable_twofactor':
				$this->mo2f_enable_disable_twofactor();
				break;
			case 'mo2f_enable_disable_inline':
				$this->mo2f_enable_disable_inline();
				break;
			case 'mo2f_shift_to_onprem':
				$this->mo2f_shift_to_onprem();
				break;
			case 'lms_logout_form':
				$this->lms_logout_form();
				break;
			case 'molms_update_plan':
				$this->molms_update_plan();
				break;
		}
	}

	public function mo2f_save_email_verification() {
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'EmailVerificationSaveNonce' ) ) {
			echo "NonceDidNotMatch";
			exit;
		} else {
			$email         = sanitize_text_field( $_POST['email'] );
			$currentMethod = sanitize_text_field( $_POST['current_method'] );
			$error         = false;
			$user_id       = sanitize_text_field( $_POST['user_id'] );
			if ( MOLMS_IS_ONPREM ) {
				$twofactor_transactions = new molms_DB;
				$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );

				if ( $exceeded ) {
					echo "USER_LIMIT_EXCEEDED";
					exit;
				}
			}
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$error = true;
			}
			if ( $email != '' && ! $error ) {
				global $molms_db_queries;
				if ( $currentMethod == 'EmailVerification' ) {
					$molms_db_queries->update_user_details(
						get_current_user_id(),
						array(
							'mo2f_EmailVerification_config_status' => true,
							'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS',
							'mo2f_configured_2FA_method'           => "Email Verification",
							'mo2f_user_email'                      => $email
						)
					);
				} else {
					$molms_db_queries->update_user_details(
						get_current_user_id(),
						array(
							'mo2f_EmailVerification_config_status' => true,
							'mo2f_user_email'                      => $email
						)
					);
				}
				update_user_meta( $user_id, 'tempEmail', $email );
				echo "settingsSaved";
				exit;
			} else {
				echo "invalidEmail";
				exit;
			}
		}
	}

	public function mo2f_unlimitted_user() {
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'unlimittedUserNonce' ) ) {
			echo "NonceDidNotMatch";
			exit;
		} else {
			if ( sanitize_text_field( $_POST['enableOnPremise'] ) == 'on' ) {
				global $wp_roles;
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
				foreach ( $wp_roles->role_names as $id => $name ) {
					add_site_option( 'mo2fa_' . $id, 1 );
					if ( $id == 'administrator' ) {
						add_site_option( 'mo2fa_' . $id . '_login_url', admin_url() );
					} else {
						add_site_option( 'mo2fa_' . $id . '_login_url', home_url() );
					}
				}
				echo "OnPremiseActive";
				exit;
			} else {
				echo "OnPremiseDeactive";
				exit;
			}
		}
	}

	public function mo2f_check_user_exist_miniOrange() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'checkuserinminiOrangeNonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );
			echo "NonceDidNotMatch";
			exit;
		}

		if ( ! get_site_option( 'mo2f_customerKey' ) ) {
			echo "NOTLOGGEDIN";
			exit;
		}
		$user = wp_get_current_user();
		global $molms_db_queries;
		$email = $molms_db_queries->get_user_detail( 'mo2f_user_email', $user->ID );
		if ( $email == '' or is_null( $email ) ) {
			$email = $user->user_email;
		}


		if ( isset( $_POST['email'] ) ) {
			$email = sanitize_email( $_POST['email'] );
		}

		$enduser    = new Molms_Two_Factor_Setup();
		$check_user = json_decode( $enduser->mo_check_user_already_exist( $email ), true );


		if ( strcasecmp( $check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) == 0 ) {
			echo "alreadyExist";
			exit;
		} else {
			update_user_meta( $user->ID, 'mo2f_email_miniOrange', $email );
			echo "USERCANBECREATED";
			exit;
		}
	}

	public function mo2f_single_user() {
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'singleUserNonce' ) ) {
			echo "NonceDidNotMatch";
			exit;
		} else {
			$current_user      = wp_get_current_user();
			$current_userID    = $current_user->ID;
			$miniorangeID      = get_site_option( 'mo2f_miniorange_admin' );
			$is_customer_admin = $miniorangeID == $current_userID ? true : false;

			if ( is_null( $miniorangeID ) or $miniorangeID == '' ) {
				$is_customer_admin = true;
			}

			if ( $is_customer_admin ) {
				update_site_option( 'is_onprem', 0 );
				wp_send_json( 'true' );
			} else {
				$adminUser = get_user_by( 'id', $miniorangeID );
				$email     = $adminUser->user_email;
				wp_send_json( $email );
			}
		}
	}

	public function molms_CheckEVStatus() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'molms-CheckEVStatus-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );
			echo "NonceDidNotMatch";
			exit;
		}
		if ( isset( $_POST['txid'] ) ) {
			$txid   = sanitize_text_field( $_POST['txid'] );
			$status = get_site_option( $txid );
			if ( $status == 1 || $status == 0 ) {
				delete_site_option( $txid );
			}
			wp_send_json( $status );
			exit();
		}
		wp_send_json( "empty txid" );
		exit;
	}

	public function mo2f_role_based_2_factor() {
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'unlimittedUserNonce' ) ) {
			wp_send_json( 'ERROR' );

			return;
		}
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		foreach ( $wp_roles->role_names as $id => $name ) {
			update_site_option( 'mo2fa_' . $id, 0 );
		}

		if ( isset( $_POST['enabledrole'] ) ) {
			$enabledrole = sanitize_text_field( $_POST['enabledrole'] );
		} else {
			$enabledrole = array();
		}
		foreach ( $enabledrole as $role ) {
			update_site_option( $role, 1 );
		}
		wp_send_json( 'true' );

		return;
	}

	public function mo2f_enable_disable_twofactor() {
		$nonce = isset( $_POST['mo2f_nonce_enable_2FA'] ) ? sanitize_text_field( $_POST['mo2f_nonce_enable_2FA'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-nonce-enable-2FA' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );
		}

		$enable = sanitize_text_field( $_POST['mo2f_enable_2fa'] );
		if ( $enable == 'true' ) {
			update_site_option( 'molms_activate_plugin', true );
			wp_send_json( 'true' );
		} else {
			update_site_option( 'molms_activate_plugin', false );
			wp_send_json( 'false' );
		}
	}

	public function mo2f_enable_disable_inline() {
		$nonce = isset( $_POST['mo2f_nonce_enable_inline'] ) ? sanitize_text_field( $_POST['mo2f_nonce_enable_inline'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'mo2f-nonce-enable-inline' ) ) {
			wp_send_json( "error" );
		}
		$enable = sanitize_text_field( $_POST['mo2f_inline_registration'] );
		if ( $enable == 'true' ) {
			update_site_option( 'mo2f_inline_registration', 1 );
			wp_send_json( 'true' );
		} else {
			update_site_option( 'mo2f_inline_registration', 0 );
			wp_send_json( 'false' );
		}
	}

	public function mo2f_shift_to_onprem() {
		$current_user   = wp_get_current_user();
		$current_userID = $current_user->ID;
		$miniorangeID   = get_site_option( 'mo2f_miniorange_admin' );
		if ( is_null( $miniorangeID ) or $miniorangeID == '' ) {
			$is_customer_admin = true;
		} else {
			$is_customer_admin = $miniorangeID == $current_userID ? true : false;
		}
		if ( $is_customer_admin ) {
			update_site_option( 'is_onprem', 1 );
			update_site_option( 'mo2f_remember_device', 0 );
			wp_send_json( 'true' );
		} else {
			$adminUser = get_user_by( 'id', $miniorangeID );
			$email     = $adminUser->user_email;
			wp_send_json( $email );
		}
	}

	public function lms_logout_form() {
		global $molms_utility, $molms_db_queries;
		if ( ! $molms_utility->check_empty_or_null( get_site_option( 'mo_lms_registration_status' ) ) ) {
			delete_site_option( 'mo2f_email' );
		}
		delete_site_option( 'mo2f_customerKey' );
		delete_site_option( 'mo2f_api_key' );
		delete_site_option( 'mo2f_customer_token' );
		delete_site_option( 'mo_lms_transactionId' );
		delete_site_option( 'mo_lms_registration_status' );
		delete_site_option( 'mo_2factor_admin_registration_status' );
		$molms_db_queries->delete_all_user_details();

		$two_fa_settings = new molms_Miniorange_Authentication();
		$two_fa_settings->mo_auth_deactivate();
	}

	function molms_update_plan() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'molms-upgradeform-nonce' ) ) {
			$error = new WP_Error();
			$error->add( 'empty_username', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Request.' ) );
			echo "NonceDidNotMatch";
			exit;
		}
		$molms_all_plannames = isset( $_POST['planname'] ) ? sanitize_text_field( $_POST['planname'] ) : '';
		$molms_plan_type     = isset( $_POST['planType'] ) ? sanitize_text_field( $_POST['planType'] ) : '';
		update_option( 'molms_planname', $molms_all_plannames );
		if ( $molms_all_plannames == 'addon_plan' ) {
			update_option( 'molms_planname', 'addon_plan' );
			update_site_option( 'mo_2fa_addon_plan_type', $molms_plan_type );
		} elseif ( $molms_all_plannames == '2fa_plan' ) {
			update_option( 'molms_planname', '2fa_plan' );
			update_site_option( 'molms_plan_type', $molms_plan_type );
		}
	}

	public function mo2f_ajax() {
		$GLOBALS['mo2f_is_ajax_request'] = true;
		switch ( sanitize_text_field( $_POST['mo2f_ajax_option'] ) ) {
			case "mo2f_ajax_kba":
				$this->mo2f_ajax_kba();
				break;
			case "mo2f_ajax_login":
				$this->mo2f_ajax_login();
				break;
			case "mo2f_ajax_otp":
				$this->mo2f_ajax_otp();
				break;
		}
	}

	public function mo2f_ajax_kba() {
		$obj = new Molms_Password_2Factor_Login();
		$obj->check_kba_validation();
	}

	public function mo2f_ajax_login() {
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'miniorange-2-factor-login-nonce' ) ) {
			wp_send_json( "ERROR" );
			exit;
		} else {
			$username = sanitize_text_field( $_POST['username'] );
			$password = sanitize_text_field( $_POST['password'] );
			apply_filters( 'authenticate', null, $username, $password );
		}
	}

	public function mo2f_ajax_otp() {
		$obj = new Molms_Password_2Factor_Login();
		$obj->check_miniorange_soft_token();
	}
}

new molms_ajax;
