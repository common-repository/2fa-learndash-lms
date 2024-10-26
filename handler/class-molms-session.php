<?php

class Molms_Session {
	function __construct() {
		add_filter( 'mo2f_session_addon', array( $this, 'mo2f_plugin_session_addon' ), 5, 2 );
		add_action( 'plugins_loaded', array( $this, 'mo2fa_load_textdomain' ) );
		register_deactivation_hook( __FILE__, array( $this, 'mo_session_addon_deactivation' ) );
		register_activation_hook( __FILE__, array( $this, 'mo_session_addon_activation' ) );
		if ( ( ! get_site_option( 'mo2f_activate_plugin' ) && get_site_option( 'mo2f_simultaneous_session_enable' ) ) || ! get_site_option( 'is_onprem' ) && get_site_option( 'mo_2factor_admin_registration_status' ) != 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' ) {
			remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
			add_filter( 'authenticate', array( $this, 'mo2f_check_session_limit' ), 99999, 4 );
		}
		if ( get_site_option( 'mo2f_session_logout_time_enable' ) ) {
			add_filter( 'auth_cookie_expiration', array( $this, 'mo2f_user_session_expiry' ), 10, 3 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'mo2f_idle_session_script' ) );
		if ( get_site_option( 'mo2f_idle_session_logout_enable' ) ) {
			add_action( 'init', array( $this, 'miniorange_idle_session_logout_init' ) );
		}
	}

	public static function molms_lt( $string ) {

		return __( $string, '2fa-learndash-lms' );
	}

	function mo2f_check_session_limit( $user, $username, $password ) {
		if ( is_a( $user, 'WP_Error' ) && ! empty( $user ) ) {
			return $user;
		}
		$currentuser = wp_authenticate_username_password( $user, $username, $password );
		if ( is_wp_error( $currentuser ) ) {
			$currentuser->add( 'invalid_username_password', '<strong>' . molms_lt( 'ERROR' ) . '</strong>: ' . molms_lt( 'Invalid Username or password.' ) );

			return $currentuser;
		} else {
			$session_action = apply_filters( 'mo2f_session_addon', 'block', $currentuser );

			if ( $session_action == 'block' ) {
				return 'block';
			} else {
				$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : null;
				$session_id  = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : null;
				$user_id     = $currentuser->ID;
				$mo2f_factor = new Molms_Password_2Factor_Login();
				$mo2f_factor->miniorange_pass2login_start_session();
				$session_id_encrypt = $mo2f_factor->create_session();
				$currentuser        = get_user_by( 'id', $user_id );
				wp_set_current_user( $user_id, $currentuser->user_login );
				$mobile_login = new Miniorange_Mobile_Login();
				$mobile_login->remove_current_activity( $session_id_encrypt );
				wp_set_auth_cookie( $user_id, true );
				do_action( 'wp_login', $currentuser->user_login, $currentuser );
				redirect_user_to( $currentuser, $redirect_to );
				exit;
			}
		}
	}

	function mo_session_addon_activation() {
		update_site_option( 'mo2f_session_addon_installed', 1 );
	}

	function mo_session_addon_deactivation() {
		update_site_option( 'mo2f_session_addon_installed', 0 );
		update_site_option( 'mo2f_simultaneous_session_enable', 0 );
		update_site_option( 'mo2f_idle_session_logout_enable', 0 );
		update_site_option( 'mo2f_session_logout_time_enable', 0 );
	}

	function mo2fa_load_textdomain() {
		load_plugin_textdomain( '2fa-learndash-lms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	function mo2f_user_session_expiry() {
		$expiry_hours = get_site_option( 'mo2f_number_of_timeout_hours' );
		if ( $expiry_hours < 4 ) {
			$expiry_time = 14 * 24 * 60 * 60;

			return $expiry_time;
		}
		if ( $expiry_hours ) {
			$expiry_time = $expiry_hours * 60 * 60;
		} else {
			$expiry_time = 14 * 24 * 60 * 60;
		}

		return $expiry_time;
	}


	function mo2f_plugin_session_addon( $identity, $currentuser ) {
		$sessions_allowed = get_site_option( 'mo2f_simultaneous_session_allowed', 10 );
		$session_details  = WP_Session_Tokens::get_instance( $currentuser->ID );
		$session_count    = count( $session_details->get_all() );
		if ( $session_count >= $sessions_allowed ) {
			$session_action = get_site_option( 'mo2f_simultaneous_session_give_entry' );
			if ( $session_action ) {
				$previous_session_details = WP_Session_Tokens::get_instance( $currentuser->ID );
				$previous_session_details->destroy_all();

				return 'allow';
			} else {
				return 'block';
			}
		}
	}

	function mo2f_idle_session_script() {
		$idle_session = get_site_option( "mo2f_number_of_idle_hours", 10 );
		if ( $idle_session < 4 ) {
			return 24;
		}
		if ( is_user_logged_in() ) {
			if ( get_site_option( 'mo2f_idle_session_logout_enable' ) ) {
				echo '<script> var duration=' . esc_js( json_encode( $idle_session ) ) . '</script>';
				echo '<script> var nonce= "' . esc_js( wp_create_nonce( "molms-nonce" ) ) . '";</script>';
				wp_enqueue_script( 'idle_session_timeout_script', plugins_url( 'includes/js/mo_idle_session_logout.js', dirname( __FILE__ ) ), array(), MOLMS_VERSION, false );
			}
		}
	}

	public function miniorange_idle_session_logout_init() {
		if ( isset( $_POST['idle_session_timeout'] ) && isset( $_POST['update_time'] ) && sanitize_text_field( $_POST['idle_session_timeout'] ) == 'timeout' && sanitize_text_field( $_POST['update_time'] ) == 'now' ) {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'molms-nonce' ) ) {
				return;
			}
			wp_destroy_current_session();
			exit;
		}
	}
}

new Molms_Session;
